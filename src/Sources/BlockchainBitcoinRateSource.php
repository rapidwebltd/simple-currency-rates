<?php

namespace RapidWeb\SimpleCurrencyRates\Sources;

use RapidWeb\SimpleCurrencyRates\Contracts\RateSourceInterface;
use RapidWeb\SimpleCurrencyRates\Exceptions\RateSourceException;

class BlockchainBitcoinRateSource extends HttpRateSource implements RateSourceInterface
{
    public function getRates($base)
    {
        if ($base === 'XBT') {
            return [];
        }

        $response = trim($this->request('https://blockchain.info/tobtc?currency='.rawurlencode($base).'&value=1'));

        if (!is_numeric($response) || $response <= 0) {
            throw new RateSourceException('Blockchain.com returned an invalid Bitcoin rate response.');
        }

        return ['XBT' => (float) $response];
    }

    public function getCacheTtl()
    {
        return 900;
    }
}
