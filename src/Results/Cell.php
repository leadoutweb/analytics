<?php

namespace Leadout\Analytics\Results;

class Cell
{
    private $dimensions;

    private $value;

    private $metric;

    public function __construct($dimensions, $value, $metric)
    {
        $this->dimensions = $dimensions;

        $this->value = $value;

        $this->metric = $metric;
    }

    public static function make($dimensions, $value, $metric)
    {
        return new Cell($dimensions, $value, $metric);
    }

    public function getDimensions()
    {
        return $this->dimensions;
    }

    public function setDimensions($dimensions)
    {
        $this->dimensions = $dimensions;

        return $this;
    }

    public function getDimensionGroup()
    {
        return $this->dimensions->sortKeys()->map(function ($value, $key) {
            return $key . ':' . $value;
        })->implode('/');
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getMetric()
    {
        return $this->metric;
    }

    public function setMetric($metric)
    {
        $this->metric = $metric;

        return $this;
    }

    public function toArray()
    {
        return array_merge($this->dimensions->toArray(), [$this->metric => $this->value]);
    }
}
