<?php

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use RapidWeb\SimpleCurrencyRates\SimpleCurrencyRates;

class PsrCacheCompatibilityTest extends TestCase
{
    public function testPsr16CacheCanBeInjected()
    {
        $source = new ArrayRateSource(['USD' => 1.25]);
        $rates = new SimpleCurrencyRates(new TestPsr16Cache(), [$source]);

        $rates->get('GBP');
        $rates->get('GBP');

        $this->assertSame(1, $source->calls);
    }

    public function testPsr6CacheCanBeInjected()
    {
        $source = new ArrayRateSource(['USD' => 1.25]);
        $rates = new SimpleCurrencyRates(new TestPsr6Pool(), [$source]);

        $rates->get('GBP');
        $rates->get('GBP');

        $this->assertSame(1, $source->calls);
    }
}

class TestPsr16Cache implements CacheInterface
{
    private $values = [];

    public function get($key, $default = null)
    {
        return $this->has($key) ? $this->values[$key] : $default;
    }

    public function set($key, $value, $ttl = null)
    {
        $this->values[$key] = $value;

        return true;
    }

    public function delete($key)
    {
        unset($this->values[$key]);

        return true;
    }

    public function clear()
    {
        $this->values = [];

        return true;
    }

    public function getMultiple($keys, $default = null)
    {
        $values = [];

        foreach ($keys as $key) {
            $values[$key] = $this->get($key, $default);
        }

        return $values;
    }

    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return true;
    }

    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    public function has($key)
    {
        return array_key_exists($key, $this->values);
    }
}

class TestPsr6Pool implements CacheItemPoolInterface
{
    private $items = [];

    public function getItem($key)
    {
        return isset($this->items[$key]) ? $this->items[$key] : new TestPsr6Item($key);
    }

    public function getItems(array $keys = [])
    {
        $items = [];

        foreach ($keys as $key) {
            $items[$key] = $this->getItem($key);
        }

        return $items;
    }

    public function hasItem($key)
    {
        return isset($this->items[$key]);
    }

    public function clear()
    {
        $this->items = [];

        return true;
    }

    public function deleteItem($key)
    {
        unset($this->items[$key]);

        return true;
    }

    public function deleteItems(array $keys)
    {
        foreach ($keys as $key) {
            $this->deleteItem($key);
        }

        return true;
    }

    public function save(CacheItemInterface $item)
    {
        $item->markAsHit();
        $this->items[$item->getKey()] = $item;

        return true;
    }

    public function saveDeferred(CacheItemInterface $item)
    {
        return $this->save($item);
    }

    public function commit()
    {
        return true;
    }
}

class TestPsr6Item implements CacheItemInterface
{
    private $key;
    private $value;
    private $hit = false;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function get()
    {
        return $this->value;
    }

    public function isHit()
    {
        return $this->hit;
    }

    public function set($value)
    {
        $this->value = $value;

        return $this;
    }

    public function expiresAt($expiration)
    {
        return $this;
    }

    public function expiresAfter($time)
    {
        return $this;
    }

    public function markAsHit()
    {
        $this->hit = true;
    }
}
