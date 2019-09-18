<?php

namespace Leadout\Analytics\Columns;

use Illuminate\Support\Collection;
use Leadout\Analytics\Definition;

interface Contract
{
    /**
     * Get the name of the column.
     *
     * @return string the name.
     */
    public function getName();

    /**
     * Get the expression to use when the column is used in a select clause.
     *
     * @return string the expression.
     */
    public function getSelectExpression();

    /**
     * Get the expression to use when the column is used in a filter clause.
     *
     * @return string the expression.
     */
    public function getFilterExpression();

    /**
     * Get the expression to use when the column is used in a group by clause.
     *
     * @return string the expression.
     */
    public function getGroupByExpression();

    /**
     * Format the given value.
     *
     * @param string $value the value to format.
     * @return mixed the formatted value.
     */
    public function format($value);

    /**
     * Get the values that the column may assume in the given definition.
     *
     * @param Definition $definition the definition.
     * @return Collection the values.
     */
    public function values($definition);
}
