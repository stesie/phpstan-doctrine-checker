<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Rules;

use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStanDoctrineChecker\Type\QueryBuilderObjectType;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;

class QueryBuilderListener implements Rule
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

        $calleeType = $scope->getType($node->var);

        if (!$calleeType instanceof QueryBuilderObjectType) {
            return [];
        }

        switch ($node->name) {
            case 'select':
                $calleeType->getQueryBuilderInfo()->resetSelect();

                foreach ($node->args as $arg) {
                    if (!$arg->value instanceof String_) {
                        throw new \LogicException('not yet implemented');
                    }

                    $calleeType->getQueryBuilderInfo()->addSelect($arg->value->value);
                }
                break;

            case 'where':
                $calleeType->getQueryBuilderInfo()->addDirtyAlias('p');
                break;

            default:
                echo 'unhandled call: ' . $node->name . PHP_EOL;
            case 'setParameter':
            case 'setParameters':
        }

        return [];
    }
}
