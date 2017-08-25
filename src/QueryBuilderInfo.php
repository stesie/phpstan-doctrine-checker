<?php declare(strict_types = 1);

namespace PHPStanDoctrineChecker;

class QueryBuilderInfo
{
    /**
     * @var string[]
     */
    private $rootAliases = [];

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

    public function __construct(string $alias = null)
    {
        if ($alias !== null) {
            $this->rootAliases[] = $alias;
        }
    }

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
        if (!in_array($alias, $this->dirty)) {
            $this->dirty[] = $alias;
        }
    }

    /**
     * @return string[]
     */
    public function getDirtyAliases(): array
    {
        return $this->dirty;
    }

    /**
     * @return string[]
     */
    public function getConflictingFetches(): array
    {
        return array_intersect(array_diff($this->selects, $this->rootAliases), $this->dirty);
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

    public function merge(QueryBuilderInfo $queryBuilderInfo)
    {
        $this->rootAliases = array_unique(array_merge($queryBuilderInfo->rootAliases, $this->rootAliases));
        $this->selects = array_unique(array_merge($queryBuilderInfo->selects, $this->selects));
        $this->dirty = array_unique(array_merge($queryBuilderInfo->dirty, $this->dirty));
    }
}
