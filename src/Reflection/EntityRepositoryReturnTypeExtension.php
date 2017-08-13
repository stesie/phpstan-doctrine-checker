<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Reflection;

use PHPStanDoctrineChecker\Type\QueryBuilderObjectType;
use Doctrine\ORM\EntityRepository;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;

class EntityRepositoryReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public static function getClass(): string
    {
        return EntityRepository::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'createQueryBuilder';
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
    {
        return new QueryBuilderObjectType();
    }
}
