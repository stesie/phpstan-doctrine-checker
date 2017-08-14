<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Service;

use PHPStanDoctrineChecker\Type\QueryBuilderObjectType;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;

class QueryBuilderListener
{
    public function processNode(QueryBuilderObjectType $calleeType, MethodCall $node)
    {
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

            case 'setParameter':
            case 'setParameters':
        }
    }
}
