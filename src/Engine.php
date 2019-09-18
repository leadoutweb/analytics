<?php

namespace Leadout\Analytics;

use Leadout\Analytics\Results\Sheet;
use Leadout\Analytics\Tables\AbstractTable;

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
     * @return Sheet the result.
     */
    public function run($definition)
    {
        return Sheet::fromSheets($this->getSheets($definition));
    }

    /**
     * @param Definition $definition
     * @return mixed
     */
    private function getSheets($definition)
    {
        return $definition->getMetrics()
            ->groupBy(function ($metric) use ($definition) {
                return $this->getTable($definition->copyWithMetric($metric))->getName();
            })
            ->map(function ($metrics, $tableName) use ($definition) {
                return $this->tables->get($tableName)->query($definition->copyWithMetrics($metrics));
            });
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
