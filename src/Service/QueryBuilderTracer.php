<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Service;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Parser;
use PHPStanDoctrineChecker\QueryBuilderInfo;
use PHPStanDoctrineChecker\Service\QueryBuilderTracer\DummyEntityManager;
use PHPStanDoctrineChecker\Service\QueryBuilderTracer\QueryWalker;
use PHPStanDoctrineChecker\Type\QueryBuilderObjectType;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;

class QueryBuilderTracer
{
    public function processNode(QueryBuilderObjectType $calleeType, MethodCall $node)
    {
        $queryBuilderInfo = $calleeType->getQueryBuilderInfo();

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
                    $this->processWherePart($node->args[3]->value, $queryBuilderInfo);
                }
                break;

            /** @noinspection PhpMissingBreakStatementInspection */
            case 'where':
                $queryBuilderInfo->resetWhere();
                /* fall through */
            case 'andWhere':
            case 'orWhere':
                $this->processWherePart($node->args[0]->value, $queryBuilderInfo);
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
     */
    private function processWherePart(Expr $whereArg, QueryBuilderInfo $queryBuilderInfo)
    {
        if (!$whereArg instanceof String_) {
            throw new \LogicException('not yet implemented');
        }

        $this->processConditionString($whereArg->value, $queryBuilderInfo);
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
}
