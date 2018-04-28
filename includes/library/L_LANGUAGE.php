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
 * @filesource
 * @param CONST INCLUDES_VALKYRIE VARIABLE GLOBAL DE SECURITE DU CMS
 * @throws exit if constant not declared
 * @uses SECURITY-READ-FILE
 */
defined('INCLUDES_VALKYRIE') ? INCLUDES_VALKYRIE : exit;

/* @ignore IF CLASS EXIST SECURITY */
if ( !class_exists('language') )
{
	/**
	 * Class language
	 *
	 * Permet de traduire le système en fonction des langues installées ou utilisées par l'utilisateur du site
	 *
	 * @ignore <code example Declaration>
	 * GLOBAL $lang; //SET TO GLOBAL ENVIRONNEMENT
	 * $lang = new language(); //INSTANCIATION TO GLOBAL ENVIRONNEMENT
	 * <code example>
	 *
	 * @ignore <code example translate>
	 * $lang->_( '{VARIABLE BBCODE}' )
	 * $lang->TRANSLATE( '{VARIABLE BBCODE}' )
	 * <code example>
	 *
	 * @ignore <code example SET LANGUAGE OR USE USER LANGUAGE>
	 * $lang->SET_LANGUAGE('en'); //SET LANGUAGE INSTALLED OR USE DEFAULT LANGUAGE => 'fr'
	 * $lang->SET_LANGUAGE(); //SET USER LANGUAGE IF INSTALLED OR USE DEFAULT LANGUAGE => 'fr'
	 * <code example>
	 *
	 * @ignore <code example SET TEMPLATE>
	 * $lang->SET_TEMPLATE(array('bbcode1' => 'traduction'));
	 * //Traduire le BBCODE : bbcode1 ; transformé en : traduction
	 * //INSERTION DES TEMPLATES SUR LA LANGUE COURANTE, AUSSINON SI LA LANGUE EST REMISE A ZERO
	 * //ALORS IL FAUT RE-INJECTER UNE DEUXIEME FOIS LE TEMPLATE DE TRADUCTION BBCODE
	 * <code example>
	 *
	 * @example "./includes/library/L_LANGUAGE.php" 32 2 Declaration ; @todo instanciation on includes/additional.php
	 * @example "./includes/library/L_LANGUAGE.php" 37 2 Traduction
	 * @example "./includes/library/L_LANGUAGE.php" 42 2 Insérer un language
	 * @example "./includes/library/L_LANGUAGE.php" 47 4 Insérer des modèles de traduction
	 * @todo "./includes/library/language/VARIABLE_SYSTEM.php" BBCODE SYSTEM
	 * @todo "./content/language/" BBCODE de la langue installée
     */
	class language
	{
		/**
		 * @var array modèle de traduction
         */
		private $template = array();
		/**
		 * @var string la langue courante
         */
		private $language;
		/**
		 * @var string la langue par defaut = 'fr'
         */
		private $default_language;
		/**
		 * @var string la langue de l'utilisateur
         */
		private $user_language;

		/**
		 * @var string le chemin principale des modèles de traduction
         */
		private $_PATH_TEMPLATE;
		/**
		 * @var string le chemin des fichiers modèles de traduction
         */
		private $_PATH_INCLUDE_TEMPLATE;
		/**
		 * @var string le chemin du modèle de traduction par défaut
         */
		private $_PATH_DEFAULT_TEMPLATE;
		/**
		 * @var string le chemin du fichier modèle de traduction par défaut
         */
		private $_PATH_DEFAULT_INCLUDE_TEMPLATE;

		/**
		 * Interface pour appeler la traduction
		 * @param null $data BBCODE to translate
		 * @see language::TRANSLATE()
		 * @example "./includes/library/L_LANGUAGE.php" 37 2 Traduction
         */
		public function _($data = null){ return $this->TRANSLATE($data); }

		/**
		 * Get array with all template language
		 * @return mixed|null tableau 2 dimensions avec la dimension 1 = langue et la dimension 2 modèle de traduction
         */
		public function GET_TEMPLATE(){	return $this->template[$this->language]; }

		/**
		 * SET LANGUAGE inside language system and recoil data template
		 * @param null $langue type language
		 * @see language::__construct()
		 * @example "./includes/library/L_LANGUAGE.php" 42 2 Insérer un language
         */
		public function SET_LANGUAGE($langue = null)
		{
			$this->_array = null; //RESET ERROR
			$this->__construct($langue); //POLYMORPHISM LINEARE
		}

		/**
		 * language constructor.
		 *
		 * Initialise la langue dans le système de traduction. et check toutes les injections systèmes
		 *
		 * @param null $langue
         */
		public function __construct($langue = null)
		{
			try
            {
                /** DECLARATION DES VARIABLES */
                GLOBAL $phpEx;
                $this->default_language='fr';
                $this->_PATH_TEMPLATE = './content/language/';
                $this->_PATH_DEFAULT_TEMPLATE = $this->_PATH_TEMPLATE . $this->default_language . '.' . $phpEx;
                $this->_PATH_DEFAULT_INCLUDE_TEMPLATE = $this->_PATH_TEMPLATE . $this->default_language . '.' . $phpEx;

                $this->GET_USER_LANGUAGE();

				IF (empty($langue))
                {
					/** SECURITE SUR LES LANGUES RECUPERER DU NAVIGATEUR DE L'UTILISATEUR */
                    $counter=0;
                    for ($i = 0; $i < sizeof($this->user_language); $i++)
                    {
                        $elem_user_language = $this->user_language[$i];

                        if(is_array($elem_user_language))
                         {
                             if(count($elem_user_language)==1)
                             {
                                 $include = $this->_PATH_TEMPLATE . $elem_user_language[0] . '.' . $phpEx;
                                 if(file_exists($include)) //AGAINST INJECTION INCLUDE LEVEL 1
                                     $counter = ($counter == 0)? $i : $counter;
                                 break;
                             }
                         }
                    }

                    $this->language = ($counter != 0)? $this->user_language[$counter][0]: $this->default_language;
                }
				ELSE
					$this->language = htmlspecialchars($langue);

                $this->_PATH_INCLUDE_TEMPLATE = $this->_PATH_TEMPLATE . $this->language . '.' . $phpEx;

				//APPEL des fichiers Systèmes
				if(file_exists($this->_PATH_INCLUDE_TEMPLATE)) //AGAINST INJECTION INCLUDE LEVEL 1
				{
					$include = new LS_INCLUDE($this->_PATH_INCLUDE_TEMPLATE,5); //AGAINST INJECTION INCLUDE LEVEL 2
					IF(sizeof($include->_array_error)!=0) //AGAINST TRAVERSAL INJECTION INCLUDE LEVEL 1
						throw new engine_error('BAD FILE INSTALLATION.',9);
				}
				else
				{
					$this->_array[sizeof($_array)]=array('message' => 'La langue ' . htmlspecialchars($langue) . ' n\'existe pas','code'=>8); //EMULATION DE l'ERREUR
					$include = new LS_INCLUDE($this->_PATH_DEFAULT_INCLUDE_TEMPLATE,5); //AGAINST INJECTION INCLUDE LEVEL 2
					IF(sizeof($include->_array_error)!=0) //AGAINST TRAVERSAL INJECTION INCLUDE LEVEL 1
						throw new engine_error('BAD FILE INSTALLATION.',9);
					$this->language = $this->default_language;
				}

				GLOBAL $_LANG; //CALL GLOBAL
				if (!empty($_LANG) && is_array($_LANG))
				{
					IF (!array_key_exists($this->language,$this->template))
					{
						$this->template[$this->language] = $_LANG;

						$array_key=array_keys($this->template);

						foreach($array_key as $key => $value)
						{
							IF($array_key[$key] != $this->language)
								unset($this->template[$array_key[$key]]);
						}
					}
				}
			}
			CATCH(engine_error $e)
			{
				$this->status=FALSE;
				__SHOW_ENGINE_EXCEPTION($e);
			}
			CATCH(Exception $e) //CATCH ALL ERROR
			{
				throw new engine_error($e->getMessage(),9,$e);
			}
		}

		/**
         * TRIAGE DES LANGUES PAR SELECTEUR DE VIRGULE. COMPATIBLE WEBKIT ET CHROME
		 * @param $languageList
		 * @return array
         */
		private function __ACCEPT_LANGUAGE_LANGUAGELIST($languageList)
        {
            if (is_null($languageList)) {
                if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                    return array();
                }
                $languageList = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            }
            $languages = array();
            $languageRanges = explode(',', trim($languageList));
            foreach ($languageRanges as $languageRange) {
                if (preg_match('/(\*|[a-zA-Z0-9]{1,8}(?:-[a-zA-Z0-9]{1,8})*)(?:\s*;\s*q\s*=\s*(0(?:\.\d{0,3})|1(?:\.0{0,3})))?/', trim($languageRange), $match)) {
                    if (!isset($match[2])) {
                        $match[2] = '1.0';
                    } else {
                        $match[2] = (string) floatval($match[2]);
                    }
                    if (!isset($languages[$match[2]])) {
                        $languages[$match[2]] = array();
                    }
                    $languages[$match[2]][] = strtolower($match[1]);
                }
            }
            krsort($languages);
            return $languages;
        }

		/**
         * DETECTEUR DE LANGUE INSTALLEE
		 * @param $accepted
		 * @param $available
		 * @return array
         */
		private function __ACCEPT_LANGUAGE_FINDMATCHES($accepted, $available)
        {
            $matches = array();
            $any = false;
            foreach ($accepted as $acceptedQuality => $acceptedValues) {
                $acceptedQuality = floatval($acceptedQuality);
                if ($acceptedQuality === 0.0) continue;
                foreach ($available as $availableQuality => $availableValues) {
                    $availableQuality = floatval($availableQuality);
                    if ($availableQuality === 0.0) continue;
                    foreach ($acceptedValues as $acceptedValue) {
                        if ($acceptedValue === '*') {
                            $any = true;
                        }
                        foreach ($availableValues as $availableValue) {
                            $matchingGrade = $this->__ACCEPT_LANGUAGE_MATCHLANGUAGE($acceptedValue, $availableValue);
                            if ($matchingGrade > 0) {
                                $q = (string) ($acceptedQuality * $availableQuality * $matchingGrade);
                                if (!isset($matches[$q])) {
                                    $matches[$q] = array();
                                }
                                if (!in_array($availableValue, $matches[$q])) {
                                    $matches[$q][] = $availableValue;
                                }
                            }
                        }
                    }
                }
            }
            if (count($matches) === 0 && $any) {
                $matches = $available;
            }
            krsort($matches);
            return $matches;
        }

		/**
         * VERIFIE LE NOMBRE DE CORRESPONDANCE D'OCCURENCE DE LANGUE
		 * @param $a
		 * @param $b
		 * @return float|int
         */
		private function __ACCEPT_LANGUAGE_MATCHLANGUAGE($a, $b)
        {
            $a = explode('-', $a);
            $b = explode('-', $b);
            for ($i=0, $n=min(count($a), count($b)); $i<$n; $i++) {
                if ($a[$i] !== $b[$i]) break;
            }
            return $i === 0 ? 0 : (float) $i / count($a);
        }

		/**
         * RECUPERE LA LANGUE DU NAVIGATEUR DE L'UTILISATEUR. METHODE TRES DANGEUREUSE. BENCHMARK INJECTION A FAIRE
		 * @return array|null
		 * @throws engine_error
         */
		private function GET_USER_LANGUAGE()
		{
			try
			{
                $accepted = $this->__ACCEPT_LANGUAGE_LANGUAGELIST($_SERVER['HTTP_ACCEPT_LANGUAGE']);

                IF (empty($this->_PATH_TEMPLATE))
                    $this->_PATH_TEMPLATE = './content/language/';

                $repertory_file  = scandir($this->_PATH_TEMPLATE, 1);
                $content_language_installed='';
                $extension_valide='php';
                foreach ($repertory_file as $item) {
                    $ext = substr($item, strrpos($item, '.') + 1);
                    if(in_array($ext, array($extension_valide)))
                        $content_language_installed.= basename($item, '.'.$extension_valide).',';
                }
                $content_language_installed = substr($content_language_installed, 0, -1); //supprimer le dernier séparateur en trop

                if (empty($content_language_installed))
                    $content_language_installed = $this->default_language;

                $available = $this->__ACCEPT_LANGUAGE_LANGUAGELIST($content_language_installed);

                $this->user_language = $this->__ACCEPT_LANGUAGE_FINDMATCHES($accepted, $available);

				return $this->user_language;
			}
			CATCH(engine_error $e)
			{
				return null;
				__SHOW_ENGINE_EXCEPTION($e);
			}
			CATCH(Exception $e) //CATCH ALL ERROR
			{
                return null;
				throw new engine_error($e->getMessage(),8,$e);
			}			
		}

		/**
         * VERIFIE EN RETOURNE LA CORRESPONDANCE DE LA RECHERCHE A TRADUIRE DANS LES MODELES DE TRADUCTION
		 * @param null $data
		 * @return string
		 * @throws engine_error
         */
		public function TRANSLATE($data = null)
		{
			$result='';
			try
			{
				IF (empty($data))
				{
					throw new engine_error('BAD ARGUMENTS.',8);
				}
				else
				{
					IF (array_key_exists($data,$this->template[$this->language]))
						$result = $this->template[$this->language][$data];
					ELSE
						throw new engine_error('VARIABLE EMPTY.',8);
				}
				
				return $result;
			}
			CATCH(engine_error $e)
			{
				return $result;
				__SHOW_ENGINE_EXCEPTION($e);
			}
			CATCH(Exception $e) //CATCH ALL ERROR
			{
				throw new engine_error($e->getMessage(),8,$e);
			}			
		}

		/**
         * AJOUTE DES MODELES DE TRADUCTION DANS LE LANGUAGE EN COURS
		 * @param null $data
		 * @return bool
		 * @throws engine_error
         */
		public function SET_TEMPLATE($data = null)
		{
			try
			{
				if (!empty($data) && is_array($data))
				{
					$this->template[$this->language] = array_merge($this->template[$this->language], $data);
				}
				ELSE
					throw new engine_error('BAD ARGUMENTS.',8);
				
				return TRUE;
			}
			CATCH(engine_error $e)
			{
				return false;
				__SHOW_ENGINE_EXCEPTION($e);
			}
			CATCH(Exception $e) //CATCH ALL ERROR
			{
				throw new engine_error($e->getMessage(),8,$e);
			}			
		}
	}
}
else
{
	throw new engine_error('INJECTION BLOCKED.',10);
}
?>