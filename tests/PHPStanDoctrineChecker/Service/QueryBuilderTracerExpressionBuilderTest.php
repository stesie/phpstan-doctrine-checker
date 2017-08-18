<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Service;

use Doctrine\ORM\Query;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use PHPStanDoctrineChecker\QueryBuilderInfo;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar\String_;
use PHPUnit\Framework\TestCase;

class QueryBuilderTracerExpressionBuilderTest extends TestCase
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

    public function testExprLte()
    {
        $queryBuilderInfo = new QueryBuilderInfo('u');

        $this->runAndWhereWithExpressionBuilder($queryBuilderInfo, 'lte', [
            new Arg(new String_('info.age')),
            new Arg(new String_(':age')),
        ]);

        $this->assertEquals(['info'], $queryBuilderInfo->getDirtyAliases());
    }

    public function testExprOrX()
    {
        $queryBuilderInfo = new QueryBuilderInfo('u');

        $this->runAndWhereWithExpressionBuilder($queryBuilderInfo, 'orX', [
            new Arg(
                new Expr\MethodCall(
                    new Expr\MethodCall(
                        new Expr\Variable('queryBuilder'),
                        'expr'
                    ),
                    'eq',
                    [
                        new Arg(new String_('xyz.type')),
                        new Arg(new String_(':type')),
                    ]
                )
            ),
        ]);

        $this->assertEquals(['xyz'], $queryBuilderInfo->getDirtyAliases());
    }

    public function testExprIsNull()
    {
        $queryBuilderInfo = new QueryBuilderInfo('u');

        $this->runAndWhereWithExpressionBuilder($queryBuilderInfo, 'isNull', [
            new Arg(new String_('info.age')),
        ]);

        $this->assertEquals(['info'], $queryBuilderInfo->getDirtyAliases());
    }

    public function testExprIsNullWithoutFieldName()
    {
        $queryBuilderInfo = new QueryBuilderInfo('u');

        $this->runAndWhereWithExpressionBuilder($queryBuilderInfo, 'isNull', [
            new Arg(new String_('info')),
        ]);

        $this->assertEquals(['info'], $queryBuilderInfo->getDirtyAliases());
    }

    public function testExprIn()
    {
        $queryBuilderInfo = new QueryBuilderInfo('u');

        $this->runAndWhereWithExpressionBuilder($queryBuilderInfo, 'in', [
            new Arg(new String_('p.type')),
            new Arg(new Expr\Array_([new Expr\ArrayItem(new String_(':type'))])),
        ]);

        $this->assertEquals(['p'], $queryBuilderInfo->getDirtyAliases());
    }

    public function testExprInWithoutFieldName()
    {
        $queryBuilderInfo = new QueryBuilderInfo('u');

        $this->runAndWhereWithExpressionBuilder($queryBuilderInfo, 'in', [
            new Arg(new String_('p')),
            new Arg(new Expr\Array_([new Expr\ArrayItem(new String_(':phoneNumber'))])),
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
