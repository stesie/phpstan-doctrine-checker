<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker;

class QueryBuilderInfo
{
    /**
     * @var string[]
     */
    private $selects = [];

    /**
     * @var string[]
     */
    private $dirty = [];

    public function resetSelect()
    {
        $this->selects = [];
    }

    public function addSelect(string $alias)
    {
        $this->selects[] = $alias;
    }

    public function addDirtyAlias(string $alias)
    {
        $this->dirty[] = $alias;
    }

    /**
     * @return string[]
     */
    public function getConflictingFetches(): array
    {
        return array_intersect($this->selects, $this->dirty);
    }
}
