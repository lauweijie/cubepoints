<?php

class CubePointsUserProfilePointsField extends CubePoints_Module {

	public static $module = array(
		'name' => 'User Profile Points Field',
		'version' => '1.0',
		'author_name' => 'CubePoints',
		'description' => 'Adds points field to user profile.',
		'_core' => true
	);

	public function main() {
		// Adds points field to user profile
		add_action( 'show_user_profile', array( $this, 'userProfilePoints' ) );
		add_action( 'edit_user_profile', array( $this, 'userProfilePoints' ) );
		add_action( 'personal_options_update', array( $this, 'userProfilePointsUpdate' ) );
		add_action( 'edit_user_profile_update', array( $this, 'userProfilePointsUpdate' ) );

		// Adds filter for transaction log
		add_filter( 'cubepoints_txn_desc_admin', array($this, 'txnDescAdmin'), 10, 3 );
	}

	/**
	 * Adds HTML form to the user profile page
	 *
	 * @param object $user WP_User object
	 * @return void
	 */
	public function userProfilePoints( $user ) {
		echo '<h3>' . __('Points', 'cubepoints') . '</h3>';
		echo '<table class="form-table">';
		echo '<tr>';
		echo '<th><label for="cubepoints_points">' . __('Number of Points', 'cubepoints') . '</label></th>';
		echo '<td>';
		echo '<input type="text" name="cubepoints_points" id="cubepoints_points" value="' . $this->cubepoints->getPoints( $user->ID ) . '" class="regular-text"' . (current_user_can('manage_cubepoints') ? '' : ' readonly="readonly"') . ' />';
		echo '</td>';
		echo '</tr>';
		echo '</table>';
	}

	/**
	 * Process and updates points from the user profile page
	 *
	 * @param int $user_id ID of the user of which points are updated
	 * @return void
	 */
	public function userProfilePointsUpdate( $user_id ) {
		if ( ! current_user_can( 'manage_cubepoints', $user_id ) )
			return;

		$points = (int) $_POST['cubepoints_points'];

		if( ! $this->cubepoints->getOption( 'allow_negative_points' ) && $points < 0 )
			$points = 0;

		$this->cubepoints->updatePoints( 'admin', $user_id, $points, array( 'user', $this->currentUserId() ) );
	}

	/**
	 * Transaction Description: admin
	 *
	 * @return string Description of transaction
	 */
	public function txnDescAdmin( $description, $details, $admin_display ) {
		$user = get_userdata( $this->cubepoints->getTransactionMeta($details->id, 'user') );
		if( $user && $admin_display ) {
			return sprintf( __('Points adjustment by <a href="user-edit.php?user_id=%d">%s</a>', 'cubepoints'), $user->ID, $user->user_login );
		} else {
			return __('Points adjustment', 'cubepoints');
		}
	}

}