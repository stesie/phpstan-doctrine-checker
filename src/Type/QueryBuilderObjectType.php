<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Type;

use Doctrine\ORM\QueryBuilder;
use PHPStan\Type\ObjectType;
use PHPStanDoctrineChecker\QueryBuilderInfo;

class QueryBuilderObjectType extends ObjectType
{
    /**
     * @var QueryBuilderInfo
     */
    private $queryBuilderInfo;

    /**
     * @param string $class
     * @param QueryBuilderInfo $queryBuilderInfo
     */
    public function __construct(string $class, QueryBuilderInfo $queryBuilderInfo)
    {
        parent::__construct($class);

        $this->queryBuilderInfo = $queryBuilderInfo;
    }

    /**
     * @param string $alias
     * @return QueryBuilderObjectType
     */
    public static function create(string $alias): self
    {
        return new static(QueryBuilder::class, new QueryBuilderInfo($alias));
    }

    /**
     * @return QueryBuilderInfo
     */
    public function getQueryBuilderInfo(): QueryBuilderInfo
    {
        return $this->queryBuilderInfo;
    }

    /**
     * @param string $class
     * @return QueryBuilderObjectType
     */
    public function withClass(string $class): self
    {
        return new static($class, $this->getQueryBuilderInfo());
    }
}
