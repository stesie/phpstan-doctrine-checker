<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Reflection;

use Doctrine\ORM\Query;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStanDoctrineChecker\Type\QueryBuilderObjectType;
use PHPStanDoctrineChecker\Type\QueryBuilderStringType;
use PhpParser\Node\Expr\MethodCall;

class QueryExprComparisonReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public static function getClass(): string
    {
        return Query\Expr\Comparison::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === '__toString';
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
    {
        $calleeType = $scope->getType($methodCall->var);

        if (!$calleeType instanceof QueryBuilderObjectType) {
            // eeeh?
            return new StringType();
        }

        return new QueryBuilderStringType($calleeType->getQueryBuilderInfo());
    }
}
