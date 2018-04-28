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

/** @ignore Interface Exception pour le développement
 *	class Exception
 *	{
 *	  protected $message = 'Exception inconnue'; // message de l'exception
 *	  private   $string;                        // __toString cache
 *	  protected $code = 0;                      // code de l'exception défini par l'utilisateur
 *	  protected $file;                          // nom du fichier source de l'exception
 *	  protected $line;                          // ligne de la source de l'exception
 *	  private   $trace;                         // backtrace
 *	  private   $previous;                      // exception précèdente (depuis PHP 5.3)
 *	
 *	  public function __construct($message = null, $code = 0, Exception $previous = null);
 *	
 *	  final private function __clone();         // Inhibe le clonage des exceptions.
 *	
 *	  final public function getMessage();              // message de l'exception
 *	  final public function getCode();                 // code de l'exception
 *	  final public function getFile();                 // nom du fichier source
 *	  final public function getLine();                 // ligne du fichier source
 *	  final public function getTrace();                // un tableau de backtrace()
 *	  final public function getPrevious();             // exception précèdente (depuis PHP 5.3)
 *	  final public function getTraceAsString();        // chaine formatée de trace
 *	
 *	  // Remplacable
 *	  public function __toString();                    // chaine formatée pour l'affichage
 *	}
 */

/* @ignore IF CLASS EXIST SECURITY */
if ( !class_exists('engine_error') )
{
	/**
	 * Class engine_error
	 *
	 * Permet d'auto gérer les exceptions du CMS pour contrôler le système
	 *
	 * @ignore <code example>
	 *	try
	 *	{
	 *		if ( ~~CONDITION ERREUR~~ )
	 *		{
	 *			throw new engine_error( ~~DESCRIPTION ERREUR~~ , ~~1 TO 10~~ );
	 *		}
	 *	}
	 *	catch(engine_error $e)
	 *	{
	 *		__SHOW_ENGINE_EXCEPTION($e);
	 *	}
	 *	catch(Exception $e) //CATCH ALL ERROR
	 *	{
	 *		throw new engine_error($e->getMessage(),10,$e);
	 *	}
	 * </code example>
	 * @example "./includes/library/L_ERROR.php" 57 15 Utilisation
	 * @example "./includes/library/L_ERROR.php" 144 10 Voici les Types d'erreur du CMS
     */
	class engine_error extends Exception
	{
		/**
		 * @var mixed datetime de l'exception
         */
		protected $timestamp;
		/**
		 * @var mixed tabbleau interne des exceptions
         */
		protected $_array;

		/**
		 * engine_error constructor.
		 * @param null $message Le message traduit ou non par le moteur de language
		 * @param int $code le type d'exeption
		 * @param Exception|null $previous aggrégat du moteur d'exception
         */
		public function __construct($message=NULL, $code=0, Exception $previous = null)
		{
			/** Encodage UTF8 */
			$message=utf8_encode($message);
			
			/** Initialisation de l'exception */
			parent::__construct($message, $code, $previous); //POLYMORPHISM LINEARE
			$this->timestamp = time(); //STAMP DATE TO INSERT EXCEPTION
			$this->_array[sizeof($this->_array)]['message']=$this->message;
			$this->_array[sizeof($this->_array)]['code']=$this->code;
			$this->_array[sizeof($this->_array)]['timestamp']=$this->getTimestamp();
			$this->_array[sizeof($this->_array)]['traceALL']=$this->getTraceAsString();
			$this->_array[sizeof($this->_array)]['file']=basename($this->file,".php");
			$this->_array[sizeof($this->_array)]['line']=$this->line;
			$this->_array[sizeof($this->_array)]['previous']=$this->getPrevious(); //trace previous exception

			/** Instanciation de l'exception dans le moteur engine_error */
			GLOBAL $_array_error;
			$_array_error[sizeof($_array_error)]=$this->_array;
		}

		/**
		 * Retourne le datetime de l'exception
		 * @return mixed
         */
		public function getTimestamp()
		{
			return $this->timestamp;
		}
		
		/**
		 * Encode l'exception sous forme de chaine de caractère
		 * @return string
         */
		public function __toString()
		{
			return __CLASS__ . ": [{$this->getCode()}]--({$this->getTimestamp()}): {$this->getMessage()}\n [~ ".basename($this->getFile(),".php").", (LINE : {$this->getLine()})]";
		}

		/**
		 * Permet de retourner le code sous entier
		 * @example "./includes/library/L_ERROR.php" 144 10 Voici les Types d'erreur du CMS
		 * @return int
         */
		public function __getCode()
		{
			return $this->code;
			# 1 : Warning . Stay Hide
			# 2 : Warning . Insert into array
			# 3 : Warning . Echo
			# 4 : Error . Stay Hide
			# 5 : Error . Insert into array
			# 6 : Error . Echo
			# 7 : Process . Insert into array
			# 8 : Hard Error . Insert into array
			# 9 : Hard Error . Echo
			# 10 : Error System . Echo and Quite to prevent damage !
		}

		/**
		 * Permet de retourner toutes les exceptions sous forme de tableau
		 * @return object[]
         */
		public function __getArray()
		{
			return $this->_array;
		}
	}

	/**
	 * Récupère l'exception et la formate sous le système de sécurité VALKYRIE
	 * @param $e Exception
     */
	function __SHOW_ENGINE_EXCEPTION( $e )
	{
		GLOBAL $_array;
		($e->__getCode() == 3) || ($e->__getCode() == 6) || ($e->__getCode() == 9) || ($e->__getCode() == 10) ? print($e->__toString()) : '' ;
		($e->__getCode() > 9) ? exit : '' ;
		IF(($e->__getCode() == 2) || ($e->__getCode() == 5) || ($e->__getCode() == 7) || ($e->__getCode() == 8))
			$_array[sizeof($_array)]=$e->__getArray();
	}
}
else
{
	throw new engine_error('INJECTION BLOCKED.',10);
}
?>
