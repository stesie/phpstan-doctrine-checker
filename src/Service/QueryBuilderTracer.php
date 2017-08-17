<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Service;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Parser;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use PHPStanDoctrineChecker\Exceptions\NotImplementedException;
use PHPStanDoctrineChecker\QueryBuilderInfo;
use PHPStanDoctrineChecker\Service\QueryBuilderTracer\DummyEntityManager;
use PHPStanDoctrineChecker\Service\QueryBuilderTracer\QueryWalker;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;

class QueryBuilderTracer
{
    public function processNode(QueryBuilderInfo $queryBuilderInfo, MethodCall $node, Scope $scope)
    {
        switch ($node->name) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'select':
                $queryBuilderInfo->resetSelect();
                /* fall through */
            case 'addSelect':
                foreach ($node->args as $arg) {
                    if (!$arg->value instanceof String_) {
                        throw new \LogicException('not yet implemented');
                    }

                    $queryBuilderInfo->addSelect($arg->value->value);
                }
                break;

            case 'join':
            case 'innerJoin':
            case 'leftJoin':
                if (count($node->args) >= 4) {
                    $this->processWherePart($node->args[3]->value, $queryBuilderInfo, $scope);
                }
                break;

            /** @noinspection PhpMissingBreakStatementInspection */
            case 'where':
                $queryBuilderInfo->resetWhere();
                /* fall through */
            case 'andWhere':
            case 'orWhere':
                $this->processWherePart($node->args[0]->value, $queryBuilderInfo, $scope);
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
        }
    }

    /**
     * @param Expr $whereArg
     * @param QueryBuilderInfo $queryBuilderInfo
     * @param Scope $scope
     */
    private function processWherePart(Expr $whereArg, QueryBuilderInfo $queryBuilderInfo, Scope $scope)
    {
        if ($whereArg instanceof String_) {
            $this->processConditionString($whereArg->value, $queryBuilderInfo);
            return;
        }

        if ($whereArg instanceof MethodCall) {
            $thisPtr = $scope->getType($whereArg->var);

            if ($thisPtr instanceof ObjectType && $thisPtr->getClass() === Query\Expr::class) {
                $this->processWhereExpression($whereArg, $queryBuilderInfo, $scope);
                return;
            }
        }

        throw new \LogicException('not yet implemented');
    }

    /**
     * @param string $conditionStr
     * @param QueryBuilderInfo $queryBuilderInfo
     */
    public function processConditionString(string $conditionStr, QueryBuilderInfo $queryBuilderInfo)
    {
        $query = new Query(new DummyEntityManager());
        $query->setDQL($conditionStr);

        $parser = new Parser($query);
        $parser->getLexer()->moveNext();

        $walker = new QueryWalker($queryBuilderInfo);
        $walker->walk($parser->ConditionalExpression());
    }

    /**
     * @param Arg[] $args
     * @param QueryBuilderInfo $queryBuilderInfo
     */
    private function processExprCondition(array $args, QueryBuilderInfo $queryBuilderInfo)
    {
        if (count($args) !== 2) {
            throw new NotImplementedException('Handle Parse Error: two args expected');
        }

        $args = array_map(function (Arg $arg): string {
            if (!$arg->value instanceof String_) {
                throw new NotImplementedException('expr()->eq Arguments !== String_ not handled yet');
            }

            return $arg->value->value;
        }, $args);

        $this->processConditionString(implode(' = ', $args), $queryBuilderInfo);
    }


    /**
     * @param Arg[] $args
     * @param QueryBuilderInfo $queryBuilderInfo
     */
    private function processExprIsNull(array $args, QueryBuilderInfo $queryBuilderInfo)
    {
        if (count($args) !== 1) {
            throw new NotImplementedException('Handle Parse Error: only one arg expected');
        }

        $arg = reset($args);

        if (!$arg->value instanceof String_) {
            throw new NotImplementedException('expr()->isNull Arguments !== String_ not handled yet');
        }

        if (!preg_match('/^(\S+)\.\S+$/', $arg->value->value, $matches)) {
            throw new NotImplementedException('Parse error: field spec not understood: ' . $arg->value->value);
        }

        $queryBuilderInfo->addDirtyAlias($matches[1]);
    }

    /**
     * @param Arg[] $args
     * @param QueryBuilderInfo $queryBuilderInfo
     */
    private function processExprIn(array $args, QueryBuilderInfo $queryBuilderInfo)
    {
        if (count($args) !== 2) {
            throw new NotImplementedException('Handle Parse Error: two args expected');
        }

        $arg = reset($args);

        if (!$arg->value instanceof String_) {
            throw new NotImplementedException('expr()->in Argument(0) !== String_ not handled yet');
        }

        if (!preg_match('/^(\S+)\.\S+$/', $arg->value->value, $matches)) {
            throw new NotImplementedException('Parse error: field spec not understood: ' . $arg->value->value);
        }

        $queryBuilderInfo->addDirtyAlias($matches[1]);
    }

    private function processWhereExpression(MethodCall $whereArg, QueryBuilderInfo $queryBuilderInfo, Scope $scope)
    {
        switch ($whereArg->name) {
            case 'eq':
            case 'lte':
                $this->processExprCondition($whereArg->args, $queryBuilderInfo);
                return;

            case 'orX':
                foreach ($whereArg->args as $arg) {
                    if (!$arg instanceof Arg) {
                        throw new \LogicException('$whereArg->args $arg is not of type Arg');
                    }

                    if (!$arg->value instanceof Expr) {
                        throw new \LogicException('$arg->value not Expr');
                    }

                    $this->processWherePart($arg->value, $queryBuilderInfo, $scope);
                }
                return;

            case 'isNull':
                $this->processExprIsNull($whereArg->args, $queryBuilderInfo);
                return;

            case 'in':
                $this->processExprIn($whereArg->args, $queryBuilderInfo);
                return;
        }

        throw new \LogicException('unhandled Where $qb->expr()->...');
    }
}
