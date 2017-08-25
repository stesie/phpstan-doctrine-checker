<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Reflection;

use Doctrine\ORM\Query;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStanDoctrineChecker\Service\QueryExprTracer;
use PHPStanDoctrineChecker\Type\QueryBuilderObjectType;
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

        if (!$returnType instanceof ObjectType) {
            throw new \LogicException('return type of Expr::class not ObjectType');
        }

        $this->queryExprTracer->processExprMethodCall($calleeType->getQueryBuilderInfo(), $methodCall, $scope);

        return $calleeType->withClass($returnType->getClass());
    }
}
