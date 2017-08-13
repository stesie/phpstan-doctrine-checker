<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Rules;

use PHPStanDoctrineChecker\Type\QueryObjectType;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\FunctionCallParametersCheck;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleLevelHelper;

class FetchJoinCheckerRule implements Rule
{
    /**
     * @var Broker
     */
    private $broker;

    /**
     * @var FunctionCallParametersCheck
     */
    private $check;

    /**
     * @var RuleLevelHelper
     */
    private $ruleLevelHelper;

    public function __construct(
        Broker $broker,
        FunctionCallParametersCheck $check,
        RuleLevelHelper $ruleLevelHelper
    )
    {
        $this->broker = $broker;
        $this->check = $check;
        $this->ruleLevelHelper = $ruleLevelHelper;
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

        if (!$calleeType instanceof QueryObjectType) {
            return [];
        }

        echo "fluency: {$node->name} \n";

        if ($node->name !== 'getSingleResult') {
            return [];
        }

        $conflicts = $calleeType->getQueryBuilderInfo()->getConflictingFetches();

        if (empty($conflicts)) {
            return [];
        }

        return [
            'DQL Query uses invalid filtered fetch-join'
        ];
    }
}
