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
			$content .= '<p>' . sprintf( __( 'Getting started with CubePoints is easy! Once you\'ve activated the plugin, head over to <a href="%s">Settings</a> and make CubePoints work just the way you want.', 'cubepoints' ), 'admin.php?page=cubepoints_settings' ) . '</p>';
			$screen->add_help_tab( array(
				'id' => 'cubepoints_help_getting_started',
				'title' => __( 'Getting Started', 'cubepoints' ),
				'content' => $content
			) );

			$content = '<p><strong>' . __( 'Support CubePoints', 'cubepoints' ) . '</strong></p>';
			$content .= '<p>' . sprintf( __( 'Love the way CubePoints work? Support CubePoints by making a small <a href="%s" target="_blank">donation</a>!', 'cubepoints' ), CubePoints::$URL_CP_DONATE) . '</p>';
			$screen->add_help_tab( array(
				'id' => 'cubepoints_help_donate',
				'title' => __( 'Donate', 'cubepoints' ),
				'content' => $content
			) );

			$content = '<p><strong>' . __( 'Support', 'cubepoints' ) . '</strong></p>';
			$content .= '<p>' . sprintf( __( 'For full documentation of functions and APIs, read the <a href="%s" target="_blank">Documentations</a> or visit the <a href="%s" target="_blank">CubePoints Forum</a>.', 'cubepoints' ), CubePoints::$URL_CP_DOCS, CubePoints::$URL_CP_FORUM) . '</p>';
			$screen->add_help_tab( array(
				'id' => 'cubepoints_help_docs',
				'title' => __( 'Support', 'cubepoints' ),
				'content' => $content
			) );
		}
	}
	
}