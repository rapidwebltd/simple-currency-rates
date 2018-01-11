<?php

namespace RapidWeb\SimpleCurrencyRates;

use \rapidweb\RWFileCache\RWFileCache;

class SimpleCurrencyRates
{
    const FIXER_IO_RATES_CACHE_EXPIRY_MINUTES = 60;
    const BLOCKCHAIN_RATE_CACHE_EXPIRY_MINUTES = 15;

    private $cache;

    public function __construct()
    {
        $this->cache = new RWFileCache();
        $this->cache->changeConfig(['cacheDirectory' => __DIR__.'/../cache/']);
    }

    public function get($base)
    {
        $rates = [];

        $rates[$base] = (float) 1.00;

        $rates['XBT'] = $this->getBitcoinRate($base);

        $fiatRates = $this->getFiatRates($base);

        $rates = array_merge($rates, $fiatRates);
        
        ksort($rates);

        return $rates;  
    }

    private function getBitcoinRate($base)
    {
        $cacheKey = 'blockchainRate'.$base;

        $rate = $this->cache->get($cacheKey);

        if ($rate) {
            return $rate;
        }

        $rate = (float) file_get_contents('https://blockchain.info/tobtc?currency='.$base.'&value=1');

        $this->cache->set($cacheKey, $rate, self::FIXER_IO_RATES_CACHE_EXPIRY_MINUTES * 60);

        return $rate;
    }

    private function getFiatRates($base)
    {
        $cacheKey = 'fixerIORates'.$base;

        $rates = $this->cache->get($cacheKey);

        if ($rates) {
            return $rates;
        }

        $response = file_get_contents('https://api.fixer.io/latest?base='.$base);
        $response = json_decode($response, true);
        $rates = isset($response['rates']) ? $response['rates'] : [];

        $this->cache->set($cacheKey, $rates, self::FIXER_IO_RATES_CACHE_EXPIRY_MINUTES * 60);

        return $rates;
    }
}