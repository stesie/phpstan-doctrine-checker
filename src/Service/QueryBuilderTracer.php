<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Service;

use PHPStanDoctrineChecker\Type\QueryBuilderObjectType;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;

class QueryBuilderTracer
{
    public function processNode(QueryBuilderObjectType $calleeType, MethodCall $node)
    {
        switch ($node->name) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'select':
                $calleeType->getQueryBuilderInfo()->resetSelect();
                /* fall through */
            case 'addSelect':
                foreach ($node->args as $arg) {
                    if (!$arg->value instanceof String_) {
                        throw new \LogicException('not yet implemented');
                    }

                    $calleeType->getQueryBuilderInfo()->addSelect($arg->value->value);
                }
                break;

            /** @noinspection PhpMissingBreakStatementInspection */
            case 'where':
                $calleeType->getQueryBuilderInfo()->resetWhere();
                /* fall through */
            case 'andWhere':
            case 'orWhere':
                $calleeType->getQueryBuilderInfo()->addDirtyAlias('p');
                break;

            case 'setFirstResult':
            case 'setMaxResults':
                $calleeType->getQueryBuilderInfo()->setIsRangeFiltered(true);
                /* those unconditionally limit the result set, i.e. always problematic */
                break;

            case 'setParameter':
            case 'setParameters':
            case 'distinct':
                /* do nothing, those neither select nor filter data */
                break;
        }
    }
}
