<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Rules;

use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStanDoctrineChecker\Service\QueryBuilderListener;
use PHPStanDoctrineChecker\Type\QueryBuilderObjectType;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;

class QueryBuilderListenerRule implements Rule
{
    /**
     * @var QueryBuilderListener
     */
    private $queryBuilderListener;

    public function __construct(QueryBuilderListener $queryBuilderListener)
    {
        $this->queryBuilderListener = $queryBuilderListener;
    }

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

        $this->queryBuilderListener->processNode($calleeType, $node);
        return [];
    }
}
