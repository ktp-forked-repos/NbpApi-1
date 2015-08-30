# NbpApi

NbpApi is PHP Wrapper of Web API available at: http://www.nbp.pl/homen.aspx?f=/kursy/instrukcja_pobierania_kursow_walut_en.html

## Basic Usage

You can autoload NbpApi with composer if you have set PSR-0 autoloading in **composer.json.** or load it by yourself.

    <?php

    use NbpApi\NbpApi;

    $apiInstance = new NbpApi();

    $eurValue = $apiInstance->getCurrencyForDate('eur', '150713');
    echo $eurValue;
    // 4.155

## Methods available

### getCurrencyForDate($currency_name, $date)
`$currency_name` **Type:** string

`$date` **Type:** string **Format:** 'ymd' e.g. 150730

`return:` float|null

### lastCurrencyValue($currency_name)
`$currency_name` **Type:** string

**Example output:**

    array (size=1)
      '2015-08-28' => float 4.2325 

### getCurrenciesForDate($currencies_names, $date)
`$currency_name` **Type:** array

`$date` **Type:** string **Format:** 'ymd' e.g. 150828

`return:` array

**Example output:**

    array (size=1)
      '2015-07-20' => 
        array (size=35)
          'usd' => float 3.7868
          'aud' => float 2.7935
          'hkd' => float 0.4885
          'cad' => float 2.9176
          'nzd' => float 2.4886
          'sgd' => float 2.76
          'eur' => float 4.1083

### getCurrencyForRange($currency_name, $date_from, $date_to)
`$currency_name` **Type** string

`$date_from` **Type:** string **Format:** 'ymd'

`$date_to` **Type:** string **Format:** 'ymd'

**Example output:**

    array (size=4)
      '2015-07-15' => float 4.1319
      '2015-07-16' => float 4.1111
      '2015-07-17' => float 4.1021
      '2015-07-20' => float 4.1083

### getCurrenciesForRange($currency_names, $date_from, $date_to)
`$currency_names` **Type** array

`$date_from` **Type:** string **Format:** 'ymd'

`$date_to` **Type:** string **Format:** 'ymd'

**Example output:**

    array (size=4)
      '2015-07-15' =>
        array (size=1)
          '2015-07-15' =>
            array (size=2)
              'usd' => float 3.747
              'eur' => float 4.1319
      '2015-07-16' =>
        array (size=1)
          '2015-07-16' =>
            array (size=2)
              'usd' => float 3.7694
              'eur' => float 4.1111

### Currency slugs

    [thb] => bat (Tajlandia)
    [usd] => dolar amerykański
    [aud] => dolar australijski
    [hkd] => dolar Hongkongu
    [cad] => dolar kanadyjski
    [nzd] => dolar nowozelandzki
    [sgd] => dolar singapurski
    [eur] => euro
    [huf] => forint (Węgry)
    [chf] => frank szwajcarski
    [gbp] => funt szterling
    [uah] => hrywna (Ukraina)
    [jpy] => jen (Japonia)
    [czk] => korona czeska
    [dkk] => korona duńska
    [isk] => korona islandzka
    [nok] => korona norweska
    [sek] => korona szwedzka
    [hrk] => kuna (Chorwacja)
    [ron] => lej rumuński
    [bgn] => lew (Bułgaria)
    [try] => lira turecka
    [ils] => nowy izraelski szekel
    [clp] => peso chilijskie
    [php] => peso filipińskie
    [mxn] => peso meksykańskie
    [zar] => rand (Republika Południowej Afryki)
    [brl] => real (Brazylia)
    [myr] => ringgit (Malezja)
    [rub] => rubel rosyjski
    [idr] => rupia indonezyjska
    [inr] => rupia indyjska
    [krw] => won południowokoreański
    [cny] => yuan renminbi (Chiny)
    [xdr] => SDR (MFW)
