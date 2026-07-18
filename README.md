# Simple Currency Rates

Simple Currency Rates provides current fiat and Bitcoin exchange rates through a small, framework-independent PHP API. It supports PHP 5.6 through current PHP releases.

By default, fiat rates come from the keyless [Frankfurter API](https://frankfurter.dev/) and Bitcoin rates come from [Blockchain.com](https://www.blockchain.com/explorer/api/exchange_rates_api). Responses are cached on the local filesystem.

## Installation

```bash
composer require rapidwebltd/simple-currency-rates
```

## Usage

```php
use RapidWeb\SimpleCurrencyRates\SimpleCurrencyRates;

$rates = (new SimpleCurrencyRates())->get('GBP');
```

The base currency is normalised to uppercase and always has a rate of `1.0`. Rates are returned as a currency-code-keyed array sorted alphabetically:

```php
[
    'EUR' => 1.16,
    'GBP' => 1.0,
    'USD' => 1.35,
    'XBT' => 0.00001234,
]
```

The base must be a three-letter currency code. Network failures and invalid provider responses throw `RateSourceException` instead of returning incomplete data.

## Using another cache

The constructor and `setCache()` accept PSR-16 caches, PSR-6 cache pools, and compatible objects exposing `get`, `set`, and optionally `has`. No PSR package is forced on applications that use the built-in cache.

```php
$rates = new SimpleCurrencyRates($psr16Cache);

// Or replace it later.
$rates->setCache($psr6CachePool);
```

To change the built-in cache directory:

```php
use RapidWeb\SimpleCurrencyRates\Cache\FilesystemCache;

$rates = new SimpleCurrencyRates(
    new FilesystemCache('/var/cache/my-application/currency-rates')
);
```

## Using custom rate sources

A source implements `RateSourceInterface` and returns positive rates keyed by three-letter currency code. `getCacheTtl()` returns its cache lifetime in seconds; return `0` to disable caching.

```php
use RapidWeb\SimpleCurrencyRates\Contracts\RateSourceInterface;

class CompanyRateSource implements RateSourceInterface
{
    public function getRates($base)
    {
        return [
            'EUR' => 1.16,
            'USD' => 1.35,
        ];
    }

    public function getCacheTtl()
    {
        return 300;
    }
}
```

Pass sources as the second constructor argument, replace them with `setSources()`, or append one with `addSource()`:

```php
$rates = new SimpleCurrencyRates($cache, [
    new CompanyRateSource(),
]);

$rates
    ->setSources([new CompanyRateSource()])
    ->addSource($anotherSource);
```

When multiple sources return the same currency, the later source wins. The requested base currency is always reset to `1.0`.

The bundled `FrankfurterRateSource` and `BlockchainBitcoinRateSource` accept an optional callable HTTP client, which is useful for application-specific transports and deterministic tests:

```php
use RapidWeb\SimpleCurrencyRates\Sources\FrankfurterRateSource;

$source = new FrankfurterRateSource(function ($url) use ($httpClient) {
    return $httpClient->get($url)->getBody()->getContents();
});
```

## Testing

```bash
composer install
vendor/bin/phpunit
```
