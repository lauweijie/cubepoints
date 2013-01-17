<?php

class CubePointsAdminContextualHelp extends CubePointsModule {

	public static $module = array(
		'name' => 'Admin Contextual Help',
		'version' => '1.0',
		'author_name' => 'CubePoints',
		'description' => 'Contextual help for CubePoints admin pages.',
		'_core' => true
	);

	/**
	 * Automatically triggered when module is active
	 */
	public function main() {
		add_action( 'cubepoints_admin_menus_loaded', array($this, 'adminMenusLoaded') );
	}

	/**
	 * Runs when all admin pages are loaded
	 */
	public function adminMenusLoaded( $admin_screens ) {
		$this->admin_screens = $admin_screens;
		add_filter('contextual_help', array($this, 'adminContextualHelp'), 10, 3);
	}

	/**
	 * Contextual help for admin pages
	 */
	public function adminContextualHelp($contextual_help, $screen_id, $screen) {
		// @TODO: Update this with useful information
		if( in_array($screen_id, $this->admin_screens) ) {
			$content = '<p><strong>' . __( 'Getting started with CubePoints', 'cubepoints' ) . '</strong></p>';
			$content .= '<p>' . sprintf( __( 'Getting started with CubePoints is easy! Once you\'ve activated the plugin, head over to the <a href="%s">Settings</a> page and make CubePoints work just the way you want.', 'cubepoints' ), 'admin.php?page=cubepoints_settings' ) . '</p>';
			$screen->add_help_tab( array(
				'id' => 'cubepoints_help_getting_started',
				'title' => __( 'Getting Started', 'cubepoints' ),
				'content' => $content
			) );

			$content = '<p><strong>' . __( 'Support CubePoints', 'cubepoints' ) . '</strong></p>';
			$content .= '<p>' . sprintf( __( 'Love the way CubePoints work? Support CubePoints by making a small <a href="%s" target="_blank">donation</a>!', 'cubepoints' ), 'http://cubepoints.com/donate/?utm_source=plugin&utm_medium=contextual_help&utm_campaign=cubepoints' ) . '</p>';
			$screen->add_help_tab( array(
				'id' => 'cubepoints_help_donate',
				'title' => __( 'Donate', 'cubepoints' ),
				'content' => $content
			) );
		}
	}
	
}