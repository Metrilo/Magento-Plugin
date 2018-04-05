<?php
class Metrilo_Analytics_Helper_Requestclient extends Mage_Core_Helper_Abstract
{
    public function post($url, $bodyArray = false)
    {
        $encodedBody = $bodyArray ? json_encode($bodyArray) : '';
        $parsedUrl = parse_url($url);
        $headers = [
            'Content-Type: application/json',
            'Accept: */*',
            'User-Agent: RequestClient/2.0.0',
            'Connection: Close',
            'Host: '.$parsedUrl['host']
        ];

        return $this->curlCall($url, $headers, $encodedBody, 'POST');
    }

    private function curlCall($url, $headers = [], $body = '', $method)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_COOKIESESSION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, 2000);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);

        $response = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        return array(
            'response' => $response,
            'code' => $code
        );
    }
}