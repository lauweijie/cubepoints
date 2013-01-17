<?php

class CubePointsUsersPointsColumn extends CubePointsModule {

	public static $module = array(
		'name' => 'User Points Column',
		'version' => '1.0',
		'author_name' => 'CubePoints',
		'description' => 'Adds a column displaying the points a user has in the WordPress users table.',
		'_core' => true
	);

	/**
	 * Automatically triggered when module is active
	 */
	public function main() {
		// Add points column to the users table
		add_action( 'manage_users_custom_column',  array( $this, 'manageUsersCustomColumn' ), 10, 3 );
		add_filter( 'manage_users_columns', array( $this, 'manageUsersColumns' ) );

		// Add points column to network admin users table
		add_filter( 'wpmu_users_columns', array( $this, 'manageUsersColumns' ) );
	}

	/**
	 * Register the points column
	 */
	public function manageUsersColumns( $columns ) {
		$columns['cubepoints'] = __('Points', 'cubepoints');
		return $columns;
	}

	/**
	 * Display the column content
	 */
	public function manageUsersCustomColumn( $value, $column_name, $user_id ) {
        if ( 'cubepoints' != $column_name )
           return $value;
        return $this->cubepoints->displayPoints( $user_id, false );
	}

}