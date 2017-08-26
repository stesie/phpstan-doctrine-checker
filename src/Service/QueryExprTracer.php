<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Service;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Parser;
use PHPStan\Analyser\Scope;
use PHPStanDoctrineChecker\Exceptions\NotImplementedException;
use PHPStanDoctrineChecker\QueryBuilderInfo;
use PHPStanDoctrineChecker\Service\QueryBuilderTracer\DummyEntityManager;
use PHPStanDoctrineChecker\Service\QueryBuilderTracer\QueryWalker;
use PHPStanDoctrineChecker\Type\QueryBuilderInfoOwningType;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar;

class QueryExprTracer
{
    public function processExprMethodCall(QueryBuilderInfo $queryBuilderInfo, MethodCall $methodCall, Scope $scope)
    {
        switch ($methodCall->name) {
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
                $this->processExactNumExpressions(2, $methodCall->args, $queryBuilderInfo, $scope);
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
                $this->processExactNumExpressions(1, $methodCall->args, $queryBuilderInfo, $scope);
                return;

            case 'andX':
            case 'orX':
                foreach ($methodCall->args as $arg) {
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
                $this->processInExpression($methodCall, $queryBuilderInfo, $scope);
                return;

            case 'all':
                $this->processExpression($methodCall->args[0]->value, $queryBuilderInfo, $scope);
                return;
        }

        throw new \LogicException('unhandled Where $qb->expr()->...: ' . $methodCall->name);
    }


    /**
     * @param Expr $whereArg
     * @param QueryBuilderInfo $queryBuilderInfo
     * @param Scope $scope
     */
    public function processWherePart(Expr $whereArg, QueryBuilderInfo $queryBuilderInfo, Scope $scope)
    {
        if ($whereArg instanceof Cast\String_) {
            $whereArg = $whereArg->expr;
        }

        if ($whereArg instanceof Scalar\String_) {
            $this->processConditionString($whereArg->value, $queryBuilderInfo);
            return;
        }

        $this->processExpression($whereArg, $queryBuilderInfo, $scope);
    }



    /**
     * @param int $num
     * @param Arg[] $args
     * @param QueryBuilderInfo $queryBuilderInfo
     * @param Scope $scope
     */
    private function processExactNumExpressions(int $num, array $args, QueryBuilderInfo $queryBuilderInfo, Scope $scope)
    {
        if (\count($args) !== $num) {
            throw new NotImplementedException('Handle Parse Error: wrong number of arguments');
        }

        foreach ($args as $arg) {
            $this->processExpression($arg->value, $queryBuilderInfo, $scope);
        }
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
        if ($whereArg instanceof Scalar\String_) {
            $this->processArithmeticExpression($whereArg->value, $queryBuilderInfo);
            return;
        }

        // possibly extract sub-query from '(' + $subQuery + ')' form
        if ($whereArg instanceof Expr\BinaryOp\Concat &&
            $whereArg->right instanceof Scalar\String_ &&
            $whereArg->left instanceof Expr\BinaryOp\Concat &&
            $whereArg->left->left instanceof Scalar\String_ &&
            \trim($whereArg->left->left->value) === '(' &&
            \trim($whereArg->right->value) === ')'
        ) {
            $whereArg = $whereArg->left->right;
        }

        $whereArgType = $scope->getType($whereArg);

        if ($whereArgType instanceof QueryBuilderInfoOwningType) {
            $queryBuilderInfo->merge($whereArgType->getQueryBuilderInfo());
            return;
        }

        throw new \LogicException('not yet implemented');
    }

    private function processInExpression(MethodCall $methodCall, QueryBuilderInfo $queryBuilderInfo, Scope $scope)
    {
        $this->processExpression($methodCall->args[0]->value, $queryBuilderInfo, $scope);

        if ($methodCall->args[1]->value instanceof Expr\Array_) {
            return;
        }

        $type = $scope->getType($methodCall->args[1]->value);

        if ($type instanceof QueryBuilderInfoOwningType) {
            $queryBuilderInfo->merge($type->getQueryBuilderInfo());
        }
    }
}
