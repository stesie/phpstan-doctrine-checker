<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Rules;

use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStanDoctrineChecker\Type\QueryBuilderObjectType;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;

class FetchJoinCheckerRule implements Rule
{
    /**
     * @return string Class implementing \PhpParser\Node
     */
    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param \PhpParser\Node $node
     * @param \PHPStan\Analyser\Scope $scope
     * @return string[] errors
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof MethodCall) {
            throw new \LogicException();
        }

        if (!\in_array($node->name, ['getResult', 'getSingleResult'])) {
            return [];
        }

        $calleeType = $scope->getType($node->var);

        if (!$calleeType instanceof QueryBuilderObjectType) {
            return [];
        }

        $errors = [];

        $conflictingFetches = $calleeType->getQueryBuilderInfo()->getConflictingFetches();

        if (!empty($conflictingFetches)) {
            $errors[] = 'DQL Query uses invalid filtered fetch-join on ' . \implode(',', $conflictingFetches);
        }

        if ($calleeType->getQueryBuilderInfo()->isRangeFiltered()) {
            $errors[] = 'DQL Query uses setFirstResult/setMaxResults with fetch-join';
        }

        return $errors;
    }
}
