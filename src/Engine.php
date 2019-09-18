<?php

namespace Leadout\Analytics;

use Leadout\Analytics\Tables\AbstractTable;
use Illuminate\Support\Collection;

class Engine
{
    /**
     * The table factory.
     *
     * @var TableFactory
     */
    private $tables;

    /**
     * Instantiate the class and inject the dependencies.
     *
     * @param TableFactory $tables the table factory.
     */
    public function __construct(TableFactory $tables)
    {
        $this->tables = $tables;
    }

    /**
     * Run the given definition.
     *
     * @param Definition $definition the definition.
     * @return Collection the result.
     */
    public function run($definition)
    {
        return $this->getTable($definition)->query($definition);
    }

    /**
     * Get the table that can run the given definition.
     *
     * @param Definition $definition the definition.
     * @return AbstractTable the table.
     */
    private function getTable($definition)
    {
        return $this->tables->all()->first(
            function ($table) use ($definition) {
                /** @var AbstractTable $table */
                return $table->canRun($definition);
            },
            function () {
                throw AnalyticsException::invalidDimensionsAndMetrics();
            }
        );
    }
}
