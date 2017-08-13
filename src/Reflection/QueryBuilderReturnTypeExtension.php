<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Reflection;

use PHPStanDoctrineChecker\Type\QueryBuilderObjectType;
use PHPStanDoctrineChecker\Type\QueryObjectType;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

class QueryBuilderReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public static function getClass(): string
    {
        return QueryBuilder::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return true; // $methodReflection->getName() === 'select';
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
    {
        $calleeType = $scope->getType($methodCall->var);

        if (!$calleeType instanceof QueryBuilderObjectType) {
            // eeeh?
            return $methodReflection->getReturnType();
        }

        $returnType = $methodReflection->getReturnType();

        if (!$returnType instanceof ObjectType) {
            return $returnType;
        }

        if ($returnType->getClass() === QueryBuilder::class) {
            // pass on fluency
            return $calleeType;
        }

        if ($returnType->getClass() === Query::class) {
            return new QueryObjectType($calleeType->getQueryBuilderInfo());

        }

        // whatever ...
        return $returnType;
    }
}
