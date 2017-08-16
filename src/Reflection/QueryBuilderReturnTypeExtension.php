<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Reflection;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStanDoctrineChecker\Service\QueryBuilderTracer;
use PHPStanDoctrineChecker\Type\QueryBuilderObjectType;
use PHPStanDoctrineChecker\Type\QueryObjectType;
use PhpParser\Node\Expr\MethodCall;

class QueryBuilderReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    /**
     * @var QueryBuilderTracer
     */
    private $queryBuilderListener;

    public function __construct(QueryBuilderTracer $queryBuilderListener)
    {
        $this->queryBuilderListener = $queryBuilderListener;
    }

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
            // tell node processor
            $this->queryBuilderListener->processNode($calleeType->getQueryBuilderInfo(), $methodCall, $scope);

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
