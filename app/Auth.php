<?php


namespace PHP7API\App;

use PHP7API\Connection\MySql;

/**
 * Class Auth
 */
class Auth{

    /**
     * @var Base Configuration of Auth (configs.php)
     */
    protected $config;
    /**
     * @var string Auth Type mentioned in configs.php
     */
    protected $type;

    /**
     * All App Authorization Methods
     */
    public const NONE = 'none';
    public const BASIC = 'basic';
    public const BEARER = 'bearer';
    public const HEADER = 'header';
    public const DIGEST = 'digest';
    public const DATABASE_API = 'database_api_key';

    /**
     * @var bool Check for validated
     */
    protected $validated = false;

    /**
     * Auth constructor.
     */
    public function __construct(){
        $this->config = Config::config(null, 'auth');
        $this->config = arrayToObject($this->config);
        $this->type = $this->config->type
            ?? debugPrint('Auth Type is not given');
    }

    /**
     * Validates the Authorization using authorization methods
     *
     * @return bool
     */
    public function validate(){

        switch (strtolower($this->type)):
            case static::BASIC:
                return $this->validated = $this->validateBasicAuth();
            case static::BEARER:
                return $this->validated = $this->validateBearerAuth();
            case static::HEADER:
                return $this->validated = $this->validateHeaderKeyAuth();
            case static::DIGEST:
                return $this->validated = $this->validateDigestAuth();
            case static::DATABASE_API:
                return $this->validated = $this->validateDatabaseAuth();
            case static::NONE:
                return $this->validated = true;
        endswitch;

        return $this->validated = false;
    }

    /**
     * Get Authorization Header
     *
     * @return string|null
     */
    public function getAuthorizationHeader(){
        $header = null;
        if (isset($_SERVER['Authorization'])):
            $header = trim($_SERVER["Authorization"]);
        elseif (isset($_SERVER['HTTP_AUTHORIZATION'])): //Nginx or fast CGI
            $header = trim($_SERVER["HTTP_AUTHORIZATION"]);
        elseif (function_exists('apache_request_headers')):
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])):
                $header = trim($requestHeaders['Authorization']);
            endif;
        endif;
        return $header;
    }

    /**
     * Parses the Digest Authorization Header.
     *
     * @param string $digest
     * @return array|bool
     */
    public function digestParse(string $digest) {
        // protect against missing data
        $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
        $data = [];

        preg_match_all('@(\w+)=(?:(?:")([^"]+)"|([^\s,$]+))@', $digest, $matches, PREG_SET_ORDER);

        foreach ($matches as $m) :
            $data[$m[1]] = $m[2] ? $m[2] : $m[3];
            unset($needed_parts[$m[1]]);
        endforeach;

        return $needed_parts ? false : $data;
    }

    /**
     * Returns Authorization Token
     * e.g Basic, Bearer, Digest
     *
     * @param $type
     * @return mixed|null
     */
    public function getHeaderToken($type) {
        $headers = $this->getAuthorizationHeader();
        if (!empty($headers)) {
            if (preg_match("/{$type}\s(\S+)/", $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    /**
     * Returns Basic Token
     *
     * @return array
     */
    public function getBasicToken() {

        if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])):
            return [
                'username' => $_SERVER['PHP_AUTH_USER'] ?? '',
                'password' => $_SERVER['PHP_AUTH_PW'] ?? '',
            ];
        endif;

        $token = $this->getHeaderToken('Basic');
        $token = explode(':', base64_decode($token));

        return [
            'username' => $token[0]?? '',
            'password' =>  $token[1]?? '',
        ];
    }

    /**
     * Returns Digest Token
     *
     * @return array|bool
     */
    public function getDigestToken() {

        $digest = '';
        // mod_php
        if (isset($_SERVER['PHP_AUTH_DIGEST'])) {
            $digest = $_SERVER['PHP_AUTH_DIGEST'];
            // most other servers
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $digest = $this->getHeaderToken('Digest');
        }

        return $this->digestParse($digest);
    }

    /**
     * Validates Basic Authorization
     *
     * @return bool
     */
    protected function validateBasicAuth(){
        $token = $this->getBasicToken();
        if (empty($this->config->username) || empty($this->config->password)):
            debugPrint('Basic Auth requires username & password');
        endif;
        return $token['username'] === $this->config->username && $token['password'] === $this->config->password;
    }

    /**
     * Validates Bearer Authorization
     * @return bool
     */
    protected function validateBearerAuth(){
        $token = $this->getHeaderToken('Bearer');
        if (empty($this->config->token)):
            debugPrint('Bearer Auth requires token');
        endif;
        return $token === $this->config->token;
    }

    /**
     * Validates Header Authorization
     * @return bool
     */
    protected function validateHeaderKeyAuth(){

        $configKey = strtoupper($this->config->key);
        if (empty($configKey) || empty($this->config->value)):
            debugPrint('Header Auth requires key & value');
        endif;
        if (!isset($_SERVER["HTTP_{$configKey}"])):
            return false;
        endif;

        return $_SERVER["HTTP_{$configKey}"] === $this->config->value;
    }

    /**
     * Validates Digest Authorization
     * @return bool
     */
    protected function validateDigestAuth(){
        $token = $this->getDigestToken();

        $user = $this->config->username;
        $password = $this->config->password;
        $realm = $this->config->realm;
        if (empty($user) || empty($password) || empty($realm) ):
            debugPrint('Digest Auth requires username, password, realm');
        endif;

        if ($user !== $token['username']):
            return false;
        endif;

        $A1 = md5($user . ':' . $realm . ':' . $password);
        $A2 = md5($_SERVER['REQUEST_METHOD'].':'.$token['uri']);
        $valid_response = md5($A1.':'.$token['nonce'].':'.$token['nc'].':'.$token['cnonce'].':'.$token['qop'].':'.$A2);

        return $token['response'] === $valid_response;
    }

    /**
     * Validates Given Bearer Token from Database
     * @return bool
     */
    protected function validateDatabaseAuth(){
        $token = $this->getHeaderToken('Bearer');

        $tableName = $this->config->table_name ?? 'auth_api';
        $keyName = $this->config->key_field ?? 'api_key';

        $db = MySql::instance();
        $exists = $db->fetch("SELECT `{$keyName}` FROM `{$tableName}` 
                                WHERE `{$keyName}` = ?",
            [$token],
            's'
        );
        if (is_null($exists)):
            if ($db->getErrorNum() == 1146):
                debugPrint('Auth table does not exists');
            elseif ($db->getErrorNum() == 1054):
                debugPrint('Key column does not exists in database');
            else:
                debugPrint($db->getError());
            endif;
            return false;
        endif;

        return !$exists->isEmpty();
    }
}
