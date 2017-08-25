<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker\Type;

use PHPStanDoctrineChecker\QueryBuilderInfo;

interface QueryBuilderInfoOwningType
{
    public function getQueryBuilderInfo(): QueryBuilderInfo;
}
