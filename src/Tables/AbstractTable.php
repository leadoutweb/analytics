<?php

namespace Leadout\Analytics\Tables;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Leadout\Analytics\AnalyticsException;
use Leadout\Analytics\Columns\Column;
use Leadout\Analytics\Definition;
use Leadout\Analytics\Filter;
use Leadout\Analytics\Ordering;
use Leadout\Analytics\Results\Sheet;

abstract class AbstractTable
{
    /**
     * Get the name of the table.
     *
     * @return string the name.
     */
    abstract public function getName();

    /**
     * Get the table for the given definition.
     *
     * @param Definition $definition the definition to query the table with.
     * @return Builder the table.
     */
    abstract protected function getTable($definition);

    /**
     * Get the columns in the table.
     *
     * @return Collection the columns.
     */
    abstract protected function getColumns();

    /**
     * Determine if the table can run the given definition.
     *
     * @param Definition $definition the definition.
     * @return boolean true if the table can run the definition.
     */
    public function canRun($definition)
    {
        return $definition->getColumns()->diff($this->getColumns()->map->getName())->isEmpty();
    }

    /**
     * Query the table.
     *
     * @param Definition $definition the definition to query the table with.
     * @return Sheet the result.
     */
    public function query($definition)
    {
        return Sheet::parse($this->getData($definition), $definition);
    }

    private function getData($definition)
    {
        return $this->getDefaultData($definition)
            ->merge($this->parse($this->getRawData($definition), $definition))
            ->values();
    }

    /**
     * Get the data for the the given definition.
     *
     * @param Definition $definition the definition.
     * @return Collection the data.
     */
    private function getRawData($definition)
    {
        $query = $this->getTable($definition);

        $this->applyDimensions($query, $definition);

        $this->applyMetrics($query, $definition);

        $this->applyFilters($query, $definition);

        $this->applyOrderings($query, $definition);

        return $query->get();
    }

    /**
     * Apply the dimensions to the given query.
     *
     * @param Builder    $query      the query.
     * @param Definition $definition the definition to get the dimensions from.
     * @return void
     */
    private function applyDimensions($query, $definition)
    {
        $definition->getDimensions()->each(function ($dimension, $index) use ($query) {
            $query
                ->selectRaw($this->getSelectExpression($dimension) . ' as dimension_' . $index)
                ->groupBy('dimension_' . $index);
        });
    }

    /**
     * Apply the metrics to the given query.
     *
     * @param Builder    $query      the query.
     * @param Definition $definition the definition to get the metrics from.
     * @return void
     */
    private function applyMetrics($query, $definition)
    {
        $definition->getMetrics()->each(function ($metric, $index) use ($query) {
            $query->selectRaw($this->getSelectExpression($metric) . ' as metric_' . $index);
        });
    }

    /**
     * Apply the filters to the given query.
     *
     * @param Builder    $query      the query.
     * @param Definition $definition the definition to get the filters from.
     * @return void
     */
    private function applyFilters($query, $definition)
    {
        $definition->getFilters()->each(function ($filter) use ($query) {
            $this->applyFilter($query, $filter);
        });
    }

    /**
     * Apply the given filter to the given query.
     *
     * @param Builder $query  the query.
     * @param Filter  $filter the filter.
     * @throws AnalyticsException if the filter type is invalid.
     */
    private function applyFilter($query, $filter)
    {
        switch ($filter->getType()) {
            case Filter::BASIC:
                return $this->applyBasicFilter($query, $filter);
            case Filter::IN:
                return $this->applyInFilter($query, $filter);
            default:
                throw AnalyticsException::invalidFilterType();
        }
    }

    /**
     * Apply the given basic filter to the query.
     *
     * @param Builder $query  the query.
     * @param Filter  $filter the filter.
     * @return void
     */
    private function applyBasicFilter($query, $filter)
    {
        $query->where(
            DB::raw($this->getFilterExpression($filter->getColumn())),
            $filter->getOperator(),
            $filter->getValue()
        );
    }

    /**
     * Apply the given "in" filter to the query.
     *
     * @param Builder $query  the query.
     * @param Filter  $filter the filter.
     * @return void
     */
    private function applyInFilter($query, $filter)
    {
        $query->whereIn(DB::raw($this->getFilterExpression($filter->getColumn())), $filter->getValue());
    }

