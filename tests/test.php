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
var_dump($api->getCurrencyForRange('EUR', '150820', '150825'));
