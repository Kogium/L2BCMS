<?php
/**
 * @param CONST INCLUDES_VALKYRIE VARIABLE GLOBAL DE SECURITE DU CMS
 * @throws exit if constant not declared
 * @uses SECURITY-READ-FILE
 */
defined('INCLUDES_VALKYRIE') ? INCLUDES_VALKYRIE : exit;

/**
 * Class phphooks
 * @author eric.wzy@gmail.com
 * @version API 1.2
 * @version VALKYRIE 1.4
 * @package phphooks
 * @category Plugins
 * @category Modules
 * @link https://code.google.com/archive/p/phphooks/ PROJECT GOOGLE
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 */
class phphooks {
	
	/**
	 * plugins option data
	 * @var array
	 */
	var $plugins = array ();

	/**
	 * modules option data
     * @since 1.2
	 * @var array
	 */
	var $modules = array ();
	
	/**
	 * UNSET means load all plugins, which is stored in the plugin folder. ISSET just load the plugins in this array.
	 * @var array
	 */
	var $active_plugins = NULL;

    /**
     * UNSET means load all modules, which is stored in the module folder. ISSET just load the modules in this array.
     * @since 1.2
     * @var array
     */
    var $active_modules = NULL;
	
	/**
	 * all plugins header information array.
	 * @var array
	 */
	var $plugins_header = array ();

    /**
     * all modules header information array.
     * @since 1.2
     * @var array
     */
    var $modules_header = array ();
	
	/**
	 * hooks data
	 * @var array
	 */
	var $hooks = array ();
	
	/**
	 * register hook name/tag, so plugin developers | module developers can attach functions to hooks
	 * @package phphooks
	 * @since 1.0
	 * 
	 * @param string $tag. The name of the hook.
	 */
	function set_hook($tag) {
		$this->hooks [$tag] = '';
	}
	
	/**
	 * register multiple hooks name/tag
	 * @package phphooks
	 * @since 1.0
	 * 
	 * @param array $tags. The name of the hooks.
	 */
	function set_hooks($tags) {
		foreach ( $tags as $tag ) {
			$this->set_hook ( $tag );
		}
	}
	
	/**
	 * write hook off
	 * @package phphooks
	 * @since 1.0
	 * 
	 * @param string $tag. The name of the hook.
	 */
	function unset_hook($tag) {
		unset ( $this->hooks [$tag] );
	}
	
	/**
	 * write multiple hooks off
	 * @package phphooks
	 * @since 1.2
	 * 
	 * @param array $tags. The name of the hooks.
	 */
	function unset_hooks($tags) {
		foreach ( $tags as $tag ) {
			$this->unset_hook ( $tag );
		}
	}
	
	/**
	 * load plugins from specific folder, includes *.plugin.php files
	 * @package phphooks
	 * @since 1.1
	 * 
	 * @param string $from_folder optional. load plugins from folder, if no argument is supplied, a 'plugins/' constant will be used
	 */
	function load_plugins($from_folder = PATH_PLUG)
    {
        /* @global objet d'extension */
        GLOBAL $phpEx;

		if ($handle = @opendir ( $from_folder )) {
			
			while ( $file = readdir ( $handle ) ) {
				if (is_file ( $from_folder . $file )) {
					if (($this->active_plugins == NULL || in_array ( $file, $this->active_plugins )) && strpos ( $from_folder . $file, '.plugin.' . $phpEx )) {
						
						require_once $from_folder . $file;
						$this->plugins [$file] ['file'] = $file;
					}
				} else if ((is_dir ( $from_folder . $file )) && ($file != '.') && ($file != '..')) {
					$this->load_plugins ( $from_folder . $file . '/' );
				}
			}
			
			closedir ( $handle );
		}
	}

    /**
     * load modules from specific folder, includes *.module.php files
     * @package phphooks
     * @since 1.2
     *
     * @param string $from_folder optional. load modules from folder, if no argument is supplied, a 'modules/' constant will be used
     */
    function load_modules($from_folder = PATH_MOD)
    {
        /* @global objet d'extension */
        GLOBAL $phpEx;

        if ($handle = @opendir ( $from_folder )) {

            while ( $file = readdir ( $handle ) ) {
                if (is_file ( $from_folder . $file )) {
                    if (($this->active_modules == NULL || in_array ( $file, $this->active_modules )) && strpos ( $from_folder . $file, '.module.' . $phpEx )) {
                        require_once $from_folder . $file;
                        $this->modules [$file] ['file'] = $file;
                    }
                } else if ((is_dir ( $from_folder . $file )) && ($file != '.') && ($file != '..')) {
                    $this->load_modules ( $from_folder . $file . '/' );
                }
            }
            closedir ( $handle );
        }
    }
	
