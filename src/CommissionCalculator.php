<?php

namespace roman;

class CommissionCalculator
{
    public function calculateCommissionsFromFile($filePath)
    {
        $rows = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($rows as $row) {
            $data = explode(',', $row);
            $bin = trim(explode(':', $data[0])[1], '"');
            $amount = (float)trim(explode(':', $data[1])[1], '"');
            $currency = trim(explode(':', $data[2])[1], '"}');

            $countryCode = $this->getCountryCodeFromBin($bin);
            $isEu = $this->isEu($countryCode);
            $rate = $this->getExchangeRate($currency);
            // echo 'current rate: '.$rate;

            $commission = $this->calculateCommission($amount, $isEu, $rate, $currency);
            $result = $this->ceilToCent($commission);

            echo $result."\n";
        }
    }

    private function getCountryCodeFromBin($bin)
    {
        // $curl_handle = curl_init();
        // curl_setopt($curl_handle, CURLOPT_URL, 'https://lookup.binlist.net/' . $bin);
        // curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        // curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Calculate Commission fee');
        // $query = curl_exec($curl_handle);
        $binResults = file_get_contents('https://lookup.binlist.net/' . $bin);
        if (!$binResults) {
            throw new \Exception('Error retrieving BIN information.');
        }

        $result = json_decode($binResults);
        return $result->country->alpha2;
        // curl_close($curl_handle);
    }

    private function isEu($countryCode)
    {
        $euCountries = [
            'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI',
            'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT',
            'NL', 'PO', 'PT', 'RO', 'SE', 'SI', 'SK'
        ];

        return in_array($countryCode, $euCountries) ? 'yes' : 'no';
    }

    private function getExchangeRate($currency)
    {
        $exchangeRates = json_decode(file_get_contents('https://developers.paysera.com/tasks/api/currency-exchange-rates'), true)['rates'];
        return $currency === 'EUR' ? 1 : $exchangeRates[$currency];
    }

    private function calculateCommission($amount, $isEu, $rate, $currency)
    {
        $amntFixed = $currency === 'EUR' || $rate == 0 ? $amount : $amount / $rate;
        // if (!is_int($amntFixed) && strlen($amntFixed) < 10) {
        //     // If not an integer and length is less than 10, format with the current number of decimal places
        //     $formattedResult = rtrim(sprintf('%.10f', $amntFixed / 100), '0'); // Remove trailing zeros
        // } else {
        //     $formattedResult = $amntFixed; // If integer or length is 10 or more, display as it is
        // }
        return $amntFixed * ($isEu == 'yes' ? 0.01 : 0.02);
    }

    private function ceilToCent($amount)
    {
        return round($amount, 9);
    }
}

$commissionCalculator = new CommissionCalculator();
$commissionCalculator->calculateCommissionsFromFile('./input.txt');