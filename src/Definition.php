<?php

namespace Leadout\Analytics;

use Closure;
use Illuminate\Support\Collection;

class Definition
{
    /**
     * The metrics to get data for.
     *
     * @var Collection
     */
    private $metrics;

    /**
     * The dimensions to break the data down with.
     *
     * @var Collection
     */
    private $dimensions;

    /**
     * The filters to use when getting the data.
     *
     * @var Collection
     */
    private $filters;

    /**
     * The orderings to use when getting the data.
     *
     * @var Collection
     */
    private $orderings;

    /**
     * Instantiate the class and inject the dependencies.
     *
     * @param array|Collection $metrics the metrics to get data for.
     */
    public function __construct($metrics)
    {
        $this->metrics = new Collection($metrics);

        $this->dimensions = new Collection;

        $this->filters = new Collection;

        $this->orderings = new Collection;
    }

    /**
     * Make a new definition.
     *
     * @param array|Collection $metrics the metrics to get data for.
     * @return Definition the definition.
     */
    public static function make($metrics)
    {
        return new Definition($metrics);
    }

    /**
     * Get the metrics to get data for.
     *
     * @return Collection the metrics.
     */
    public function getMetrics()
    {
        return $this->metrics;
    }

    /**
     * Get the dimensions to break the data down with.
     *
     * @return Collection the dimensions.
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * Set the dimensions to break the data down with.
     *
     * @param array|Collection $dimensions the dimensions.
     * @return $this
     */
    public function setDimensions($dimensions)
    {
        $this->dimensions = new Collection($dimensions);

        return $this;
    }

    /**
     * Add a dimension to the definition.
     *
     * @param string $dimension the dimension.
     * @return $this
     */
    public function addDimension($dimension)
    {
        $this->dimensions->push($dimension);

        return $this;
    }

    /**
     * Get the dimensions and metrics in the definition.
     *
     * @return Collection the dimensions and metrics.
     */
    public function getDimensionsAndMetrics()
    {
        return $this->getDimensions()->merge($this->getMetrics());
    }

    /**
     * Get the columns in the definition.
     *
     * @return Collection the columns.
     */
    public function getColumns()
    {
        return $this->getDimensionsAndMetrics()->merge($this->getFilters()->map->getColumn());
    }

    /**
     * Get the filters to use when getting the data.
     *
     * @return Collection the filters.
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Set the filters to use when getting the data.
     *
     * @param array|Collection $filters the filters.
     * @return $this
     */
    public function setFilters($filters)
    {
        $this->filters = collect($filters);

        return $this;
    }

    /**
     * Add a filter to the definition.
     *
     * @param Filter $filter the filter.
     * @return $this
     */
    public function addFilter($filter)
    {
        $this->filters->push($filter);

        return $this;
    }

    /**
     * Get the orderings to use when getting the data.
     *
     * @return Collection the orderings.
     */
    public function getOrderings()
    {
        return $this->orderings;
    }

    /**
     * Set the orderings to use when getting the data.
     *
     * @param array|Collection $orderings the orderings.
     * @return $this
     */
    public function setOrderings($orderings)
    {
        $this->orderings = collect($orderings);

        return $this;
    }

    /**
     * Add a ordering to the definition.
     *
     * @param Ordering $ordering the ordering.
     * @return $this
     */
    public function addOrdering($ordering)
    {
        $this->orderings->push($ordering);

        return $this;
    }

    /**
     * Determine if the definition has any of the given columns.
     *
     * @return bool true if the definition has  any of the columns.
     */
    public function hasColumn()
    {
        return collect(func_get_args())->intersect($this->getColumns())->isNotEmpty();
    }

    /**
     * Conditionally run the given callback on the definition.
     *
     * @param boolean $condition the condition.
     * @param Closure $callback  the callback.
     * @return $this
     */
    public function when($condition, $callback)
    {
        if ($condition) {
            return $callback($this);
        }

        return $this;
    }
}
