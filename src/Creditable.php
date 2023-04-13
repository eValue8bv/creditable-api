<?php

namespace Creditable;

class CreditablePayWall
{
    private const ApiServer = "https://api.creditable.news/";
    private $apiKey;

    public function __construct($apiKey)
    {
        $this->setApiKey($apiKey);
    }

    /**
     * @param string $apiKey The Creditable API key
     * @throws Exception if empty
     */
    private function setApiKey($apiKey)
    {
        $apiKey = trim($apiKey);
        $this->apiKey = $apiKey;
    }

    /**
     * @param array $data Associative array containing POST values.
     * @return CreditableResponse|boolean
     * @throws Exception If the request fails.
     */
    public function check($data = [])
    {
        if (!function_exists('curl_version') && !ini_get('allow_url_fopen')) {
            throw new Exception("Curl and allow_url_fopen are both disabled");
        }

        if (function_exists('curl_version')) {
            [$status, $response] = $this->curlCheckPaid($data);
        } else {
            [$status, $response] = $this->fgcCheckPaid($data);
        }

        if ($status !== 200) {
            return false;
        }

        $response = json_decode($response);
        return new CreditableResponse($response);
    }

    /**
     * Send a POST request without using PHP's curl functions.
     *
     * @param array $data Associative array containing POST values.
     * @return array The output response.
     * @throws Exception If the request fails.
     */
    private function fgcCheckPaid($data = [])
    {
        $endpoint = self::ApiServer . "/credit/check.php";
        $data['apikey'] = $this->apiKey;
        $data = json_encode($data);

        $streamOptions = [
            'http' =>
                [
                    'method' => 'POST', //We are using the POST HTTP method.
                    'header' => ['Content-Type: application/json' . "\r\n"
                        . 'Content-Length: ' . strlen($data) . "\r\n"],
                    'content' => $data, //Our URL-encoded query string.
                    "ignore_errors" => true
                ]
        ];

        $streamContext = stream_context_create($streamOptions);
        if (!$response = file_get_contents($endpoint, false, $streamContext)) {
            throw new Exception("Failed to open stream");
        }

        $status_line = $http_response_header[0];
        preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
        $status = $match[1];

        return [$status, $response];
    }

    /**
     * Send a POST request using PHP's curl functions.
     *
     * @param array $data Associative array containing POST values.
     * @return array The output response.
     * @throws Exception If the request fails.
     */

    private function curlCheckPaid(array $data = [])
    {
        //Add the apikey to the data
        $data['apikey'] = $this->apiKey;

        //Transform our POST array into a json string.
        $data = json_encode($data);

        // set the endpoint
        $endpoint = self::ApiServer . "/credit/check.php";

        // Create a curl handle
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        // add referrer
        curl_setopt($curl, CURLOPT_REFERER, $_SERVER['HTTP_HOST']);
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);

        curl_setopt($curl, CURLOPT_URL, $endpoint);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        if (!$response = curl_exec($curl)) {
            curl_close($curl);
            throw new Exception(curl_errno($curl) . " CURL Failed ");
        }

        // Set default error to 400.
        $status = 400;
        if (!curl_errno($curl)) {
            $info = curl_getinfo($curl);
            $status = $info['http_code'];
        }

        curl_close($curl);
        return [$status, $response];
    }

    public function getJsDependency(): string
    {
        return "https://partner.creditable.news/paywall/js/creditable.js";
    }

    public function getCssDependency(): string
    {
        return "https://partner.creditable.news/paywall/css/creditable.css";
    }

    public static function slugify($text, string $divider = '-')
    {
        // replace non letter or digits by divider
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, $divider);

        // remove duplicate divider
        $text = preg_replace('~-+~', $divider, $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}
