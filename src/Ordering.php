<?php

namespace Leadout\Analytics;

class Ordering
{
    /**
     * The column to sort by.
     *
     * @var string
     */
    private $column;

    /**
     * The direction of the sorting.
     *
     * @var string
     */
    private $direction;

    /**
     * Instantiate the class and set the properties.
     *
     * @param string $column    the column to filter on.
     * @param string $direction the direction of the sorting.
     */
    public function __construct($column, $direction)
    {
        $this->column = $column;

        $this->direction = $direction;
    }

    /**
     * Make a new filter.
     *
     * @param string $column    the column to filter on.
     * @param string $direction the direction of the sorting.
     * @return Ordering the ordering.
     */
    public static function make($column, $direction)
    {
        return new Ordering($column, $direction);
    }

    /**
     * Get the column to filter on.
     *
     * @return string the column.
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * Get the direction of the sorting.
     *
     * @return string the direction.
     */
    public function getDirection()
    {
        return $this->direction;
    }
}
