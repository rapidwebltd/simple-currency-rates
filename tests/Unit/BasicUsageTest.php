<?php

use PHPUnit\Framework\TestCase;
use RapidWeb\SimpleCurrencyRates\Contracts\RateSourceInterface;
use RapidWeb\SimpleCurrencyRates\SimpleCurrencyRates;
use RapidWeb\SimpleCurrencyRates\Sources\BlockchainBitcoinRateSource;
use RapidWeb\SimpleCurrencyRates\Sources\FrankfurterRateSource;

class BasicUsageTest extends TestCase
{
    public function testRatesFromCustomSourcesAreNormalisedAndSorted()
    {
        $rates = (new SimpleCurrencyRates(new ArrayTestCache(), [
            new ArrayRateSource(['usd' => '1.25', 'EUR' => 1.1]),
        ]))->get('gbp');

        $this->assertSame(['EUR', 'GBP', 'USD'], array_keys($rates));
        $this->assertSame(1.0, $rates['GBP']);
        $this->assertSame(1.25, $rates['USD']);
    }

    public function testSourceResultsAreCached()
    {
        $source = new ArrayRateSource(['USD' => 1.25]);
        $rates = new SimpleCurrencyRates(new ArrayTestCache(), [$source]);

        $rates->get('GBP');
        $rates->get('GBP');

        $this->assertSame(1, $source->calls);
    }

    public function testSourcesCanBeReplacedAndAddedFluently()
    {
        $rates = new SimpleCurrencyRates(new ArrayTestCache(), []);
        $rates->addSource(new ArrayRateSource(['USD' => 1.25]));

        $this->assertSame(1.25, $rates->get('GBP')['USD']);
    }

    public function testFrankfurterResponseIsParsed()
    {
        $source = new FrankfurterRateSource(function ($url) {
            $this->assertStringStartsWith('https://api.frankfurter.dev/v1/latest?base=GBP', $url);

            return '{"base":"GBP","rates":{"EUR":1.16,"USD":1.35}}';
        });

        $rates = (new SimpleCurrencyRates(new ArrayTestCache(), [$source]))->get('GBP');

        $this->assertSame(1.16, $rates['EUR']);
        $this->assertSame(1.35, $rates['USD']);
    }

    public function testBlockchainResponseIsParsed()
    {
        $source = new BlockchainBitcoinRateSource(function ($url) {
            $this->assertStringStartsWith('https://blockchain.info/tobtc?currency=GBP', $url);

            return '0.00001234';
        });

        $rates = (new SimpleCurrencyRates(new ArrayTestCache(), [$source]))->get('GBP');

        $this->assertSame(0.00001234, $rates['XBT']);
    }

    public function testInvalidBaseCurrencyIsRejected()
    {
        $this->expectException(InvalidArgumentException::class);

        (new SimpleCurrencyRates(new ArrayTestCache(), []))->get('sterling');
    }
}

class ArrayRateSource implements RateSourceInterface
{
    public $calls = 0;
    private $rates;

    public function __construct(array $rates)
    {
        $this->rates = $rates;
    }

    public function getRates($base)
    {
        ++$this->calls;

        return $this->rates;
    }

    public function getCacheTtl()
    {
        return 60;
    }
}

class ArrayTestCache
{
    private $values = [];

    public function get($key, $default = null)
    {
        return array_key_exists($key, $this->values) ? $this->values[$key] : $default;
    }

    public function has($key)
    {
        return array_key_exists($key, $this->values);
    }

    public function set($key, $value, $ttl = null)
    {
        $this->values[$key] = $value;

        return true;
    }
}
