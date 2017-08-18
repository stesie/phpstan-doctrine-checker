<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Integration;

use Doctrine\ORM\Query;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use PHPStanDoctrineChecker\QueryBuilderInfo;
use PHPStanDoctrineChecker\Service\QueryBuilderTracer;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar\String_;

class ExpressionBuilderCheckerTest extends IntegrationTestCase
{
    public function testExprEq()
    {
        $queryBuilderInfo = new QueryBuilderInfo('u');

        $this->runAndWhereWithExpressionBuilder($queryBuilderInfo, 'eq', [
            new Arg(new String_('p.type')),
            new Arg(new String_(':type')),
        ]);

        $this->assertEquals(['p'], $queryBuilderInfo->getDirtyAliases());
    }

    public function testExprEqWithoutFieldName()
    {
        $queryBuilderInfo = new QueryBuilderInfo('u');

        $this->runAndWhereWithExpressionBuilder($queryBuilderInfo, 'eq', [
            new Arg(new String_('x')),
            new Arg(new String_(':phoneNumber')),
        ]);

        $this->assertEquals(['x'], $queryBuilderInfo->getDirtyAliases());
    }

    public function testExprLteViolation()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/ExprLteViolationTest.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join', 13, $errors);
    }

    public function testExprOrXViolation()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/ExprOrXViolationTest.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join', 13, $errors);
    }

    public function testExprIsNullViolation()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/ExprIsNullViolationTest.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join', 13, $errors);
    }

    public function testExprIn()
    {
        $queryBuilderInfo = new QueryBuilderInfo('u');

        $this->runAndWhereWithExpressionBuilder($queryBuilderInfo, 'in', [
            new Arg(new String_('p.type')),
            new Arg(new Expr\Array_([new String_(':type')])),
        ]);

        $this->assertEquals(['p'], $queryBuilderInfo->getDirtyAliases());
    }

    public function testExprInWithoutFieldName()
    {
        $queryBuilderInfo = new QueryBuilderInfo('u');

        $this->runAndWhereWithExpressionBuilder($queryBuilderInfo, 'in', [
            new Arg(new String_('p')),
            new Arg(new Expr\Array_([new String_(':phoneNumber1')])),
        ]);

        $this->assertEquals(['p'], $queryBuilderInfo->getDirtyAliases());
    }

    /**
     * @param QueryBuilderInfo $queryBuilderInfo
     * @param string $exprMethodName
     * @param Arg[] $exprMethodArgs
     */
    protected function runAndWhereWithExpressionBuilder(
        QueryBuilderInfo $queryBuilderInfo,
        string $exprMethodName,
        array $exprMethodArgs
    )
    {
        $basePtr = new Expr\MethodCall(
            new Expr\Variable('queryBuilder'),
            'expr'
        );

        $methodCall = new Expr\MethodCall(
            new Expr\Variable('queryBuilder'),
            'andWhere',
            [
                new Arg(
                    new Expr\MethodCall(
                        $basePtr,
                        $exprMethodName,
                        $exprMethodArgs
                    )
                ),
            ]
        );

        $scope = $this->getMockBuilder(Scope::class)
            ->disableOriginalConstructor()
            ->getMock();

        $scope
            ->method('getType')
            ->with($this->equalTo($basePtr))
            ->willReturn(new ObjectType(Query\Expr::class));

        (new QueryBuilderTracer())->processNode($queryBuilderInfo, $methodCall, $scope);
    }
}
