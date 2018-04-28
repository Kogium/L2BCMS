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
if ( !class_exists('LS_INCLUDE') )
{
    /**
     * Class LS_INCLUDE
	 *
	 * Permet d'inclure en sécurité des fichiers systèmes
	 * @throws engine_error Permet de contourner les erreurs engine PHP par le moteur engine_error
	 * @ignore <code exemple>
	 *	$include = new LS_INCLUDE( ~~PATH FILE~~ , ~~ CODE ENGINE ERROR ~~, ['ONCE' | 'MULTI'] );
	 * </code exemple>
	 * @example "./includes/library/L_INCLUDE.php" 31 1 Comment Utiliser include de fichier en mode sécure
     */
    class LS_INCLUDE
	{
        /**
         * @var bool status d'inclusion du fichier système
         */
        public $status;

        /**
         * LS_INCLUDE constructor.
         * @param string $file adresse du fichier [UNIQUEMENT EN MODE CHEMIN RELATIF]
         * @param int $code code du type d'erreur si le fichier n'est pas inclut
         * @example "./includes/library/L_ERROR.php" 144 10 Voici les Types d'erreur du CMS
         * @param string $option_secure option d'inclusion unique ou pas ['ONCE' | 'MULTI']
         * @return TRUE|FALSE IF FILE STATEMENT IS OKAY OR ALREADY STATED
         */
        public function __construct($file='', $code=10, $option_secure = 'ONCE')
		{
            /* @global objet de traduction */
            GLOBAL $lang;

			/** Check si le chemin du fichier n'est pas Vide */
			$_error=empty($file) ? TRUE : FALSE ;

            /** Check le type d'inclusion de fichier système */
			$option_secure=empty($option_secure) ? 'ONCE' : 'MULTI' ;
			
            /** Securite des arguments de la classe */
			IF($_error == FALSE)
			{
				try
				{
                    /** Verifie si le fichier exist ou n'est pas un répertoire */
					IF(!file_exists($file))
						THROW NEW engine_error($lang->_('TS_ERROR_INCLUDE'),$code);

                    /**
                     * INCLUSION SYSTEME
                     * SECURITY CODE BETWEEN 1 TO 10
                     */
					IF (($code <= 10) && ($code >= 1))
					{
						IF ($option_secure == 'MULTI')
							if (!include($file)) { THROW NEW engine_error($lang->_('TS_ERROR_INCLUDE'),$code); }
						ELSE
							if (!include_once($file)) { THROW NEW engine_error($lang->_('TS_ERROR_INCLUDE_ONCE'),$code); }

                        /** @return TRUE ALRIGHT */
                        $this->status=TRUE;
					}
					ELSE
					{
                        /** @return FALSE BAD ARGUMENT CLASS */
                        $this->status=FALSE;
                        throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),9);
					}
				}
				CATCH(engine_error $e)
				{
                    /** @return FALSE ENGINE ERROR */
					$this->status=FALSE;
					__SHOW_ENGINE_EXCEPTION($e);
				}
				CATCH(Exception $e) //CATCH ALL ERROR
				{
                    /** @return FALSE SYSTEM PHP ERROR */
					$this->status=FALSE;
					throw new engine_error($e->getMessage(),10,$e);
				}			
			}
			ELSE
			{
                /** @return FALSE BAD ARGUMENT CLASS */
                $this->status=FALSE;
                throw new engine_error($lang->_('TS_BAD_ARGUMENTS'),9);
			}
		}
	}
}
else
{
	throw new engine_error($lang->_('TS_INJECTION_ENGINE'),10);
}
?>