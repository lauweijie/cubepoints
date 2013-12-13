<?php

class CubePointsShortcode extends CubePointsModule {

	public static $module = array(
		'name' => 'Shortcode',
		'version' => '1.0',
		'author_name' => 'CubePoints',
		'description' => 'Enables the CubePoints shortcode in posts and pages.',
		'_core' => true
	);

	/**
	 * Automatically triggered when module is active
	 */
	public function main() {
		add_shortcode( 'cubepoints', array($this, 'shortcode') );
	}

	/**
	 * CubePoints Shortcode Callback
	 */
	public function shortcode( $atts, $content = null ) {
		$atts = shortcode_atts( array('loggedin' => -1), $atts );
		if( $content === null ) {
			return '';
		}
		if( $atts['loggedin'] == "1" && ! is_user_logged_in() ) {
			return '';
		}
		if( $atts['loggedin'] == "0" && is_user_logged_in() ) {
			return '';
		}
		return $this->cubepoints->formatText( $content );
	}

}