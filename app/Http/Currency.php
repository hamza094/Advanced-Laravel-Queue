<?php

//When we call Currency::exchangeRateFor('CAD'), the currency converter will use the ExchangeRateApi service to get the USD-to-local-currency exchange rate and store it in the $rates static property.Next time we call Currency::exchangeRateFor() to convert the tax, the converter will not use the ExchangeRateApi service again. It'll use the rate stored inside the $rates property instead.

class Currency{

 public static $rates = [];
 public static function exchangeRateFor($currency)
 {
 if (!isset(static::$rates[$currency])) {
 static::$rates[$currency] = ExchangeRateApi::get(
 'USD', $currency
 );
 }
 return static::$rates[$currency];
 }

}
