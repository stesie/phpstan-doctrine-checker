<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Type;

use PHPStanDoctrineChecker\QueryBuilderInfo;
use Doctrine\ORM\Query;
use PHPStan\Type\ObjectType;

class QueryObjectType extends ObjectType
{
    /**
     * @var QueryBuilderInfo
     */
    private $queryBuilderInfo;

    public function __construct(QueryBuilderInfo $queryBuilderInfo)
    {
        parent::__construct(Query::class);
        $this->queryBuilderInfo = $queryBuilderInfo;
    }

    /**
     * @return QueryBuilderInfo
     */
    public function getQueryBuilderInfo(): QueryBuilderInfo
    {
        return $this->queryBuilderInfo;
    }
}
