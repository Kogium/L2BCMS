<?php
/**
 * Module Name: vk_authenticator
 * Type Component: module
 * Module Title: Manageur d'authentification
 * Module URI: https://leroy.valkyrie-group.info
 * Description: Gestion des Sessions
 * Version: 1.0
 * Author: Kogium
 * Author URI:
 */

/**
 * @copyright (c) 2014-2018 Kogium.
 * @author Kogium <kogium@valkyrie-group.info>
 *
 * @licence CC BY-SA 4.0
 * @licence http://creativecommons.org/licenses/by-sa/4.0/deed.fr
 *
 */

/** Security */
defined('INCLUDES_VALKYRIE') ? INCLUDES_VALKYRIE : exit;

/** module data */
$plugin_id = basename(__FILE__); // basename(__FILE__)
$data['name'] = "Manageur Authentification";
$data['author'] = "Kogium";
$data['url'] = "https://leroy.valkyrie-group.info";

self::register_module($plugin_id, $data);

class sns_authenticator
{
    /**
     * Server secret key
     * @since 1.0
     * @var mixed|null
     */
    protected static $key = null;

    private $SGBDR;

    private $session;

    private $cookie;

    private $config;

    private $crypt_valkyrie;

    /**
     * sns_user constructor.
     */
    public function __construct($config=NULL)
    {
        GLOBAL $lang;

        // Check config format
        if (!is_array($config))
            throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),8);
        else
            $this->config = $config;

        // REQUIRED SETTING
        if (!isset($this->config['key']))
            throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),8);
        else
            if(is_string($this->config['key'])) //SECURITE ARGUMENT
                self::$key = $this->config['key'];
            else
                throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),8);

        $this->SGBDR = new sgbdr('sqlserver_valkyrie');

        $this->session = new session($this->config);
        session_set_save_handler($this->session, true);

        $this->cookie= new cookie($this->config);

        $this->crypt_valkyrie = new cipher_system;
    }

    public function open_authentication($username=NULL,$password=NULL,$secure=true)
    {
        global $lang,$hook;

        if(is_null($username) || is_null($password))
            return false;
        else
        {
            $this->session->start();
            if ( ! $this->session->isValid())
            {
                $this->session->forget();
                return false;
            }
            else
            {
                if($this->session->get_session_status())
                {
                    if ($this->check_auth($username, $password, $secure))
                    {
                        try
                        {
                            $ip=$this->get_client_ip();
                            //USER DATA
                            $data_user=NULL;
                            if ($hook->hook_exist ( 'USER_GetByNAME' ))
                                $data_user=$hook->execute_hook ( 'USER_GetByNAME', $username );

                            if(is_null($data_user))
                                throw new engine_error('ERROR ARG',8);

                            $id_user=NULL;
                            if(isset($data_user[0]['id']))
                                $id_user=$data_user[0]['id'];
                            else
                                throw new engine_error('ERROR ARG',8);

                            //MODULE_DEVICE
                            if (!isset($_SESSION['_fingerprint']))
                                throw new engine_error('ERROR ARG FINGERPRINT',8);
                            else
                                $device_print=$_SESSION['_fingerprint'];

                            $device = $_SERVER['HTTP_USER_AGENT'] . ((ip2long($ip) & ip2long('255.255.0.0')));

                            $data="SELECT * FROM [module_device] WHERE device_print = :device_print";
                            $arg_data=array (':device_print' => $device_print);
                            $query=$this->SGBDR->__PREPARE(array('query' => $data, 'statement' => $arg_data));
                            $device_activate=true;

                            if(count($query) == 0)
                            {
                                $data = "INSERT INTO [module_device] (device_print, device, activate) VALUES ( :device_print, :device, :activate)";
                                $arg_data = array (':device_print' => $device_print,
                                    ':device' => $device,
                                    ':activate' => true
                                );
                                $query=$this->SGBDR->__PREPARE(array('query' => $data, 'insert_statement' => $arg_data));

                                $device_id=$this->SGBDR->ENGINE_SGBDR_HANDLE->GET_lastInsertId();
                            }
                            else
                            {
                                if (isset($query[0]['id']))
                                    $device_id = $query[0]['id'];
                                else
                                    $device_id = NULL;

                                $device_activate=$query[0]['activate'];
                            }

                            if(!$device_activate)
                                throw new engine_error('DEVICE EXPIRED',8);

                            //SESSION
                            $ip=$this->get_client_ip();
                            $SID=$this->session->get_session_id();
                            $expire=$this->session->get_session_expire();
                            $last_activity=$this->session->get_session_last_activity();

                            //MODULE CHECK SESSION LIAISON
                            $data = "SELECT * FROM [L_user_session] WHERE id_user = :id";
                            $arg_data = array (':id' => $id_user );

                            $query=$this->SGBDR->__PREPARE(array('query' => $data, 'statement' => $arg_data));

                            $liaison_data=$query;

                            $right_session_id=NULL;
                            $session_data=array();

                            foreach($liaison_data AS $key => $value) // CHECK ALL SESSION
                            {
                                $id_session=$value['id'];

                                $data = "SELECT * FROM [module_session] WHERE id = :id_session";
                                $arg_data = array (':id_session' => $id_session );
                                $query=$this->SGBDR->__PREPARE(array('query' => $data, 'statement' => $arg_data));

                                if(!empty($query))
                                {
                                    $session_data=$query[0];

                                    if (isset($this->config['inactive']))
                                        $ttl = $this->config['inactive'];
                                    else
                                        $ttl = 60; //1 heure par défaut

                                    if (((strtotime($session_data['expire']) >= time()) && (time() - strtotime($session_data['last_activity']) < $ttl * 60)) && (($session_data['current_ip'] == $ip) || ($session_data['last_ip'] == $ip)))
                                        if (is_null($right_session_id))
                                            $right_session_id = $id_session;
                                }
                            }

                            if( !is_null($right_session_id) ) //TAKE OVER SESSION ALREADY OPEN AND NOT EXPIRED
                            {
                                $data = "UPDATE [module_session] SET [current_SID]=:current_SID,[last_SID]=:last_SID,[expire]=CONVERT(datetime, :expire,  126),[current_ip]=:current_ip,[last_ip]=:last_ip,[last_activity]=CONVERT(datetime, :last_activity,  126) WHERE id = :session_id";

                                $arg_data = array ( ':session_id' => $right_session_id,
                                    ':current_SID' => $SID,
                                    ':last_SID' => $session_data['current_SID'],
                                    ':last_activity' => date('Y-m-d\TH:i:s', $last_activity),
                                    ':expire' => date('Y-m-d\TH:i:s', $expire),
                                    ':current_ip' => $ip,
                                    ':last_ip' => $session_data['current_ip']
                                );

                                $query=$this->SGBDR->__PREPARE(array('query' => $data, 'insert_statement' => $arg_data));
                            }
                            else
                            {
                                //CREATE MODULE_SESSION
                                $data = "INSERT INTO [module_session] (current_SID, last_SID, [key], last_activity, expire, current_ip, last_ip) VALUES ( :current_SID, :last_SID, :key, CONVERT(datetime, :last_activity,  126), CONVERT(datetime, :expire,  126), :current_ip, :last_ip )";

                                $arg_data = array (':current_SID' => $SID,
                                    ':last_SID' => $SID,
                                    ':key' => self::$key,
                                    ':last_activity' => date('Y-m-d\TH:i:s', $last_activity),
                                    ':expire' => date('Y-m-d\TH:i:s', $expire),
                                    ':current_ip' => $ip,
                                    ':last_ip' => $ip
                                );

                                $query=$this->SGBDR->__PREPARE(array('query' => $data, 'insert_statement' => $arg_data));

                                $last_session_ID=$this->SGBDR->ENGINE_SGBDR_HANDLE->GET_lastInsertId();

                                //CREATION LIAISON
                                $data = "INSERT INTO [L_user_session] (id_user, id_session, id_device) VALUES ( :id_user, :id_session, :id_device)";
                                $arg_data = array (':id_user' => $id_user,
                                    ':id_session' => $last_session_ID,
                                    ':id_device' => $device_id
                                );
                                $query=$this->SGBDR->__PREPARE(array('query' => $data, 'insert_statement' => $arg_data));
                            }
                        }
                        CATCH(engine_error $e){ $error[]='ERROR'; }
                        catch(PDOException $e){ $error[]='SQLERROR'; }
                        CATCH(Exception $e)   { $error[]='ERROR_ENGINE'; }

                        if(count($error) == 0)
                            return $this->session->put_data('username',$username);
                        else
                            return false;
                    }
                    else
                        return false;
                }
                else
                    return false;
            }
        }
    }

    /**
     * @param null $data
     * @param null $key
     * @param null $categoryname
     * @return bool|mixed|void
     */
    public function SET_NAVIGATION($data=NULL, $key=NULL, $_USECOOKIE=FALSE)
    {
        global $hook, $lang;

        if (is_null($data))
            return false;

        $HandleNavigation = $this->GET_NAVIGATION();

        if(is_null($key))
            $key = 'SNS_NAVIGATION';

        $HandleNavigation[$key]=$data;
        if($_USECOOKIE)
            return $this->cookie->set_cookie('SNS_NAVIGATION', json_encode($HandleNavigation), self::$key, 0);
        else
            return $this->session->put_data($key,json_encode($HandleNavigation));
    }

    public function GET_NAVIGATION($key=NULL, $_USECOOKIE=FALSE)
    {
        global $hook,$lang;

        if(is_null($key))
            $key = 'SNS_NAVIGATION';

        if($_USECOOKIE)
        {
            $$array = json_decode($this->cookie->get_cookie_value('SNS_NAVIGATION'),true);
            return ${$array[$key]};
        }
        else
            return json_decode($this->session->get_data($key),true);
    }

    public function get_expire_authentication()
    {
        GLOBAL $hook,$lang;

        $this->session->start();
        if ( ! $this->session->isValid())
        {
            $this->session->forget();
            return 0;
        }
        else
        {
            return $this->session->get_session_expire();
        }
    }

    public function get_last_activity_authentication()
    {
        GLOBAL $hook,$lang;

        $this->session->start();
        if ( ! $this->session->isValid())
        {
            $this->session->forget();
            return 0;
        }
        else
        {
            return $this->session->get_session_last_activity();
        }
    }

    public function get_session_username()
    {
        $this->session->start();
        if ( ! $this->session->isValid())
        {
            $this->session->forget();
            return false;
        }
        else
        {
            if($this->session->get_session_status())
            {
                $username=$this->session->get_data('username');
                if( $username != false || !empty($username))
                {
                    return $username;
                }
                else
                    return false;
            }
            else
                return false;
        }
    }

    public function check_authentication($secure = true)
    {
        GLOBAL $hook,$lang;

        $this->session->start();
        if ( ! $this->session->isValid())
        {
            $this->session->forget();
            return false;
        }
        else
        {
            if($this->session->get_session_status())
            {
                $username=$this->get_session_username();
                if( $username != false || !empty($username))
                {
                    try
                    {
                        //USER DATA
                        $data_user=NULL;
                        if ($hook->hook_exist ( 'USER_GetByNAME' ))
                            $data_user=$hook->execute_hook ( 'USER_GetByNAME', $username );

                        if(is_null($data_user))
                            throw new engine_error('ERROR ARG',8);

                        $id_user=NULL;
                        if(isset($data_user[0]['id']))
                            $id_user=$data_user[0]['id'];
                        else
                            throw new engine_error('ERROR ARG',8);

                        //SESSION
                        $ip=$this->get_client_ip();
                        $SID=$this->session->get_session_id();
                        $expire=$this->session->get_session_expire();
                        $last_activity=$this->session->get_session_last_activity();

                        //MODULE CHECK SESSION LIAISON
                        $data = "SELECT * FROM [L_user_session] WHERE id_user = :id";
                        $arg_data = array (':id' => $id_user );

                        $query=$this->SGBDR->__PREPARE(array('query' => $data, 'statement' => $arg_data));

                        $liaison_data=$query;

                        $right_session_id=NULL;
                        $session_data=array();

                        foreach($liaison_data AS $key => $value) // CHECK ALL SESSION
                        {
                            $id_session=$value['id'];

                            $data = "SELECT * FROM [module_session] WHERE id = :id_session";
                            $arg_data = array (':id_session' => $id_session );
                            $query=$this->SGBDR->__PREPARE(array('query' => $data, 'statement' => $arg_data));

                            if(!empty($query))
                            {
                                $session_data=$query[0];

                                if (isset($this->config['inactive']))
                                    $ttl = $this->config['inactive'];
                                else
                                    $ttl = 60; //1 heure par défaut

                                if (((strtotime($session_data['expire']) >= time()) && (time() - strtotime($session_data['last_activity']) <= $ttl * 60)) && (($session_data['current_ip'] == $ip) || ($session_data['last_ip'] == $ip)))
                                    if (is_null($right_session_id))
                                        $right_session_id = $id_session;
                            }
                        }

                        if( !is_null($right_session_id) ) //TAKE OVER SESSION ALREADY OPEN AND NOT EXPIRED
                        {
                            $data = "UPDATE [module_session] SET [current_SID]=:current_SID,[last_SID]=:last_SID,[current_ip]=:current_ip,[last_ip]=:last_ip,[last_activity]=CONVERT(datetime, :last_activity,  126) WHERE id = :session_id";

                            $arg_data = array ( ':session_id' => $right_session_id,
                                ':current_SID' => $SID,
                                ':last_SID' => $session_data['current_SID'],
                                ':last_activity' => date('Y-m-d\TH:i:s', $last_activity),
                                ':current_ip' => $ip,
                                ':last_ip' => $session_data['current_ip']
                            );

                            $query=$this->SGBDR->__PREPARE(array('query' => $data, 'insert_statement' => $arg_data));
                        }
                        else
                            throw new engine_error('ERROR LOG SESSION',8);
                    }
                    CATCH(engine_error $e){ $error[]='ERROR'; }
                    catch(PDOException $e){ $error[]='SQLERROR'; }
                    CATCH(Exception $e)   { $error[]='ERROR_ENGINE'; }

                    if(count($error) == 0)
                        return true;
                    else
                        return false;
                }
                else
                    return false;
            }
            else
                return false;
        }
    }

    public function close_authentication()
    {
        $this->session->start();
        $this->session->forget();
        return true;
    }

    public function check_active($username)
    {
        if (is_null($username))
            return false;
        else
        {
            try
            {
                $result = $this->SGBDR->__PREPARE(array('query' => 'SELECT t.[activate] FROM [user] t WHERE t.[name] = :name', 'statement' => array(':name' => $username)));

                if (count($result) == 1) {
                    $result = $result[count($result) - 1]['activate'];
                    return $result;
                } else
                    return false;
            }
            CATCH(engine_error $e){ return false; }
            catch(PDOException $e){ return false; }
            CATCH(Exception $e)   { return false; }
        }
    }

    public function set_activated($username=NULL,$activate=NULL)
    {
        if(is_null($username) || is_null($activate))
            return false;
        else
        {
            try
            {
                return $this->SGBDR->__PREPARE(array('query' => "UPDATE [user] SET [activate] =:activate WHERE [name] = :name", 'insert_statement' => array(':name' => $username,':activate' => $activate)));
            }
            CATCH(engine_error $e){ return false; }
            catch(PDOException $e){ return false; }
            CATCH(Exception $e)   { return false; }
        }
    }

    protected function get_user_password($username)
    {
        if(is_null($username))
            return false;
        else
        {
            try
            {
                $hashdatabase = $this->SGBDR->__PREPARE(array('query' => 'SELECT t.[password] FROM [user] t WHERE t.[name] = :name', 'statement' => array(':name' => $username)));

                if (count($hashdatabase) == 1) {
                    $hashdatabase = $hashdatabase[count($hashdatabase) - 1]['password'];
                    return $hashdatabase;
                } else
                    return false;
            }
            CATCH(engine_error $e){ return false; }
            catch(PDOException $e){ return false; }
            CATCH(Exception $e)   { return false; }
        }
    }

    public function set_user_password($username=NULL,$password=NULL)
    {
        if(is_null($username) || is_null($password))
            return false;
        else
        {
            try
            {
                return $this->SGBDR->__PREPARE(array('query' => "UPDATE [user] SET [password] =:password WHERE [name] = :name", 'insert_statement' => array(':name' => $username,':password' => $password)));
            }
            CATCH(engine_error $e){ return false; }
            catch(PDOException $e){ return false; }
            CATCH(Exception $e)   { return false; }
        }
    }

    public function set_password_authentication($username=NULL,$password=NULL)
    {
        if(is_null($username) || is_null($password))
            return false;
        else
        {
            $newhash= $this->hash_password($password);
            $check_hash = $this->check_hash_password($password, $newhash);
            if ($check_hash)
                return $this->set_user_password($username, $newhash);
              elseif (is_string($check_hash))
                return $this->set_user_password($username, $check_hash);
            else
                return false;
        }
    }

    public function check_auth($username=NULL,$password=NULL,$secure=true)
    {
        if(is_null($username) || is_null($password))
            return false;
        else
        {
            if($this->check_active($username))
            {
                $passworddatabase = $this->get_user_password($username);
                if(!$passworddatabase)
                    return false;
                else
                {
                    $newhash= $this->hash_password($password);
                    $check_hash = $this->check_hash_password($password, $passworddatabase);
                    if ($check_hash)
                        if($secure)
                            return $this->set_user_password($username, $newhash);
                        else
                            return true;
                    elseif (is_string($check_hash))
                        return $this->set_user_password($username, $check_hash);
                    else
                        return false;
                }
            }
            else
                return false;
        }
    }

    public function hash_password($password)
    {
        $options = [
            'cost' => 11,
            'salt' => mcrypt_create_iv(64, MCRYPT_DEV_URANDOM),
        ];

        //CIPHER HOMING
        $password=$this->crypt_valkyrie->encrypt_128_caesar_cipher(self::$key,$password);

        return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    public function check_hash_password($password, $hash)
    {
        $options = [
            'cost' => 11,
            'salt' => mcrypt_create_iv(64, MCRYPT_DEV_URANDOM),
        ];

        //CIPHER HOMING
        $password_static=$this->crypt_valkyrie->encrypt_128_caesar_cipher(self::$key,$password);

        // Vérifions d'abord que le mot de passe correspond au hachage stocké
        if (password_verify($password_static, $hash))
        {
            // Le hachage correspond, on vérifie au cas où un nouvel algorithme de hachage
            // serait disponible ou si le coût a été changé
            if (password_needs_rehash($hash, PASSWORD_BCRYPT, $options))
            {
                // On crée un nouveau hachage afin de mettre à jour l'ancien
                return $this->hash_password($password);
            }
            else
                return true;
        }
        else
            return false;
    }

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

    protected function get_client_ip() {
        // check for shared internet/ISP IP
        if (!empty($_SERVER['HTTP_CLIENT_IP']) && $this->validate_ip($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        // check for IPs passing through proxies
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // check if multiple ips exist in var
            if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
                $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach ($iplist as $ip) {
                    if ($this->validate_ip($ip))
                        return $ip;
                }
            } else {
                if ($this->validate_ip($_SERVER['HTTP_X_FORWARDED_FOR']))
                    return $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED']) && $this->validate_ip($_SERVER['HTTP_X_FORWARDED']))
            return $_SERVER['HTTP_X_FORWARDED'];
        if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && $this->validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
            return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && $this->validate_ip($_SERVER['HTTP_FORWARDED_FOR']))
            return $_SERVER['HTTP_FORWARDED_FOR'];
        if (!empty($_SERVER['HTTP_FORWARDED']) && $this->validate_ip($_SERVER['HTTP_FORWARDED']))
            return $_SERVER['HTTP_FORWARDED'];
        // return unreliable ip since all else failed
        return $_SERVER['REMOTE_ADDR'];
    }

    private function validate_ip($ip) {
        if (strtolower($ip) === 'unknown')
            return false;
        // generate ipv4 network address
        $ip = ip2long($ip);
        // if the ip is set and not equivalent to 255.255.255.255
        if ($ip !== false && $ip !== -1)
        {
            // make sure to get unsigned long representation of ip
            // due to discrepancies between 32 and 64 bit OSes and
            // signed numbers (ints default to signed in PHP)
            $ip = sprintf('%u', $ip);
            // do private network range checking
            if ($ip >= 0 && $ip <= 50331647) return false;
            if ($ip >= 167772160 && $ip <= 184549375) return false;
            if ($ip >= 2130706432 && $ip <= 2147483647) return false;
            if ($ip >= 2851995648 && $ip <= 2852061183) return false;
            if ($ip >= 2886729728 && $ip <= 2887778303) return false;
            if ($ip >= 3221225984 && $ip <= 3221226239) return false;
		    if ($ip >= 3232235520 && $ip <= 3232301055) return false;
		    if ($ip >= 4294967040) return false;
	    }
        return true;
    }

    public function __INSTALL()
    {
        try
        {
            $proc = "IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'module_session')
                BEGIN
                
                CREATE TABLE dbo.module_session
                (
                    id BIGINT PRIMARY KEY NOT NULL IDENTITY,
                    current_SID NVARCHAR(255) NOT NULL,
                    last_SID NVARCHAR(255) NOT NULL,
                    [key] NVARCHAR(255) NOT NULL,
                    last_activity DATETIME NOT NULL,
                    expire DATETIME NOT NULL,
                    current_ip NVARCHAR(255) NOT NULL,
                    last_ip NVARCHAR(255) NOT NULL
                );
                
                IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'module_device')
                BEGIN
                    
                    CREATE TABLE dbo.module_device
                    (
                        id BIGINT PRIMARY KEY NOT NULL IDENTITY,
                        activate BIT DEFAULT 0 NOT NULL,
                        device_print NVARCHAR(255) NOT NULL,
                        device NVARCHAR(255) NOT NULL
                    );
                    
                    IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'L_user_session')
                    BEGIN
                    
                    CREATE TABLE dbo.L_user_session
                    (
                        id BIGINT PRIMARY KEY NOT NULL IDENTITY,
                        id_user BIGINT,
                        id_session BIGINT,
                        id_device BIGINT,
                        CONSTRAINT L_user_session_user_id_fk FOREIGN KEY (id_user) REFERENCES [user] (id),
                        CONSTRAINT L_user_session_module_session_id_fk FOREIGN KEY (id_session) REFERENCES module_session (id),
                        CONSTRAINT L_user_session_module_device_id_fk FOREIGN KEY (id_device) REFERENCES module_device (id)
                    )
                    END
                END
            END";
            return $this->SGBDR->__QUERY($proc);
        }
        CATCH(engine_error $e){ return false; }
        catch(PDOException $e){ return false; }
        CATCH(Exception $e)   { return false; }
    }

    public function __UNINSTALL()
    {
        try
        {
            $proc = "IF EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'L_user_session')
                BEGIN
                
                ALTER TABLE dbo.L_user_session DROP CONSTRAINT L_user_session_user_id_fk;
                ALTER TABLE dbo.L_user_session DROP CONSTRAINT L_user_session_module_session_id_fk;
                ALTER TABLE dbo.L_user_session DROP CONSTRAINT L_user_session_module_device_id_fk;
                DROP TABLE dbo.L_user_session;
                
                END
                
                IF EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'module_session')
                BEGIN
                
                DROP TABLE dbo.module_session;
                
                END
                
                IF EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'module_device')
                BEGIN
                
                DROP TABLE dbo.module_device;
                
                END";
            return $this->SGBDR->__QUERY($proc);
        }
        CATCH(engine_error $e){ return false; }
        catch(PDOException $e){ return false; }
        CATCH(Exception $e)   { return false; }
    }
}
GLOBAL $__COOKIE_CONFIG;
$sns_authenticator = new sns_authenticator ($__COOKIE_CONFIG);
self::add_hook ( 'SET_PASSWORD_ACCOUNT', array(&$sns_authenticator,'set_password_authentication'));
self::add_hook ( 'GET_ACTIVATE_ACCOUNT', array(&$sns_authenticator,'check_active'));
self::add_hook ( 'SET_ACTIVATE_ACCOUNT', array(&$sns_authenticator,'set_activated'));
self::add_hook ( 'AUTHENTICATOR_PASSWORD', array(&$sns_authenticator,'check_auth'));
self::add_hook ( 'OPEN_AUTHENTICATION', array(&$sns_authenticator,'open_authentication'));
self::add_hook ( 'CHECK_AUTHENTICATION', array(&$sns_authenticator,'check_authentication'));
self::add_hook ( 'CLOSE_AUTHENTICATION', array(&$sns_authenticator,'close_authentication'));
self::add_hook ( 'SET_NAVIGATION', array(&$sns_authenticator,'SET_NAVIGATION'));
self::add_hook ( 'GET_NAVIGATION', array(&$sns_authenticator,'GET_NAVIGATION'));
self::add_hook ( 'GET_EXPIRE_NAVIGATION', array(&$sns_authenticator,'get_expire_authentication'));
self::add_hook ( 'GET_LASTACTIVITY_NAVIGATION', array(&$sns_authenticator,'get_last_activity_authentication'));
self::add_hook ( 'GET_USERNAME', array(&$sns_authenticator,'get_session_username'));

self::add_hook ( 'INSTALL', array(&$sns_authenticator,'__INSTALL'));
self::add_hook ( 'UNINSTALL', array(&$sns_authenticator,'__UNINSTALL'));
?>