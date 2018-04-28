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
if ( !class_exists('buffer_output') )
{
	/**
	 * Class buffer_output
     * @package library\Buffer
     * @internal Moteur de bufferisation
     *
     * @ignore <code example instanciation>
     * $ob = new buffer_output();
     * <code example>
     *
     * @ignore <code example SET AND GET BUFFER>
     * $closure = function($buffer) {    return (str_replace('{test}', 'BBCODE', $buffer));  };
     * 
     * $ob->SET_FLUSH($closure); #example avec closure
     * echo 'contenu1{test}';
     * $buffer = $ob->GET_FLUSH();
     * var_dump($buffer);
     * 
     * $ob->SET_FLUSH(); #set buffer without show content
     * echo 'contenu2';
     * $buffer2 = $ob->GET_FLUSH(FALSE);
     * var_dump($buffer2);
     * 
     * $ob->SET_FLUSH(); #set buffer without show content
     * echo 'contenu3';
     * $buffer3 = $ob->GET_FLUSH();
     * var_dump($buffer3);
     * 
     * $ob->SET_FLUSH(); #set buffer with include content
     * echo 'contenu4';
     * $buffer4 = $ob->GET_FLUSH(TRUE);
     * var_dump($buffer4);
     *
     * =====================================================
     * Résultats :
     * =====================================================
     * string 'contenu1BBCODE' (length=14)
     * string 'contenu2' (length=8)
     * string 'contenu3' (length=8)
     * contenu4
     * string 'contenu4' (length=8)
     * <code example>
     *
     * @ignore <code example GET ALL BUFFER>
     * $buffer = $ob->GET_BUFFER();
     * var_dump($buffer);
     * =====================================================
     * Résultats :
     * =====================================================
     * array (size=4)
     *      0 => string 'contenu1BBCODE' (length=14)
     *      1 => string 'contenu2' (length=8)
     *      2 => string 'contenu3' (length=8)
     *      3 => string 'contenu4' (length=8)
     * <code example>
     * @example "./includes/library/L_BUFFER_OUTPUT.php" 29 1 Initialisation de la Bufferisation
     * @example "./includes/library/L_BUFFER_OUTPUT.php" 33 30 Utilisation
     * @example "./includes/library/L_BUFFER_OUTPUT.php" 66 10 Récupérer tous le contenu des Buffers enregistrés
     */
	class buffer_output
	{
		/**
		 * @var int Détermine le niveau abstrait d'élément dans le buffer de donnée
         */
		private $level=0;
		/**
		 * @var array contenu des buffers
         */
		private $FLUSH_BUFFER=array();

		/**
         * Mise en place du contrôle des Buffers
		 * buffer_output constructor.
         */
		public function __construct()
		{
			try
			{
				$this->END_ALL_FLUSH();
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
         * Récupère le niveau du buffer courant
		 * @return int
		 * @throws engine_error
         */
		public function GET_LEVEL()
		{
			try
			{
				return ob_get_level();
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
         * Supprime tous les niveaux de bufferisation et le contenu de tous les buffers
		 * @throws engine_error
         */
		private function END_ALL_FLUSH()
		{
			try
			{
				for ($i=0; $i<$this->GET_LEVEL(); $i++)
				{
					$this->END_FLUSH();
				}
				//SECURITY LEVEL
				$this->level=0;
				$this->FLUSH_BUFFER=array();
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
         * Supprime un niveau de Bufferisation
		 * @throws engine_error
         */
		public function END_FLUSH()
		{
			try
			{
				ob_end_flush();
				$this->level--;
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
		 * Ajoute un nouveau Buffer en cours
		 * @param null $closure
		 * @return bool
		 * @throws engine_error
         */
		public function SET_FLUSH($closure = NULL)
		{
			/* @global objet de traduction */
			GLOBAL $lang;

			try
			{
				IF ( $this->GET_LEVEL() == $this->level )
				{
					IF ( is_object($closure) && ($closure instanceof Closure) ) //SECURITY CLOSURE LEVEL 1
						ob_start($closure);
					ELSE
						ob_start();
					$this->level++;
					return true;
				}
				ELSE
				{
					return false;
					throw new engine_error($lang->_('TS_BAD_ENV'),9);
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
		 * Rècupère tout le contenu du Buffer en cours et l'enregistre
         * @param false $show permet d'afficher ou non le contenu du buffer en cours
         * @return bool|mixed
         * @throws engine_error
         */
        public function GET_FLUSH($show = false)
		{
            /* @global objet de traduction */
            GLOBAL $lang;

			try
			{
                for ($i = 0; $i < $this->GET_LEVEL(); $i++) //CALL REAL LEVEL FOR READ
                {
                    if (!array_key_exists($i, $this->FLUSH_BUFFER))
                    {
                        $this->FLUSH_BUFFER[$i] = ob_get_contents();

                        if($show != true)
                            ob_clean();
                    }
                    else
                    {
                        $content = ob_get_contents();
                        if($content != '')
                            $this->FLUSH_BUFFER[] = $content;

                        if($show != true)
                            ob_clean();
                    }
                    $this->END_FLUSH();
                }
                return end($this->FLUSH_BUFFER);
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
		 * Retourne le contenu de tous les Buffers en cours
		 * @return array
         */
		public function GET_BUFFER() { return $this->FLUSH_BUFFER;}

		/**
		 * Permet de supprimer toutes les instances de Buffer après l'exécution du système
		 * @throws engine_error
         */
		public function __destruct()
		{
			try
			{
				$this->END_ALL_FLUSH();
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
	}
}
else
{
	throw new engine_error($lang->_('TS_INJECTION_ENGINE'),10);
}
?>