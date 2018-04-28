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
if ( !class_exists('instance') )
{
	/**
	 * Class instance
     * @package library\SGBDR
	 * @internal CALL instance like __construct()
	 * @ignore <code example>
	 * $instance = new instance('127.0.0.1', 'root', 'password', 'database_name', 3606, TRUE {transaction with auto commit} , $OPTIONS {= array} );
	 * $engine_sgbdr = new sgbdr('mysql_valkyrie',&$instance);
	 * <code example>
	 * @example "./includes/library/L_SGBDR.php" 28 2 Appel du template avec le parsseur de language
     */
	class instance
	{
		/**
		 * @var null HOST
         */
		private $HOST;
		/**
		 * @var null USER
         */
		private $USER;
		/**
		 * @var null PASSWORD
         */
		private $PASSWORD;
		/**
		 * @var null DATABASE
         */
		private $DATABASE;
		/**
		 * @var null PORT
         */
		private $PORT;
		/**
		 * @var bool|null TRANSACTION
         */
		private $TRANSACTION = FALSE;
		/**
		 * @var null OPTIONS
         */
		private $OPTIONS;

		/**
		 * Refactoring d'instanciation SGBDR
		 * @param null $HOST
		 * @param null $USER
		 * @param null $PASSWORD
		 * @param null $DATABASE
		 * @param null $PORT
		 * @param null $TRANSACTION
         * @param null $OPTIONS
         */
		public function __construct($HOST=NULL, $USER=NULL, $PASSWORD=NULL, $DATABASE=NULL, $PORT=NULL, $TRANSACTION=NULL, $OPTIONS=NULL)
		{

			$this->HOST=$HOST;
			$this->USER=$USER;
			$this->PASSWORD=$PASSWORD;
			$this->DATABASE=$DATABASE;
			$this->PORT=$PORT;
			$this->TRANSACTION=$TRANSACTION;
			$this->OPTIONS=$OPTIONS;
		}

		/**
		 * GET HOST
		 * @return null
         */
		public function GET_HOST() { return $this->HOST;}

		/**
		 * GET USER
		 * @return null
         */
		public function GET_USER() { return $this->USER;}

		/**
		 * GET PASSWORD
		 * @return null
         */
		public function GET_PASSWORD() { return $this->PASSWORD;}

		/**
		 * GET DATABASE
		 * @return null
         */
		public function GET_DATABASE() { return $this->DATABASE;}

		/**
		 * GET PORT
		 * @return null
         */
		public function GET_PORT() { return $this->PORT;}

		/**
		 * GET TRANSACTION
		 * @return bool|null
         */
		public function GET_TRANSACTION() { return $this->TRANSACTION;}

		/**
		 * GET OPTION
		 * @return null
         */
		public function GET_OPTIONS() { return $this->OPTIONS;}
	}
}

