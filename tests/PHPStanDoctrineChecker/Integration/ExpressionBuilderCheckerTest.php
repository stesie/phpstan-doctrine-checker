<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Integration;

use Doctrine\ORM\QueryBuilder;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Broker\Broker;
use PHPStan\Type\ObjectType;
use PHPStanDoctrineChecker\QueryBuilderInfo;
use PHPStanDoctrineChecker\Service\QueryBuilderTracer;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar\String_;
use PhpParser\PrettyPrinter\Standard;

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

    public function testExprInViolation()
    {
        $errors = $this->runAnalyse(__DIR__ . '/data/ExprInViolationTest.php');
        $this->assertSingleError('DQL Query uses invalid filtered fetch-join', 13, $errors);
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
        $methodCall = new Expr\MethodCall(
            new Expr\Variable('queryBuilder'),
            'andWhere',
            [
                new Arg(
                    new Expr\MethodCall(
                        new Expr\MethodCall(
                            new Expr\Variable('queryBuilder'),
                            'expr'
                        ),
                        $exprMethodName,
                        $exprMethodArgs
                    )
                ),
            ]
        );

        $prettyPrinter = new Standard();
        $scope = new Scope(
            $this->getContainer()->getByType(Broker::class),
            $prettyPrinter,
            new TypeSpecifier($prettyPrinter),
            'some_file_name',
            null,
            false,
            null,
            null,
            null,
            [
                'queryBuilder' => new ObjectType(QueryBuilder::class),
            ]
        );

        (new QueryBuilderTracer())->processNode($queryBuilderInfo, $methodCall, $scope);
    }
}
