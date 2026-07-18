<?php

namespace RapidWeb\SimpleCurrencyRates\Sources;

use RapidWeb\SimpleCurrencyRates\Contracts\RateSourceInterface;
use RapidWeb\SimpleCurrencyRates\Exceptions\RateSourceException;

class FrankfurterRateSource extends HttpRateSource implements RateSourceInterface
{
    public function getRates($base)
    {
        $response = $this->request('https://api.frankfurter.dev/v1/latest?base='.rawurlencode($base));
        $decoded = json_decode($response, true);

        if (!is_array($decoded) || !isset($decoded['rates']) || !is_array($decoded['rates'])) {
            throw new RateSourceException('Frankfurter returned an invalid currency rate response.');
        }

        return $decoded['rates'];
    }

    public function getCacheTtl()
    {
        return 3600;
    }
}
