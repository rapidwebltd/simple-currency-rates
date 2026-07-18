<?php

namespace RapidWeb\SimpleCurrencyRates\Cache;

use InvalidArgumentException;
use RuntimeException;

class CacheAdapter
{
    private $cache;

    public function __construct($cache)
    {
        if (!is_object($cache)) {
            throw new InvalidArgumentException('The cache must be a PSR-6 pool, a PSR-16 cache, or a compatible cache object.');
        }

        $this->cache = $cache;

        if (!$this->isPsr6() && (!method_exists($cache, 'get') || !method_exists($cache, 'set'))) {
            throw new InvalidArgumentException('The cache must be a PSR-6 pool, a PSR-16 cache, or a compatible cache object.');
        }
    }

    public function get($key, &$found)
    {
        if ($this->isPsr6()) {
            $item = $this->cache->getItem($key);
            $found = $item->isHit();

            return $found ? $item->get() : null;
        }

        if (method_exists($this->cache, 'has')) {
            $found = $this->cache->has($key);

            return $found ? $this->cache->get($key) : null;
        }

        $value = $this->cache->get($key);
        $found = $value !== null;

        return $value;
    }

    public function set($key, $value, $ttl)
    {
        if ($this->isPsr6()) {
            $item = $this->cache->getItem($key);
            $item->set($value);
            $item->expiresAfter($ttl);

            if (!$this->cache->save($item)) {
                throw new RuntimeException('The PSR-6 cache rejected the currency rates.');
            }

            return;
        }

        if ($this->cache->set($key, $value, $ttl) === false) {
            throw new RuntimeException('The cache rejected the currency rates.');
        }
    }

    private function isPsr6()
    {
        return interface_exists('Psr\\Cache\\CacheItemPoolInterface')
            && $this->cache instanceof \Psr\Cache\CacheItemPoolInterface;
    }
}
