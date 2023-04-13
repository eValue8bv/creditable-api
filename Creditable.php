<?php

namespace Creditable;

class CreditablePayWall
{
    private const ApiServer = "https://api.creditable.news/";
    private $apiKey;
    public $paid;
    protected $result;
    public $uid;

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
     * @return string The output response.
     * @throws Exception If the request fails.
     */
    public function checkPaid($data = array()){
        if(function_exists('curl_version')){
            // use CURL
            $status = $this->curlCheckPaid($data);
        }
        elseif(ini_get('allow_url_fopen')){
            // use file_get_contents
            $status = $this->fgcCheckPaid($data);
        }
        else{
            throw new Exception("Failed to connect");
            $status = 500; // server error
        }

        if($status == 200) {
            $this->paid = true;
            $response = json_decode($result);
            $this->uid = $response['id'] ?? NULL;
            return true;
        }
        //echo "statuscode NOK". $status;
        $this->paid = false;
        $this->uid = NULL;
        return false;
    }

    /**
     * Send a POST request without using PHP's curl functions.
     *
     * @param array $data Associative array containing POST values.
     * @return string The output response.
     * @throws Exception If the request fails.
     */
    private function fgcCheckPaid($data = array())    {
        //Add the apikey to the data
        $data['apikey'] = $this->apiKey;

        //Transform our POST array into a json string.
        $data = json_encode($data);
        //Create an $options array that can be passed into stream_context_create.
        $streamOptions = array(
            'http' =>
                array(
                    'method' => 'POST', //We are using the POST HTTP method.
                    'header' => array('Content-Type: application/json' . "\r\n"
                        . 'Content-Length: ' . strlen($data) . "\r\n"),
                    'content' => $data, //Our URL-encoded query string.
                    "ignore_errors" => true
                )
        );
        // Note the use of "ignore_errors" => true in the http context map - this will prevent the function from throwing errors for non-2xx status codes.

        //Pass our $options array into stream_context_create.
        //This will return a stream context resource.
        $streamContext = stream_context_create($streamOptions);
        //Use PHP's file_get_contents function to carry out the request.
        //We pass the $streamContext variable in as a third parameter.

        // set the endpoint
        $endpoint = $this->apiServer. "/credit/check.php";

        if($this->result = file_get_contents($endpoint, false, $streamContext)) {
            /**
             * @var array $http_response_header materializes out of thin air
             */
            $status_line = $http_response_header[0];
            preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
            $status = $match[1];

        }
        else {
            throw new Exception("Failed to open stream");
            $status = 500; // server error
        }

        return $status;
    }

    /**
     * Send a POST request using PHP's curl functions.
     *
     * @param array $data Associative array containing POST values.
     * @return string The output response.
     * @throws Exception If the request fails.
     */

    private function curlCheckPaid($data = array())    {
        //Add the apikey to the data
        $data['apikey'] = $this->apiKey;

        //Transform our POST array into a json string.
        $data = json_encode($data);

        // set the endpoint
        $endpoint = $this->apiServer . "/credit/check.php";

        // Create a curl handle
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        // add referrer
        curl_setopt($curl, CURLOPT_REFERER, $_SERVER['HTTP_HOST']);
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);

        curl_setopt($curl, CURLOPT_URL, $endpoint);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        if ($this->result = curl_exec($curl)) {
            // Check if any error occurred
            $status = "";
            if (!curl_errno($curl)) {
                $info = curl_getinfo($curl);
                $status = $info['http_code'];
            }
            else {
                $status = 400; // bad request
                if(DEBUG){
                    echo $data;
                }
            }
        }
        else {
            throw new Exception(curl_errno($curl)." CURL Failed ");
            $status = 500; // server error
        }

        curl_close($curl);
        return $status;
    }

    public function getJsDependency() {
        $jsUrl = "https://partner.creditable.news/paywall/js/creditable.js";
        return $jsUrl;
    }

    public function getCssDependency() {
        $cssUrl = "https://partner.creditable.news/paywall/css/creditable.css";
        return $cssUrl;
    }

    public function slugify($text, string $divider = '-')
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
