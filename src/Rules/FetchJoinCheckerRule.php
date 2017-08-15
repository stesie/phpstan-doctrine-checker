<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Rules;

use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStanDoctrineChecker\Type\QueryObjectType;
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

        if ($node->name !== 'getSingleResult') {
            return [];
        }

        $calleeType = $scope->getType($node->var);

        if (!$calleeType instanceof QueryObjectType) {
            return [];
        }

        $errors = [];

        if (!empty($calleeType->getQueryBuilderInfo()->getConflictingFetches())) {
            $errors[] = 'DQL Query uses invalid filtered fetch-join';
        }

        if ($calleeType->getQueryBuilderInfo()->isRangeFiltered()) {
            $errors[] = 'DQL Query uses setFirstResult/setMaxResults with fetch-join';
        }

        return $errors;
    }
}
