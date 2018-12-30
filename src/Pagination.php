<?php

namespace Herbie;

class Pagination implements \IteratorAggregate, \Countable
{
    /** @var array */
    protected $items;

    /** @var int */
    protected $limit;

    /** @var string */
    protected $name;

    /**
     * @param \IteratorAggregate|array $items
     * @param int $limit
     * @param string $name
     * @throws \Exception
     */
    public function __construct($items, int $limit = 10, string $name = 'page')
    {
        $this->items = [];
        if (is_array($items)) {
            $this->items = $items;
        } elseif ($items instanceof \IteratorAggregate) {
            $this->items = (array)$items->getIterator();
        } else {
            throw new \Exception('The param $items must be an array or an object implementing IteratorAggregate.', 500);
        }
        $this->setLimit($limit);
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        $page = isset($_GET[$this->name]) ? intval($_GET[$this->name]) : 1;
        $calculated = ceil($this->count() / $this->limit);
        if ($page > $calculated) {
            $page = $calculated;
        }
        return $page;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit)
    {
        $limit = (0 == $limit) ? 1000 : intval($limit);
        $this->limit = $limit;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator
    {
        $offset = ($this->getPage() - 1) * $this->limit;
        $items = array_slice($this->items, $offset, $this->limit);
        return new \ArrayIterator($items);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return bool
     */
    public function hasNextPage(): bool
    {
        return ($this->limit * $this->getPage()) < $this->count();
    }

    /**
     * @return int
     */
    public function getNextPage(): int
    {
        return max(2, $this->getPage() + 1);
    }

    /**
     * @return bool
     */
    public function hasPrevPage(): bool
    {
        return 1 < $this->getPage();
    }

    /**
     * @return int
     */
    public function getPrevPage(): int
    {
        return max(1, $this->getPage() - 1);
    }
}