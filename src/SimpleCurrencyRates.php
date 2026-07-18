<?php

namespace RapidWeb\SimpleCurrencyRates;

use InvalidArgumentException;
use RapidWeb\SimpleCurrencyRates\Cache\CacheAdapter;
use RapidWeb\SimpleCurrencyRates\Cache\FilesystemCache;
use RapidWeb\SimpleCurrencyRates\Contracts\RateSourceInterface;
use RapidWeb\SimpleCurrencyRates\Sources\BlockchainBitcoinRateSource;
use RapidWeb\SimpleCurrencyRates\Sources\FrankfurterRateSource;
use UnexpectedValueException;

class SimpleCurrencyRates
{
    private $cache;
    private $sources = [];

    public function __construct($cache = null, array $sources = null)
    {
        $this->setCache($cache ?: new FilesystemCache());
        $this->setSources($sources === null ? $this->getDefaultSources() : $sources);
    }

    public function setCache($cache)
    {
        $this->cache = new CacheAdapter($cache);

        return $this;
    }

    public function setSources(array $sources)
    {
        $this->sources = [];

        foreach ($sources as $source) {
            $this->addSource($source);
        }

        return $this;
    }

    public function addSource(RateSourceInterface $source)
    {
        $this->sources[] = $source;

        return $this;
    }

    public function get($base)
    {
        if (!is_string($base)) {
            throw new InvalidArgumentException('The base currency must be a three-letter currency code.');
        }

        $base = strtoupper(trim($base));

        if (!preg_match('/^[A-Z]{3}$/D', $base)) {
            throw new InvalidArgumentException('The base currency must be a three-letter currency code.');
        }

        $rates = [];

        foreach ($this->sources as $index => $source) {
            $cacheKey = 'simple_currency_rates_'.sha1($index.'|'.get_class($source).'|'.$base);
            $sourceRates = $this->cache->get($cacheKey, $found);

            if (!$found) {
                $sourceRates = $source->getRates($base);

                if (!is_array($sourceRates)) {
                    throw new UnexpectedValueException('Rate sources must return an array.');
                }

                if ($source->getCacheTtl() > 0) {
                    $this->cache->set($cacheKey, $sourceRates, $source->getCacheTtl());
                }
            }

            foreach ($sourceRates as $currency => $rate) {
                $currency = strtoupper((string) $currency);

                if (!preg_match('/^[A-Z]{3}$/D', $currency) || !is_numeric($rate) || $rate <= 0) {
                    throw new UnexpectedValueException('Rate sources must return positive rates keyed by three-letter currency codes.');
                }

                $rates[$currency] = (float) $rate;
            }
        }

        $rates[$base] = (float) 1.0;
        ksort($rates);

        return $rates;
    }

    private function getDefaultSources()
    {
        return [
            new FrankfurterRateSource(),
            new BlockchainBitcoinRateSource(),
        ];
    }
}
