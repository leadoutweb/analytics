<?php

namespace Leadout\Analytics;

class Filter
{
    /**
     * Basic type filter.
     *
     * @var string
     */
    const BASIC = 'basic';

    /**
     * "In" type filter.
     *
     * @var string
     */
    const IN = 'in';

    /**
     * The column to filter on.
     *
     * @var string
     */
    private $column;

    /**
     * The operator to use in the filter.
     *
     * @var string
     */
    private $operator;

    /**
     * The value to use in the filter.
     *
     * @var mixed
     */
    private $value;

    /**
     * Instantiate the class and set the properties.
     *
     * @param string $column   the column to filter on.
     * @param string $operator the operator to use in the filter.
     * @param mixed  $value    the value to use in the filter.
     */
    public function __construct($column, $operator, $value)
    {
        $this->column = $column;

        $this->operator = $operator;

        $this->value = $value;
    }

    /**
     * Make a new filter.
     *
     * @param string $column   the column to filter on.
     * @param string $operator the operator to use in the filter.
     * @param mixed  $value    the value to use in the filter.
     * @return Filter the filter.
     */
    public static function make($column, $operator, $value)
    {
        return new Filter($column, $operator, $value);
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
     * Get the operator to use in the filter.
     *
     * @return string the operator.
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Get the value to use in the filter.
     *
     * @return mixed the value.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the type of the filter.
     *
     * @return string the type.
     */
    public function getType()
    {
        if ($this->operator == 'in') {
            return Filter::IN;
        }

        return Filter::BASIC;
    }
}
