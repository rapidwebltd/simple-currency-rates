<?php

use PHPUnit\Framework\TestCase;
use RapidWeb\SimpleCurrencyRates\SimpleCurrencyRates;

class BasicUsageTest extends TestCase
{
    private $expectedCurrencies = ['GBP','XBT','AUD','BGN','BRL','CAD','CHF','CNY','CZK',
        'DKK','HKD','HRK','HUF','IDR','ILS','INR','JPY','KRW','MXN','MYR','NOK','NZD','PHP',
        'PLN','RON','RUB','SEK','SGD','THB','TRY','USD','ZAR','EUR'];

    public function testExpectedCurrenciesArePresent()
    {
        $rates = (new SimpleCurrencyRates())->get('GBP');

        foreach($this->expectedCurrencies as $expectedCurrency) {
            $this->assertTrue(array_key_exists($expectedCurrency, $rates), 'The expected currency code ('.$expectedCurrency.') is missing.');
        }
        
    }

    public function testBaseCurrencyIsEqualToOne()
    {
        $rates = (new SimpleCurrencyRates())->get('GBP');

        $this->assertEquals(1, $rates['GBP'], 'Base currency does not equal 1.');
    }

    public function testRatesArrayIsFormattedCorrectly()
    {
        $rates = (new SimpleCurrencyRates())->get('GBP');

        $this->assertTrue(is_array($rates));

        foreach($rates as $currency => $rate) {
            $this->assertTrue(is_string($currency), 'Currency code ('.$currency.') is not a string.');
            $this->assertEquals(3, strlen($currency), 'Currency code ('.$currency.') is not 3 characters.');
            $this->assertTrue(is_float($rate), 'Currency rate (for '.$currency.') is not a float.');
            $this->assertTrue($rate>0, 'Currency rate (for '.$currency.') must be greater than zero.');
        }
    }
}