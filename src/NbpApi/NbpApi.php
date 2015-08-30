<?php

namespace NbpApi;

use Carbon\Carbon;
use InvalidArgumentException;

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

    /*
     * URLs used in API.
     *
     * 'dir' - file containing all the name of the files for last year
     * 'dirY' - files containing all file names for specific year (will manage it with regex)
     * 'lastA' - xmls file containing last average exchange rates
     * 'xml' - just a basic url for retreiving all the data e.g. http://www.nbp.pl/kursy/xml/[filename].xml, where
     * specific filename of an xml is retrieved from dir.txt
     */
    private $_urls = [
        'dir' => 'http://www.nbp.pl/kursy/xml/dir.txt',
        'dirY' => 'http://www.nbp.pl/kursy/xml/dir[date].txt',
        'lastA' => 'http://www.nbp.pl/kursy/xml/LastA.xml',
        'xml' => 'http://www.nbp.pl/kursy/xml/'
    ];

    /*
     * Messages returned where encountering an error.
     * Maybe I'll throw an Exception later.
     */
    private $_messages = [
        'value_not_found' => 'There is no currency value for that specific day.',
        'values_not_found' => 'There are no data for given currency names or specific day.',
        'unsupported_date_format' => 'Unsupported date format passed.'
    ];

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
        $output = [$date => $this->_getCurrency($currency_name)];

        return $output;

    }


    /**
     * Returns last currency values and date for given currency names.
     *
     * @param $currencies_names
     * @return array
     */
    public function lastCurrenciesValues($currencies_names) {
        $xml = file_get_contents($this->_urls['lastA']);
        $this->json = $this->_parseXml($xml);

        return $this->_getCurrencies($currencies_names);
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
     * Returns currency value for given date.
     *
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
            return $this->_getCurrency($currency);
        }
        return $this->_messages['value_not_found'];
    }


    /**
     * Returns currency values for given currency names and date.
     *
     * @param $currency_names
     * @param null $date
     * @return array
     */
    public function getCurrenciesForDate($currency_names = [], $date = null) {
        $this->_getDir();

        if ($date === null) {
            $date = Carbon::now()->format('ymd');
        }
        //
        if($this->_getXml($date)) {
            return $this->_getCurrencies($currency_names);
        }
        return $this->_messages['values_not_found'];

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
            return $this->_messages['unsupported_date_format'];
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
                        $result[$date->format('Y-m-d')] = $this->_getCurrency($currency_name);
                    }

                }
            }
        }

        return $result;
    }


    /**
     * Returns currency values for specific date range.
     *
     * @param array $currency_names
     * @param null $from
     * @param null $to
     * @return array
     */
    public function getCurrenciesForRange($currency_names = [], $from = null, $to = null) {
        $result = [];
        $this->_getDir();

        try {
            $from = Carbon::createFromFormat('ymd', $from)->second(0);
            $to = Carbon::createFromFormat('ymd', $to)->second(0);
        }
        catch (InvalidArgumentException $e) {
            return $this->_messages['unsupported_date_format'];
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
                        $result[$date->format('Y-m-d')] = $this->_getCurrencies($currency_names);
                    }

                }
            }
        }

        return $result;
    }

    private function _getFile($file) {
        $file = file($file);
        return $file;
    }

    /**
     * Get dir method wrapper.
     */
    private function _getDir() {
        $this->dir = $this->_getFile($this->_urls['dir']);
    }

    /**
     * Get XML with currency data for specific date.
     * Returns true if xml was found or false respectively if not.
     *
     * @param null $date
     * @return bool
     */
    private function _getXml($date = null) {
        if(!$date) {
            $date = Carbon::now()->format('ymd');
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
     * Return currency value for given currency name.
     *
     * @param $currency_name
     * @return float|null
     */
    private function _getCurrency($currency_name) {
        $array = json_decode($this->json, true);

        foreach($array['pozycja'] as $entry) {
            if(strtolower($entry['kod_waluty']) === strtolower($currency_name)) {
                return convert2Float($entry['kurs_sredni']);
            }
        }
        return null;
    }

    /**
     * Returns currency values for given currency names.
     *
     * @param array $currency_names
     * @return array
     */
    private function _getCurrencies($currency_names) {
        $array = json_decode($this->json, true);
        $date = $array['data_publikacji'];
        $output = [$date => []];

        foreach($array['pozycja'] as $entry) {
            $currency = strtolower($entry['kod_waluty']);
            if($currency_names === []) {
                $output[$date][$currency] = convert2Float($entry['kurs_sredni']);
            }
            elseif(in_array($currency, $currency_names)) {
                $output[$date][$currency] = convert2Float($entry['kurs_sredni']);
            }
        }

        return $output;
    }
}

function convert2Float($number) {
    return (float) preg_replace('/,/', '.', $number);
}