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

/* @global objet de traduction */
GLOBAL $lang;

//Interface PDO
/*	PDO by php.net
 * 	{
 *		public __construct ( string $dsn [, string $username [, string $password [, array $options ]]] )
 *		public bool beginTransaction ( void )
 *		public bool commit ( void )
 *		public mixed errorCode ( void )
 *		public array errorInfo ( void )
 *		public int exec ( string $statement )
 *		public mixed getAttribute ( int $attribute )
 *		public static array getAvailableDrivers ( void )
 *		public bool inTransaction ( void )
 *		public string lastInsertId ([ string $name = NULL ] )
 *		public PDOStatement prepare ( string $statement [, array $driver_options = array() ] )
 *		public PDOStatement query ( string $statement )
 *		public string quote ( string $string [, int $parameter_type = PDO::PARAM_STR ] )
 *		public bool rollBack ( void )
 *		public bool setAttribute ( int $attribute , mixed $value )
 *	}
 
	GET CLASS by Kogium
  0 => string '__construct' (length=11)
  1 => string 'prepare' (length=7)
  2 => string 'beginTransaction' (length=16)
  3 => string 'commit' (length=6)
  4 => string 'rollBack' (length=8)
  5 => string 'inTransaction' (length=13)
  6 => string 'setAttribute' (length=12)
  7 => string 'exec' (length=4)
  8 => string 'query' (length=5)
  9 => string 'lastInsertId' (length=12)
  10 => string 'errorCode' (length=9)
  11 => string 'errorInfo' (length=9)
  12 => string 'getAttribute' (length=12)
  13 => string 'quote' (length=5)
  14 => string '__wakeup' (length=8)
  15 => string '__sleep' (length=7)
  16 => string 'getAvailableDrivers' (length=19)
  
  ORM BDD :
    http://lessql.net/
	A INSTALLER
  
 */

