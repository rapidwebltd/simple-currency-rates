# Simple Currency Rates

This package provides simple access to current currency exchange rates.

## Installation

Simple Currency Rates can be easily installed using Composer. Just run the following command from the root of your project.

```
composer require rapidwebltd/simple-currency-rates
```

If you have never used the Composer dependency manager before, head to the [Composer website](https://getcomposer.org/) for more information on how to get started.

## Usage

To get the current exchange rates, just create a new `SimpleCurrencyRates` object and call its `get` method with the base currency of your choice (e.g. GBP, USD, EUR). The following code snippet shows how to do this.

```php
$rates = (new SimpleCurrencyRates)->get('GBP');
```

Exchange rates are returned as an array in the format `['CURRENCYCODE' => RATE, ...]`. An example rates array is shown below.

```php
Array
(
    [AUD] => 1.7307
    [BGN] => 2.201
    [BRL] => 4.423
    [CAD] => 1.7099
    [CHF] => 1.3274
    [CNY] => 8.8656
    [CZK] => 28.715
    [DKK] => 8.3831
    [EUR] => 1.1254
    [GBP] => 1
    [HKD] => 10.767
    [HRK] => 8.3542
    [HUF] => 347.46
    [IDR] => 18357
    [ILS] => 4.7002
    [INR] => 88.15
    [JPY] => 152.37
    [KRW] => 1465.7
    [MXN] => 25.837
    [MYR] => 5.457
    [NOK] => 10.838
    [NZD] => 1.8915
    [PHP] => 69.704
    [PLN] => 4.6942
    [RON] => 5.231
    [RUB] => 77.678
    [SEK] => 11.054
    [SGD] => 1.8214
    [THB] => 44.015
    [TRY] => 5.2259
    [USD] => 1.3763
    [XBT] => 0.00013526
    [ZAR] => 16.877
)
```
