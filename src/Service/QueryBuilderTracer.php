<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Service;

use PHPStan\Analyser\Scope;
use PHPStanDoctrineChecker\QueryBuilderInfo;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar;

class QueryBuilderTracer
{
    /**
     * @var QueryExprTracer
     */
    private $queryExprTracer;

    public function __construct(QueryExprTracer $queryExprTracer)
    {
        $this->queryExprTracer = $queryExprTracer;
    }

    public function processNode(QueryBuilderInfo $queryBuilderInfo, MethodCall $node, Scope $scope)
    {
        switch ($node->name) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'select':
                $queryBuilderInfo->resetSelect();
                /* fall through */
            case 'addSelect':
                if (\count($node->args) >= 1 && $node->args[0]->value instanceof Expr\Array_) {
                    $args = $node->args[0]->value->items;
                } else {
                    $args = $node->args;
                }

                foreach ($args as $arg) {
                    if (!$arg->value instanceof Scalar\String_) {
                        throw new \LogicException('not yet implemented');
                    }

                    $queryBuilderInfo->addSelect($arg->value->value);
                }
                break;

            case 'join':
            case 'innerJoin':
            case 'leftJoin':
                if (\count($node->args) >= 4) {
                    $this->queryExprTracer->processWherePart($node->args[3]->value, $queryBuilderInfo, $scope);
                }
                break;

            /** @noinspection PhpMissingBreakStatementInspection */
            case 'where':
                $queryBuilderInfo->resetWhere();
                /* fall through */
            case 'andWhere':
            case 'orWhere':
                $this->queryExprTracer->processWherePart($node->args[0]->value, $queryBuilderInfo, $scope);
                break;

            case 'setFirstResult':
            case 'setMaxResults':
                $queryBuilderInfo->setIsRangeFiltered(true);
                /* those unconditionally limit the result set, i.e. always problematic */
                break;

            case 'setParameter':
            case 'setParameters':
            case 'distinct':
                /* do nothing, those neither select nor filter data */
                break;

            case 'expr':
                /* ignore here, creates new trace and possibly merges via other call */
                break;

            case 'getQuery':
                /* everything should be traced already, nothing left to do :-) */
                break;

            default:
                echo 'processNode ignored call for: ' . $node->name . \PHP_EOL;
        }
    }
}
