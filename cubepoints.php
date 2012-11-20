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
	 * Constructor
	 *--------------------------------------------*/
	
	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	function __construct() {
		
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

	} // end constructor

	/**
	 * Fired when the plugin is activated.
	 *
	 * @params	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
	 */
	public function activate( $network_wide ) {
		// check if it is a network activation - if so, run the activation function for each blog id
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
	 */
	private function _activate() {
		
		// creates database
		global $wpdb;
		if( (int) $this->get_option('db_version', 0) < 1 ) {
			$sql = "CREATE TABLE '" . $this->db_name . "' (
				  id bigint(20) NOT NULL AUTO_INCREMENT,
				  uid bigint(20) NOT NULL,
				  type VARCHAR(256) NOT NULL,
				  data TEXT NOT NULL,
				  key1 VARCHAR(256) NOT NULL,
				  key2 VARCHAR(256) NOT NULL,
				  points bigint(20) NOT NULL,
				  timestamp bigint(20) NOT NULL,
				  UNIQUE KEY id (id)
				);";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta($sql);
			$this->update_option('cp_db_version', 1);
		}
		
		// adds default options
		$options = array(
			'auth_key' => substr( md5(uniqid()) , 3 , 10 ),
			'version' => $this->get_version(),
			'prefix' => '$',
			'suffix' => '',
			'comment_points' => 5,
			'del_comment_points' => 5,
			'reg_points' => 100,
			'post_points' => 20
		);
		foreach( $options as $option_name => $option_value )
			$this->update_option( $option_name, $option_value );

	} // end activate_do

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @params	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
	 */
	public function deactivate( $network_wide ) {
		// TODO define deactivation functionality here		
	} // end deactivate

	/**
	 * Fired when the plugin is uninstalled.
	 *
	 * @params	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
	 */
	public function uninstall( $network_wide ) {
		// TODO define uninstall functionality here

	} // end uninstall

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
	 * @return string Plugin version
	 */
	public function get_version() {
		if ( ! function_exists( 'get_plugins' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
		$plugin_file = basename( ( __FILE__ ) );
		return $plugin_folder[$plugin_file]['Version'];
	} // end get_version

	/**
	 * Returns the database name
	 */
	public function db_name() {
		return $wpdb->base_prefix . 'cubepoints';
	} // end db_name

	/**
	 * Returns values for a named option.
	 * 
	 * @param $option Name of the option to retrieve.
	 * @param $default Optional. The default value to return if no value is returned. Default false.
	 * @return mixed Values for the option.
	 */
	function get_option($option, $default) {

		// prefix options to prevent namespace conflicts
		$option = 'cubepoints_' . $option;

		return get_option( $option, $default );

	} // end get_option


	/**
	 * Updates a named option with specified value.
	 * 
	 * @param $option Name of the option to update.
	 * @param $new_value The new value for this option name.
	 * @return bool True if option value has changed, false if not or if update failed.
	 */
	function update_option($option, $new_value) {

		// prefix options to prevent namespace conflicts
		$option = 'cubepoints_' . $option;

		return update_option( $option, $new_value );

	} // end update_option

	/*--------------------------------------------*
	 * Core Functions
	 *---------------------------------------------*/

	/**
 	 * Note:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *		  WordPress Actions: http://codex.wordpress.org/Plugin_API#Actions
	 *		  Action Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 */
	function action_method_name() {
    	// TODO define your action method here
	} // end action_method_name

	/**
	 * Note:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *		  WordPress Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *		  Filter Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 */
	function filter_method_name() {
	    // TODO define your filter method here
	} // end filter_method_name

} // end class

$cubepoints = new CubePoints();