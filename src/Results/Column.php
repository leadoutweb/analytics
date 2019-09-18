<?php

namespace Leadout\Analytics\Results;

class Column
{
    private $cells;

    public function __construct()
    {
        $this->cells = collect();
    }

    public static function make()
    {
        return new Column;
    }

    public static function parse($data, $metric, $definition)
    {
        return Column::make()->setCells($data->map(function ($row) use ($metric, $definition) {
            return Cell::make($row->only($definition->getDimensions()), $row->get($metric), $metric);
        }));
    }

    public function getCells()
    {
        return $this->cells;
    }

    public function setCells($cells)
    {
        $this->cells = $cells;

        return $this;
    }

    public function addCell($cell)
    {
        $this->cells->push($cell);

        return $this;
    }
}