/* @ignore IF CLASS EXIST SECURITY */
if ( !class_exists('sgbdr') )
{
    /**
     * Class sgbdr
     * @package library\SGBDR
     * @internal Moteur SGBDR; interface sous moteur SQL
     * @ignore <code example>
     * $engine_sgbdr = new sgbdr('mysql_valkyrie');
     *
     * $query = $engine_sgbdr->__PREPARE(array('query' => 'SELECT * FROM table1 WHERE NAME = :name', 'statement' => array (':name' => 'NAME1' )));
     * var_dump($query);
     * $query2 = $engine_sgbdr->__PREPARE(array('query' => 'SELECT * FROM table1 WHERE NAME = :name', 'statement' => array (':name' => 'NAME2')));
     * var_dump($query2);
     * $query3 = $engine_sgbdr->__PREPARE(array('query' => 'SELECT * FROM table1 WHERE NAME = :name', 'statement' => array (':name' => 'NAME2')));
     * var_dump($query3);
     * <code example>
     * @example "./includes/library/L_SGBDR.php" 142 8 exemple d'utilisation du moteur sgbdr
     */
    class sgbdr
	{
        /**
         * @var string Chemin du moteur SGBDR
         */
        private $PATH_ENGINE_DIRECTORY;
        /**
         * @var null Nom du moteur SGBDR
         */
        private $NAME_ENGINE;
        /**
         * @var mixed[] Erreur interne du moteur engine error
         */
        public $_array_error; //$this-> IS LOCAL
        /**
         * @var Pointer du moteur appelé
         */
        public $ENGINE_SGBDR_HANDLE; //HANDLE public : pour la modularité d'utilisation du moteur pour les développeurs

        /**
         * Construction du moteur de la base de donnée en PDO et avec l'instanciation auto
         * sgbdr constructor.
         * @param null $type
         * @param instance|NULL $instance
         */
        public function __construct($type = NULL, instance &$instance=NULL)
		{
			/* @global objet de traduction */
			GLOBAL $lang;
			GLOBAL $phpEx;

			try
			{
				/** sécurité d'argument */
				IF(!empty($instance))
					IF(GET_CLASS($instance) != "instance")
						throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),9);

				$this->NAME_ENGINE = !empty($type) ? $type : NULL; //INITIALISATION TYPE ENVIRONNEMENT SGBDR
				$this->PATH_ENGINE_DIRECTORY = defined('ROOT_SGBDR_DIRECTORY') ? ROOT_SGBDR_DIRECTORY : define('ROOT_SGBDR_DIRECTORY', ROOT_PATH . ROOT_INCLUDES . ROOT_LIBRARY . 'sgbdr/');
				
				$bool = !empty($type) ? (boolean)true : (boolean)false;
				$NAME_ENGINE_END = '_engine.';
				$PATH_ENGINE = $this->PATH_ENGINE_DIRECTORY . $this->NAME_ENGINE . $NAME_ENGINE_END . $phpEx;

                /** Emplacement pour inclure de force les scripts sous moteurs */
				if($bool)
				{
					if(file_exists($PATH_ENGINE)) //AGAINST INJECTION INCLUDE LEVEL 1
					{
						$include = new LS_INCLUDE($PATH_ENGINE,8); //AGAINST INJECTION INCLUDE LEVEL 2
						IF(sizeof($include->_array_error)==0) //AGAINST TRAVERSAL INJECTION INCLUDE LEVEL 1
							IF ( class_exists($type) ) //AGAINST TRAVERSAL INJECTION INCLUDE LEVEL 2
								$this->ENGINE_SGBDR_HANDLE = new $type($instance); //HACK INTERPRETOR TO INSTANCE DYNAMIC OBJECT CLASS
                            else
                                throw new engine_error($lang->_('TS_BAD_FILE_INSTALL'),9);
						else
							throw new engine_error($lang->_('TS_BAD_FILE_INSTALL'),9);
					}
					else
					{
						throw new engine_error($lang->_('TS_BAD_FILE_INSTALL'),9);
					}
				}
				else
				{
					throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),9);
				}
			}
			catch(engine_error $e)
			{
				__SHOW_ENGINE_EXCEPTION($e);
			}
			catch(Exception $e)
			{
				throw new engine_error($e->getMessage(),9,$e);
			}			
		}

        /**
         * Préparation d'une méthode de type query
         * @param null $data
         * @return mixed
         * @throws engine_error
         */
        public function __QUERY($data = NULL)
		{
			/* @global objet de traduction */
			GLOBAL $lang;

			try
			{
				if(!empty($data))
					return $this->ENGINE_SGBDR_HANDLE->__QUERY($data);
				else
				{
					throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),9);
				}
			}
			catch(engine_error $e)
			{
				__SHOW_ENGINE_EXCEPTION($e);
			}
			catch(Exception $e)
			{
				throw new engine_error($e->getMessage(),9,$e);
			}		
		}

        /**
         * Préparation d'une méthhode de type prepare
         * @param null $data
         * @return mixed
         * @throws engine_error
         */
        public function __PREPARE($data = NULL)
		{
			/* @global objet de traduction */
			GLOBAL $lang;

			try
			{
				if(!empty($data))
					return $this->ENGINE_SGBDR_HANDLE->__PREPARE($data);
				else
				{
					throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),9);
				}
			}
			catch(engine_error $e)
			{
				__SHOW_ENGINE_EXCEPTION($e);
			}
			catch(Exception $e)
			{
				throw new engine_error($e->getMessage(),9,$e);
			}		
		}

        /**
         * Destruction de toutes les instances SGBDR
         */
        public function __destruct()
		{
			//Destruction de l'instance SGBDR
			if(isset($this->ENGINE_SGBDR_HANDLE))
				$this->ENGINE_SGBDR_HANDLE->__destruct(); //POLYMORPHISME LINEARE : DESTROY LEAK OBJECT
			$this->ENGINE_SGBDR_HANDLE=NULL;  //DESTROY LEAK MEMORY
		}
		
	}
}
?>