    /**
     * Apply the orderings to the given query.
     *
     * @param Builder    $query      the query.
     * @param Definition $definition the definition to get the orderings from.
     * @return void
     */
    private function applyOrderings($query, $definition)
    {
        $definition->getOrderings()->each(function ($ordering) use ($query) {
            /** @var Ordering $ordering */
            $query->orderBy(DB::raw($this->getFilterExpression($ordering->getColumn())), $ordering->getDirection());
        });
    }

    /**
     * Get the select expression for the column with the given name.
     *
     * @param string $name the name.
     * @return string the select expression.
     */
    private function getSelectExpression($name)
    {
        return $this->getColumn($name)->getSelectExpression();
    }

    /**
     * Get the filter expression for the column with the given name.
     *
     * @param string $name the name.
     * @return string the filter expression.
     */
    private function getFilterExpression($name)
    {
        return $this->getColumn($name)->getFilterExpression();
    }

    /**
     * Get the column with the given name.
     *
     * @param string $name the name of the column.
     * @return Column the column.
     */
    private function getColumn($name)
    {
        return $this->getColumns()->first(function ($column) use ($name) {
            return $column->getName() == $name;
        });
    }

    /**
     * Get the default data for the given raw data and definition.
     *
     * @param Definition $definition the definition that the data came from.
     * @return Collection the default data.
     */
    private function getDefaultData($definition)
    {
        if ($definition->getDimensions()->count() != 1) {
            return collect();
        }

        $column = $this->getColumn($definition->getDimensions()->first());

        return $column->values($definition)->mapWithKeys(function ($value) use ($column, $definition) {
            return ['key/' . $value => $this->getDefaultRow($column, $value, $definition)];
        });
    }

    /**
     * Get a default row for the given dimension column, value and definition.
     *
     * @param Column     $column     the dimension column.
     * @param mixed      $value      the value of the column.
     * @param Definition $definition the query definition.
     * @return array the default row.
     */
    private function getDefaultRow($column, $value, $definition)
    {
        return collect([$column->getName() => $value])->merge($this->getDefaultMetrics($definition));
    }

    /**
     * Get the default metrics for the given definition.
     *
     * @param Definition $definition the definition.
     * @return Collection the default metrics.
     */
    private function getDefaultMetrics($definition)
    {
        return $definition->getMetrics()->mapWithKeys(function ($metric) {
            return [$metric => 0];
        });
    }

    /**
     * Parse the given raw data.
     *
     * @param Collection $data       the raw data.
     * @param Definition $definition the definition that the data came from.
     * @return Collection the parsed data.
     */
    private function parse($data, $definition)
    {
        return $data
            ->keyBy(function ($row) use ($definition) {
                return $this->getDimensionKey($row, $definition);
            })
            ->map(function ($row) use ($definition) {
                return $this->parseRow($row, $definition);
            });
    }

    /**
     * Get the dimension key for the given row. It is prepended with the string 'key' to force the
     *
     * @param object     $row        the row.
     * @param Definition $definition the definition that the data came from.
     * @return string the dimension key.
     */
    private function getDimensionKey($row, $definition)
    {
        return $definition->getDimensions()
            ->map(function ($dimension, $index) use ($row) {
                return $row->{'dimension_' . $index};
            })->prepend('key')->implode('/');
    }

    /**
     * Parse the given row.
     *
     * @param object     $row        the row.
     * @param Definition $definition the definition that the data came from.
     * @return Collection the parsed row.
     */
    private function parseRow($row, $definition)
    {
        return $this->parseDimensions($row, $definition)->merge($this->parseMetrics($row, $definition));
    }

    private function parseDimensions($row, $definition)
    {
        return $definition->getDimensions()->mapWithKeys(function ($column, $index) use ($row) {
            return [$column => $this->getColumn($column)->format($row->{'dimension_' . $index})];
        });
    }

    private function parseMetrics($row, $definition)
    {
        return $definition->getMetrics()->mapWithKeys(function ($column, $index) use ($row) {
            return [$column => $this->getColumn($column)->format($row->{'metric_' . $index})];
        });
    }
}