	/**
	 * return the all plugins ,which is stored in the plugin folder, header information.
	 * 
	 * @package phphooks
	 * @since 1.2
	 * @param string $from_folder optional. load plugins from folder, if no argument is supplied, a 'plugins/' constant will be used
	 * @return array. return the all plugins ,which is stored in the plugin folder, header information.
	 */
	function get_plugins_header($from_folder = PATH_PLUG)
    {
        /* @global objet d'extension */
        GLOBAL $phpEx;

		if ($handle = @opendir ( $from_folder )) {
			
			while ( $file = readdir ( $handle ) ) {
				if (is_file ( $from_folder . $file )) {
					if (strpos ( $from_folder . $file, '.plugin.' . $phpEx )) {
						$fp = fopen ( $from_folder . $file, 'r' );
						// Pull only the first 8kiB of the file in.
						$plugin_data = fread ( $fp, 8192 );
						fclose ( $fp );
						
						preg_match ( '|Plugin Name:(.*)$|mi', $plugin_data, $name );
                        preg_match ( '|Type Component:(.*)$|mi', $plugin_data, $type );
						preg_match ( '|Plugin Title:(.*)$|mi', $plugin_data, $title );
						preg_match ( '|Plugin URI:(.*)$|mi', $plugin_data, $uri );
						preg_match ( '|Version:(.*)|i', $plugin_data, $version );
						preg_match ( '|Description:(.*)$|mi', $plugin_data, $description );
						preg_match ( '|Author:(.*)$|mi', $plugin_data, $author_name );
						preg_match ( '|Author URI:(.*)$|mi', $plugin_data, $author_uri );
						
						foreach ( array ('name', 'type', 'title' , 'uri', 'version', 'description', 'author_name', 'author_uri' ) as $field ) { //[MAJ]Correction TYPE
							if (! empty ( ${$field} ))
								${$field} = trim ( ${$field} [1] );
							else
								${$field} = '';
						}
						
						$title = ($title == '') ? $name : $title; //[MAJ]Correction TYPE
                        $type = (empty($type)) ? 'plugin' : $type ;
						$plugin_data = array ('filename' => $file, 'Name' => $name, 'type' => $type, 'Title' => $title, 'ComponentURI' => $uri, 'Description' => $description, 'Author' => $author_name, 'AuthorURI' => $author_uri, 'Version' => $version );  //[MAJ]Correction TYPE
						$this->plugins_header [] = $plugin_data;
					}
				} else if ((is_dir ( $from_folder . $file )) && ($file != '.') && ($file != '..')) {
					$this->get_plugins_header ( $from_folder . $file . '/' );
				}
			}
			
			closedir ( $handle );
		}
		return $this->plugins_header;
	}

    /**
     * return the all modules ,which is stored in the module folder, header information.
     *
     * @package phphooks
     * @since 1.2
     * @param string $from_folder optional. load modules from folder, if no argument is supplied, a 'modules/' constant will be used
     * @return array. return the all modules ,which is stored in the module folder, header information.
     */
    function get_modules_header($from_folder = PATH_MOD)
    {
        /* @global objet d'extension */
        GLOBAL $phpEx;

        if ($handle = @opendir ( $from_folder )) {

            while ( $file = readdir ( $handle ) ) {
                if (is_file ( $from_folder . $file )) {
                    if (strpos ( $from_folder . $file, '.module.' . $phpEx )) {
                        $fp = fopen ( $from_folder . $file, 'r' );
                        // Pull only the first 8kiB of the file in.
                        $module_data = fread ( $fp, 8192 );
                        fclose ( $fp );

                        preg_match ( '|Module Name:(.*)$|mi', $module_data, $name );
                        preg_match ( '|Type Component:(.*)$|mi', $module_data, $type );
                        preg_match ( '|Module Title:(.*)$|mi', $module_data, $title );
                        preg_match ( '|Module URI:(.*)$|mi', $module_data, $uri );
                        preg_match ( '|Version:(.*)|i', $module_data, $version );
                        preg_match ( '|Description:(.*)$|mi', $module_data, $description );
                        preg_match ( '|Author:(.*)$|mi', $module_data, $author_name );
                        preg_match ( '|Author URI:(.*)$|mi', $module_data, $author_uri );

                        foreach ( array ('name', 'type', 'title' , 'uri', 'version', 'description', 'author_name', 'author_uri' ) as $field ) { //[MAJ]Correction TYPE
                            if (! empty ( ${$field} ))
                                ${$field} = trim ( ${$field} [1] );
                            else
                                ${$field} = '';
                        }

                        $title = ($title == '') ? $name : $title; //[MAJ]Correction TYPE
                        $type = (empty($type)) ? 'module' : $type ;
                        $module_data = array ('filename' => $file, 'Name' => $name, 'type' => $type, 'Title' => $title, 'ComponentURI' => $uri, 'Description' => $description, 'Author' => $author_name, 'AuthorURI' => $author_uri, 'Version' => $version );  //[MAJ]Correction TYPE
                        $this->modules_header [] = $module_data;
                    }
                } else if ((is_dir ( $from_folder . $file )) && ($file != '.') && ($file != '..')) {
                    $this->get_modules_header ( $from_folder . $file . '/' );
                }
            }

            closedir ( $handle );
        }
        return $this->modules_header;
    }
	
