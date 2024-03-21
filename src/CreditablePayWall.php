<?php
/*
Copyright © 2022 eValue8 BV

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

To obtain a commercial license to this software or for more information,
please visit https://partner.creditable.news.
*/

namespace Creditable;

class CreditablePayWall
{
    private $ApiServer = "https://api.creditable.news";
    private $options = [];
    private $apiKey;

    /**
     * Constructs a new instance of the CreditablePayWall class.
     *
     * @param string $apiKey The Creditable API key.
     * @param array $options An optional array of configuration options. Possible keys:
     *                       'environment' - If set to 'dev', the API server will be set to the beta server.
     */
    public function __construct($apiKey, $options = [])
    {
        $this->options = $options;
        $this->setApiKey($apiKey);
        if (isset($options['environment']) && $options['environment'] === 'dev') {
            $this->setApiServer("https://api-beta.creditable.news");
        }
    }

    /**
     * @param string $apiKey The Creditable API key
     * @throws \Exception if empty
     */
    private function setApiKey($apiKey)
    {
        $apiKey = trim($apiKey);
        $this->apiKey = $apiKey;
    }

    /**
     * @param string $apiServer The Creditable API server
     */
    private function setApiServer($apiServer)
    {
        $this->ApiServer = $apiServer;
    }

    /**
     * @param array $data Associative array containing POST values.
     * @return CreditableResponse|boolean
     * @throws \Exception If the request fails.
     */
    public function check(array $data = []): CreditableResponse
    {
        if (!function_exists('curl_version') && !ini_get('allow_url_fopen')) {
            throw new \Exception("Curl and allow_url_fopen are both disabled");
        }

        if (function_exists('curl_version')) {
            [$status, $response] = $this->curlCheckPaid($data);
        } else {
            [$status, $response] = $this->fgcCheckPaid($data);
        }

        $response = json_decode($response, true);
        return new CreditableResponse($status, $response);
    }

    /**
     * Send a POST request without using PHP's curl functions.
     *
     * @param array $data Associative array containing POST values.
     * @return array The output response.
     * @throws \Exception If the request fails.
     */
    private function fgcCheckPaid(array $data = []): array
    {
        $endpoint = $this->ApiServer . "/credit/check.php";
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
            throw new \Exception("Failed to open stream");
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
     * @throws \Exception If the request fails.
     */

    private function curlCheckPaid(array $data = []): array
    {
        //Add the apikey to the data
        $data['apikey'] = $this->apiKey;

        //Transform our POST array into a json string.
        $data = json_encode($data);

        // set the endpoint
        $endpoint = $this->ApiServer . "/credit/check.php";

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
            throw new \Exception(curl_errno($curl) . " CURL Failed ");
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
        if (isset($this->options['environment']) && $this->options['environment'] === 'dev') {
            return "https://partner-beta.creditable.news/plugins/paywall/js/creditable.min.js";
        }
        return "https://partner.creditable.news/plugins/paywall/js/creditable.min.js";
    }

    public function getCssDependency(): string
    {
        if (isset($this->options['environment']) && $this->options['environment'] === 'dev') {
            return "https://partner-beta.creditable.news/plugins/paywall/css/creditable.min.css";
        }
        return "https://partner.creditable.news/plugins/paywall/css/creditable.min.css";
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
