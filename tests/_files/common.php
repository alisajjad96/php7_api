<?php

if (!function_exists('getAuthorizationHeader')):
    function getAuthorizationHeader(){
        $config = \PHP7API\App\Config::config(null, 'auth');

        switch (strtolower($config['type'])):
            case \PHP7API\App\Auth::BASIC:
                $user = $config['username'] ?? '';
                $pass = $config['password'] ?? '';
                return "Authorization: Basic {$user}:{$pass}";
            case \PHP7API\App\Auth::BEARER:
                $token = $config['token'] ?? '';
                return "Authorization: Bearer {$token}";
            case \PHP7API\App\Auth::HEADER:
                $key = $config['key'] ?? '';
                $value = $config['value'] ?? '';
                return "{$key}: {$value}";
            case \PHP7API\App\Auth::DIGEST:
                $user = $config['username'] ?? '';
                $pass = $config['password'] ?? '';
                $realm = $config['realm'] ?? '';
                $nonce = uniqid();
                $opaque = md5($realm);
                return "Authorization: Digest realm=\"{$realm}\",qop=\"auth\",nonce=\"{$nonce}\",opaque=\"{$opaque}\"";
            case \PHP7API\App\Auth::NONE:
            default:
                return null;
        endswitch;
    }
endif;

if (!function_exists('getUrl')):
    function getUrl(){
        $protocol = (   isset($_SERVER["HTTPS"]) &&
        $_SERVER["HTTPS"] == 'on' ? "https://" : "http://");
        $serverName = $_SERVER["SERVER_NAME"];
        $port = '';
        if( $_SERVER["SERVER_PORT"] != '80' &&
            $_SERVER["SERVER_PORT"] != '443' ):
            $port = ':'. $_SERVER["SERVER_PORT"];
        endif;

        return $protocol.$serverName.$port;
    }
endif;

if (!function_exists('getResponseFromCurl')):
    function getResponseFromCurl($url, $data, $auth = null){
        $ch = curl_init();

        $curlData = http_build_query(array_merge([

        ], $data));

        $headers = [
            'Content-Type: application/json'
        ];
        if (!empty($auth)):
            $headers[] = $auth;
        endif;

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($data));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close ($ch);

        $response = json_decode($server_output, true);
        if (!empty($response['time'])):
            unset($response['time']);
        endif;
        return $response;
    }

endif;