	/**
	 * attach custom function to hook
	 * @package phphooks
	 * @since 1.0
	 * 
	 * @param string $tag. The name of the hook.
	 * @param string $function. The function you wish to be called.
	 * @param int $priority optional. Used to specify the order in which the functions associated with a particular action are executed.(range 0~20, 0 first call, 20 last call)
	 */
	function add_hook($tag, $function, $priority = 10)
    {
		if (! isset ( $this->hooks [$tag] )) {
			#die ( "There is no such place ($tag) for hooks." );
			/** Insert factory return */
            return false;
		} else {
			$this->hooks [$tag] [$priority] [] = $function;
		}
	}
	
	/**
	 * check whether any function is attached to hook
	 * @package phphooks
	 * @since 1.0
	 * 
	 * @param string $tag The name of the hook.
	 */
	function hook_exist($tag) {
		if(is_array($this->hooks [$tag])) //[MAJ]Correction TYPE
			return empty( $this->hooks [$tag] ) ? false : true;
		else
			return (trim ( $this->hooks [$tag] ) == "") ? false : true;
	}
	
	/**
	 * execute all functions which are attached to hook, you can provide argument (or arguments via array)
	 * @package phphooks
	 * @since 1.1
     * @version API 1.1
     * @version VALKYRIE 1.2
	 * 
	 * @param string $tag. The name of the hook.
	 * @param mix $args optional.The arguments the function accept (default none)
	 * @return optional.
	 */
	function execute_hook($tag, $args = '')
    {
        if (isset ( $this->hooks [$tag] )) {
			$these_hooks = $this->hooks [$tag];
			for($i = 0; $i <= 20; $i ++) {
				if (isset ( $these_hooks [$i] )) {
					foreach ( $these_hooks [$i] as $hook ) {
						//$args = $result; //[MAJ]Correction TYPE
						$result = (is_array($args)) ? call_user_func_array ( $hook, $args ) : call_user_func ( $hook, $args );
					}
				}
			}
			return $result;
		} else {
			//die ( "There is no such place ($tag) for hooks." );
            /** Insert factory return */
            return false;
		}
	}

	/**
	 * filter $args and after modify, return it. (or arguments via array)
	 * @package phphooks
	 * @since 1.1
	 * 
	 * @param string $tag. The name of the hook.
	 * @param mix $args optional.The arguments the function accept to filter(default none)
	 * @return array. The $args filter result.
	 */
	function filter_hook($tag, $args = '')
    {
        $result = $args;
		if (isset ( $this->hooks [$tag] )) {
			$these_hooks = $this->hooks [$tag];
			for($i = 0; $i <= 20; $i ++) {
				if (isset ( $these_hooks [$i] )) {
					foreach ( $these_hooks [$i] as $hook ) {
						$args = $result;
						$result = call_user_func ( $hook, $args );
					}
				}
			}
			return $result;
		} else {
			//die ( "There is no such place ($tag) for hooks." );
            /** Insert factory return */
            return false;
		}
	}
	
	/**
	 * register plugin data in $this->plugin
	 * @package phphooks
	 * @since 1.0
	 *
	 * @param string $plugin_id. The name of the plugin.
	 * @param array $data optional.The data the plugin accessorial(default none)
	 */
	function register_plugin($plugin_id, $data = '') {
		foreach ( $data as $key => $value ) {
			$this->plugins [$plugin_id] [$key] = $value;
		}
	}

    /**
     * register module data in $this->module
     * @package phphooks
     * @since 1.2
     *
     * @param string $module_id. The name of the module.
     * @param array $data optional.The data the module accessorial(default none)
     */
    function register_module($module_id, $data = '') {
        foreach ( $data as $key => $value ) {
            $this->modules [$module_id] [$key] = $value;
        }
    }
}
?>