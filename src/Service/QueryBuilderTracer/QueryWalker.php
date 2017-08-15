<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Service\QueryBuilderTracer;

use Doctrine\ORM\Query\AST;
use PHPStanDoctrineChecker\QueryBuilderInfo;

class QueryWalker
{
    /**
     * @var QueryBuilderInfo
     */
    private $queryBuilderInfo;

    /**
     * @param QueryBuilderInfo $queryBuilderInfo
     */
    public function __construct(QueryBuilderInfo $queryBuilderInfo)
    {
        $this->queryBuilderInfo = $queryBuilderInfo;
    }

    public function walk(AST\Node $node)
    {
        if ($node instanceof AST\ArithmeticExpression) {
            $this->walkArithmeticExpression($node);
            return;
        }

        if ($node instanceof AST\ComparisonExpression) {
            $this->walkComparisonExpression($node);
            return;
        }

        if ($node instanceof AST\ConditionalPrimary) {
            $this->walkConditionalPrimary($node);
            return;
        }

        if ($node instanceof AST\InputParameter) {
            /* parameters don't taint anything -> ignore */
            return;
        }

        if ($node instanceof AST\Literal) {
            /* literals don't taint anything -> ignore */
            return;
        }

        if ($node instanceof AST\PathExpression) {
            $this->queryBuilderInfo->addDirtyAlias($node->identificationVariable);
            return;
        }

        throw new \LogicException();
    }

    private function walkConditionalPrimary(AST\ConditionalPrimary $conditionalPrimary)
    {
        if ($conditionalPrimary->isSimpleConditionalExpression()) {
            if ($conditionalPrimary->simpleConditionalExpression === null) {
                throw new \LogicException();
            }

            $this->walk($conditionalPrimary->simpleConditionalExpression);
        } else {
            if ($conditionalPrimary->conditionalExpression === null) {
                throw new \LogicException();
            }

            $this->walk($conditionalPrimary->conditionalExpression);
        }
    }

    private function walkComparisonExpression(AST\ComparisonExpression $expr)
    {
        $this->walk($expr->leftExpression);
        $this->walk($expr->rightExpression);
    }

    private function walkArithmeticExpression(AST\ArithmeticExpression $expr)
    {
        if ($expr->isSimpleArithmeticExpression()) {
            if ($expr->simpleArithmeticExpression === null) {
                throw new \LogicException();
            }

            $this->walk($expr->simpleArithmeticExpression);
        } else {
            if ($expr->subselect === null) {
                throw new \LogicException();
            }

            $this->walk($expr->subselect);
        }
    }
}
