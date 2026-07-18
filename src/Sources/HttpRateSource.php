<?php

namespace RapidWeb\SimpleCurrencyRates\Sources;

use RapidWeb\SimpleCurrencyRates\Exceptions\RateSourceException;

abstract class HttpRateSource
{
    private $request;

    public function __construct(callable $request = null)
    {
        $this->request = $request;
    }

    protected function request($url)
    {
        if ($this->request !== null) {
            return call_user_func($this->request, $url);
        }

        $context = stream_context_create([
            'http' => [
                'header' => "Accept: application/json\r\nUser-Agent: simple-currency-rates\r\n",
                'timeout' => 10,
            ],
        ]);
        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            throw new RateSourceException('The currency rate service could not be reached.');
        }

        return $response;
    }
}
