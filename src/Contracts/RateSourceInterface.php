<?php

namespace RapidWeb\SimpleCurrencyRates\Contracts;

interface RateSourceInterface
{
    /**
     * Return rates keyed by three-letter currency code.
     *
     * @param string $base
     *
     * @return array
     */
    public function getRates($base);

    /**
     * Return the cache lifetime in seconds, or zero to disable caching.
     *
     * @return int
     */
    public function getCacheTtl();
}
