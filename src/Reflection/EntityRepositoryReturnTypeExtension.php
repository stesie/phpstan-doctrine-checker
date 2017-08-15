<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Reflection;

use Doctrine\ORM\EntityRepository;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use PHPStanDoctrineChecker\Type\QueryBuilderObjectType;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;

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
        $aliasArg = $methodCall->args[0]->value;

        if (!$aliasArg instanceof String_) {
            throw new \LogicException('not yet implemented');
        }

        return new QueryBuilderObjectType($aliasArg->value);
    }
}
