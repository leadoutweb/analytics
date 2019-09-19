<?php

namespace Leadout\Analytics\Columns;

use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Leadout\Analytics\Definition;

class TimeColumn implements Contract
{
    /**
     * The name of the column.
     *
     * @var string
     */
    private $name;

    /**
     * The column to use as a time column.
     *
     * @var string
     */
    private $column;

    /**
     * The format for the time in the database.
     *
     * @var string
     */
    private $format;

    /**
     * The date interval expression.
     *
     * @var string
     */
    private $dateInterval;

    /**
     * The format for the time when used in a Carbon object.
     *
     * @var string
     */
    private $carbonFormat;

    /**
     * Instantiate the class and set the properties.
     *
     * @param string $name         the name of the column.
     * @param string $column       the column to use as a time column.
     * @param string $format       the format for the time in the database.
     * @param string $dateInterval the date interval expression.
     * @param string $carbonFormat the format for the time when used in a Carbon object.
     */
    public function __construct($name, $column, $format, $dateInterval, $carbonFormat)
    {
        $this->name = $name;

        $this->column = $column;

        $this->format = $format;

        $this->dateInterval = $dateInterval;

        $this->carbonFormat = $carbonFormat;
    }

    /**
     * Make a new time column.
     *
     * @param string $name         the name of the column.
     * @param string $column       the column to use as a time column.
     * @param string $format       the format for the time in the database.
     * @param string $dateInterval the date interval expression.
     * @param string $carbonFormat the format for the time when used in a Carbon object.
     * @return TimeColumn the time column.
     */
    public static function make($name, $column, $format, $dateInterval, $carbonFormat)
    {
        return new TimeColumn($name, $column, $format, $dateInterval, $carbonFormat);
    }

    /**
     * Make a new year column.
     *
     * @param string $column the column to use as a year column.
     * @return TimeColumn the time column.
     */
    public static function year($column)
    {
        return TimeColumn::make('year', $column, '%Y', 'P1Y', 'Y');
    }

    /**
     * Make a new month column.
     *
     * @param string $column the column to use as a month column.
     * @return TimeColumn the time column.
     */
    public static function month($column)
    {
        return TimeColumn::make('month', $column, '%Y-%m', 'P1M', 'Y-m');
    }

    /**
     * Make a new date column.
     *
     * @param string $column the column to use as a date column.
     * @return TimeColumn the time column.
     */
    public static function date($column)
    {
        return TimeColumn::make('date', $column, '%Y-%m-%d', 'P1D', 'Y-m-d');
    }

    /**
     * Make a new hour column.
     *
     * @param string $column the column to use as a hour column.
     * @return TimeColumn the time column.
     */
    public static function hour($column)
    {
        return TimeColumn::make('hour', $column, '%Y-%m-%d %H:00', 'PT1H', 'Y-m-d H:00');
    }

    /**
     * Make a new minute column.
     *
     * @param string $column the column to use as a minute column.
     * @return TimeColumn the time column.
     */
    public static function minute($column)
    {
        return TimeColumn::make('minute', $column, '%Y-%m-%d %H:%i', 'PT1M', 'Y-m-d H:i');
    }

    /**
     * Make a new second column.
     *
     * @param string $column the column to use as a second column.
     * @return TimeColumn the time column.
     */
    public static function second($column)
    {
        return TimeColumn::make('second', $column, '%Y-%m-%d %H:%i:%s', 'PT1s', 'Y-m-d H:i:s');
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
        return $this->getExpression();
    }

    /**
     * Get the expression to use when the column is used in a filter clause.
     *
     * @return string the expression.
     */
    public function getFilterExpression()
    {
        return $this->getExpression();
    }

    /**
     * Get the expression for this column.
     *
     * @return string the expression.
     */
    private function getExpression()
    {
        return 'DATE_FORMAT(' . $this->column . ', "' . $this->format . '")';
    }

    /**
     * Format the given value.
     *
     * @param string $value the value to format.
     * @return mixed the formatted value.
     */
    public function format($value)
    {
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
        $values = new Collection;

        foreach ($this->getPeriod($definition) as $value) {
            $values->push($value->format($this->carbonFormat));
        }

        return $values;
    }

    /**
     * Get the period for the given definition.
     *
     * @param Definition $definition the definition.
     * @return CarbonPeriod the period.
     */
    public function getPeriod($definition)
    {
        return CarbonPeriod::create()
            ->setStartDate($definition->getStart())
            ->setEndDate($definition->getEnd())
            ->setDateInterval($this->dateInterval);
    }
}
