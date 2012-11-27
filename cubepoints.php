<?php
/*
Plugin Name: CubePoints
Plugin URI: http://cubepoints.com
Description: CubePoints is a point management system for sites running on WordPress. Users can earn virtual credits on your site by posting comments, creating posts, or even by logging in each day! Install CubePoints and watch your visitor interaction soar by offering them points which could be used to view certain posts, exchange for downloads or even real items!
Version: 4.0-dev
Author: Jonathan Lau & Peter Zhang
Author URI: http://cubepoints.com
Author Email: developers@cubepoints.com
License:

  Copyright 2012 CubePoints (developers@cubepoints.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

class CubePoints {

	/*--------------------------------------------*
	 * Properties
	 *--------------------------------------------*/

	private $loaded_modules = array();

	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/
	
	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	function __construct() {
	
		// load modules
		$this->_loadModules();
		
		// load plugin text domain
		add_action( 'init', array( $this, 'textdomain' ) );

		// Register admin styles and scripts
		add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );
	
		// Register site styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_scripts' ) );

		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
		register_uninstall_hook( __FILE__, array( $this, 'uninstall' ) );
		
	    /*
	     * TODO:
	     * Define the custom functionality for your plugin. The first parameter of the
	     * add_action/add_filter calls are the hooks into which your code should fire.
	     *
	     * The second parameter is the function name located within this class. See the stubs
	     * later in the file.
	     *
	     * For more information: 
	     * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
	     */
	    add_action( 'TODO', array( $this, 'action_method_name' ) );
	    add_filter( 'TODO', array( $this, 'filter_method_name' ) );
		
		do_action( 'cubepoints_loaded' );

	} // end constructor

	/**
	 * Fired when the plugin is activated.
	 *
	 * @params	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public function activate( $network_wide ) {
		// check if it is network-wide; if so, run the activation function for each blog id
		if( function_exists('is_multisite') && is_multisite() && $network_wide ){
			global $wpdb;
			$curr_blogid = $wpdb->blogid;
			$blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs"));
			foreach($blogids as $blogid){
				switch_to_blog($blogid);
				$this->_activate();
			}
			switch_to_blog($curr_blogid);
		} else {
			$this->_activate();
		}
	} // end activate

	/**
	 * Sets up default options and creates database.
	 *
	 * @access private
	 */
	private function _activate() {
		// creates database
		global $wpdb;
		if( (int) $this->getOption('db_version', 0) < 1 || $wpdb->get_var("SHOW TABLES LIKE '" . $this->dbName() . "'") != $this->dbName() ) {
			$sql = "CREATE TABLE " . $this->dbName() . " (
				  id bigint(20) NOT NULL AUTO_INCREMENT,
				  uid bigint(20) NOT NULL,
				  type VARCHAR(256) NOT NULL,
				  data1 TEXT NOT NULL,
				  data2 TEXT NOT NULL,
				  data3 TEXT NOT NULL,
				  points bigint(20) NOT NULL,
				  timestamp bigint(20) NOT NULL,
				  UNIQUE KEY id (id)
				);";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta($sql);
			$this->updateOption('db_version', 1);
		}
		
		// adds default options
		$options = array(
			'auth_key' => substr( md5(uniqid()) , 3 , 10 ),
			'version' => $this->getVersion(),
			'prefix' => '$',
			'suffix' => '',
			'activated_modules' => array(),
			'comment_points' => 5,
			'del_comment_points' => 5,
			'reg_points' => 100,
			'post_points' => 20
		);
		foreach( $options as $option_name => $option_value )
			$this->updateOption( $option_name, $option_value );
	} // end _activate

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @params	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public function deactivate( $network_wide ) {
		// TODO define deactivation functionality here		
	} // end deactivate

	/**
	 * Fired when the plugin is uninstalled.
	 *
	 * @params	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public function uninstall( $network_wide ) {
		// check if it is network-wide; if so, run the uninstall function for each blog id
		if( function_exists('is_multisite') && is_multisite() && $network_wide ){
			global $wpdb;
			$curr_blogid = $wpdb->blogid;
			$blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs"));
			foreach($blogids as $blogid){
				switch_to_blog($blogid);
				$this->_uninstall();
			}
			switch_to_blog($curr_blogid);
		} else {
			$this->_uninstall();
		}
	} // end uninstall

	/**
	 * Removes plugin options and database.
	 *
	 * @access private
	 */
	private function _uninstall() {
		
		// TODO test uninstall
		
		// removes database
		global $wpdb;
		$sql = "DROP TABLE '" . $this->dbName() . "';" ;
		$wpdb->query($sql);
		$this->deleteOption('cp_db_version');
		
		// removes plugin options
		$options = array(
			'auth_key',
			'version',
			'prefix',
			'suffix',
			'comment_points',
			'del_comment_points',
			'reg_points',
			'post_points'
		);
		foreach( $options as $option_name )
			$this->deleteOption( $option_name );

	} // end _uninstall

	/**
	 * Loads the plugin text domain for translation
	 */
	public function textdomain() {
		load_plugin_textdomain( 'cubepoints', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
	}

	/**
	 * Registers and enqueues admin-specific styles.
	 */
	public function register_admin_styles() {
	
		// TODO change 'plugin-name' to the name of your plugin
		wp_enqueue_style( 'plugin-name-admin-styles', plugins_url( 'plugin-name/css/admin.css' ) );
	
	} // end register_admin_styles

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 */	
	public function register_admin_scripts() {
	
		// TODO change 'plugin-name' to the name of your plugin
		wp_enqueue_script( 'plugin-name-admin-script', plugins_url( 'plugin-name/js/admin.js' ) );
	
	} // end register_admin_scripts

	/**
	 * Registers and enqueues plugin-specific styles.
	 */
	public function register_plugin_styles() {
	
		// TODO change 'plugin-name' to the name of your plugin
		wp_enqueue_style( 'plugin-name-plugin-styles', plugins_url( 'plugin-name/css/display.css' ) );
	
	} // end register_plugin_styles

	/**
	 * Registers and enqueues plugin-specific scripts.
	 */
	public function register_plugin_scripts() {
	
		// TODO change 'plugin-name' to the name of your plugin
		wp_enqueue_script( 'plugin-name-plugin-script', plugins_url( 'plugin-name/js/display.js' ) );
	
	} // end register_plugin_scripts

	/**
	 * Returns current plugin version.
	 * 
	 * @return string Plugin version.
	 */
	public function getVersion() {
		if ( ! function_exists( 'get_plugins' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
		$plugin_file = basename( ( __FILE__ ) );
		return $plugin_folder[$plugin_file]['Version'];
	} // end getVersion

	/**
	 * Returns the database name
	 */
	public function dbName() {
		global $wpdb;
		return $wpdb->base_prefix . 'cubepoints';
	} // end dbName

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

		return get_option( $option, $default );

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

		return update_option( $option, $new_value );

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

		return delete_option( $option );

	} // end deleteOption

	/*--------------------------------------------*
	 * Core Functions
	 *---------------------------------------------*/

	/**
	 * Gets difference in time.
	 * 
	 * @param int $timestamp Unix timestamp.
	 * @return string Relative time difference between given timestamp and current time.
	 */
	public function relativeTime( $timestamp ) {
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
	 * Gets ID of the current logged in user.
	 * 
	 * @return int|bool ID of the current logged in user. False if no user logged in.
	 */
	public function currentUserId() {
		if( ! is_user_logged_in() ){
			global $current_user;
			get_currentuserinfo();
			return $current_user->ID;
		}
		else {
			return false;
		}
	} // end currentUserId

	/**
	 * Gets number of points for a specifed user.
	 * 
	 * @param int $user_id User ID to retrieve points for.
	 * @return int Number of points for the user specified.
	 */
	public function getPoints( $user_id ) {
		$points = get_user_meta($user_id, 'cubepoints', 1);
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
		update_user_meta($uid, 'cubepoints', $points);
	} // end setPoints

	/**
	 * Adds or subtracts a certain number of points from a specifed user.
	 * 
	 * @param int $user_id User ID to set points for.
	 * @param int $points Number of points to add.
	 * @return void
	 */
	public function alterPoints( $user_id, $points ){
		$this->setPoints( $user_id , $this->getPoints($user_id) + $points );
	} // end addPoints

	/**
	 * Adds the prefix and suffix to a given number of points
	 * 
	 * @param int $points Number of points.
	 * @return string Points with prefix and suffix.
	 */
	public function formatPoints( $points ){
		if($points == 0) { $points = '0'; }
		return $this->getOption('cp_prefix') . $points . $this->getOption('cp_suffix');
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
	 * Adds transaction to logs database
	 * 
	 * @access private
	 *
	 * @param string $type An ID used internally by CubePoints to determine the type of transaction.
	 * @param int $user_id User ID of which the transaction belongs to.
	 * @param int $points Number of points added or removed.
	 * @param mixed $data1 Optional. Any supplementary data associated with transaction.
	 * @param mixed $data2 Optional. Any supplementary data associated with transaction.
	 * @param mixed $data3 Optional. Any supplementary data associated with transaction.
	 * @return void
	 */
	public function _addLog( $type, $user_id, $points, $data1 = null, $data2 = null, $data3 = null ){
		list($data1, $data2, $data3) = array_map('serialize', array($data1, $data2, $data3));
		global $wpdb;
		$wpdb->query("INSERT INTO `" . $this->dbName() . "` (`uid`, `type`, `data1`, `data2`, `data3`, `points`, `timestamp`) " .
					  "VALUES ('".$user_id."', '".$type."', '".$data1."', '".$data2."', '".$data3."', '".$points."', ".time().");");
	} // end _addLog

	/**
	 * Adds or subtracts points from a specified user and logs the transaction to the database
	 *
	 * @param string $type An ID used internally by CubePoints to determine the type of transaction.
	 * @param int $user_id ID of user to add or subtract points from.
	 * @param int $points Number of points added or removed.
	 * @param mixed $data1 Optional. Any supplementary data associated with transaction.
	 * @param mixed $data2 Optional. Any supplementary data associated with transaction.
	 * @param mixed $data3 Optional. Any supplementary data associated with transaction.
	 * @return void
	 */
	public function addPoints( $type, $user_id, $points, $data1 = null, $data2 = null, $data3 = null ){
		$continue = true;
		$points = apply_filters( 'cubepoints_addPoints', $type, $user_id, $points, $data1, $data2, $data3, $continue );
		if( $continue ) {
			$this->alterPoints( $user_id, $points );
			$this->_addLog( $type, $user_id, $points, $data1, $data2, $data3 );
			do_action( 'cubepoints_addPoints', $type, $user_id, $points, $data1, $data2, $data3 );
		}
	} // end addPoints

	/**
	 * Updates the number of points a specified user has and logs the transaction to the database
	 *
	 * @param string $type An ID used internally by CubePoints to determine the type of transaction.
	 * @param int $user_id ID of user to update points from.
	 * @param int $points Number of points to set.
	 * @param mixed $data1 Optional. Any supplementary data associated with transaction.
	 * @param mixed $data2 Optional. Any supplementary data associated with transaction.
	 * @param mixed $data3 Optional. Any supplementary data associated with transaction.
	 * @return void
	 */
	public function updatePoints( $type, $user_id, $points, $data1 = null, $data2 = null, $data3 = null ){
		$pointsToAdd = $points - $this->getPoints( $user_id );
		addPoints( $type, $user_id, $pointsToAdd, $data1, $data2, $data3 );
	} // end updatePoints

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
	}

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

		if( ! class_exists( $module ) ) {
			$this->deactivateModule( $module );
			return false;
		}

		if( ! is_subclass_of( $module, 'CubePointsModule' ) )
			return false;

		do_action( 'cubepoints_module_prerun', get_class($this) );
		do_action( 'cubepoints_module_' . get_class($this) . '_prerun' );

		$this->loaded_modules[$module] = new $module;
		$this->loaded_modules[$module]->main();

		do_action( 'cubepoints_module_postrun', get_class($this) );
		do_action( 'cubepoints_module_' . get_class($this) . '_postrun' );

		return true;
	}

	/**
	 * Checks if a specified module is loaded
	 *
	 * @return bool True if module is loaded. False if otherwise.
	 */
	public function moduleLoaded( $name ) {
		return isset( $this->loaded_modules[$name] );
	}

	/**
	 * Includes all module files in the modules directory
	 *
	 * @access private
	 *
	 * @return void
	 */
	private function _includeModules() {
		$modules = array_merge(
			glob( ABSPATH . PLUGINDIR . '/' . dirname(plugin_basename(__FILE__)) . '/modules/*.mod.php' ),
			glob( ABSPATH . PLUGINDIR . '/' . dirname(plugin_basename(__FILE__)) . '/modules/*/*.mod.php' )
		);
		foreach ( $modules as $module ) {
			require_once( $module );
		}
	}

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
		do_action( 'cubepoints_module_activation' );
		$activatedModules = $this->getOption('activated_modules');
		foreach( $activatedModules as $module ) {
				$this->_loadModule( $module );
		}
		do_action( 'cubepoints_modules_loaded' );
	}

	/**
	 * Checks if a specified module is activated
	 *
	 * @return bool True if module is activated. False if otherwise.
	 */
	public function moduleActivated( $module ) {
		return in_array( $module, $this->getOption('activated_modules') );
	}

	/**
	 * Activates a specified module
	 *
	 * @return bool True if module is activated successfully. False if otherwise.
	 */
	public function activateModule( $module ) {
		if( $this->moduleActivated( $module ) )
			return false;

			if( ! class_exists( $module ) )
			return false;

			if( ! is_subclass_of( $module, 'CubePointsModule' ) )
			return false;

		$activatedModules = $this->getOption('activated_modules');
		$activatedModules[] = $module;
		$this->updateOption('activated_modules', $activatedModules);

		return true;
	}

	/**
	 * Deactivates a specified module
	 *
	 * @return bool True if module is deactivated successfully. False if otherwise.
	 */
	public function deactivateModule( $module ) {
		$activatedModules = $this->getOption('activated_modules');
		if( ($key = array_search($module, $activatedModules)) !== false ) {
			unset($activatedModules[$key]);
			$this->updateOption('activated_modules', $activatedModules);
			return true;
		} else {
			return false;
		}
	}

} // end CubePoints class

abstract class CubePointsModule {

	abstract public function main();

} // end CubePointsModule class

if ( function_exists( 'add_action' ) ) {
	add_action('plugins_loaded', create_function('', 'global $cubepoints; $cubepoints = new CubePoints;'));
}