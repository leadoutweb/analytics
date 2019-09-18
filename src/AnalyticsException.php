<?php

namespace Leadout\Analytics;

use Exception;

class AnalyticsException extends Exception
{
    /**
     * The selected dimensions and metrics cannot be queried together
     *
     * @return AnalyticsException the exception.
     */
    public static function invalidDimensionsAndMetrics()
    {
        return new AnalyticsException('The selected dimensions and metrics cannot be queried together.', 1);
    }

    /**
     * The filter type is invalid.
     *
     * @return AnalyticsException the exception.
     */
    public static function invalidFilterType()
    {
        return new AnalyticsException('The filter type is invalid.', 2);
    }
}
