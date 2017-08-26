<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Service;

use Doctrine\ORM\Query;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use PHPStanDoctrineChecker\QueryBuilderInfo;
use PHPUnit\Framework\TestCase;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar\String_;

class QueryExprTracerExpressionBuilderTest extends TestCase
{
    /**
     * @dataProvider comparisonFunctionNameProvider
     * @param string $functionName
     */
    public function testExprComparison(string $functionName)
    {
        $queryBuilderInfo = new QueryBuilderInfo('u');

        $this->callExprMethod($queryBuilderInfo, $functionName, [
            new Arg(new String_('p.type')),
            new Arg(new String_(':type')),
        ]);

        $this->assertEquals(['p'], $queryBuilderInfo->getDirtyAliases());
    }

    /**
     * @return string[][]
     */
    public function comparisonFunctionNameProvider(): array
    {
        return [
            ['eq'],
            ['neq'],
            ['lt'],
            ['lte'],
            ['gt'],
            ['gte'],
            ['like'],
            ['notLike'],
        ];
    }

    public function testExprEqWithoutFieldName()
    {
        $queryBuilderInfo = new QueryBuilderInfo('u');

        $this->callExprMethod($queryBuilderInfo, 'eq', [
            new Arg(new String_('x')),
            new Arg(new String_(':phoneNumber')),
        ]);

        $this->assertEquals(['x'], $queryBuilderInfo->getDirtyAliases());
    }

    public function testExprAndX()
    {
        $queryBuilderInfo = new QueryBuilderInfo('u');

        $this->callExprMethod($queryBuilderInfo, 'andX', [
            new Arg(new String_('xyz.type = :type')),
        ]);

        $this->assertEquals(['xyz'], $queryBuilderInfo->getDirtyAliases());
    }

    public function testExprOrX()
    {
        $queryBuilderInfo = new QueryBuilderInfo('u');

        $this->callExprMethod($queryBuilderInfo, 'orX', [
            new Arg(new String_('xyz.type = :type')),
        ]);

        $this->assertEquals(['xyz'], $queryBuilderInfo->getDirtyAliases());
    }

    /**
     * @dataProvider logicalFunctionNameProvider
     * @param string $functionName
     */
    public function testExprLogicalFunction(string $functionName)
    {
        $queryBuilderInfo = new QueryBuilderInfo('u');

        $this->callExprMethod($queryBuilderInfo, $functionName, [
            new Arg(new String_('info.someFlag')),
        ]);

        $this->assertEquals(['info'], $queryBuilderInfo->getDirtyAliases());
    }

    /**
     * @return string[][]
     */
    public function logicalFunctionNameProvider(): array
    {
        return [
            ['not'],
            ['isNull'],
            ['isNotNull'],
        ];
    }

    public function testExprIsNullWithoutFieldName()
    {
        $queryBuilderInfo = new QueryBuilderInfo('u');

        $this->callExprMethod($queryBuilderInfo, 'isNull', [
            new Arg(new String_('info')),
        ]);

        $this->assertEquals(['info'], $queryBuilderInfo->getDirtyAliases());
    }

    public function testExprIn()
    {
        $queryBuilderInfo = new QueryBuilderInfo('u');

        $this->callExprMethod($queryBuilderInfo, 'in', [
            new Arg(new String_('p.type')),
            new Arg(new Expr\Array_([new Expr\ArrayItem(new String_(':type'))])),
        ]);

        $this->assertEquals(['p'], $queryBuilderInfo->getDirtyAliases());
    }

    public function testExprInWithoutFieldName()
    {
        $queryBuilderInfo = new QueryBuilderInfo('u');

        $this->callExprMethod($queryBuilderInfo, 'in', [
            new Arg(new String_('p')),
            new Arg(new Expr\Array_([new Expr\ArrayItem(new String_(':phoneNumber'))])),
        ]);

        $this->assertEquals(['p'], $queryBuilderInfo->getDirtyAliases());
    }

    public function testExprNotIn()
    {
        $queryBuilderInfo = new QueryBuilderInfo('u');

        $this->callExprMethod($queryBuilderInfo, 'notIn', [
            new Arg(new String_('p.type')),
            new Arg(new Expr\Array_([new Expr\ArrayItem(new String_(':type'))])),
        ]);

        $this->assertEquals(['p'], $queryBuilderInfo->getDirtyAliases());
    }

    /**
     * @dataProvider singleArgMathOrAggregationFunctionNameProvider
     * @param string $functionName
     */
    public function testExprSingleArgMath(string $functionName)
    {
        $queryBuilderInfo = new QueryBuilderInfo('u');

        $this->callExprMethod($queryBuilderInfo, $functionName, [
                new Arg(new String_('xyz.value')),
        ]);

        $this->assertEquals(['xyz'], $queryBuilderInfo->getDirtyAliases());
    }

    /**
     * @return string[][]
     */
    public function singleArgMathOrAggregationFunctionNameProvider(): array
    {
        return [
            ['avg'],
            ['min'],
            ['max'],
            ['count'],
            ['countDistinct'],
            ['abs'],
            ['sqrt'],
        ];
    }

    /**
     * @dataProvider biArgMathFunctionNameProvider
     * @param string $functionName
     */
    public function testExprBiArgMath(string $functionName)
    {
        $queryBuilderInfo = new QueryBuilderInfo('u');

        $this->callExprMethod($queryBuilderInfo, $functionName, [
            new Arg(new String_('xyz.valueA')),
            new Arg(new String_('xyz.valueB')),
        ]);

        $this->assertEquals(['xyz'], $queryBuilderInfo->getDirtyAliases());
    }

    /**
     * @return string[][]
     */
    public function biArgMathFunctionNameProvider(): array
    {
        return [
            ['prod'],
            ['diff'],
            ['sum'],
            ['quot'],
        ];
    }


    /**
     * @param QueryBuilderInfo $queryBuilderInfo
     * @param string $exprMethodName
     * @param Arg[] $exprMethodArgs
     */
    protected function callExprMethod(
        QueryBuilderInfo $queryBuilderInfo,
        string $exprMethodName,
        array $exprMethodArgs
    )
    {
        $basePtr = new Expr\MethodCall(new Expr\Variable('queryBuilder'), 'expr');
        $methodCall = new Expr\MethodCall($basePtr, $exprMethodName, $exprMethodArgs);

        $scope = $this->getMockBuilder(Scope::class)
            ->disableOriginalConstructor()
            ->getMock();

        $scope
            ->method('getType')
            ->with($this->equalTo($basePtr))
            ->willReturn(new ObjectType(Query\Expr::class));

        (new QueryExprTracer())->processExprMethodCall($queryBuilderInfo, $methodCall, $scope);
    }
}
