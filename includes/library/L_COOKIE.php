<?php
/**
 * @copyright (c) 2014-2018 Kogium.
 * @author Kogium <kogium@valkyrie-group.info>
 *
 * @licence CC BY-SA 4.0
 * @licence http://creativecommons.org/licenses/by-sa/4.0/deed.fr
 *
 */

/**
 * @param CONST INCLUDES_VALKYRIE VARIABLE GLOBAL DE SECURITE DU CMS
 * @throws exit if constant not declared
 * @uses SECURITY-READ-FILE
 */
defined('INCLUDES_VALKYRIE') ? INCLUDES_VALKYRIE : exit;

/* @ignore IF CLASS EXIST SECURITY */
if ( !class_exists('cookie') )
{
    /******************************************************************************
     * Cookie Class
     * @author BigOrNot_CookieManager
     * @author Mattieu Huguet
     * @author Mark A. LaDoux
     * @author Kogium
     * @version API 1.1
     * @version VALKYRIE 1.2
     * @package cookie
     * @link http://bigornot.blogspot.com/2008/06/securing-cookies-php-implementation.html BigOrNot_CookieManager
     * @link http://www.cse.msu.edu/%7Ealexliu/publications/Cookie/cookie.pdf A secure cookie scheme
    /******************************************************************************/
    class cookie
    {
        /**
         * Server secret key
         * @since 1.0
         * @var mixed|null
         */
        protected static $key = null;

        /**
         * Cryptographic algorithm used to encrypt cookie data
         * @since 1.1
         * @var mixed|string
         */
        protected static $calg = MCRYPT_RIJNDAEL_256;

        /**
         * Cryptographic mode
         * @since 1.1
         * @var mixed|string
         */
        protected static $mode = MCRYPT_MODE_CBC;

        /**
         * mcrypt module resource
         * @since 1.1
         * @var null|resource
         */
        protected static $cmod = null;

        /**
         * High confidentiality mode ( whether or not our cookie data is encrypted. )
         * @since 1.1
         * @var bool|mixed
         */
        protected static $hi_c = true;

        /**
         * SSL support
         * @since 1.1
         * @var bool|mixed
         */
        protected static $ssl = false;

        /**
         * Hash algorithm for HMAC
         * @since 1.1
         * @var mixed|string
         */
        protected static $halg = 'sha512';

        /**
         * Current domain name
         * @since 1.2
         * @var mixed|string
         */
        protected $domain_name = '';

        /**
         * HTTP SECURE ACTIVATED
         * @since 1.2
         * @var bool|mixed
         */
        protected $httpsecure = false;

        /**
         * SESSION STATUS
         * @since 1.2
         * @var bool|mixed
         */
        protected $sessionstatus = false;

        /**************************************************************************
         * Constructor
         * @since 1.1
         * @access public
         * @param  array $config Settings for the cookie class
         * @return null                Function does not return a result
         * /**************************************************************************/
        public function __construct($config = null)
        {
            GLOBAl $lang;

            try
            {
                // Check config format
                if (!is_array($config))
                    throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),8); //die('CLASS ERROR (Cookie): Config must be an array!');

                // REQUIRED SETTING
                if (!isset($config['key']))
                    throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),8); //die('CLASS ERROR (Cookie): Secret key must be set!');
                else
                    if(is_string($config['key'])) //SECURITE ARGUMENT
                        self::$key = $config['key'];
                    else
                        throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),8);

                // OPTIONAL SETTINGS
                if (isset($config['calg'])) self::$calg = $config['calg'];
                if (isset($config['mode'])) self::$mode = $config['mode'];
                if (isset($config['hi_c'])) self::$hi_c = $config['hi_c'];
                if (isset($config['ssl'])) self::$ssl = $config['ssl'];
                if (isset($config['halg'])) self::$halg = $config['halg'];

                // Load mcrypt module
                self::$cmod = mcrypt_module_open(self::$calg, '', self::$mode, '');
                if (self::$cmod === false)
                    throw new engine_error($lang->_('TS_BAD_ENV'),9); //die('CLASS ERROR (Cookie): Error loading mcrypt module');

                /** Automatisation des déclarations en fonction de l'environnement*/
                $this->domain_name = $this->get_current_domain();
                $this->httpsecure = $this->get_ssl_status();
                $this->sessionstatus = $this->get_session_status();
            }
            catch(engine_error $e)
            {
                unset($this); //SECURITY INTERPRETER AGAINST INJECTION
                __SHOW_ENGINE_EXCEPTION($e);
            }
            catch(Exception $e)
            {
                unset($this); //SECURITY INTERPRETER AGAINST INJECTION
                throw new engine_error($e->getMessage(),9,$e);
            }
        }

        /**************************************************************************
         * Get High Confidentiality Mode
         *
         * Reports whether High Confidentiality Mode is enabled or disabled.
         * @since 1.1
         * @access public
         * @return bool        TRUE if enabled, FALSE if not
         * /**************************************************************************/
        public function get_hi_c()
        {
            return self::$hi_c;
        }

        /**************************************************************************
         * Set High Confidentiality Mode ( Enabled by default )
         *
         * Turns cookie data encryption on and off
         * @since 1.1
         * @access public
         * @param  bool $enable TRUE to enable, FALSE to disable
         * @return mixed               returns self instance
         * /**************************************************************************/
        public function set_hi_c($enable)
        {
            // if format is invalid, do nothing and return instance
            if (!is_bool($enable)) return self;

            // set value
            self::$hi_c = $enable;

            // return instance
            return self;
        }

        /**************************************************************************
         * Get SSL Suport
         *
         * Reports whether or not SSL is enabled or disabled
         * @since 1.1
         * @access public
         * @return bool        TRUE if enabled, FALSE if disabled
         * /**************************************************************************/
        public function get_ssl()
        {
            return self::$ssl;
        }

        /**************************************************************************
         * Set SSL Support ( Disabled by default )
         *
         * Turns SSL support on or off.
         * @since 1.1
         * @access public
         * @param  bool $enable TRUE to enable, FALSE to disable
         * @return mixed               Returns self instance
         * /**************************************************************************/
        public function set_ssl($enable)
        {
            // if format is invalid, do nothing and return instance
            if (!is_bool($enable)) return self;

            // set value
            self::$ssl = $enable;

            // return instance
            return self;
        }

        /**************************************************************************
         * GET CURRENT DOMAIN ( )
         *
         * Reports whether or not The local name used
         * @since 1.2
         * @access private
         * @return string
         * /**************************************************************************/
        private function get_current_domain()
        {
            return $domain =
                isset($_SERVER['HTTP_X_FORWARDED_HOST']) ?
                    $_SERVER['HTTP_X_FORWARDED_HOST'] :
                    isset($_SERVER['HTTP_HOST']) ?
                        $_SERVER['HTTP_HOST'] :
                        $_SERVER['SERVER_NAME'];
        }

        /**************************************************************************
         * GET CURRENT SESSION STATUS ( )
         *
         * Reports whether or not starting session PHP
         * @since 1.2
         * @access private
         * @return mixed                TRUE if session is ready, FALSE if not session is ready
         * /**************************************************************************/
        private function get_session_status()
        {
            if ( php_sapi_name() !== 'cli' ) {
                if ( version_compare(phpversion(), '5.4.0', '>=') ) {
                    return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
                } else {
                    return session_id() === '' ? FALSE : TRUE;
                }
            }
            return FALSE;
        }

        /**************************************************************************
         * GET CURRENT SSL STATUS ( )
         *
         * Reports whether or not SSL is used
         * @since 1.2
         * @access private
         * @return mixed                TRUE if SSL is used, FALSE if not used
         * /**************************************************************************/
        private function get_ssl_status()
        {
            if ( isset($_SERVER['HTTPS']) ) {
                if ( 'on' == strtolower($_SERVER['HTTPS']) )
                    return true;
                if ( '1' == $_SERVER['HTTPS'] )
                    return true;
            }
            elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) )
            {
                return true;
            }
            return false;
        }

        /**************************************************************************
         * Set a secure cookie
         * @since 1.1
         * @access public
         * @param  string $name name of cookie
         * @param  string $value cookie data
         * @param  string $cid cookie ID
         * @param  int $expire cookie TTL
         * @param  string $path cookie path
         * @param  string $domain cookie domain
         * @param  bool $secure when TRUE, cookie requires an SSL connection
         * @param  bool $httponly when TRUE, cookie is only accessable through
         * HTTP protocol
         * @return mixed                function return TRUE OR NOT
         * /**************************************************************************/
        public function set_cookie(
            $name,
            $value,
            $cid,
            $expire = 0,
            $path = '/',
            $domain = NULL,
            $secure = NULL,
            $httponly = NULL
        )
        {
            //DECLARATION
            if(is_null($domain)) $domain = $this->domain_name;
            if(is_null($secure)) $secure = $this->httpsecure;
            if(is_null($httponly)) $httponly = $this->sessionstatus;

            // secure cookie value
            $secure_value = self::secure_cookie_value($value, $cid, $expire);

            // set the cookie
            return self::set_classic_cookie(
                $name,
                $secure_value,
                $expire,
                $path,
                $domain,
                $secure,
                $httponly
            );
        }

        /**************************************************************************
         * Delete a cookie
         * @since 1.1
         * @access public
         * @param  string $name name of cookie
         * @param  string $path cookie path
         * @param  string $domain cookie domain
         * @param  bool $secure When TRUE, cookies require an SSL connection
         * @param  bool $httponly When TRUE, cookie is only accessable through
         * HTTP protocol
         * @return null                function does not return a result
         * /**************************************************************************/
        public function delete_cookie(
            $name,
            $path = '/',
            $domain = NULL,
            $secure = NULL,
            $httponly = NULL
        )
        {
            // set expiration to 1980-01-01
            $expire = 315554400;

            //DECLARATION
            if(is_null($domain)) $domain = $this->domain_name;
            if(is_null($secure)) $secure = $this->httpsecure;
            if(is_null($httponly)) $httponly = $this->sessionstatus;

            // set cookie
            $query =  self::set_classic_cookie(
                $name,
                '',
                $expire,
                $path,
                $domain,
                $secure,
                $httponly
            );

            //UNSET CURRENT SESSION COOKIE
            unset($_COOKIE[$name]);

            return $query;
        }

        /**************************************************************************
         * Get secure cookie value
         *
         * Verifies the integrity of the cookie data and decrypts it.
         * If cookie is invalid, it can be automatically destroyed (default).
         * @since 1.1
         * @access public
         * @param  string $name name of cookie
         * @param  string $del_invalid destroy invalid cookies
         * @return mixed                   if valid, returns array, otherwise false
         * /**************************************************************************/
        public function get_cookie_value($name, $del_invalid = true)
        {
            // check if cookie exists
            if (!self::cookie_exists($name)) return false;

            $CookieData = json_decode( $_COOKIE[ $name ] );

            if ( !property_exists($CookieData, 'data') ) return false; //SECURITY INJECTION LEVEl 2

            // get cookie data
            $values = explode('|', $CookieData->data);

            if ((count($values) === 4) && ($values[1] == 0 || $values[1] >= time()))
            {
                // prepare cookie data
                $key = hash_hmac(self::$halg, $values[0] . $values[1], self::$key);
                $cookie_data = base64_decode($values[2]);
                if (self::get_hi_c())
                    $data = $this->decrypt($cookie_data, $key, md5($values[1]));
                else
                    $data = $cookie_data;

                // verify data
                if (self::$ssl && isset($_SERVER['SSL_SESSION_ID']))
                    $verify_key = hash_hmac(
                        self::$halg,
                        $values[0] . $values[1] . $data . $_SERVER['SSL_SESSION_ID'],
                        $key
                    );
                else
                    $verify_key = hash_hmac(
                        self::$halg,
                        $values[0] . $values[1] . $data,
                        $key
                    );

                if ($verify_key == $values[3]) return $data;
            }

            // Delete invalid cookies
            if ($del_invalid) self::delete_cookie($name);

            // Delete invalid cookies after expire date
            if( time() >= self::get_cookie_expire($name) ) self::delete_cookie($name);

            // return false
            return false;
        }

        /**************************************************************************
         * Get COOKIE DateTime
         *
         * @since 1.2
         * @access public
         * @param  string $name name of cookie
         * @return mixed                   if valid, returns time, otherwise false
         * /**************************************************************************/
        public function get_cookie_expire($name)
        {
            // check if cookie exists
            if (!self::cookie_exists($name)) return false;

            $CookieData = json_decode( $_COOKIE[ $name ] );

            if ( property_exists($CookieData, 'expire') )
                return $CookieData->expire;
            else
                return false; //SECURITY INJECTION LEVEl 2

            // Delete invalid cookies after expire date
            if( time() >= $CookieData->expire ) self::delete_cookie($name);
        }

        /**************************************************************************
         * Set a classic cookie ( unsecure )
         * @since 1.1
         * @access public
         * @param  string $name cookie name
         * @param  string $value cookie value
         * @param  int $expire cookie TTL
         * @param  string $path cookie path
         * @param  string $domain cookie domain
         * @param  bool $secure when TRUE, requires SSL connection
         * @param  bool $httponly when true, cookie only available over HTTP
         * protocol
         * @return null                function does not return anything
         * /**************************************************************************/
        function set_classic_cookie(
            $name,
            $value,
            $expire = 0,
            $path = '/',
            $domain = NULL,
            $secure = NULL,
            $httponly = NULL
        )
        {
            //DECLARATION
            if(is_null($domain)) $domain = $this->domain_name;
            if(is_null($secure)) $secure = $this->httpsecure;
            if(is_null($httponly)) $httponly = $this->sessionstatus;

            //correction lifetime
            if($expire == 0) $expire= time() + (365 * 24 * 60 * 60); //ADD 1 YEAR TO CURRENT TIME

            //GESTION DE LA DATE DU COOKIE
            $value = array( "data" => $value, "expire" => $expire );
            $value = json_encode( $value );

            // DEPRECIATED: for use with PHP < 5.2
            if ($httponly === false)
                $return = setcookie( $name, $value, $expire, $path, $domain, $secure );
            else
                $return = setcookie( $name, $value, $expire, $path, $domain, $httponly );

            //SET CURRENT SESSION COOKIE IF HTTPONLY
            IF ($return){ $_COOKIE[$name] = $value; }

            return $return;
        }

        /**************************************************************************
         * Check if cookie exists
         * @since 1.1
         * @access public
         * @param  $name       name of cookie to check
         * @return bool        TRUE if exists, FALSE if doesn't
         * /**************************************************************************/
        public function cookie_exists($name)
        {
            return isset($_COOKIE[$name]);
        }

        /**************************************************************************
         * Secure Cookie Value
         *
         * the initial value is transformed with this protocol :
         *
         * secure_value = cid|expire|base64((value)k,expire)|HMAC(cid|expire|value,k)
         * where k = HMAC(cid|expire, sk) and sk is server's secret key.
         *
         * (value)k,md5(expire) is the result of a cryptographic function
         * ( ie: AES256 ) on "value" with key k and initialization vector
         * md5(expire).
         * @since 1.1
         * @access protected
         * @param  string $value insecure value
         * @param  string $cid cookie id
         * @param  int $expire data TTL
         * @return string                  secured value
         * /**************************************************************************/
        protected function secure_cookie_value($value, $cid, $expire)
        {
            // generate key
            $key = hash_hmac(self::$halg, $cid . $expire, self::$key);

            // encrypt data
            if (self::get_hi_c())
                $secure_value = base64_encode(self::encrypt(
                    $value,
                    $key,
                    md5($expire)
                ));
            else
                $secure_value = base64_encode($value);

            // generate verification key
            if (self::$ssl && isset($_SERVER['SSL_SESSION_ID']))
                $verify_key = hash_hmac(
                    self::$halg,
                    $cid . $expire . $value . $_SERVER['SSL_SESSION_ID'],
                    $key
                );
            else
                $verify_key = hash_hmac(
                    self::$halg,
                    $cid . $expire . $value,
                    $key
                );

            // prepare data
            $result = array($cid, $expire, $secure_value, $verify_key);

            return implode('|', $result);
        }

        /**************************************************************************
         * Encrypt a given data with a given key and a given initialization vector
         * @since 1.1
         * @access protected
         * @param  string $data data to encrypt
         * @param  string $key secret key
         * @param  string $iv initialisation vector
         * @return string              encrypted data
         * /**************************************************************************/
        protected function encrypt($data, $key, $iv)
        {
            // prepare the initialization vectore
            $iv = self::validate_iv($iv);

            // prepare the secret key
            $key = self::validate_key($key);

            // encrypt data
            mcrypt_generic_init(self::$cmod, $key, $iv);
            $res = mcrypt_generic(self::$cmod, $data);
            mcrypt_generic_deinit(self::$cmod);

            // return encrypted data
            return $res;
        }

        /**************************************************************************
         * Decrypt a given data with a given key and a given initialization vector
         * @since 1.1
         * @access protected
         * @param  string $data data to decrypt
         * @param  string $key secret key
         * @param  string $iv initialization vector
         * @return string              decrypted data
         * /**************************************************************************/
        protected function decrypt($data, $key, $iv)
        {
            // prepare the initialization vector
            $iv = self::validate_iv($iv);

            // prepare the secret key
            $key = self::validate_key($key);

            // decrypt data
            mcrypt_generic_init(self::$cmod, $key, $iv);
            $decrypted_data = mdecrypt_generic(self::$cmod, $data);
            $res = str_replace("\x0", '', $decrypted_data);
            mcrypt_generic_deinit(self::$cmod);

            // return decrypted data
            return $res;
        }

        /**************************************************************************
         * Validate initialization vector
         *
         * If the given IV is too long for the selected mcrypt algorithm, it will
         * be truncated.
         * @since 1.1
         * @access protected
         * @param  string $iv Initialization vector
         * @return string              truncated initialization vector
         * /**************************************************************************/
        protected function validate_iv($iv)
        {
            // get IV size
            $iv_size = mcrypt_enc_get_iv_size(self::$cmod);

            // truncate IV
            if (strlen($iv) > $iv_size) $iv = substr($iv, 0, $iv_size);

            // return truncated IV
            return $iv;
        }

        /**************************************************************************
         * Validate key
         *
         * If the given secret key is too long for the selected mcrypt algorithm,
         * it will be truncated.
         * @since 1.1
         * @access protected
         * @param  string $key Secret key
         * @return string              Truncated secret key
         * /**************************************************************************/
        protected function validate_key($key)
        {
            // get key size
            $key_size = mcrypt_enc_get_key_size(self::$cmod);

            // truncate key
            if (strlen($key) > $key_size) $key = substr($key, 0, $key_size);

            // return truncated key
            return $key;
        }
    }
}
else
{
    throw new engine_error($lang->_('TS_INJECTION_ENGINE'),10);
}
?>