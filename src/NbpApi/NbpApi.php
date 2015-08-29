<?php

namespace NbpApi;

use Carbon\Carbon;
use InvalidArgumentException;

function convert2Float($number) {
    return (float) preg_replace('/,/', '.', $number);
}
/*
 * NbpApi is simple wrapper written in PHP to get data from NbpApi which can be found here:
 * http://www.nbp.pl/home.aspx?f=/kursy/instrukcja_pobierania_kursow_walut.html
 *
 * @property    string  $dir
 * @property    string  $json
 */
class NbpApi {

    public $dir;
    public $json;

    /*
     * TODO:
     * - Fetching old directories (change of 1 July 2015)
     *
     */
    private $_urls = array(
        'dir' => 'http://www.nbp.pl/kursy/xml/dir.txt',
        'dirY' => 'http://www.nbp.pl/kursy/xml/dir[date].txt',
        'lastA' => 'http://www.nbp.pl/kursy/xml/LastA.xml',
        'xml' => 'http://www.nbp.pl/kursy/xml/'
    );

    private function _getFile($file) {
        $file = file($file);
        return $file;
    }

    /**
     *
     */private function _getDir() {
        $this->dir = $this->_getFile($this->_urls['dir']);
    }

    /**
     * @param null $date
     * @return bool
     */
    private function _getXml($date = null) {
        if(!$date) {
            $date = date('ymd');
        }
        $pattern = '/^a\d{3}.' . $date . '/';

        foreach($this->dir as $n => $line) {
            if(preg_match($pattern, $line)) {

                // Build url for xml file.
                $xml = $this->_urls['xml'] . trim($line) . '.xml';

                // Get xml file
                $xml = file_get_contents($xml);

                // Parse xml to json.
                $this->json = $this->_parseXml($xml);
                return true;
            }
        }
        return false;
    }

    /**
     * Checks last average exchange rates for given $currency_name and returns it as an array.
     *
     * @param $currency_name
     * @return array
     */
    public function lastCurrencyValue($currency_name) {
        $xml = file_get_contents($this->_urls['lastA']);
        $this->json = $this->_parseXml($xml);
        $arr = json_decode($this->json, true);

        // Getting date of publication and exchange rate for that currency name.
        $date = $arr['data_publikacji'];
        $output = [$date => $this->getCurrency($currency_name)];

        return $output;

    }


    public function lastCurrenciesValues($currencies_names) {
        $xml = file_get_contents($this->_urls['lastA']);
        $this->json = $this->_parseXml($xml);

        return $this->getCurrencies($currencies_names);
    }

    /**
     * Parse XML to JSON
     *
     * @param $xml_string
     * @return string
     */
    private function _parseXml($xml_string) {
        $xml = simplexml_load_string($xml_string);
        $json = json_encode($xml);
        return $json;
    }

    /**
     * @param $currency_name
     * @return float|null
     */
    public function getCurrency($currency_name) {
        $array = json_decode($this->json, true);

        foreach($array['pozycja'] as $entry) {
            if(strtolower($entry['kod_waluty']) === strtolower($currency_name)) {
                return convert2Float($entry['kurs_sredni']);
            }
        }
        return null;
    }

    /**
     * @param string $currency
     * @param null $date
     * @return float|null|string
     */
    public function getCurrencyForDate($currency = 'EUR', $date = null) {
        $this->_getDir();
        if($date === null) {
            $date = Carbon::now()->format('ymd');
        }
        if($this->_getXml($date)) {
            return $this->getCurrency($currency);
        }
        return 'There is no currency value for that specific day.';
    }

    /**
     * @param $currency_names
     * @return array
     */
    public function getCurrencies($currency_names) {
        $array = json_decode($this->json, true);
        $date = $array['data_publikacji'];
        $output = [$date => []];

        $slugs = [];
        foreach($array['pozycja'] as $entry) {
            $currency = strtolower($entry['kod_waluty']);
            $slugs[] = $currency;
            if(in_array($currency, $currency_names)) {
                $output[$date][$currency] = convert2Float($entry['kurs_sredni']);
            }
        }
        print_r($slugs);
        return $output;
    }

    public function getCurrenciesForDate($currency_names, $date = null) {
        if ($date === null) {

        }
    }
    /*
     * Get list of currency values in period of time.
     *
     * $currency_name is slug for currency name
     * $from and $to are dates in ymd format
     *
     * @param string $currency_name
     * @param string $from
     * @param string $to
     *
     * @return array
     */
    public function getCurrencyForRange($currency_name = 'EUR', $from = null, $to = null) {
        $result = [];
        $this->_getDir();

        try {
            $from = Carbon::createFromFormat('ymd', $from)->second(0);
            $to = Carbon::createFromFormat('ymd', $to)->second(0);
        }
        catch (InvalidArgumentException $e) {
            return "Unsupported date format passed.";
        }

        if($from and $to) {
            $pattern = '/^a\d{3}.(\d{6})/';

            foreach($this->dir as $line_num => $line) {
                if(preg_match($pattern, $line, $matches)) {

                    // Get date from the file name
                    $date = Carbon::createFromFormat('ymd', $matches[1] . '')->second(0);

                    // Check if date is in range we're looking for
                    if($date->gte($from) && $date->lte($to)) {
                        // Get the data for given day in range.
                        $this->_getXml($date->format('ymd'));
                        $result[$date->format('Y-m-d')] = $this->getCurrency($currency_name);
                    }

                }
            }
        }

        return $result;
    }
}
