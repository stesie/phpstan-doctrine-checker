<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Reflection;

use Doctrine\ORM\Query;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\StringType;
use PHPStanDoctrineChecker\QueryBuilderInfo;
use PHPStanDoctrineChecker\Service\QueryExprTracer;
use PHPStanDoctrineChecker\Type\QueryBuilderObjectType;
use PHPStanDoctrineChecker\Type\QueryBuilderStringType;
use PHPUnit\Framework\TestCase;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar\String_;

class QueryExprReturnTypeExtensionTest extends TestCase
{
    public function testIsNull()
    {
        $methodReflection = $this->getMockBuilder(MethodReflection::class)->getMock();
        $methodReflection->method('getReturnType')->willReturn(new StringType());

        $basePtr = new Expr\MethodCall(new Expr\Variable('queryBuilder'), 'expr');
        $methodCall = new Expr\MethodCall($basePtr, 'isNull', [new Arg(new String_('p.type'))]);

        $queryBuilderInfo = new QueryBuilderInfo();

        $scope = $this->getMockBuilder(Scope::class)
            ->disableOriginalConstructor()
            ->getMock();

        $scope
            ->method('getType')
            ->with($this->equalTo($basePtr))
            ->willReturn(new QueryBuilderObjectType(Query\Expr::class, $queryBuilderInfo));

        $expressionTracer = $this->getMockBuilder(QueryExprTracer::class)->getMock();
        $expressionTracer->expects($this->once())->method('processExprMethodCall');

        /** @var QueryBuilderStringType $returnType */
        $returnType = (new QueryExprReturnTypeExtension($expressionTracer))
            ->getTypeFromMethodCall($methodReflection, $methodCall, $scope);

        $this->assertInstanceOf(QueryBuilderStringType::class, $returnType);
        $this->assertSame($queryBuilderInfo, $returnType->getQueryBuilderInfo());
    }
}
