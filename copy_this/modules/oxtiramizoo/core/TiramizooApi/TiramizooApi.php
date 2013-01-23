<?php
/**
 * Pure class used for sending requests to the API via cURL.
 *
 * @package: oxTiramizoo
 */
class TiramizooApi 
{
    /**
     * API url
     *             
     * @var string
     */
    protected $api_url = null;

    /**
     * API token
     *             
     * @var string
     */    
    protected $api_token = null;

    /**
     * Construct the object with api key and url
     * @param string $api_url   API url
     * @param string $api_token API token to authenticate
     */
    protected function __construct($api_url, $api_token) 
    {
        $this->api_url = $api_url;
        $this->api_token = $api_token;
    }
    
    /**
     * Build http connection to the API via cURL
     * 
     * @param  string  $path API path
     * @param  array   $data  Data to send
     * @param  boolean $result result
     * @return boolean Return true if success otherwise false
     */
    public function request($path, $data = array(), &$result = false) 
    {
        $c = curl_init();

        //@todo: set 1 before launch
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($c, CURLOPT_URL, $this->api_url.'/'.$path.'?api_token='. $this->api_token);
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, preg_replace_callback('/(\\\u[0-9a-f]{4})/', array($this, "json_unescape"), json_encode($data)));

        curl_setopt($c, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Accept: application/json"
        ));

        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($c);
        $status = curl_getinfo($c, CURLINFO_HTTP_CODE);

        $result = array('http_status' => $status, 'response' => json_decode($response));

        curl_close($c);
    }   
    
    /**
     * Build http connection to the API via cURL
     * 
     * @param  string  $path API path
     * @param  array   $data  Data to send
     * @param  boolean $result result
     * @return boolean Return true if success otherwise false
     */
    public function requestGet($path, $data = array(), &$result = false) 
    {
        $c = curl_init();

        //@todo: set 1 before launch
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($c, CURLOPT_URL, $this->api_url.'/'.$path.'?api_token='. $this->api_token . '&' . http_build_query($data));

        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($c);
        $status = curl_getinfo($c, CURLINFO_HTTP_CODE);

        $result = array('http_status' => $status, 'response' => json_decode($response));

        curl_close($c);
    }   

    /**
     * Unescape json items
     * 
     * @param  string $m Element's value
     * @return string unescaped value
     */
    protected function json_unescape($m) 
    {
        return json_decode('"'.$m[1].'"');
    }
}