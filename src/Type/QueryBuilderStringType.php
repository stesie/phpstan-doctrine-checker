<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Type;

use PHPStan\Type\StringType;
use PHPStanDoctrineChecker\QueryBuilderInfo;

class QueryBuilderStringType extends StringType
{
    /**
     * @var QueryBuilderInfo
     */
    private $queryBuilderInfo;

    public function __construct(QueryBuilderInfo $queryBuilderInfo)
    {
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
