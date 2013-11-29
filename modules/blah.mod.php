<?php

class Blah extends CubePointsModule {

	public static $module = array(
		'name' => 'The Blah',
		'version' => '0.1',
		'module_uri' => 'http://jon.sg/blah',
		'author_name' => 'Blah Co',
		'author_uri' => 'http://blah.sg',
		'description' => 'Blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah blah.',
		'settings_link' => 'admin.php?page=cubepoints_settings#blah',
		'_core' => false
	);

	public function main() {
		
	}
	
	public function activate() {
		echo 'This plugin is activating!';
	}
	public function deactivate() {
		echo 'This plugin is deactivating!';
	}
	
}



























