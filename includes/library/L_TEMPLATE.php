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
if ( !class_exists('template') )
{
	/**
	 * Class template
	 * @package library\template
	 * @internal Moteur template
	 *
	 * @ignore <code example CALL TEMPLATE>
	 *  $template = new template();
	 *  $template->CALL('theme1/t1.tpl');
	 *
	 *  $template = new template('t2.tpl');
	 * <code example>
	 * @example "./includes/library/L_TEMPLATE.php" 29 4 Appel du template avec le parsseur de language
	 */
	class template
	{
		/**
		 * @var buffer_output library L_BUFFER_OUTPUT
		 */
		private $_BUFFER;
		/**
		 * @var cache library L_CACHE
		 */
		private $_CACHE;
		/**
		 * @var bool CONST CACHE @todo includes/config.php
		 */
		private $ACTIVATE_CACHE;
		/**
		 * @var string Chemin du fichier
		 */
		private $path_file;
		/**
		 * @var array White list des extensions autorisés
		 */
		private $type_whitelist = array('tpl','xml','css','html','htm');
		/**
		 * @var string chemin du répertoire parent des modèles
		 */
		private $PATH_TEMPLATE;
		/**
		 * @var string Chemin du répertoire des thèmes
		 */
		private $PATH_THEME ;

		/**
		 * Initialisation du moteur de template. Si argument alors lancement de l'appel du template
		 * @param null $path_file
		 * @see template::CALL()
		 */
		public function __construct($path_file = NULL)
		{
			/* @global objet de traduction */
			GLOBAL $lang;

			$this->PATH_TEMPLATE = 'template/';
			$this->PATH_THEME = $this->PATH_TEMPLATE . 'theme/';

			try
			{
				/** Aggrégat de la librairie */
				$this->_BUFFER = new buffer_output;
				$this->_CACHE = new cache();

				/** Initialisation du moteur de template */
				$this->path_file = $path_file;
				$this->ACTIVATE_CACHE = defined('CACHE_TEMPLATE') ? CACHE_TEMPLATE : define('CACHE_TEMPLATE', FALSE);
				IF ($this->ACTIVATE_CACHE)
					$this->_CACHE->ENABLE();
				else
					$this->_CACHE->DISABLE();

				IF((!empty($path_file)) && (!is_null($path_file)))
					$this->CALL($path_file);
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
		 * Appel du template avec parssage du BBCode
		 * @param null $path_file
		 * @throws engine_error
		 */
		public function CALL($path_file = NULL, $lang_template = TRUE, $show_file = TRUE)
		{
			/* @global objet de traduction */
			GLOBAL $lang;

			try
			{
				/** Securité d'argument */
				IF((!empty($path_file)) && (is_string($path_file)))
					$this->path_file = $path_file;
				ELSE
					throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),9);

				$FILE_TEMPLATE = $this->PATH_THEME . $this->path_file;

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
				$this->path_file = realpath($this->PATH_THEME) . $path_dir_template_file . $pathname_filetheme; //ABSOLUTE PA

				IF(!empty($this->path_file))
				{
					/** Check File before to secure include */
					$this->path_file = $this->CHECK_FILE($this->path_file);
					IF(!file_exists($this->path_file))
						throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),8);

					/** Parseur des templates pour convertir le BBCODE */
					$closure = function($buffer)
					{
						/* @global objet de traduction */
						GLOBAL $lang;

						$patterns = array();
						$replacements = array();

						foreach ($lang->GET_TEMPLATE() as $k => $v)
						{
							$patterns[] = '/{' . $k . '}/';  // use i to ignore case
							$replacements[] = $v;
						}
						return (preg_replace($patterns, $replacements, $buffer));
					};

					/** Ouverture du système de Buffer */
					if($lang_template)
					    $this->_BUFFER->SET_FLUSH($closure);
                    else
                        $this->_BUFFER->SET_FLUSH();
					/** Appeler le moteur de cache pour include secure le contenu du template */
                    $this->_CACHE->CACHE($path_file,TRUE);
					/** Fermeture du système de Buffer */
                    if($show_file)
					    $this->_BUFFER->GET_FLUSH(TRUE);
                    else
                        return $this->_BUFFER->GET_FLUSH(False);
				}
			}
			catch(engine_error $e)
			{
				$this->path_file='';
				__SHOW_ENGINE_EXCEPTION($e);
                if(! $show_file) return '';
			}
			catch(Exception $e)
			{
				throw new engine_error($e->getMessage(),9,$e);
                if(! $show_file) return '';
			}
		}

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

	}
}
else
{
	throw new engine_error($lang->_('TS_INJECTION_ENGINE'),10);
}
?>