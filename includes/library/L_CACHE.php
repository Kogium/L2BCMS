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
if ( !class_exists('cache') )
{
	/**
	 * Class cache
	 *
	 * Permet de mettre en cache les éléments du répertoire template et faire une copie dans le répertoire cache
	 *
	 * @ignore <code example SET CACHE FILE>
	 * $cache = new cache('t2.tpl'); //SET FILE t2.tpl and include it
	 * $cache = new cache('t2.tpl',true); //SET FILE t2.tpl and include it
	 * $cache = new cache('t2.tpl',false); //SET FILE t2.tpl
	 *
	 * $cache->CACHE('t2.tpl'); //SET FILE t2.tpl and include it
	 * $cache->CACHE('t2.tpl',true); //SET FILE t2.tpl and include it
	 * $cache->CACHE('t2.tpl',false); //SET FILE t2.tpl
	 * <code example>
	 *
	 * @ignore <code example SET CACHE REPERTORY>
	 * $cache = new cache('theme1'); //SET REPERTORY theme1
	 * $cache = new cache('theme1',true); //SET REPERTORY theme1
	 * $cache = new cache('theme1',false); //SET REPERTORY theme1
	 *
	 * $cache->CACHE('theme1'); //SET REPERTORY theme1
	 * $cache->CACHE('theme1',true); //SET REPERTORY theme1
	 * $cache->CACHE('theme1',false); //SET REPERTORY theme1
	 * <code example>
	 *
	 * @ignore <code example ENABLE|DISABLE CACHE SYSTEM>
	 * $cache->ENABLE(); //ACTIVE
	 * $cache->DISABLE(); //DESACTIVE
	 *
	 * Automatique @todo includes/config.php ; CONST CACHE_TEMPLATE FOR ENABLE OR DISABLE CACHE SYSTEM
	 * <code example>
	 * @todo includes/config.php ; CONST CACHE_TEMPLATE FOR ENABLE OR DISABLE CACHE SYSTEM
	 *
	 * @ignore <code example IS CACHED>
	 * $cache->IS_CACHED('t2.tpl'); //FILE          //RETURN TRUE OR FALSE
	 * $cache->IS_CACHED('theme1'); //REPERTORY     //RETURN TRUE OR FALSE
	 * <code example>
	 *
	 * @ignore <code example CLEAR CACHE>
	 * $cache->CLEAR_ALL(); //Purge toute la cache et supprime tous les fichiers et dossiers en cache
	 * $cache->CLEAR('t2.tpl'); //SUPPRIME LE FICHIER DES CACHES
	 * $cache->CLEAR('theme1'); //SUPPRIME LE REPERTOIRE ET SON CONTENU DES CACHES
	 * <code example>
	 *
	 * @example "./includes/library/L_CACHE.php" 29 7 Comment mettre en cache un fichier
	 * @example "./includes/library/L_CACHE.php" 39 7 Comment mettre en cache un répertoire
	 * @example "./includes/library/L_CACHE.php" 49 4 ACTIVER OU DESACTIVER LA MIS EN CACHE SYSTEM
	 * @example "./includes/library/L_CACHE.php" 57 2 Vérifier si le fichier ou répertoire est dans les caches
	 * @example "./includes/library/L_CACHE.php" 62 3 Vérifier si le fichier ou répertoire est dans les caches
	 */
	class cache
	{
		/**
		 * @var string Chemin du fichier à passer en cache
		 */
		private $path_file;
		/**
		 * @var string Chemin du fichier qui sera dans le répertoire cache
		 */
		private $path_file_cache;
		/**
		 * @var string chemin du répertoire parent template
		 */
		private $PATH_TEMPLATE;
		/**
		 * @var string chemin du répertoire cache
		 */
		private $PATH_CACHE;
		/**
		 * @var string chemin du répertoire des thèmes
		 */
		private $PATH_THEME;
		/**
		 * @var array extension autorisé par le moteur de cache
		 */
		private $type_whitelist = array('tpl','xml','css','html','htm');
		/**
		 * @var string temps par défaut pour la mise en cache d'un fichier
		 */
		private $limit_time_to_cache= '3600'; //secondes
		/**
		 * @var bool activation du système de cache
		 */
		private $ACTIVATE = FALSE;

		/**
		 * Permet de mettre en cache le fichier ou dossier. Egalement on peut inclure le fichier en cache
		 * @param null $path_file
		 * @param true $include_cache_file
		 * @throws engine_error
		 */
		public function __construct($path_file = null, $include_cache_file = true)
		{
			/* @global objet de traduction */
			GLOBAL $lang;

			/** Declaration */
			$this->ACTIVATE = defined('CACHE_TEMPLATE') ? CACHE_TEMPLATE : $this->ACTIVATE;
			$this->limit_time_to_cache = defined('CACHE_TEMPLATE_TIME') ? CACHE_TEMPLATE_TIME : $this->limit_time_to_cache;
			$this->PATH_TEMPLATE = 'template/';
			$this->PATH_CACHE = $this->PATH_TEMPLATE . 'cache/';
			$this->PATH_THEME = $this->PATH_TEMPLATE . 'theme/';

			try
			{
				IF(!is_null($path_file))
				{
					IF((!empty($path_file)) && (is_string($path_file)))
						$this->path_file = $path_file;
					ELSE
						throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),9);

					/** CHEMIN DU REPERTOIRE DE CACHE HERITE */
					$FILE_CACHE = $this->PATH_CACHE . $this->path_file; //JUMEAU WITH TEMPLATE
					/** CHEMIN DU REPERTOIRE DE TEMPLATE HERITE */
					$FILE_TEMPLATE = $this->PATH_THEME . $this->path_file;

					$pathname_filetheme='';
					if (DIRECTORY_SEPARATOR == '\\') //GET ENV NOT POSIX BECAUSE POSIX IS LIKE '/'
					{
						$path_dir_file = explode('/', dirname($FILE_CACHE));
						IF (count($path_dir_file) > 0)
						{
							unset($path_dir_file[0]);
							unset($path_dir_file[1]);

							IF (count($path_dir_file) == 0)
								$path_dir_template_file = implode('\\', $path_dir_file) . '\\';
							ELSE
								$path_dir_template_file = '\\' . implode('\\', $path_dir_file) . '\\';
						}
						ELSE
							$path_dir_template_file ='';
                        $pathname_filetheme=basename($this->path_file);
					}
					else
					{
						$path_dir_template_file ='/';
                        $pathname_filetheme=$this->path_file;
					}

					/** CHEMIN DU CACHE ; WORK WITH ABSOLUTE PATH */
					$this->path_file_cache = realpath($this->PATH_CACHE) . $path_dir_template_file . $pathname_filetheme; //ABSOLUTE PATH

                    $pathname_filetheme='';
					if (DIRECTORY_SEPARATOR == '\\') //GET ENV NOT POSIX BECAUSE POSIX IS LIKE '/'
					{
						$path_dir_file = explode('/', dirname($FILE_TEMPLATE));
						IF (count($path_dir_file) > 0)
						{
							unset($path_dir_file[0]);
							unset($path_dir_file[1]);

							IF (count($path_dir_file) == 0)
								$path_dir_template_file = implode('\\', $path_dir_file) . '\\';
							ELSE
								$path_dir_template_file = '\\' . implode('\\', $path_dir_file) . '\\';
						}
						ELSE
							$path_dir_template_file ='';
                        $pathname_filetheme=basename($this->path_file);
					}
					else
					{
						$path_dir_template_file ='/';
                        $pathname_filetheme=$this->path_file;
					}

					/** CHEMIN DU FICHIER ; WORK WITH ABSOLUTE PATH */
					$this->path_file = realpath($this->PATH_THEME) . $path_dir_template_file . $pathname_filetheme; //ABSOLUTE PATH

					/** Verification du fichier */
					IF(!empty($this->path_file))
					{
						$this->path_file = $this->CHECK_FILE($this->path_file);
						IF(!file_exists($this->path_file))
							throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),8);

						$checked = $this->CHECK_STAMPDATE($this->path_file);

						IF($checked)
						{
							/** SECURE INCLUDE IF NOT CACHED AND IF ACTIVATED */
							IF((!file_exists($this->path_file_cache)) || (!$this->ACTIVATE))
							{
								IF ($this->ACTIVATE)
								{
									if (!file_exists(dirname($this->path_file_cache)))
										mkdir(dirname($this->path_file_cache), 0777, true);

									if (!$this->copy($this->path_file, $this->path_file_cache))
										throw new engine_error($lang->_('TS_BAD_FILE_PERMISSION'),8);

									$include = (!is_dir($this->path_file_cache)AND($include_cache_file)) ? new LS_INCLUDE($this->path_file_cache,9): true;
								}
								ELSE
									$include = (!is_dir($this->path_file)AND($include_cache_file)) ? new LS_INCLUDE($this->path_file,9): true;
							}
							else
								$include = (!is_dir($this->path_file_cache)AND($include_cache_file)) ? new LS_INCLUDE($this->path_file_cache,9): true;
						}
						ELSE
						{
							/** CHECK FILE WITH REFRESH FILE AND SECURE INCLUDE IF NOT CACHED AND IF ACTIVATED */
							IF((!file_exists($this->path_file_cache)) || (!$this->ACTIVATE))
							{
								IF ($this->ACTIVATE)
								{
									if (!file_exists(dirname($this->path_file_cache)))
										mkdir(dirname($this->path_file_cache), 0777, true);

									if (!$this->copy($this->path_file, $this->path_file_cache))
										throw new engine_error($lang->_('TS_BAD_FILE_PERMISSION'),8);

									$include = (!is_dir($this->path_file_cache)AND($include_cache_file)) ? new LS_INCLUDE($this->path_file_cache,9): true;
								}
								ELSE
									$include = (!is_dir($this->path_file)AND($include_cache_file)) ? new LS_INCLUDE($this->path_file,9): true;
							}
							else
							{
								$this->CLEAR($this->path_file_cache);

								if (!file_exists(dirname($this->path_file_cache)))
									mkdir(dirname($this->path_file_cache), 0777, true);

								if (!$this->copy($this->path_file, $this->path_file_cache))
									throw new engine_error($lang->_('TS_BAD_FILE_PERMISSION'),8);

								$include = (!is_dir($this->path_file_cache)AND($include_cache_file)) ? new LS_INCLUDE($this->path_file_cache,9): true;
							}
						}
					}
				}
			}
			catch(engine_error $e)
			{
				$this->path_file='';
				__SHOW_ENGINE_EXCEPTION($e);
			}
			catch(Exception $e)
			{
				throw new engine_error($e->getMessage(),9,$e);
			}
		}

		/**
		 * ENABLE CACHE
		 */
		public function ENABLE(){$this->ACTIVATE = TRUE;}

		/**
		 * DISABLE CACHE
		 */
		public function DISABLE(){$this->ACTIVATE = FALSE;}

		/**
		 * SECURITY FILE CHECKER
		 * Permet de vérifier l'état du fichier, son extensions et son mime
		 * @param null $path_file
		 * @return null
		 * @throws engine_error
		 */
		private function CHECK_FILE($path_file = null)
		{
			/* @global objet de traduction */
			GLOBAL $lang;

			try
			{
				$result=$path_file;

				IF((!empty($path_file)) && (!is_null($path_file)))
				{
					$absPath = realpath($path_file);
					if( (file_exists($path_file)) || ($absPath === true) || ( (!$absPath) && (DIRECTORY_SEPARATOR == '/') ) ) //AGAINST INJECTION INCLUDE LEVEL 1
					{
						IF (!in_array(pathinfo($path_file, PATHINFO_EXTENSION),$this->type_whitelist)) //AGAINST INJECTION MIME LEVEL 1
							throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),8);

						$finfo = new finfo(FILEINFO_MIME_TYPE);  //AGAINST INJECTION MIME LEVEL 2
						$type = $finfo->file($path_file);
						IF (!preg_match("/(text|empty|octet-stream)\b/i", $type)) //ONLY MIME TYPE : text/... OR Empty
							throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),8);

						$result = $path_file;
					}
					else
						throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),8);
				}
				else
					throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),9);

				return $result;
			}
			catch(engine_error $e)
			{
				return $result;
				__SHOW_ENGINE_EXCEPTION($e);
			}
			catch(Exception $e)
			{
				throw new engine_error($e->getMessage(),9,$e);
			}
		}

		/**
		 * Vérification de la date de modification du cache sous la norme ISO 8601. Si le fichier est trop vieux alors on refresh son contenu.
		 * @param null $path_file
		 * @return bool
		 * @throws engine_error
		 */
		private function CHECK_STAMPDATE($path_file = null)
		{
			/* @global objet de traduction */
			GLOBAL $lang;

			try
			{
				IF((!empty($path_file)) && (!is_null($path_file)))
				{
					$DATE_FILE = date('c', filemtime($this->path_file)); // FORMAT ISO 8601
					$DATE_NOW = date('c'); // FORMAT ISO 8601
					$DATE_LIMIT = date('c',(time() - ($this->limit_time_to_cache))); // FORMAT ISO 8601

					if (($DATE_FILE >= $DATE_LIMIT) && ($DATE_FILE <= $DATE_NOW))
						return true;
					else
						return false;
				}
				else
					throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),9);
			}
			catch(engine_error $e)
			{
				return false;
				__SHOW_ENGINE_EXCEPTION($e);
			}
			catch(Exception $e)
			{
				throw new engine_error($e->getMessage(),9,$e);
			}
		}

		/**
		 * Supprime tous les éléments dans le répertoire cache
		 * @return bool
		 * @throws engine_error
		 */
		public function CLEAR_ALL()
		{
			try
			{
				$PATH_CACHE = realpath($this->PATH_CACHE);
				$GLOB_SYSTEM = array_diff( glob($PATH_CACHE . '\{,.}*', GLOB_BRACE) , array($PATH_CACHE . '\.',$PATH_CACHE . '\..')); //AND HIDDEN WITHOUT FOLDER HERE AND PARENT

				foreach ($GLOB_SYSTEM as $file)
				{
					IF (is_dir($file))
						$this->CLEAR_TREE($file);
					ELSE
						unlink($file);
				}
			}
			catch(engine_error $e)
			{
				return false;
				__SHOW_ENGINE_EXCEPTION($e);
			}
			catch(Exception $e)
			{
				throw new engine_error($e->getMessage(),9,$e);
			}
		}

		/**
		 * Check if file | repertory is cached
		 * @param null $path_file
		 * @return bool|null
		 * @throws engine_error
		 */
		public function IS_CACHED($path_file = null)
		{
			/* @global objet de traduction */
			GLOBAL $lang;

			try
			{
				$result = false;
				IF((!empty($path_file)) && (!is_null($path_file)))
				{
					$path_file = $this->PATH_CACHE . $path_file;
					$absPath = realpath($path_file);

					if( (file_exists($path_file)) || ($absPath === true) ) //AGAINST INJECTION INCLUDE LEVEL 1
						$result = true;
				}
				else
					throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),9);

				return $result;
			}
			catch(engine_error $e)
			{
				return null;
				__SHOW_ENGINE_EXCEPTION($e);
			}
			catch(Exception $e)
			{
				throw new engine_error($e->getMessage(),9,$e);
			}
		}

		/**
		 * Permet de supprimer des documents en récursif
		 * @param null $path_file
		 * @return bool
		 * @throws engine_error
		 */
		private function CLEAR_TREE($path_file = null)
		{
			/* @global objet de traduction */
			GLOBAL $lang;

			try
			{
				IF((!empty($path_file)) && (!is_null($path_file)) && (is_dir($path_file)))
				{
					$GLOB_SYSTEM = array_diff( glob($path_file . '\{,.}*', GLOB_BRACE) , array($path_file . '\.',$path_file . '\..')); //AND HIDDEN WITHOUT FOLDER HERE AND PARENT

					foreach ($GLOB_SYSTEM as $file) {
						(is_dir($file)) ? $this->CLEAR_TREE($file) : unlink($file);
					}
					return rmdir($path_file);
				}
				else
					throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),9);
			}
			catch(engine_error $e)
			{
				return false;
				__SHOW_ENGINE_EXCEPTION($e);
			}
			catch(Exception $e)
			{
				throw new engine_error($e->getMessage(),9,$e);
			}
		}

		/**
		 * Permet d'effacer le cache d'un fichier ou d'un dossier
		 * @param null $path_file
		 * @return bool
		 * @throws engine_error
		 */
		public function CLEAR($path_file = null)
		{
			/* @global objet de traduction */
			GLOBAL $lang;

			try
			{
				IF((!empty($path_file)) && (!is_null($path_file)))
				{
					$path_file = $this->PATH_CACHE . $path_file;
					$absPath = realpath($path_file);

					if( (file_exists($path_file)) || ($absPath === true) ) //AGAINST INJECTION INCLUDE LEVEL 1
					{
						IF (is_dir($path_file))
							$this->CLEAR_TREE($path_file);
						ELSE
							unlink($path_file);
					}
					ELSE
						throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),8);
				}
				else
					throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),9);
			}
			catch(engine_error $e)
			{
				return false;
				__SHOW_ENGINE_EXCEPTION($e);
			}
			catch(Exception $e)
			{
				throw new engine_error($e->getMessage(),9,$e);
			}
		}

		/**
		 * Permet de mettre en cache un fichier
		 * @internal Polymorphism LINEARE FOR RECOIL INSTANCIATION OBJECT
		 * @param null $path_file
		 * @param true $include_cache_file
		 * @return bool
		 * @throws engine_error
		 * @see cache::__construct()
		 */
		public function CACHE($path_file = null, $include_cache_file = true)
		{
			/* @global objet de traduction */
			GLOBAL $lang;

			try
			{
				/** METHOD ARGUMENT SECURITY */
				IF((!empty($path_file)) && (!is_null($path_file)))
					$this->__construct($path_file,$include_cache_file);
				else
					throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),9);
			}
			catch(engine_error $e)
			{
				return false;
				__SHOW_ENGINE_EXCEPTION($e);
			}
			catch(Exception $e)
			{
				throw new engine_error($e->getMessage(),9,$e);
			}
		}

		/**
		 * COPIE RECURSIF DE DOSSIER OU DE FICHIER
		 * @param $source
		 * @param $dest
		 * @return bool
		 */
		private function copy($source, $dest)
		{
			try
			{
				// Check for symlinks
				if (is_link($source)) {
					return symlink(readlink($source), $dest);
				}

				// Simple copy for a file
				if (is_file($source)) {
					return copy($source, $dest);
				}

				// Make destination directory
				if (!is_dir($dest)) {
					mkdir($dest);
				}

				// Loop through the folder
				$dir = dir($source);
				while (false !== $entry = $dir->read()) {
					// Skip pointers
					if ($entry == '.' || $entry == '..') {
						continue;
					}

					// Deep copy directories
					$this->copy("$source/$entry", "$dest/$entry");
				}

				// Clean up
				$dir->close();
				return true;
			}
			catch(engine_error $e)
			{
				return false;
				__SHOW_ENGINE_EXCEPTION($e);
			}
			catch(Exception $e)
			{
				throw new engine_error($e->getMessage(),9,$e);
			}
		}

		/**
		 * RESET CACHE POSIX AFTER SCRIPT EXECUTION FOR PREVENT LEAK OR PROCESSUS OVER
		 * AND REFRESH DIRECTORY OR FILE USED INSIDE SYSTEM DIRECTORY PROCESSUS
		 * LIKE BSD:FINDER; LINUX:POSIX; WINDOWS:EXPLORER.EXE; AND OTHER
		 */
		public function __destruct()
		{
			//RESET CACHE POSIX OR DOS AFTER ACTION IN FILE OR DOC
			clearstatcache();
		}
	}
}
else
{
	throw new engine_error($lang->_('TS_INJECTION_ENGINE'),10);
}
?>