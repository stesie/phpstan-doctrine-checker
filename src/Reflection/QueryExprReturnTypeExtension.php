<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Reflection;

use Doctrine\ORM\Query;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStanDoctrineChecker\Service\QueryExprTracer;
use PHPStanDoctrineChecker\Type\QueryBuilderObjectType;
use PHPStanDoctrineChecker\Type\QueryBuilderStringType;
use PhpParser\Node\Expr\MethodCall;

class QueryExprReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    /**
     * @var QueryExprTracer
     */
    private $queryExprTracer;

    public function __construct(QueryExprTracer $queryExprTracer)
    {
        $this->queryExprTracer = $queryExprTracer;
    }

    public static function getClass(): string
    {
        return Query\Expr::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return true;
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
    {
        $calleeType = $scope->getType($methodCall->var);
        $returnType = $methodReflection->getReturnType();

        if (!$calleeType instanceof QueryBuilderObjectType) {
            // eeeh?
            return $returnType;
        }

        if (!$returnType instanceof ObjectType && !$returnType instanceof StringType) {
            throw new \LogicException('return type of Expr::class neither ObjectType nor StringType');
        }

        $this->queryExprTracer->processExprMethodCall($calleeType->getQueryBuilderInfo(), $methodCall, $scope);

        if ($returnType instanceof ObjectType) {
            return $calleeType->withClass($returnType->getClass());
        } elseif ($returnType instanceof StringType) {
            return new QueryBuilderStringType($calleeType->getQueryBuilderInfo());
        } else {
            throw new \LogicException('not reachable');
        }
    }
}
