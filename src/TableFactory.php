<?php

namespace Leadout\Analytics;

use Leadout\Analytics\Tables\AbstractTable;
use Illuminate\Support\Collection;

class TableFactory
{
    /**
     * The available tables.
     *
     * @var array
     */
    private $tables;

    /**
     * Instantiate the class.
     */
    public function __construct()
    {
        $this->tables = new Collection;
    }

    /**
     * Get all tables.
     *
     * @return Collection the tables.
     */
    public function all()
    {
        return $this->tables;
    }

    public function get($name)
    {
        return $this->tables->first(function($table) use ($name) {
            /** @var AbstractTable $table */
            return $table->getName() == $name;
        });
    }

    /**
     * Add the given table to the factory.
     *
     * @param AbstractTable $table the table.
     * @return void
     */
    public function add($table)
    {
        $this->tables->push($table);
    }
}
