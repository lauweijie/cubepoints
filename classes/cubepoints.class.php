<?php

class CubePoints {

	/*--------------------------------------------*
	 * Constants
	 *--------------------------------------------*/

	const VERSION = '4.0-dev';
	static $URL_CP_MODULES = 'http://cubepoints.com/modules/';
	static $URL_CP_DONATE = 'http://cubepoints.com/donate/';

	/*--------------------------------------------*
	 * Properties
	 *--------------------------------------------*/

	private $loaded_modules = array();
	public $plugin_file = '';
	public $transaction_types = array();
	private $admin_menus = array();

	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/
	
	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	function __construct() {

		// Set plugin file
		$this->plugin_file = dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'cubepoints.php';

		// Load modules
		$this->_loadModules();

		// Handles activation and deactivation of modules
		add_action( 'init', array( $this, 'moduleActionHook' ) );
				
		// Load plugin text domain
		add_action( 'init', array( $this, 'textdomain' ) );

		// Handles plugin activation, deactivation and uninstall
		register_activation_hook( $this->plugin_file, array( $this, 'activate' ) );
		register_deactivation_hook( $this->plugin_file, array( $this, 'deactivate' ) );
		
		// @TODO fix uninstall hook
		// register_uninstall_hook( $this->plugin_file, array( __class__, 'uninstall' ) );

		// Add admin menus
		if( function_exists('is_multisite') && is_multisite() ) {
			add_action( 'network_admin_menu', array( $this, 'insertAdminMenus' ) );
		}
		else {
			add_action( 'admin_menu', array( $this, 'insertAdminMenus' ) );
		}

		// Adds filters for saving screen options
		add_filter( 'set-screen-option', array($this, 'adminPageTransactionsScreenOptionsSet'), 10, 3 );
		
		do_action( 'cubepoints_loaded' );

	} // end constructor

	/*--------------------------------------------*
	 * Plugin-related Methods
	 *--------------------------------------------*/

	/**
	 * Fired when the plugin is activated.
	 *
	 * @params	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public function activate( $network_wide ) {

		// disallow activation for a single site on a multisite network
		if( function_exists('is_multisite') && is_multisite() && ! $network_wide )
			wp_die( '<strong>' . __('ERROR' , 'cubepoints') . ':</strong> ' . __('CubePoints cannot be activated for a single site on a multisite network. To use CubePoints in a multisite network, activate it for the entire network from the network administration menu.', 'cubepoints') );

		// creates database
		global $wpdb;
		if( (int) $this->getOption('db_version', 0) < 1 || $wpdb->get_var("SHOW TABLES LIKE '{$this->db('cubepoints')}'") != $this->db('cubepoints') ) {
			$sql1 = "CREATE TABLE {$this->db('cubepoints')} (
					id BIGINT(20) NOT NULL AUTO_INCREMENT,
					uid BIGINT(20) NOT NULL,
					type VARCHAR(255) NOT NULL,
					points BIGINT(20) NOT NULL,
					timestamp DATETIME NOT NULL,
					UNIQUE KEY id (id)
					);";
			$sql2 = "CREATE TABLE {$this->db('cubepoints_meta')} (
					id BIGINT(20) NOT NULL AUTO_INCREMENT,
					txn_id BIGINT(20) NOT NULL,
					meta_key VARCHAR(255) NOT NULL,
					meta_value TEXT NOT NULL,
					UNIQUE KEY id (id)
					);";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta($sql1);
			dbDelta($sql2);
			$this->updateOption('db_version', 1);
		}

		// adds default options
		$this->addOption( 'auth_key' , substr( md5(uniqid()) , 3 , 10 ) );
		$this->addOption( 'activated_modules' , array() );
		$this->addOption( 'points_prefix' , '$' );
		$this->addOption( 'points_suffix' , '' );
		$this->addOption( 'allow_negative_points' , false );

		// sets up default user capabilities for managing points
		if( ! $network_wide ){
			$role = get_role( 'administrator' );
			if( $role != null )
				$role->add_cap( 'manage_cubepoints' );
		}
		
		// updates installed version
		$this->updateOption( 'version' , $this->getVersion() );
		$this->updateOption( 'network_wide_install' , $network_wide );
	} // end activate

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @params bool $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public function deactivate( $network_wide ) {
		// @TODO define deactivation functionality here	
	} // end deactivate

	/**
	 * Fired when the plugin is uninstalled.
	 *
	 * @params bool $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public function uninstall( $network_wide ) {
		// deactivates all activated modules
			// @TODO
	
		// removes database
		global $wpdb;
		$wpdb->query("DROP TABLE {$this->db('cubepoints')}");
		$wpdb->query("DROP TABLE {$this->db('cubepoints_meta')}");
		$this->deleteOption('cp_db_version');
		
		// removes plugin options
		$this->deleteOption( 'auth_key' );
		$this->deleteOption( 'activated_modules' );
		$this->deleteOption( 'points_prefix' );
		$this->deleteOption( 'points_suffix' );
		$this->deleteOption( 'allow_negative_points' );

		// removes the manage_cubepoints capability from all roles
		$this->removeCapFromAllRoles( 'manage_cubepoints' );

		// removes version data
		$this->deleteOption( 'version' );
		$this->deleteOption( 'network_wide_install' );

		// clear capabilities
		$this->removeCapFromAllRoles( 'manage_cubepoints' );
	} // end uninstall

	/**
	 * Loads the plugin text domain for translation
	 */
	public function textdomain() {
		load_plugin_textdomain( 'cubepoints', false, dirname( plugin_basename( $this->plugin_file ) ) . '/lang' );
	}

