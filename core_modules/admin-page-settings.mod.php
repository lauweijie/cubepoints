<?php

class CubePointsAdminPageSettings extends CubePointsModule {

	public static $module = array(
		'name' => 'Admin Page: Settings',
		'version' => '1.0',
		'author_name' => 'CubePoints',
		'description' => 'Admin page to modify plugin settings.',
		'_core' => true
	);

	public function main() {
		add_filter( 'cubepoints_add_admin_submenu', array($this, 'adminPageSettings_add') );
		add_action( 'admin_init', array($this, 'admin_init') );
	}

	/**
	 * Filter to add admin menu
	 */
	public function adminPageSettings_add( $submenus ) {
		$submenus[] = array(
			__('CubePoints', 'cubepoints') . ' &ndash; ' .  __('Settings', 'cubepoints'),
			__('Settings', 'cubepoints'),
			'update_core',
			'cubepoints_settings',
			array($this, 'adminPageSettings')
		);
		return $submenus;
	}

	public function admin_init() {
		add_settings_section('cubepoints_general', __('General Settings', 'cubepoints'), array($this, 'settings_general'), 'cubepoints');

		add_settings_field('cubepoints_points_prefix', 'Points Prefix', array($this, 'points_prefix_field'), 'cubepoints', 'cubepoints_general');
		register_setting( 'cubepoints', 'cubepoints_points_prefix' );

		add_settings_field('cubepoints_points_suffix', 'Points Suffix', array($this, 'points_suffix_field'), 'cubepoints', 'cubepoints_general');
		register_setting( 'cubepoints', 'cubepoints_points_suffix' );

		add_settings_field('cubepoints_points_suffix', 'Points Suffix', array($this, 'points_suffix_field'), 'cubepoints', 'cubepoints_general');
		}

	public function settings_general() {
		echo '<p>' . __('The points prefix and suffix will be prepended and appended to the point value when it is displayed to the users.', 'cubepoints') . '</p>';
	}

	public function points_prefix_field() {
		echo "<input id='cubepoints_points_prefix' name='cubepoints_points_prefix' size='40' type='text' value='{$this->cubepoints->getOption('points_prefix')}' />";
	}

	public function points_suffix_field() {
		echo "<input id='cubepoints_points_suffix' name='cubepoints_points_suffix' size='40' type='text' value='{$this->cubepoints->getOption('points_suffix')}' />";
	}

	/**
	 * HTML for the Settings page
	 */
	public function adminPageSettings() {
		?>
		<div class="wrap">
			<div id="icon-options-general" class="icon32"></div>
			<h2>
				<?php _e('CubePoints', 'cubepoints'); ?> <?php _e('Settings', 'cubepoints'); ?>
			</h2>
			
			<form name="cubepoints-settings" method="post" action="options.php">
				<?php settings_fields('cubepoints'); ?>
				<?php do_settings_sections('cubepoints'); ?>
				<p class="submit"><input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" /></p>
			</form>

		</div>
		<?php
	}

}