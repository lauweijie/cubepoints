<?php

class CubePointsDailyPoints extends CubePointsModule {

	public static $module = array(
		'name' => 'Daily Points',
		'version' => '1.0',
		'author_name' => 'CubePoints',
		'author_uri' => 'http://cubepoints.com/',
		'description' => 'Award points to users for visiting your site daily.'
	);

	/**
	 * Automatically triggered when module is activated
	 */
	public function activate() {
		$this->cubepoints->addOption( 'dailyPoints_points' , 5 );
		$this->cubepoints->addOption( 'dailyPoints_interval' , 86400 );
	}

	/**
	 * Automatically triggered when module is active
	 */
	public function main() {
		add_action( 'admin_init', array($this, 'admin_init') );
		$this->cubepoints->registerTransactionType( 'dailypoints', __('Daily Points', 'cubepoints'), array($this, 'txnDescComment') );
		global $wpdb;
		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM `{$this->cubepoints->db('cubepoints')}` WHERE type = 'dailypoints' AND uid = %d AND timestamp > DATE_ADD(NOW(), INTERVAL -%d SECOND)",
				$this->cubepoints->currentUserId(),
				$this->cubepoints->getOption('dailyPoints_interval')
			)
		);
		if($count == 0) {
			$this->cubepoints->addPoints( 'dailypoints', $this->cubepoints->currentUserId(), $this->cubepoints->getOption('dailyPoints_points') );
		}
	}

	/**
	 * Settings link hook
	 */
	public function settings_link() {
		return admin_url('admin.php?page=cubepoints_settings#cubepoints_points');
	}

	/**
	 * Hook triggered when user accesses the admin area
	 */
	public function admin_init() {
		add_settings_section('dailyPoints', __('Daily Points', 'cubepoints'), array($this, 'dailyPointsSectionDescription'), 'cubepoints_points');
		add_settings_field('cubepoints_dailyPoints_points', __('Points', 'cubepoints'), array($this, 'dailyPointsPointsField'), 'cubepoints_points', 'dailyPoints');
		register_setting('cubepoints_points', 'cubepoints_dailyPoints_points', 'intval');
		add_settings_field('cubepoints_dailyPoints_interval', __('Time Interval (in sec)', 'cubepoints'), array($this, 'dailyPointsIntervalField'), 'cubepoints_points', 'dailyPoints');
		register_setting('cubepoints_points', 'cubepoints_dailyPoints_interval', 'intval');
	}

	/**
	 * Description for the points display section
	 */
	public function dailyPointsSectionDescription() {
		echo '<p>' . __('Award points to users for visiting your site hourly, daily or for every specific interval.', 'cubepoints') . '</p>';
	}

	/**
	 * HTML form element for the points field
	 */
	public function dailyPointsPointsField() {
		echo "<input id='cubepoints_dailyPoints_points' name='cubepoints_dailyPoints_points' size='40' type='text' value='{$this->cubepoints->getOption('dailyPoints_points')}' />";
	}

	/**
	 * HTML form element for the interval field
	 */
	public function dailyPointsIntervalField() {
		echo "<input id='cubepoints_dailyPoints_interval' name='cubepoints_dailyPoints_interval' size='40' type='text' value='{$this->cubepoints->getOption('dailyPoints_interval')}' />";
	}

	/**
	 * Transaction Description: dailypoints
	 */
	public function txnDescComment( $description, $details, $admin_display ) {
		return __('Site visit', 'cubepoints');
	}

}