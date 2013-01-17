<?php

class CubePointsAdminPageSettingsPoints extends CubePointsModule {

	public static $module = array(
		'name' => 'Points Settings',
		'version' => '1.0',
		'author_name' => 'CubePoints',
		'description' => 'Allows points prefix and suffix to be changed from the admin page.',
		'_core' => true
	);

	public function main() {
		add_action( 'cubepoints_settings_form', array($this,'x') );
	}

	public function x() {
		echo 'x-test';
	}

}