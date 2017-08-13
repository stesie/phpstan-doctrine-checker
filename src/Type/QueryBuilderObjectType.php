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

    public function __construct()
    {
        parent::__construct(QueryBuilder::class);

        $this->queryBuilderInfo = new QueryBuilderInfo();
    }

    /**
     * @return QueryBuilderInfo
     */
    public function getQueryBuilderInfo(): QueryBuilderInfo
    {
        return $this->queryBuilderInfo;
    }
}
