<?php

class CubePointsUserProfilePointsField extends CubePointsModule {

	public static $module = array(
		'name' => 'User Profile Points Field',
		'version' => '1.0',
		'author_name' => 'CubePoints',
		'description' => 'Adds points field to user profile.',
		'_core' => true
	);

	/**
	 * Automatically triggered when module is active
	 */
	public function main() {
		// Adds points field to user profile
		add_action( 'show_user_profile', array( $this, 'userProfilePoints' ) );
		add_action( 'edit_user_profile', array( $this, 'userProfilePoints' ) );
		add_action( 'personal_options_update', array( $this, 'userProfilePointsUpdate' ) );
		add_action( 'edit_user_profile_update', array( $this, 'userProfilePointsUpdate' ) );
		$this->cubepoints->registerTransactionType( 'admin', __('Admin Adjustments', 'cubepoints'), array($this, 'txnDescAdmin') );
	}

	/**
	 * Adds HTML form to the user profile page
	 */
	public function userProfilePoints( $user ) {
		echo '<h3>' . $this->cubepoints->getOption('points_name') . '</h3>';
		echo '<table class="form-table">';
		echo '<tr>';
		echo '<th><label for="cubepoints_points">' . __('Amount', 'cubepoints') . '</label></th>';
		echo '<td>';
		echo '<input type="text" name="cubepoints_points" id="cubepoints_points" value="' . $this->cubepoints->getPoints( $user->ID ) . '" class="regular-text"' . (current_user_can('manage_cubepoints') ? '' : ' readonly="readonly"') . ' />';
		echo '</td>';
		echo '</tr>';
		echo '</table>';
	}

	/**
	 * Process and updates points from the user profile page
	 */
	public function userProfilePointsUpdate( $user_id ) {
		if ( ! current_user_can( 'manage_cubepoints', $user_id ) )
			return;

		$points = (int) $_POST['cubepoints_points'];

		if( ! $this->cubepoints->getOption( 'allow_negative_points' ) && $points < 0 )
			$points = 0;

		$this->cubepoints->updatePoints(
			'admin',
			$user_id,
			$points,
			array( 'user', $this->currentUserId() )
		);
	}

	/**
	 * Transaction Description: admin
	 */
	public function txnDescAdmin( $description, $details, $admin_display ) {
		$user = get_userdata( $this->cubepoints->getTransactionMeta($details->id, 'user') );
		if( $user && $admin_display ) {
			return sprintf( __('Adjusted by <a href="user-edit.php?user_id=%d">%s</a>', 'cubepoints'), $user->ID, $user->user_login );
		} else {
			return __('Points adjustment', 'cubepoints');
		}
	}

}