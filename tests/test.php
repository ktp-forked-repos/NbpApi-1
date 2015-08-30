<?php
/**
 * Created by PhpStorm.
 * User: ShataN
 * Date: 29/08/2015
 * Time: 12:52
 */

require_once __DIR__ . '/../vendor/autoload.php';

use NbpApi\NbpApi;

$api = new NbpApi();
echo 'getCurrencyForRange';
var_dump($api->getCurrencyForRange('eur', '150715', '150720'));
echo 'getCurrencyForDate';
var_dump($api->getCurrencyForDate('eur', '150713'));
echo 'lastCurrencyValue';
var_dump($api->lastCurrencyValue('eur'));
echo 'lastCurrenciesValues';
var_dump($api->lastCurrenciesValues(['eur', 'usd']));

echo 'getCurrenciesForDate';
var_dump($api->getCurrenciesForDate([], '150720'));
echo 'getCurrenciesForRange';
var_dump($api->getCurrenciesForRange(['eur', 'usd'], '150715', '150720'));

