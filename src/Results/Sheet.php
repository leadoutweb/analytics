<?php

namespace Leadout\Analytics\Results;

class Sheet
{
    private $columns;

    public function __construct()
    {
        $this->columns = collect();
    }

    public static function make()
    {
        return new Sheet;
    }

    public static function parse($data, $definition)
    {
        return Sheet::make()->setColumns($definition->getMetrics()->map(function ($metric) use ($data, $definition) {
            return Column::parse($data, $metric, $definition);
        }));
    }

    public static function fromSheets($sheets)
    {
        return Sheet::make()->setColumns($sheets->flatMap(function ($sheet) {
            return $sheet->getColumns();
        }));
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function setColumns($columns)
    {
        $this->columns = $columns;

        return $this;
    }

    public function addColumn($column)
    {
        $this->columns->push($column);

        return $this;
    }

    public function toArray()
    {
        return $this->columns
            ->flatMap(function ($column) {
                return $column->getCells();
            })
            ->groupBy(function ($cell) {
                return $cell->getDimensionGroup();
            })
            ->map(function ($cells) {
                return array_merge(...$cells->map->toArray());
            })
            ->values();
    }
}
