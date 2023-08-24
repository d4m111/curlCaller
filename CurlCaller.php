<?php

/**
*
* @author Damián Curcio
* @version 1.8
*
**/

abstract class CurlCaller {
    private static $curlError;
    private static $curlInfo;
    private static $curlResponse;
    private static $settings = [
        'url'                   => '',
        'paramToJson'           => false,
        'responseJsonToArray'   => false,
        'acceptCharset'         => 'UTF-8',
        'contentType'           => '', // application/json | application/x-www-form-urlencoded | multipart/form-data
        'userAgent'             => '',
        'returnTransfer'        => true,
        'sslVerifypeer'         => false,
        'sslVerifyhost'         => false,
        'connectTimeout'        => 3, // seg
        'queryTimeout'          => 30, // seg
        'verbose'               => false,
        'basicAuth'             => ['user' => '', 'password' => ''],
        'headers'               => [],
        'httpMinErrorCode'      => 300
    ];

    public static function setSettings(array $settings){
        foreach($settings as $k=>$v){
            self::$settings[$k] = $v;
        }
    }

	public static function call(string $path, string $metodo, $params = null){
        $url = self::$settings['url'].$path;
        
        if(!$url || !$metodo) throw new Exception("[".__METHOD__."] Parametros Incorrectos");
        
        self::$curlError = "";
        self::$curlInfo = "";
        self::$curlResponse = "";

        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_SSL_VERIFYPEER => self::$settings['sslVerifypeer'],
            CURLOPT_SSL_VERIFYHOST => self::$settings['sslVerifyhost'],
            CURLOPT_RETURNTRANSFER => self::$settings['returnTransfer'],
            CURLOPT_CONNECTTIMEOUT => self::$settings['connectTimeout'],
            CURLOPT_TIMEOUT => self::$settings['queryTimeout'],
            CURLOPT_CUSTOMREQUEST => $metodo,
            CURLOPT_VERBOSE => self::$settings['verbose'],
        ));

        if(self::$settings['paramToJson'] !== true && is_array($params)){

            if(strtoupper($metodo) == 'POST'){
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            }else if($params){
                curl_setopt($ch, CURLOPT_URL, $url.'?'.http_build_query($params)); // rawurldecode(http_build_query($params)) // si quiero que no encodee las comas etc
            }

        }else if((self::$settings['paramToJson'] === true) && is_array($params)){

            curl_setopt($ch, CURLOPT_POSTFIELDS, @json_encode($params));

        }else if($params){

            curl_setopt($ch, CURLOPT_POSTFIELDS,$params);

        }

        if(self::$settings['responseJsonToArray'] === true){
            self::$settings['contentType'] = (!self::$settings['contentType']) ? 'application/json' : self::$settings['contentType'];
        }

        $headersList = [];

        if(self::$settings['acceptCharset']) $headersList[] = "Accept-Charset: ".self::$settings['acceptCharset'];
        if(self::$settings['userAgent']) $headersList[] = "User-Agent: ".self::$settings['userAgent'];
        if(self::$settings['contentType']) $headersList[] = "Content-Type: ".self::$settings['contentType'];

        if(is_array(self::$settings['headers'])){
            foreach(self::$settings['headers'] as $v){
                $headersList[] = $v;
            }  
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headersList);

        if(self::$settings['basicAuth'] && (self::$settings['basicAuth']['user'] || self::$settings['basicAuth']['password'])){
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);     
            curl_setopt($ch, CURLOPT_USERPWD, self::$settings['basicAuth']['user'].':'.self::$settings['basicAuth']['password']);
        }

        self::$curlResponse = curl_exec($ch);
        
        self::$curlError = curl_error($ch);
        self::$curlInfo = curl_getinfo($ch);

        curl_close($ch);
        
        $httpCode = self::$curlInfo['http_code'];

        if(self::$curlError){		
            throw new Exception("CURL ERROR [URL: $url METHOD: $metodo CODE: $httpCode ERROR: ".self::$curlError."]");
        }

        $httpMinErrorCode = (is_numeric(self::$settings['httpMinErrorCode'])) ? self::$settings['httpMinErrorCode'] : 300;
        
        return [
            'url' => $url,
            'httpCode' => $httpCode,
            'responseType' => ($httpCode < $httpMinErrorCode) ? 'success' : 'error',
            'response' => (self::$settings['responseJsonToArray'] === true) ? @json_decode(self::$curlResponse,true) : self::$curlResponse        
        ]; 
    }

    public static function getLastCurlError(){
        return self::$curlError;
    }

    public static function getLastCurlInfo(){
        return self::$curlInfo;
    }

    public static function get(string $url, $params = null, bool $ignoreNotFoundError = false){
        
        $r = self::call($url, 'GET', $params);

        if($r['responseType'] == 'error' && !($ignoreNotFoundError && $r['httpCode'] == 404)){
            $resp = (is_array($r['response'])) ? json_encode($r['response']) : $r['response'];

            throw new Exception("HTTP ERROR [URL: {$r['url']} METHOD: GET CODE: {$r['httpCode']} RESP: $resp ]");
        } 

        return $r['response'];
    }

    public static function post(string $url, $params = null){

        $r = self::call($url, 'POST', $params);

        if($r['responseType'] == 'error'){
            $resp = (is_array($r['response'])) ? json_encode($r['response']) : $r['response'];

            throw new Exception("HTTP ERROR [URL: {$r['url']} METHOD: POST CODE: {$r['httpCode']} RESP: $resp ]");
        }

        return $r['response'];
    }

    public static function put(string $url, $params = null){

        $r = self::call($url, 'PUT', $params);

        if($r['responseType'] == 'error'){
            $resp = (is_array($r['response'])) ? json_encode($r['response']) : $r['response'];

            throw new Exception("HTTP ERROR [URL: {$r['url']} METHOD: POST CODE: {$r['httpCode']} RESP: $resp ]");
        }

        return $r['response'];
    }

    public static function patch(string $url, $params = null){

        $r = self::call($url, 'PATCH', $params);

        if($r['responseType'] == 'error'){
            $resp = (is_array($r['response'])) ? json_encode($r['response']) : $r['response'];

            throw new Exception("HTTP ERROR [URL: {$r['url']} METHOD: PATCH CODE: {$r['httpCode']} RESP: $resp ]");
        }

        return $r['response'];
    }

    public static function delete(string $url, $params = null){

        $r = self::call($url, 'DELETE', $params);

        if($r['responseType'] == 'error'){
            $resp = (is_array($r['response'])) ? json_encode($r['response']) : $r['response'];

            throw new Exception("HTTP ERROR [URL: {$r['url']} METHOD: DELETE CODE: {$r['httpCode']} RESP: $resp ]");
        }

        return $r['response'];
    }
}