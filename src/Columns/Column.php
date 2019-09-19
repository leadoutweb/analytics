<?php

namespace Leadout\Analytics\Columns;

use Closure;
use Illuminate\Support\Collection;
use Leadout\Analytics\Definition;

class Column implements Contract
{
    /**
     * The name of the column.
     *
     * @var string
     */
    private $name;

    /**
     * The expression to use when the column is used in a select clause.
     *
     * @var string
     */
    private $selectExpression;

    /**
     * The expression to use when the column is used in a filter clause.
     *
     * @var string
     */
    private $filterExpression;

    /**
     * A closure that formats a value in the column.
     *
     * @var Closure|null
     */
    private $formatter;

    /**
     * Instantiate the class and set the properties.
     *
     * @param string       $name             the name of the column.
     * @param string       $selectExpression the expression to use when the column is used in a select clause.
     * @param string       $filterExpression the expression to use when the column is used in a filter clause
     * @param Closure|null $formatter        a closure that formats a value in the column.
     */
    public function __construct($name, $selectExpression, $filterExpression,$formatter = null)
    {
        $this->name = $name;

        $this->selectExpression = $selectExpression;

        $this->filterExpression = $filterExpression;

        $this->formatter = $formatter;
    }

    /**
     * Make a new column.
     *
     * @param string       $name             the name of the column.
     * @param string       $selectExpression the expression to use when the column is used in a select clause.
     * @param string       $filterExpression the expression to use when the column is used in a filter clause
     * @param Closure|null $formatter        a closure that formats a value in the column.
     * @return Column the column.
     */
    public static function make($name, $selectExpression, $filterExpression, $formatter = null)
    {
        return new Column($name, $selectExpression, $filterExpression,$formatter);
    }

    /**
     * Instantiate a column containing a string.
     *
     * @param string $name       the name of the column.
     * @param string $expression the expression for the column.
     * @return Column the column.
     */
    public static function string($name, $expression)
    {
        return new Column($name, $expression, $expression, function ($value) {
            return (string)$value;
        });
    }

    /**
     * Instantiate a column containing an integer.
     *
     * @param string $name       the name of the column.
     * @param string $expression the expression for the column.
     * @return Column the column.
     */
    public static function integer($name, $expression)
    {
        return new Column($name, $expression, $expression, function ($value) {
            return (int)$value;
        });
    }

    /**
     * Instantiate a column containing a float.
     *
     * @param string $name       the name of the column.
     * @param string $expression the expression for the column.
     * @return Column the column.
     */
    public static function float($name, $expression)
    {
        return new Column($name, $expression, $expression, function ($value) {
            return (float)$value;
        });
    }

    /**
     * Instantiate a column containing a boolean.
     *
     * @param string $name       the name of the column.
     * @param string $expression the expression for the column.
     * @return Column the column.
     */
    public static function boolean($name, $expression)
    {
        return new Column($name, $expression, $expression, function ($value) {
            return (boolean)$value;
        });
    }

    /**
     * Instantiate a column containing a sum.
     *
     * @param string $name       the name of the column.
     * @param string $expression the expression for the column.
     * @return Column the column.
     */
    public static function sum($name, $expression)
    {
        return new Column($name, 'SUM(' . $expression . ')', $expression, function ($value) {
            return (float)$value;
        });
    }

    /**
     * Instantiate a column containing a sum.
     *
     * @param string $name       the name of the column.
     * @param string $expression the expression for the column.
     * @return Column the column.
     */
    public static function average($name, $expression)
    {
        return new Column($name, 'AVG(' . $expression . ')', $expression, function ($value) {
            return (float)$value;
        });
    }

    /**
     * Instantiate a column containing a count.
     *
     * @param string $name the name of the column.
     * @return Column the column.
     */
    public static function count($name)
    {
        return Column::integer($name, 'COUNT(*)');
    }

    /**
     * Instantiate a column with a value mapping.
     *
     * @param string           $name    the name of the column.
     * @param string           $column  the column to map.
     * @param array|Collection $map     the mapping.
     * @param string|null      $default a default value for the mapping.
     * @return Column the column.
     */
    public static function map($name, $column, $map, $default = null)
    {
        return Column::string($name, static::getCaseExpression($column, $map, $default));
    }

    /**
     * Get a case expression.
     *
     * @param string           $column  the column to map.
     * @param array|Collection $map     the mapping.
     * @param string|null      $default a default value for the mapping.
     * @return string the case expression.
     */
    private static function getCaseExpression($column, $map, $default)
    {
        return 'CASE ' . static::getMap($column, $map) . static::getDefaultCase($default) . ' END';
    }

    /**
     * Get a map expression for the given column.
     *
     * @param string           $column the column to map.
     * @param array|Collection $map    the mapping.
     * @return string the map.
     */
    private static function getMap($column, $map)
    {
        return collect($map)->map(function ($value, $key) use ($column) {
            return 'WHEN ' . $column . ' = "' . $key . '" THEN "' . $value . '"';
        })->implode(' ');
    }

    /**
     * Get the default case for the given default value.
     *
     * @param string|null $default the default value.
     * @return string the default case.
     */
    private static function getDefaultCase($default)
    {
        if ($default) {
            return ' ELSE "' . $default . '"';
        }

        return '';
    }

    /**
     * Get the name of the column.
     *
     * @return string the name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the expression to use when the column is used in a select clause.
     *
     * @return string the expression.
     */
    public function getSelectExpression()
    {
        return $this->selectExpression;
    }

    /**
     * Get the expression to use when the column is used in a filter clause.
     *
     * @return string the expression.
     */
    public function getFilterExpression()
    {
        return $this->filterExpression;
    }

    /**
     * Format the given value.
     *
     * @param string $value the value to format.
     * @return mixed the formatted value.
     */
    public function format($value)
    {
        if ($this->formatter) {
            return $this->formatter->call($this, $value);
        }

        return $value;
    }

    /**
     * Get the values that the column may assume in the given definition.
     *
     * @param Definition $definition the definition.
     * @return Collection the values.
     */
    public function values($definition)
    {
        return collect();
    }
}