#Sécurité d'Injection Haut Niveau
if ( !class_exists('mysql_valkyrie') )
{
	class mysql_valkyrie Extends PDO
	{
		#--[start]INITIALISATION--#
		private $HOST;
		private $USER;
		private $PASSWORD;
		private $DATABASE;
		private $PORT;
		private $SESSION = NULL;
		private $PROMPT = NULL;
		private $INSTANCE = NULL;
		private $TRANSACTION = FALSE;
		private $OPTIONS;
		#--[end]INITIALISATION--#
		
		#--[start]INITIALISATION_OBJECT--#
		private $phpEx;
		private $lang;
		#--[end]INITIALISATION_OBJECT--#
		
		public function __construct(instance &$instance=NULL)
		{
			try
			{
				#--[start]INITIALISATION_OBJECT--#
				GLOBAL $phpEx;
				GLOBAL $lang;
				$this->phpEx = $phpEx;
				$this->lang = $lang;
				#--[end]INITIALISATION_OBJECT--#
				
				#--[start]INSTANCE--#
				IF(!empty($instance)) //INSERT INSTANCE
				{
					IF(GET_CLASS($instance) != 'instance')
						throw new engine_error($this->lang->_('TS_BAD_ARGUMENTS'),9);
					ELSE
					{
						#--[start]INITIALISATION--#
						$this->HOST = (!empty($instance->GET_HOST())) ? $instance->GET_HOST() : NULL;
						$this->USER = (!empty($instance->GET_USER())) ? $instance->GET_USER() : NULL;
						$this->PASSWORD = (!empty($instance->GET_PASSWORD())) ? $instance->GET_PASSWORD() : NULL;
						$this->DATABASE = (!empty($instance->GET_DATABASE())) ? $instance->GET_DATABASE() : NULL;
						$this->PORT = (!empty($instance->GET_PORT())) ? $instance->GET_PORT() : NULL;
						$this->TRANSACTION = (!empty($instance->GET_TRANSACTION())) ? $instance->GET_TRANSACTION() : NULL;
						$this->OPTIONS = (!empty($instance->GET_OPTIONS())) ? $instance->GET_OPTIONS() : NULL;
						#--[end]INITIALISATION--#
					}
				}
				ELSE //NO INSERT INSTANCE AND CALL DEFAULT
				{
					#--[start]INITIALISATION--#
					$this->HOST = defined('PDO_HOST') ? PDO_HOST : NULL;
					$this->USER = defined('PDO_USER') ? PDO_USER : NULL;
					$this->PASSWORD = defined('PDO_PASSWORD') ? PDO_PASSWORD : NULL;
					$this->DATABASE = defined('PDO_DATABASE') ? PDO_DATABASE : NULL;
					$this->PORT = defined('PDO_PORT') ? PDO_PORT : NULL;
					$this->TRANSACTION = defined('PDO_TRANSACTION') ? PDO_TRANSACTION : NULL;
					#set Attribute PDO by instantiation [=>__construct] more dynamic with MVC
					$this->OPTIONS[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
					$this->OPTIONS[PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = true;
					$this->OPTIONS[PDO::ATTR_EMULATE_PREPARES] = false; // (5.3.6 < VERSION_MYSQL) ? false : true ; # emulate prepared statements more useful in old version
					$this->OPTIONS[PDO::ATTR_DEFAULT_FETCH_MODE] = PDO::FETCH_OBJ; // Default : parent::FETCH_ASSOC but FETCH_OBJ stack the mysql engine
					$this->OPTIONS[PDO::ATTR_PERSISTENT] = true; //stack the login in cache
					#--[end]INITIALISATION--#
				}
				#--[end]INSTANCE--#
					
				#--[start]INSTANCE_PDO--#
				$this->LOGIN();
				#--[end]INSTANCE_PDO--#
			}
			#--[start]DISPLAY_ERROR--#
			catch(engine_error $e)
			{
				//création session PDO
				$this->SESSION = FALSE;
				$this->PROMPT =  NULL;
				
				($e->__getCode() == 3) || ($e->__getCode() == 6) || ($e->__getCode() == 9) || ($e->__getCode() == 10) ? print($e->__toString()) : null ;
				($e->__getCode() > 9) ? exit : null ;
				#--[start]ERROR_CUSTOM--#
				IF(($e->__getCode() == 2) || ($e->__getCode() == 5) || ($e->__getCode() == 7) || ($e->__getCode() == 8)) {  $_array[sizeof($_array)]=$e->__getArray();}
				#--[end]ERROR_CUSTOM--#
			}
			catch(PDOException $e)
			{
				throw new engine_error($e->getMessage(),9,$e);
			}
			catch(Exception $e)
			{
				throw new engine_error($e->getMessage(),10,$e);
			}
			#--[end]DISPLAY_ERROR--#
			
			/* $this->INSTANCE = parent::prepare('SELECT * FROM test');
			$this->INSTANCE->execute();
			$this->PROMPT = $this->INSTANCE->fetchall();
			var_dump($this->PROMPT);
			$this->INSTANCE->closeCursor();
			var_dump($this->PROMPT);
			$this->INSTANCE = NULL; */
		}
		
		private function GET_INSTANCE()
		{
			if(is_null($this->INSTANCE) | empty($this->INSTANCE))
			{
				try
				{
					$this->SESSION = FALSE;
					$this->INSTANCE = new PDO('mysql:host='.$this->HOST.';port='.$this->PORT.';dbname='.$this->DATABASE, $this->USER, $this->PASSWORD, $this->OPTIONS);
					$this->SESSION = TRUE;
					$this->PROMPT = NULL;
				}
				#--[start]DISPLAY_ERROR--#
				catch(engine_error $e)
				{
					($e->__getCode() == 3) || ($e->__getCode() == 6) || ($e->__getCode() == 9) || ($e->__getCode() == 10) ? print($e->__toString()) : null ;
					($e->__getCode() > 9) ? exit : null ;
					#--[start]ERROR_CUSTOM--#
					IF(($e->__getCode() == 2) || ($e->__getCode() == 5) || ($e->__getCode() == 7) || ($e->__getCode() == 8)) {  $_array[sizeof($_array)]=$e->__getArray();}
					$this->PROMPT = NULL;
					$this->SESSION = FALSE;
					#--[end]ERROR_CUSTOM--#
				}
				catch(PDOException $e)
				{
					throw new engine_error($e->getMessage(),9,$e);
				}
				catch(Exception $e)
				{
					throw new engine_error($e->getMessage(),10,$e);
				}
				#--[end]DISPLAY_ERROR--#
			}
			return $this->INSTANCE;
		}
		
		public function LOGIN()
		{
			try
			{
				#--[start]INSTANCE_PDO--#
				if(empty($this->SESSION) OR !$this->SESSION)
				{
					$this->GET_INSTANCE();
				}
				else
				{
					if($this->SESSION) //sécurité injection par héritage
					{
						$this->LOGOUT();
						$this->LOGIN();
					}
				}
				#--[end]INSTANCE_PDO--#
			}
			#--[start]DISPLAY_ERROR--#
			catch(engine_error $e)
			{
				//création session PDO
				$this->SESSION = FALSE;
				$this->PROMPT =  NULL;
				
				($e->__getCode() == 3) || ($e->__getCode() == 6) || ($e->__getCode() == 9) || ($e->__getCode() == 10) ? print($e->__toString()) : null ;
				($e->__getCode() > 9) ? exit : null ;
				#--[start]ERROR_CUSTOM--#
				IF(($e->__getCode() == 2) || ($e->__getCode() == 5) || ($e->__getCode() == 7) || ($e->__getCode() == 8)) {  $_array[sizeof($_array)]=$e->__getArray();}
				$this->PROMPT = NULL;
				$this->SESSION = FALSE;
				#--[end]ERROR_CUSTOM--#
			}
			catch(PDOException $e)
			{
				throw new engine_error($e->getMessage(),9,$e);
			}
			catch(Exception $e)
			{
				throw new engine_error($e->getMessage(),10,$e);
			}
			#--[end]DISPLAY_ERROR--#
		}
		
		public function LOGOUT()
		{
			try
			{
				#--[start]INSTANCE_PDO--#
				IF($this->SESSION)
				{
					IF(!is_null($this->PROMPT) | !empty($this->PROMPT))
					{
						$this->PROMPT->closeCursor();
						$this->PROMPT =  NULL;
					}
					$this->INSTANCE = NULL;
					$this->SESSION = FALSE;
				}
				#--[end]INSTANCE_PDO--#
			}
			#--[start]DISPLAY_ERROR--#
			catch(engine_error $e)
			{
				//création session PDO
				$this->SESSION = FALSE;
				$this->PROMPT =  NULL;
				
				($e->__getCode() == 3) || ($e->__getCode() == 6) || ($e->__getCode() == 9) || ($e->__getCode() == 10) ? print($e->__toString()) : null ;
				($e->__getCode() > 9) ? exit : null ;
				#--[start]ERROR_CUSTOM--#
				IF(($e->__getCode() == 2) || ($e->__getCode() == 5) || ($e->__getCode() == 7) || ($e->__getCode() == 8)) {  $_array[sizeof($_array)]=$e->__getArray();}
				$this->PROMPT = NULL;
				$this->SESSION = FALSE;
				#--[end]ERROR_CUSTOM--#
			}
			catch(PDOException $e)
			{
				throw new engine_error($e->getMessage(),9,$e);
			}
			catch(Exception $e)
			{
				throw new engine_error($e->getMessage(),10,$e);
			}
			#--[end]DISPLAY_ERROR--#
		}
		
		# Appel de la fonction affichage du Moteur SGBDR
		/* Paramètres :
		 * 1 entrée
		 *  • type (array , tableau) > Représente les options de lecture du Moteur MAW.
		 *   - data > [array] = { TABLE , FIELD , CONDITION , INDEX } ;
		 * 1 sortie
		 *   - array par index
		 */
		# Constructeur de la classe
		public function __QUERY($data)
		{
			try
			{
				#--[start]INSTANCE_PDO--#
				return $this->LS_query($data);
				#--[end]INSTANCE_PDO--#
			}
			#--[start]DISPLAY_ERROR--#
			catch(engine_error $e)
			{
				//création session PDO
				$this->SESSION = FALSE;
				$this->PROMPT =  NULL;
				
				($e->__getCode() == 3) || ($e->__getCode() == 6) || ($e->__getCode() == 9) || ($e->__getCode() == 10) ? print($e->__toString()) : null ;
				($e->__getCode() > 9) ? exit : null ;
				#--[start]ERROR_CUSTOM--#
				IF(($e->__getCode() == 2) || ($e->__getCode() == 5) || ($e->__getCode() == 7) || ($e->__getCode() == 8)) {  $_array[sizeof($_array)]=$e->__getArray();}
				$this->PROMPT = NULL;
				$this->SESSION = FALSE;
				#--[end]ERROR_CUSTOM--#
			}
			catch(PDOException $e)
			{
				throw new engine_error($e->getMessage(),9,$e);
			}
			catch(Exception $e)
			{
				throw new engine_error($e->getMessage(),10,$e);
			}
			#--[end]DISPLAY_ERROR--#
		}
		
		public function __PREPARE($data)
		{
			try
			{
				#--[start]INSTANCE_PDO--#
				return $this->LS_prepare($data);
				#--[end]INSTANCE_PDO--#
			}
			#--[start]DISPLAY_ERROR--#
			catch(engine_error $e)
			{
				//création session PDO
				$this->SESSION = FALSE;
				$this->PROMPT =  NULL;
				
				($e->__getCode() == 3) || ($e->__getCode() == 6) || ($e->__getCode() == 9) || ($e->__getCode() == 10) ? print($e->__toString()) : null ;
				($e->__getCode() > 9) ? exit : null ;
				#--[start]ERROR_CUSTOM--#
				IF(($e->__getCode() == 2) || ($e->__getCode() == 5) || ($e->__getCode() == 7) || ($e->__getCode() == 8)) {  $_array[sizeof($_array)]=$e->__getArray();}
				$this->PROMPT = NULL;
				$this->SESSION = FALSE;
				#--[end]ERROR_CUSTOM--#
			}
			catch(PDOException $e)
			{
				throw new engine_error($e->getMessage(),9,$e);
			}
			catch(Exception $e)
			{
				throw new engine_error($e->getMessage(),10,$e);
			}
			#--[end]DISPLAY_ERROR--#
		}
		
		public function LS_query($data)
		{
			try
			{
				IF(!is_string($data))
					throw new engine_error($this->lang->_('TS_BAD_ARGUMENTS'),9);
				
				#--[start]INSTANCE_PDO--#
				IF($this->SESSION)
				{
					IF(!is_null($this->PROMPT) | !empty($this->PROMPT))
					{
						$this->PROMPT->closeCursor();
						$this->PROMPT =  NULL;
					}
				}
				else
				{
					$this->LOGIN();
				}
				
				IF($this->TRANSACTION)
					$this->INSTANCE->beginTransaction();
				
				$this->PROMPT = $this->INSTANCE->query($data);
				
				IF($this->TRANSACTION)
					$this->INSTANCE->commit();
				
				return $this->PROMPT->fetchAll();
				#--[end]INSTANCE_PDO--#
			}
			#--[start]DISPLAY_ERROR--#
			catch(engine_error $e)
			{
				//création session PDO
				$this->SESSION = FALSE;
				$this->PROMPT =  NULL;
				
				($e->__getCode() == 3) || ($e->__getCode() == 6) || ($e->__getCode() == 9) || ($e->__getCode() == 10) ? print($e->__toString()) : null ;
				($e->__getCode() > 9) ? exit : null ;
				#--[start]ERROR_CUSTOM--#
				IF(($e->__getCode() == 2) || ($e->__getCode() == 5) || ($e->__getCode() == 7) || ($e->__getCode() == 8)) {  $_array[sizeof($_array)]=$e->__getArray();}
				$this->PROMPT = NULL;
				$this->SESSION = FALSE;
				
				IF($this->TRANSACTION)
					$this->INSTANCE->rollBack();
			
				return null;
				#--[end]ERROR_CUSTOM--#
			}
			catch(PDOException $e)
			{
				throw new engine_error($e->getMessage(),9,$e);
			}
			catch(Exception $e)
			{
				throw new engine_error($e->getMessage(),10,$e);
			}
			#--[end]DISPLAY_ERROR--#
		}
		
		public function LS_prepare($data)
		{
			try
			{
				IF(!is_array($data))
					throw new engine_error($this->lang->_('TS_BAD_ARGUMENTS'),9);
					
				//SECURITY ARRAY ARGUMENT MULTIDIMENSION
				foreach($data['statement'] AS $key => $value)
				{
					IF(is_array($value))
						throw new engine_error($this->lang->_('TS_BAD_ARGUMENTS'),9);
				}
				
				IF(!empty($data['query']))
					$request = $data['query'];
				ELSE
					throw new engine_error($this->lang->_('TS_BAD_ARGUMENTS'),9);
				
				IF(is_array($data['statement']) && !empty($data['statement']))
					$statement = $data['statement'];
				ELSE
					throw new engine_error($this->lang->_('TS_BAD_ARGUMENTS'),9);
				
				#--[start]INSTANCE_PDO--#
				IF($this->SESSION)
				{
					IF(!is_null($this->PROMPT) | !empty($this->PROMPT))
					{
						$this->PROMPT->closeCursor();
						$this->PROMPT =  NULL;
					}
				}
				else
				{
					$this->LOGIN();
				}
				
				IF($this->TRANSACTION)
					$this->INSTANCE->beginTransaction();
				
				$this->PROMPT = $this->INSTANCE->prepare($request);
				foreach($statement as $key => $value)
				{
					$this->PROMPT->bindParam($key,$value);
				}
				$this->PROMPT->execute();
				
				IF($this->TRANSACTION)
					$this->INSTANCE->commit();

				return $this->PROMPT->fetchAll();
				#--[end]INSTANCE_PDO--#
			}
			#--[start]DISPLAY_ERROR--#
			catch(engine_error $e)
			{
				//création session PDO
				$this->SESSION = FALSE;
				$this->PROMPT =  NULL;
				
				($e->__getCode() == 3) || ($e->__getCode() == 6) || ($e->__getCode() == 9) || ($e->__getCode() == 10) ? print($e->__toString()) : null ;
				($e->__getCode() > 9) ? exit : null ;
				#--[start]ERROR_CUSTOM--#
				IF(($e->__getCode() == 2) || ($e->__getCode() == 5) || ($e->__getCode() == 7) || ($e->__getCode() == 8)) {  $_array[sizeof($_array)]=$e->__getArray();}
				$this->PROMPT = NULL;
				$this->SESSION = FALSE;
				
				IF($this->TRANSACTION)
					$this->INSTANCE->rollBack();
				
				return null;
				#--[end]ERROR_CUSTOM--#
			}
			catch(PDOException $e)
			{
				throw new engine_error($e->getMessage(),9,$e);
			}
			catch(Exception $e)
			{
				throw new engine_error($e->getMessage(),10,$e);
			}
			#--[end]DISPLAY_ERROR--#
		}
		
		public function __destruct()
		{
			#--[start]WRAPPER_PROCESS--#
			$Process = $this->__QUERY('SHOW FULL PROCESSLIST');
			IF (sizeof($Process) == 3)
			{
				$this->__CLEAR_PROCESS();
			}
			#--[end]WRAPPER_PROCESS--#
			
			$this->LOGOUT();
		}
		
		private function __CLEAR_PROCESS()
		{
			try
			{
				#--[start]INSTANCE_PDO--#
				IF($this->SESSION)
				{
					#--[start]WRAPPER_PROCESS--#
					$Process = $this->__QUERY('SHOW FULL PROCESSLIST');
					foreach($Process as $key => $value)
					{
						IF($value->State != 'init')
							$Wrapper_Process = $this->__QUERY('KILL ' . $value->Id);
					}
					#--[end]WRAPPER_PROCESS--#
					return TRUE;
				}
				else
				{
					return FALSE;
				}
				#--[end]INSTANCE_PDO--#
			}
			#--[start]DISPLAY_ERROR--#
			catch(engine_error $e)
			{
				//création session PDO
				($e->__getCode() == 3) || ($e->__getCode() == 6) || ($e->__getCode() == 9) || ($e->__getCode() == 10) ? print($e->__toString()) : null ;
				($e->__getCode() > 9) ? exit : null ;
				#--[start]ERROR_CUSTOM--#
				IF(($e->__getCode() == 2) || ($e->__getCode() == 5) || ($e->__getCode() == 7) || ($e->__getCode() == 8)) {  $_array[sizeof($_array)]=$e->__getArray();}
				return FALSE;
				#--[end]ERROR_CUSTOM--#
			}
			catch(PDOException $e)
			{
				throw new engine_error($e->getMessage(),9,$e);
			}
			catch(Exception $e)
			{
				throw new engine_error($e->getMessage(),10,$e);
			}
			#--[end]DISPLAY_ERROR--#
		}
	}
}
else
{
	throw new engine_error($lang->_('TS_INJECTION_ENGINE'),5);
}
?>