	/**
	 * Returns current plugin version.
	 * 
	 * @return string Plugin version.
	 */
	public function getVersion() {
		if ( ! function_exists( 'get_plugins' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$plugin_folder = get_plugins( '/' . plugin_basename( dirname($this->plugin_file) ) );
		$plugin_file = basename( ( $this->plugin_file ) );
		return $plugin_folder[$plugin_file]['Version'];
	} // end getVersion

	/**
	 * Returns database name prepended by WordPress' prefix
	 *
	 * @param string $db Name of database to be prepended
	 * @return string Name of database prepended by WordPress' prefix
	 */
	public function db( $db ) {
		global $wpdb;
		return $wpdb->base_prefix . $db;
	} // end db

	/**
	 * Adds a named option with specified value.
	 * 
	 * @param string $option Name of the option to add.
	 * @param mixed $new_value The value for this option name.
	 * @return bool Returns the value of the inserted rows id.
	 */
	public function addOption( $option, $value ) {
		// prefix options to prevent namespace conflicts
		$option = 'cubepoints_' . $option;

		return add_site_option( $option, $value );
	} // end addOption

	/**
	 * Returns values for a named option.
	 * 
	 * @param string $option Name of the option to retrieve.
	 * @param mixed $default Optional. The default value to return if no value is returned. Default false.
	 * @return mixed Values for the option.
	 */
	public function getOption( $option, $default = false ) {
		// prefix options to prevent namespace conflicts
		$option = 'cubepoints_' . $option;

		return get_site_option( $option, $default );
	} // end getOption

	/**
	 * Updates a named option with specified value.
	 * 
	 * @param string $option Name of the option to update.
	 * @param mixed $new_value The new value for this option name.
	 * @return bool True if option value has changed, false if not or if update failed.
	 */
	public function updateOption( $option, $new_value ) {
		// prefix options to prevent namespace conflicts
		$option = 'cubepoints_' . $option;

		return update_site_option( $option, $new_value );
	} // end updateOption

	/**
	 * Removes a named option.
	 * 
	 * @param string $option Name of the option to remove.
	 * @return bool True if the option has been successfully deleted, otherwise false.
	 */
	public function deleteOption( $option ) {
		// prefix options to prevent namespace conflicts
		$option = 'cubepoints_' . $option;

		return delete_site_option( $option );
	} // end deleteOption

	/*--------------------------------------------*
	 * Core Functions
	 *--------------------------------------------*/

	/**
	 * Gets number of points for a specifed user.
	 * 
	 * @param int $user_id User ID to retrieve points for.
	 * @return int Number of points for the user specified.
	 */
	public function getPoints( $user_id ) {
		$points = get_user_meta( $user_id, 'cubepoints', 1 );
		if ($points == '') {
			return 0;
		} else {
			return (int) $points;
		}
	} // end getPoints

	/**
	 * Sets the number of points for a specifed user.
	 * 
	 * @param int $user_id User ID to set points for
	 * @param int $points Number of points to set
	 * @return void
	 */
	public function setPoints( $user_id, $points ) {
		update_user_meta( $user_id, 'cubepoints', $points );
	} // end setPoints

	/**
	 * Adds or subtracts a certain number of points from a specifed user.
	 * 
	 * @param int $user_id User ID to set points for.
	 * @param int $points Number of points to add.
	 * @return void
	 */
	public function alterPoints( $user_id, $points ) {
		$this->setPoints( $user_id , $this->getPoints($user_id) + $points );
	} // end alterPoints

	/**
	 * Adds the prefix and suffix to a given number of points
	 * 
	 * @param int $points Number of points.
	 * @return string Points with prefix and suffix.
	 */
	public function formatPoints( $points ) {
		if($points == 0) { $points = '0'; }
		return ($points < 0 ? '&minus;' : '') . $this->getOption('points_prefix') . abs($points) . $this->getOption('points_suffix');
	} //end formatPoints

	/**
	 * Prints the number of points a specified user has
	 * 
	 * @param int $user_id Optional. User ID to retrieve points for. Defaults to current logged in user.
	 * @param bool $print Optional. True to print. Default true.
	 * @param bool $format Optional. True to format with point prefix and suffix. Default true.
	 * @return string Number of points for display.
	 */
	public function displayPoints( $user_id = null , $print = true , $format = true ) {
		if ( $user_id == null ) {
			$user_id = $this->currentUserId();
		}
		$points = $this->getPoints( $user_id );
		if ( $format ) {
			$points = $this->formatPoints( $points );
		}
		if ( $print ) {
			echo $points;
		}
		return $points;
	} // end displayPoints

	/**
	 * Retrieves a transaction meta from database
	 *
	 * @param id $transaction_id Transaction ID.
	 * @param string $meta_key The key of the meta to delete.
	 * @return mixed Value of the meta for the given transaction.
	 */
	public function getTransactionMeta( $transaction_id, $meta_key ) {
		global $wpdb;
		$meta_value = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_value FROM `{$this->db('cubepoints_meta')}` WHERE txn_id = %d AND meta_key = %s",
				$transaction_id,
				$meta_key
			)
		);
		if( is_serialized($meta_value) ){
			$meta_value = unserialize($meta_value);
		}
		return $meta_value;
	} // end getTransactionMeta

