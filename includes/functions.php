<?php

function dmm_get_currencies($currency = null)
{
    $currencies = [
        'AED' => 2,
        'AUD' => 2,
        'BGN' => 2,
        'CAD' => 2,
        'CHF' => 2,
        'CZK' => 2,
        'DKK' => 2,
        'EUR' => 2,
        'GBP' => 2,
        'HKD' => 2,
        'HRK' => 2,
        'HUF' => 2,
        'ILS' => 2,
        'ISK' => 2,
        'JPY' => 0,
        'NOK' => 2,
        'NZD' => 2,
        'PHP' => 2,
        'PLN' => 2,
        'RON' => 2,
        'RUB' => 2,
        'SEK' => 2,
        'SGD' => 2,
        'USD' => 2,
        'ZAR' => 2,
    ];

    if ($currency && array_key_exists($currency, $currencies))
        return $currencies[$currency];

    return $currencies;
}

/**
 * Human readable label for a subscription interval.
 *
 * @param string $interval Interval as stored by Mollie, e.g. '1 month'.
 *
 * @return string Translated label, or the raw interval when it is not one of the
 *                intervals the donation form offers.
 * @since 2.11.0
 */
function dmm_get_interval_label($interval)
{
    switch ($interval) {
        case '1 month':
            return __('Monthly', 'doneren-met-mollie');
        case '3 months':
            return __('Each quarter', 'doneren-met-mollie');
        case '12 months':
            return __('Annually', 'doneren-met-mollie');
    }

    return $interval;
}

function dmm_get_currency_symbol($currency = 'EUR')
{
    switch ($currency)
    {
        case 'EUR':
            $symbol = '&euro;';
            break;
        case 'USD':
            $symbol = 'US$';
            break;
        case 'GBP':
            $symbol = '&pound;';
            break;
        case 'JPY':
            $symbol = '&yen;';
            break;
        default:
            $symbol = strtoupper($currency);
    }

    return $symbol;
}