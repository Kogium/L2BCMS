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
if ( !class_exists('session') )
{
    class session extends SessionHandler
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
         * @since 1.1
         * @var bool|mixed
         */
        protected $httpsecure = false;

        /**
         * SESSION STATUS
         * @since 1.1
         * @var bool|mixed
         */
        protected $sessionstatus = false;

        protected $name;

        protected $cookie;

        private $HandleCookie;

        public function __construct($config=NULL, $name=NULL)
        {
            try
            {
                // Check config format
                if (!is_array($config))
                    throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),8); //die('CLASS ERROR (Cookie): Config must be an array!');

                if (!extension_loaded('openssl'))
                {
                    throw new \RuntimeException(sprintf(
                        "You need the OpenSSL extension to use %s",
                        __CLASS__
                    ));
                }
                if (!extension_loaded('mbstring'))
                {
                    throw new \RuntimeException(sprintf(
                        "You need the Multibytes extension to use %s",
                        __CLASS__
                    ));
                }

                // REQUIRED SETTING
                if (!isset($config['key']))
                    throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),8); //die('CLASS ERROR (Cookie): Secret key must be set!');
                else
                    if(is_string($config['key'])) //SECURITE ARGUMENT
                        self::$key = $config['key'];
                    else
                        throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),8);

                // OPTIONAL SETTINGS
                if (isset($name) && !is_null($name))
                    $this->name = $name;
                else
                    $this->name = (isset($config['name'])) ? $config['name'] : '_SESSION';

                //COOKIE SETTING
                if (isset($config['expire'])) $this->cookie['lifetime'] = $config['expire'];
                if (isset($config['path'])) $this->cookie['path'] = $config['path'];
                if (isset($config['inactive'])) $this->cookie['inactive'] = $config['inactive'];
                if (isset($config['calg'])) self::$calg = $config['calg'];
                if (isset($config['mode'])) self::$mode = $config['mode'];
                if (isset($config['hi_c'])) self::$hi_c = $config['hi_c'];
                if (isset($config['ssl'])) self::$ssl = $config['ssl'];
                if (isset($config['halg'])) self::$halg = $config['halg'];

                // Load mcrypt module
                self::$cmod = mcrypt_module_open(self::$calg, '', self::$mode, '');
                if (self::$cmod === false)
                    throw new engine_error($lang->_('TS_BAD_ENV'),9); //die('CLASS ERROR (SESSION): Error loading mcrypt module');

                /** Automatisation des déclarations en fonction de l'environnement*/
                $this->domain_name = $this->get_current_domain();
                $this->httpsecure = $this->get_ssl_status();
                $this->sessionstatus = $this->get_session_status();

                ini_set('session.use_cookies', 1);
                ini_set('session.use_only_cookies', 1);

                ini_set('session.gc_maxlifetime', 0 );
                session_cache_expire( 0 );

                session_name($this->name);
                session_set_cookie_params(
                    $this->cookie['lifetime'],
                    $this->cookie['path'],
                    $this->domain_name,
                    $this->httpsecure,
                    $this->sessionstatus
                );

                //Agrégat Cookie
                $this->HandleCookie = new cookie($config);
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

        public function get_session_id()
        {
            if (!$this->get_session_status())
                return '';

            return session_id();
        }

        public function get_session_expire()
        {
            if (!$this->get_session_status())
                return 0;

            if (! $this->isExpired())
                return isset($_SESSION['_expire'])
                ? $_SESSION['_expire']
                : 0;
            else
                return 0;
        }

        public function get_session_last_activity()
        {
            if (!$this->get_session_status())
                return 0;

            if (! $this->isInnactive())
                return isset($_SESSION['_last_activity'])
                    ? $_SESSION['_last_activity']
                    : 0;
            else
                return 0;
        }

        /**************************************************************************
         * GET CURRENT DOMAIN ( )
         *
         * Reports whether or not The local name used
         * @since 1.1
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
         * @since 1.1
         * @access private
         * @return mixed                TRUE if session is ready, FALSE if not session is ready
         * /**************************************************************************/
        public function get_session_status()
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
         * @since 1.1
         * @access private
         * @return mixed                TRUE if SSL is used, FALSE if not used
         * /**************************************************************************/
        private function get_ssl_status()
        {
            if ( isset($_SERVER['HTTPS']) )
            {
                if ( 'on' == strtolower($_SERVER['HTTPS']) )
                    return true;
                if ( '1' == $_SERVER['HTTPS'] )
                    return true;
            }
            elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) )
                return true;

            return false;
        }
        public function start()
        {
            if (!$this->get_session_status())
                if (session_start())
                    if($this->isValid())
                        if($this->isInnactive())
                            return $this->refresh();
                        else
                            return true;
                    else
                        return false;
                else
                    return false;
            else
                return false;
        }
        public function forget()
        {
            if (!$this->get_session_status())
                return false;

            $this->HandleCookie->delete_cookie($this->name);

            $result=session_destroy();
            unset($_SESSION);

            return $result;
        }
        public function refresh()
        {
            return session_regenerate_id(true);
        }
        public function read($id)
        {
            return self::get_secure_value(parent::read($id));
        }
        public function write($id, $data)
        {
            return parent::write($id, self::set_secure_value($data, $id, $this->get_session_expire()));
        }
        public function close()
        {
            return parent::close();
        }
        public function create_sid()
        {
            return parent::create_sid();
        }
        public function destroy($id)
        {
            return parent::destroy($id);
        }
        public function gc($maxlifetime)
        {
            return parent::gc($maxlifetime);
        }
        public function open($save_path, $session_name)
        {
            return parent::open($save_path, $session_name);
        }
        public function isInnactive($ttl = NULL)
        {
            if (is_null($ttl))
            {
                if (isset($this->cookie['inactive']))
                    $ttl = $this->cookie['inactive'];
                else
                    $ttl = 60; //1 heure par défaut
            }

            $last = isset($_SESSION['_last_activity'])
                ? $_SESSION['_last_activity']
                : false;

            if ($last !== false)
                if (time() - $last > $ttl * 60)
                    return true;

            $_SESSION['_last_activity'] = time();
            return false;
        }
        public function isExpired($expire = NULL)
        {
            if(isset($_SESSION['_expire']))
            {
                $last = $_SESSION['_expire'];
                if (is_null($expire))
                    $expire = $last;
            }
            else
            {
                $last=false;
                if (is_null($expire))
                {
                    if (isset($this->cookie['lifetime']))
                        $expire = $this->cookie['lifetime'];
                    else
                        $expire = time() + 3600; //1 heure par défaut
                }
            }

            $last = isset($_SESSION['_expire'])
                ? $_SESSION['_expire']
                : false;

            if ($last !== false)
                if ($expire < time())
                    return true;

            if ($last == false)
                $_SESSION['_expire'] = $expire;

            return false;
        }
        public function isFingerprint()
        {
            $hash = md5(
                $_SERVER['HTTP_USER_AGENT'] .
                (ip2long($_SERVER['REMOTE_ADDR']) & ip2long('255.255.0.0'))
            );
            if (isset($_SESSION['_fingerprint'])) {
                return $this->hash_equals($_SESSION['_fingerprint'], $hash);
            }
            $_SESSION['_fingerprint'] = $hash;
            return true;
        }
        public function isValid()
        {
            return ! $this->isInnactive() && ! $this->isExpired() && $this->isFingerprint();
        }

        public function session_exists($name)
        {
            return isset($_COOKIE[$name]);
        }
        public function delete_data($name)
        {
            if (!$this->get_session_status())
                return false;

            unset($_SESSION[$name]);
            return self;
        }
        public function get_data($name)
        {
            if (!$this->get_session_status())
                return false;

            $parsed = explode('.', $name);
            $result = $_SESSION;
            while ($parsed) {
                $next = array_shift($parsed);
                if (isset($result[$next])) {
                    $result = $result[$next];
                } else {
                    return null;
                }
            }

            // secure cookie value
            $result = self::get_secure_value($result);

            return $result;
        }
        public function put_data($name, $value)
        {
            if (!$this->get_session_status())
                return false;

            // secure cookie value
            $value = self::set_secure_value($value, $this->get_session_id(), $this->get_session_expire());

            $parsed = explode('.', $name);
            $session =& $_SESSION;
            while (count($parsed) > 1) {
                $next = array_shift($parsed);
                if ( ! isset($session[$next]) || ! is_array($session[$next])) {
                    $session[$next] = [];
                }
                $session =& $session[$next];
            }
            $session[array_shift($parsed)] = $value;
            return self;
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
        public function get_secure_value($value)
        {
             // get cookie data
            $values = explode('|', $value);

            if ((count($values) === 4) && ($values[1] == 0 || $values[1] >= time()))
            {
                // prepare cookie data
                $key = hash_hmac(self::$halg, $values[0] . $values[1], self::$key);
                $session_data = base64_decode($values[2]);

                if (self::get_hi_c() && !empty($session_data))
                    $data = $this->decrypt($session_data, $key, md5($values[1]));
                else
                    $data = $session_data;

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

            // return false
            return false;
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
        protected function set_secure_value($value, $cid, $expire)
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

        /**
         * Hash equals function for PHP 5.5+
         * @since 1.1
         * @param string $expected
         * @param string $actual
         * @return bool
         */
        protected function hash_equals($expected, $actual)
        {
            $expected     = (string) $expected;
            $actual       = (string) $actual;
            if (function_exists('hash_equals')) {
                return hash_equals($expected, $actual);
            }
            $lenExpected  = mb_strlen($expected, '8bit');
            $lenActual    = mb_strlen($actual, '8bit');
            $len          = min($lenExpected, $lenActual);
            $result = 0;
            for ($i = 0; $i < $len; $i++) {
                $result |= ord($expected[$i]) ^ ord($actual[$i]);
            }
            $result |= $lenExpected ^ $lenActual;
            return ($result === 0);
        }
    }
}
else
{
    throw new engine_error($lang->_('TS_INJECTION_ENGINE'),10);
}
?>