	/**
	 * Adds or updates transaction meta
	 *
	 * @param id $transaction_id Transaction ID.
	 * @param string $meta_key The key of the meta.
	 * @param string $meta_value The value of the meta.
	 * @return void
	 */
	public function setTransactionMeta( $transaction_id, $meta_key, $meta_value ) {
		global $wpdb;
		if( $this->getTransactionMeta( $transaction_id, $meta_key ) == null ) {
			$wpdb->insert(
				$this->db('cubepoints_meta'),
				array(
					'txn_id' => $transaction_id,
					'meta_key' => $meta_key,
					'meta_value' => serialize($meta_value)
				),
				array( '%d', '%s', '%s' )
			);
		} else {
			$wpdb->update(
				$this->db('cubepoints_meta'),
				array( 'meta_value' => serialize($meta_value) ),
				array( 'txn_id' => $transaction_id,	'meta_key' => $meta_key	),
				array( '%s' ),
				array( '%d', '%s' )
			);
		}
	} // end setTransactionMeta

	/**
	 * Deletes a transaction meta from database
	 *
	 * @param id $transaction_id Transaction ID.
	 * @param string $meta_key The key of the meta to delete.
	 * @return void
	 */
	public function deleteTransactionMeta( $transaction_id, $meta_key ) {
		global $wpdb;
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM `{$this->db('cubepoints_meta')}` WHERE txn_id = %d AND meta_key = %s",
				$transaction_id,
				$meta_key
			)
		);
	} // end deleteTransactionMeta

	/**
	 * Retrieves all transaction metas associated to a given transaction
	 *
	 * @param id $transaction_id Transaction ID.
	 * @return array Metas associated with the given transaction.
	 */
	public function getAllTransactionMetas( $transaction_id ) {
		global $wpdb;
		$raw_metas = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_key, meta_value FROM `{$this->db('cubepoints_meta')}` WHERE txn_id = %d",
				$transaction_id
			)
		);
		$metas = array();
		foreach( $raw_metas as $meta ) {
			$metas[$meta->meta_key] = unserialize($meta->meta_value);
		}
		return $metas;
	} // end getAllTransactionMetas

	/**
	 * Adds transaction to logs database
	 *
	 * @access private
	 *
	 * @param string $type An ID used internally by CubePoints to determine the type of transaction.
	 * @param int $user_id User ID of which the transaction belongs to.
	 * @param int $points Number of points added or removed.
	 * @param array $metas Array of metas associated with transaction.
	 * @return void
	 */
	private function _addLog( $type, $user_id, $points, $metas ) {
		global $wpdb;
		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO `{$this->db('cubepoints')}` (`uid`, `type`, `points`, `timestamp`) VALUES ('%d', '%s', '%d', NOW())",
				$user_id,
				$type,
				$points
			)
		);
		$transaction_id = $wpdb->insert_id;
		
		foreach($metas as $meta) {
			$this->setTransactionMeta($transaction_id, $meta[0], $meta[1]);
		}

		do_action( 'cubepoints_addPoints', $transaction_id, $type, $user_id, $points, $metas );
	} // end _addLog

	/**
	 * Adds or subtracts points from a specified user and logs the transaction to the database
	 *
	 * @param string $type An ID used internally by CubePoints to determine the type of transaction.
	 * @param int $user_id ID of user to add or subtract points from.
	 * @param int $points Number of points added or removed.
	 * @param array $meta,... Optional. Any supplementary data associated with transaction.
	 * @return bool|array True if points were added successfully. Array of error codes if otherwise.
	 */
	public function addPoints( $type, $user_id, $points, $meta = null ) {
		$errors = array();

		if( $points == 0 )
			$errors[] = 'no_change';

		if( ! $this->getOption( 'allow_negative_points' ) && ($this->getPoints($user_id) + $points) < 0 )
			$errors[] = 'negative_points';

		$metas = func_get_args();
		array_splice( $metas, 0, 3 );
		foreach($metas as $key=>$meta){
			if( ! is_array($meta) || count($meta) != 2 ){
				unset( $metas[$key] );
			}
		}
		array_merge($metas);

		list( $type, $user_id, $points, $metas, $errors ) = apply_filters( 'cubepoints_addPoints', array( $type, $user_id, $points, $metas, $errors ) );

		if( count( $errors ) == 0 ) {
			$this->alterPoints( $user_id, $points );
			$this->_addLog( $type, $user_id, $points, $metas );
			return true;
		}
		return $errors;
	} // end addPoints

	/**
	 * Updates the number of points a specified user has and logs the transaction to the database
	 *
	 * @param string $type An ID used internally by CubePoints to determine the type of transaction.
	 * @param int $user_id ID of user to update points from.
	 * @param int $points Number of points to set.
	 * @param array $meta,... Optional. Any supplementary data associated with transaction.
	 * @return bool|array True if points were added successfully. Array of error codes if otherwise.
	 */
	public function updatePoints( $type, $user_id, $points, $meta = null ) {
		$args = func_get_args();
		$args[2] = $args[2] - $this->getPoints( $args[1] );
		return call_user_func_array( array($this, 'addPoints'), $args );
	} // end updatePoints

	/**
	 * Registers a transaction type
	 *
	 * @param string $transaction_type Slug to identify the type of transaction.
	 * @param string $transaction_name Name of the particular transaction type
	 * @param callback $description_callback Optional. Callback for the function to be called when displaying transaction description.
	 * @param int Optional. Priority of which the callback function is executed.
	 * @return void
	 */
	public function registerTransactionType( $transaction_type, $transaction_name, $description_callback = null, $priority = 10 ) {
		$this->transaction_types[$transaction_type] = $transaction_name;
		if( $description_callback != null ) {
			add_filter( "cubepoints_txn_desc_{$transaction_type}", $description_callback, $priority, 3 );
		}
	}

	/**
	 * Replace standard shortcodes with actual values
	 *
	 * @param string $text Text to be displayed
	 * @return string Text with shortcodes replaced with actual values
	 */
	public function formatText( $text ) {
		$user_id = $this->currentUserId();
		$user = $user_id ? get_user_by('id', $user_id) : false;
		$shortcodes = array(
			// number of points (with prefix and suffix)
			'%points%' => $this->displayPoints( null , false , true ),
			// number of points (without prefix and suffix)
			'%npoints%' => $this->displayPoints( null , false , false ),
			// display name of logged in user
			'%name%' => $user ? $user->display_name : '',
			// display name of logged in user
			'%firstname%' => $user ? $user->first_name : '',
			// display name of logged in user
			'%lastname%' => $user ? $user->last_name : '',
			// login id of logged in user
			'%userid%' => $user ? $user->user_login : '',
			// md5 hash of logged in user's email address
			'%emailhash%' => $user ? md5($user->user_email) : ''
		);
		$shortcodes = apply_filters('cubepoints_formatText_shortcodes', $shortcodes);
		if(is_user_logged_in()) {
			$text = preg_replace('/\[logged\-in\](.+?)\[\/logged\-in\]/is', '$1', $text);
			$text = preg_replace('/\[logged\-out\](.+?)\[\/logged\-out\]/is', '', $text);
		} else {
			$text = preg_replace('/\[logged\-in\](.+?)\[\/logged\-in\]/is', '', $text);
			$text = preg_replace('/\[logged\-out\](.+?)\[\/logged\-out\]/is', '$1', $text);
		}
		//$text = preg_replace('/\[logged\-out\](.*?)b/is', 'x', $text);
		$text = str_replace(array_keys($shortcodes), array_values($shortcodes), $text);
		$text = apply_filters('cubepoints_formatText_text', $text);
		return $text;
	}

	/*--------------------------------------------*
	 * CubePoints Modules
	 *--------------------------------------------*/

	/**
	 * Gets the object of module specified if loaded
	 *
	 * @param string $module Name of module.
	 * @return object|null Object if module specified is loaded. Null if otherwise.
	 */
	public function module( $module ) {
		if( $this->moduleLoaded( $module ) )
			return $this->loaded_modules[$module];
		else
			return null;
	} // end module

	/**
	 * Get module details from module name
	 *
	 * @param string $moduleName Name of module.
	 * @return array|bool $moduleDetails Details of a specified module.
	 */
	public function moduleDetails( $moduleName ) {
		$classVars = get_class_vars( $moduleName );
		$moduleDetails = $classVars['module'];
		return $moduleDetails;
	} // end moduleDetails

	/**
	 * Loads modules and runs activated modules
	 *
	 * @access private
	 *
	 * @return void
	 */
	private function _loadModules() {
		do_action( 'cubepoints_pre_load_modules' );
		$this->_includeModules();
		do_action( 'cubepoints_modules' );
		$activatedModules = $this->getOption('activated_modules', array());
		foreach( $activatedModules as $module ) {
				$this->_loadModule( $module );
		}
		foreach( $this->availableModules() as $module ) {
			$module_vars = get_class_vars( $module );
			if(isset($module_vars['module']['_core']) && $module_vars['module']['_core'] && ! $this->moduleActivated($module)) {
				$this->activateModule($module);
			}
		}
		do_action( 'cubepoints_modules_loaded' );
	} // end _loadModules

	/**
	 * Includes all module files in the modules directory
	 *
	 * @access private
	 *
	 * @return void
	 */
	private function _includeModules() {
		$modules = array_merge(
			glob( dirname($this->plugin_file) . '/modules/*.mod.php' ),
			glob( dirname($this->plugin_file) . '/modules/*/*.mod.php' ),
			glob( dirname($this->plugin_file) . '/core_modules/*.mod.php' ),
			glob( dirname($this->plugin_file) . '/core_modules/*/*.mod.php' )
		);
		foreach ( $modules as $module ) {
			require_once( $module );
		}
	} // end _includeModules

	/**
	 * Get names of all available modules
	 *
	 * @return array $moduleNames Names of available modules.
	 */
	public function availableModules() {
		$moduleNames = array();
		foreach( get_declared_classes() as $className ){
			if( $this->isModuleValid( $className ) ) {
				$moduleNames[] = $className;
			}
		}
		return $moduleNames;
	} // end availableModules

	/**
	 * Loads a specified module by instantiating the class and running the module
	 *
	 * @access private
	 *
	 * @param string $module Name of module.
	 * @return bool True if module loaded successfully. False if otherwise.
	 */
	private function _loadModule( $module ) {
		if( $this->moduleLoaded( $module ) )
			return false;

		if( ! $this->isModuleValid( $module ) ) {
			$this->deactivateModule( $module );
			return false;
		}

		do_action( 'cubepoints_module_prerun', get_class($this) );
		do_action( 'cubepoints_module_' . get_class($this) . '_prerun' );

		$this->loaded_modules[$module] = new $module( $this );
		$this->loaded_modules[$module]->main();

		do_action( 'cubepoints_module_postrun', get_class($this) );
		do_action( 'cubepoints_module_' . get_class($this) . '_postrun' );

		return true;
	} // end _loadModule

	/**
	 * Checks if a specified module is loaded
	 *
	 * @return bool True if module is loaded. False if otherwise.
	 */
	public function moduleLoaded( $name ) {
		return isset( $this->loaded_modules[$name] );
	} // end moduleLoaded

	/**
	 * Gets the number of loaded modules
	 *
	 * @return int Number of modules loaded.
	 */
	public function loadedModulesCount() {
		return count($this->loaded_modules);
	} // end loadedModulesCount

	/**
	 * Checks for a valid CubePoints module
	 *
	 * @param string $module Name of module.
	 * @return bool True if specified CubePoints module is valid. False if otherwise.
	 */
	public function isModuleValid( $module ) {
		if( ! class_exists( $module ) )
			return false;

		if( ! is_subclass_of( $module, 'CubePointsModule' ) )
			return false;

		$module_vars = get_class_vars( $module );

		if( empty( $module_vars['module']['name'] ) )
			return false;

		if( empty( $module_vars['module']['version'] ) )
			return false;

		if( empty( $module_vars['module']['author_name'] ) )
			return false;

		if( empty( $module_vars['module']['description'] ) )
			return false;

		return true;
	} // end isModuleValid

	/**
	 * Checks if a specified module is activated
	 *
	 * @return bool True if module is activated. False if otherwise.
	 */
	public function moduleActivated( $module ) {
		return in_array( $module, $this->getOption('activated_modules', array()) );
	} // end moduleActivated

	/**
	 * Activates a specified module
	 *
	 * @param string $module Name of module.
	 * @return bool True if module is activated successfully. False if otherwise.
	 */
	public function activateModule( $module ) {
		if( $this->moduleActivated( $module ) )
			return false;

		if( ! $this->isModuleValid( $module ) )
			return false;

		$activatedModules = $this->getOption('activated_modules', array());
		$activatedModules[] = $module;
		$this->updateOption('activated_modules', $activatedModules);
		
		$this->_loadModule($module);

		if( method_exists( $this->module($module), 'activate' ) ) {
			$this->module($module)->activate();
			do_action( 'cubepoints_module_activate', $module );
		}

		return true;
	} // end activateModule

	/**
	 * Deactivates a specified module
	 *
	 * @param string $module Name of module.
	 * @return bool True if module is deactivated successfully. False if otherwise.
	 */
	public function deactivateModule( $module ) {
		$activatedModules = $this->getOption('activated_modules');
		if( ($key = array_search($module, $activatedModules)) !== false ) {
			if( method_exists( $this->module($module), 'deactivate' ) ) {
				$this->module($module)->deactivate();
			}
			unset($activatedModules[$key]);
			$this->updateOption('activated_modules', $activatedModules);
			do_action( 'cubepoints_module_deactivate', $module );
			return true;
		} else {
			return false;
		}
	} // end deactivateModule

	/**
	 * Hook for the modules admin page for activation and deactivation of modules
	 *
	 * @return void
	 */
	public function moduleActionHook() {
		if( ! is_admin() || ( ! isset($_GET['page']) || $_GET['page'] != 'cubepoints_modules' ) || empty( $_GET['action'] ) )
			return;

		$redirUri = remove_query_arg(
						array( '_wpnonce', 'action', 'module', 'activate', 'deactivate' ),
						$_SERVER[REQUEST_URI]
					);

		if( $_GET['action'] == 'activate_module' && ! empty( $_GET['module'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'activate_module_' . $_GET['module'] ) ) {
			$this->activateModule( $_GET['module'] );
			$redirUri = add_query_arg( 'activate', 'true', $redirUri );
		}

		if( $_GET['action'] == 'deactivate_module' && ! empty( $_GET['module'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'deactivate_module_' . $_GET['module'] ) ) {
			$this->deactivateModule( $_GET['module'] );
			$redirUri = add_query_arg( 'deactivate', 'true', $redirUri );
		}

		wp_redirect( $redirUri );
		exit;
	} // end activateModule

	/*--------------------------------------------*
	 * Supporting Methods
	 *--------------------------------------------*/

	/**
	 * Gets ID of the current logged in user.
	 * 
	 * @return int|bool ID of the current logged in user. False if no user logged in.
	 */
	public static function currentUserId() {
		if( is_user_logged_in() ){
			global $current_user;
			get_currentuserinfo();
			return $current_user->ID;
		}
		else {
			return false;
		}
	} // end currentUserId

	/**
	 * Gets difference in time.
	 * 
	 * @param int $timestamp Unix timestamp.
	 * @return string Relative time difference between given timestamp and current time.
	 */
	public static function relativeTime( $timestamp ) {
		$diff = abs( time() - $timestamp );

		if ($diff > 0) {
			$chunks = array(
				array(31536000, __('year', 'cubepoints'), __('years', 'cubepoints')),
				array(2592000, __('month', 'cubepoints'), __('months', 'cubepoints')),
				array(604800, __('week', 'cubepoints'), __('weeks', 'cubepoints')),
				array(86400, __('day', 'cubepoints'), __('days', 'cubepoints')),
				array(3600, __('hour', 'cubepoints'), __('hours', 'cubepoints')),
				array(60, __('min', 'cubepoints'), __('mins', 'cubepoints')),
				array(1, __('sec', 'cubepoints'), __('secs', 'cubepoints'))
			);
			for ($i = 0, $j = count($chunks); $i < $j; $i++) {
				$seconds = $chunks[$i][0];
				if (($count = floor($diff / $seconds)) != 0) {
					break;
				}
			}
			$name = ($count == 1) ? $chunks[$i][1] : $chunks[$i][2];
		}
		else {
			$count = 0;
			$name = 'secs';
		}

		if( time() > $timestamp ) {
			return sprintf(__('%d %s ago', 'cubepoints'), $count, $name);
		} else {
			return sprintf(__('%d %s to go', 'cubepoints'), $count, $name);
		}
	} // end relativeTime

	/**
	 * Removes the a specified capability from all roles
	 *
	 * @param string $cap Capability name.
	 */
	public static function removeCapFromAllRoles( $cap ) {
		global $wp_roles;
		foreach( array_keys( $wp_roles->roles ) as $role ){
			get_role( $role )->remove_cap( $cap );
		}
	} // end removeCapFromAllRoles

	/*--------------------------------------------*
	 * Admin Pages
	 *--------------------------------------------*/

	/**
	 * Adds admin menus
	 *
	 * @param array
	 * @return void
	 */
	public function addAdminMenu( $args ) {
		$defaults = array(
			'page_title' => '',
			'menu_title' => '',
			'capability' => 'manage_options',
			'menu_slug' => '',
			'function' => null,
			'position' => 10
		);
		extract( wp_parse_args( $args, $defaults ) );
		$this->admin_menus[$menu_slug] = array(
			'page_title' => $page_title,
			'menu_title' => $menu_title,
			'capability' => $capability,
			'menu_slug' => $menu_slug,
			'function' => $function,
			'position' => $position
		);
	} // end addAdminMenu

	/**
	 * Hook to insert admin menus into WordPress
	 *
	 * @return void
	 */
	public function insertAdminMenus() {
		$admin_menus_sort = array();
		foreach($this->admin_menus as $key => $admin_menu) {
			$admin_menus_sort[$key] = $admin_menu['position'];
		}
		array_multisort($admin_menus_sort, $this->admin_menus);
		
		$toplevel_menu = null;
		$admin_screens = array();
		foreach($this->admin_menus as $key => $admin_menu) {
			if($toplevel_menu == null) {
				$toplevel_menu = $admin_menu['menu_slug'];
				add_menu_page($admin_menu['page_title'], __('CubePoints', 'cubepoints'), $admin_menu['capability'], $admin_menu['menu_slug'], $admin_menu['function']);
			}
			$screen = add_submenu_page($toplevel_menu, $admin_menu['page_title'], $admin_menu['menu_title'], $admin_menu['capability'], $admin_menu['menu_slug'], $admin_menu['function']);
			$admin_screens[$admin_menu['menu_slug']] = $screen;
			$this->admin_menus[$key]['screen'] = $screen;
		}
		do_action('cubepoints_admin_menus_loaded', $admin_screens);
	} // end insertAdminMenus

} // end CubePoints class