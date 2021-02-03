<?php

/**
*
* @author Damián Curcio
* @version 1.2
*
**/

abstract class CurlCaller {
    public static $curlError;
    public static $curlInfo;
    public static $curlResponse;
    public static $settings = [
        'acceptCharset'     => 'UTF-8',
        'contentType'       => '', // application/json | application/x-www-form-urlencoded | multipart/form-data
        'userAgent'         => '',
        'returnTransfer'    => true,
        'sslVerifypeer'     => false,
        'sslVerifyhost'     => false,
        'connectTimeout'    => 3, // seg
        'queryTimeout'      => 30, // seg
        'verbose'           => false,
        'basicAuth'         => [],
        'headers'           => []
    ];

	private static function call($url, $metodo, $params){
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

        if(is_array($params)){
            if(strtoupper($metodo) == 'POST'){
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            }else if($params){
                curl_setopt($ch, CURLOPT_URL, $url.'?'.http_build_query($params));
            }
        }else{
            
            // si es un json va al body

            self::$settings['contentType'] = (!self::$settings['contentType']) ? 'application/json' : self::$settings['contentType'];

            curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
        }

        $headersList = [];

        if(self::$settings['acceptCharset']) $headersList[] = "Accept-Charset: ".self::$settings['acceptCharset'];
        if(self::$settings['userAgent']) $headersList[] = "User-Agent: ".self::$settings['userAgent'];
        if(self::$settings['contentType']) $headersList[] = "Content-Type: ".self::$settings['contentType'];

        $headersList += self::$settings['headers'];
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headersList);

        if(self::$settings['basicAuth'] && is_array(self::$settings['basicAuth'])){
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);     
            curl_setopt($ch, CURLOPT_USERPWD, self::$settings['basicAuth'][0].':'.self::$settings['basicAuth'][1]);
        }

        self::$curlResponse = curl_exec($ch);
        
        self::$curlError = curl_error($ch);
        self::$curlInfo = curl_getinfo($ch);
        
        $httpcode = self::$curlInfo['http_code'];

        curl_close($ch);

        if(self::$curlError){		
            throw new Exception("CURL ERROR [URL: $url METODO: $metodo CODE: $httpcode ERROR: ".self::$curlError."]");
        }
        
        if($httpcode >= 400 || $httpcode == 0){		
            throw new Exception("HTTP ERROR [URL: $url METODO: $metodo CODE: $httpcode ERROR: ".self::$curlError."]");
        }

        return (self::$settings['contentType'] == 'application/json') ? @json_decode(self::$curlResponse,true) : self::$curlResponse;    
    }

    public static function get($url, $params = null){
        return self::call($url, 'GET', $params);
    }

    public static function post($url, $params = null){
        return self::call($url, 'POST', $params);
    }

    public static function put($url, $params = null){
        return self::call($url, 'PUT', $params);
    }

    public static function patch($url, $params = null){
        return self::call($url, 'PATCH', $params);
    }

    public static function delete($url, $params = null){
        return self::call($url, 'DELETE', $params);
    }
}