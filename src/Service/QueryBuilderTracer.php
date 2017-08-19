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

        $this->processExpression($whereArg, $queryBuilderInfo, $scope);
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
     * @param string $conditionStr
     * @param QueryBuilderInfo $queryBuilderInfo
     */
    public function processArithmeticExpression(string $conditionStr, QueryBuilderInfo $queryBuilderInfo)
    {
        $query = new Query(new DummyEntityManager());
        $query->setDQL($conditionStr);

        $parser = new Parser($query);
        $parser->getLexer()->moveNext();

        $walker = new QueryWalker($queryBuilderInfo);
        $walker->walk($parser->ArithmeticExpression());
    }

    /**
     * @param Expr $whereArg
     * @param QueryBuilderInfo $queryBuilderInfo
     * @param Scope $scope
     */
    private function processExpression(Expr $whereArg, QueryBuilderInfo $queryBuilderInfo, Scope $scope)
    {
        if ($whereArg instanceof MethodCall) {
            $thisPtr = $scope->getType($whereArg->var);

            if ($thisPtr instanceof ObjectType && $thisPtr->getClass() === Query\Expr::class) {
                $this->processWhereExpression($whereArg, $queryBuilderInfo, $scope);
                return;
            }
        }

        if ($whereArg instanceof String_) {
            $this->processArithmeticExpression($whereArg->value, $queryBuilderInfo);
            return;
        }

        throw new \LogicException('not yet implemented');
    }

    /**
     * @param int $num
     * @param Arg[] $args
     * @param QueryBuilderInfo $queryBuilderInfo
     * @param Scope $scope
     */
    private function processExactNumExpressions(int $num, array $args, QueryBuilderInfo $queryBuilderInfo, Scope $scope)
    {
        if (count($args) !== $num) {
            throw new NotImplementedException('Handle Parse Error: wrong number of arguments');
        }

        foreach ($args as $arg) {
            $this->processExpression($arg->value, $queryBuilderInfo, $scope);
        }
    }

    private function processWhereExpression(MethodCall $whereArg, QueryBuilderInfo $queryBuilderInfo, Scope $scope)
    {
        switch ($whereArg->name) {
            case 'eq':
            case 'neq':
            case 'lt':
            case 'lte':
            case 'gt':
            case 'gte':
            case 'prod':
            case 'diff':
            case 'sum':
            case 'quot':
            case 'like':
            case 'notLike':
                $this->processExactNumExpressions(2, $whereArg->args, $queryBuilderInfo, $scope);
                return;

            case 'avg':
            case 'min':
            case 'max':
            case 'count':
            case 'countDistinct':
            case 'not':
            case 'isNull':
            case 'isNotNull':
            case 'abs':
            case 'sqrt':
                $this->processExactNumExpressions(1, $whereArg->args, $queryBuilderInfo, $scope);
                return;

            case 'andX':
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

            case 'in':
            case 'notIn':
                $this->processExpression($whereArg->args[0]->value, $queryBuilderInfo, $scope);
                return;
        }

        throw new \LogicException('unhandled Where $qb->expr()->...: ' . $whereArg->name);
    }
}
