<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Rules;

use PHPStanDoctrineChecker\Type\QueryBuilderObjectType;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\FunctionCallParametersCheck;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleLevelHelper;

class QueryBuilderListener implements Rule
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

        if (!$calleeType instanceof QueryBuilderObjectType) {
            return [];
        }

        echo "fluency: {$node->name} \n";

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

            case 'innerJoin':
                // @todo handle arg 4
                break;

            case 'where':
                $calleeType->getQueryBuilderInfo()->addDirtyAlias('p');
                break;

            /* case 'getQuery':
                $conflicts = $calleeType->getConflictingFetches();

                if (empty($conflicts)) {
                    return [];
                }

                return [
                    'DQL Query uses invalid filtered fetch-join'
                ];
                */
            default:
                echo 'unhandled call: ' . $node->name . PHP_EOL;
            case 'setParameter':
            case 'setParameters':
        }

        return [];
    }
}
