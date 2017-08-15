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

    /**
     * @var bool
     */
    private $isRangeFiltered = false;

    public function resetSelect()
    {
        $this->selects = [];
    }

    public function addSelect(string $alias)
    {
        $this->selects[] = $alias;
    }


    public function resetWhere()
    {
        $this->dirty = [];
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

    /**
     * @return bool
     */
    public function isRangeFiltered(): bool
    {
        return $this->isRangeFiltered;
    }

    /**
     * @param bool $isRangeFiltered
     */
    public function setIsRangeFiltered(bool $isRangeFiltered)
    {
        $this->isRangeFiltered = $isRangeFiltered;
    }
}
