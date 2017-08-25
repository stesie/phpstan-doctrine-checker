<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Reflection;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStanDoctrineChecker\QueryBuilderInfo;
use PHPStanDoctrineChecker\Service\QueryBuilderTracer;
use PHPStanDoctrineChecker\Type\QueryBuilderObjectType;
use PhpParser\Node\Expr\MethodCall;

class QueryBuilderReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    /**
     * @var QueryBuilderTracer
     */
    private $queryBuilderTracer;

    public function __construct(QueryBuilderTracer $queryBuilderListener)
    {
        $this->queryBuilderTracer = $queryBuilderListener;
    }

    public static function getClass(): string
    {
        return QueryBuilder::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return true;
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
            $this->queryBuilderTracer->processNode($calleeType->getQueryBuilderInfo(), $methodCall, $scope);

            // pass on fluency
            return $calleeType;
        }

        if ($returnType->getClass() === Query::class) {
            return $calleeType->withClass(Query::class);
        }

        if ($methodCall->name === 'expr') {
            return new QueryBuilderObjectType(Query\Expr::class, new QueryBuilderInfo());
        }

        // whatever ...
        return $returnType;
    }
}
