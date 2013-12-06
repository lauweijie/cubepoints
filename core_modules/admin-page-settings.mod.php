<?php

class CubePointsAdminPageSettings extends CubePointsModule {

	public static $module = array(
		'name' => 'Admin Page: Settings',
		'version' => '1.0',
		'author_name' => 'CubePoints',
		'description' => 'Admin page to modify plugin settings.',
		'_core' => true
	);

	/**
	 * Automatically triggered when module is active
	 */
	public function main() {
		$this->cubepoints->addAdminMenu( array(
			'page_title' => __('CubePoints', 'cubepoints') . ' &ndash; ' .  __('Settings', 'cubepoints'),
			'menu_title' => __('Settings', 'cubepoints'),
			'menu_slug' => 'cubepoints_settings',
			'function' => array($this, 'adminPageSettings'),
			'position' => 20
		) );
		add_filter( 'cubepoints_admin_settings_pages', array($this, 'addSettingsPage') );
		add_action( 'admin_init', array($this, 'admin_init') );
		add_action( 'cubepoints_admin_menus_loaded', array($this, 'adminMenusLoaded') );
	}

	/**
	 * Runs when all admin pages are loaded
	 */
	public function adminMenusLoaded( $admin_screens ) {
		add_action( 'admin_print_styles-' . $admin_screens['cubepoints_settings'], array($this, 'adminStyles') );
		add_action( 'admin_print_scripts-' . $admin_screens['cubepoints_settings'], array($this, 'adminScripts') );
	}

	/**
	 * Loads CSS for the settings admin page
	 */
	public function adminStyles() {
		wp_enqueue_style( 'cubepoints_admin_settings',  plugins_url( 'css/admin-settings.css', $this->cubepoints->plugin_file ) );
	}

	/**
	 * Loads scripts for the settings admin page
	 */
	public function adminScripts() {
		wp_print_scripts( 'jquery-ui-tabs' );
	}

	/**
	 * Hook triggered when user accesses the admin area
	 */
	public function admin_init() {
		add_settings_section('points_display', __('Points Display', 'cubepoints'), array($this, 'pointsDisplaySectionDescription'), 'cubepoints_general');

		add_settings_field('cubepoints_points_name', __('Name of Currency', 'cubepoints'), array($this, 'pointsNameField'), 'cubepoints_general', 'points_display');
		register_setting( 'cubepoints', 'cubepoints_points_name', array($this, 'cubepoints_points_name_sanitize') );

		add_settings_field('cubepoints_points_prefix', __('Points Prefix', 'cubepoints'), array($this, 'pointsPrefixField'), 'cubepoints_general', 'points_display');
		register_setting( 'cubepoints', 'cubepoints_points_prefix' );

		add_settings_field('cubepoints_points_suffix', __('Points Suffix', 'cubepoints'), array($this, 'pointsSuffixField'), 'cubepoints_general', 'points_display');
		register_setting( 'cubepoints', 'cubepoints_points_suffix' );
	}

	/**
	 * Filter to add a settings page
	 */
	public function addSettingsPage( $pages ) {
		$pages['general'] = 'General Settings';
		$pages['points'] = 'Points Settings';
		return $pages;
	}

	/**
	 * Description for the points display section
	 */
	public function pointsDisplaySectionDescription() {
		echo '<p>' . __('The points prefix and suffix will be prepended and appended to the point value when it is displayed to the users.', 'cubepoints') . '</p>';
	}

	/**
	 * HTML form element for the points name field
	 */
	public function pointsNameField() {
		echo "<input id='cubepoints_points_name' name='cubepoints_points_name' size='40' type='text' value='{$this->cubepoints->getOption('points_name')}' />";
	}

	/**
	 * HTML form element for the points prefix field
	 */
	public function pointsPrefixField() {
		echo "<input id='cubepoints_points_prefix' name='cubepoints_points_prefix' size='40' type='text' value='{$this->cubepoints->getOption('points_prefix')}' />";
	}

	/**
	 * HTML form element for the points suffix field
	 */
	public function pointsSuffixField() {
		echo "<input id='cubepoints_points_suffix' name='cubepoints_points_suffix' size='40' type='text' value='{$this->cubepoints->getOption('points_suffix')}' />";
	}

	/**
	 * Callback function that sanitizes the 
	 */
	public function cubepoints_points_name_sanitize( $val ) {
		$val = wp_strip_all_tags($val);
		if($val == '') {
			$val = __('Points', 'cubepoints');
		}
		return $val;
	}

	/**
	 * HTML for the Settings page
	 */
	public function adminPageSettings() {
		$settings_pages = apply_filters('cubepoints_admin_settings_pages', array());
		?>
		<div class="wrap">
			<div id="icon-options-general" class="icon32"></div>
			<h2>
				<?php _e('CubePoints', 'cubepoints'); ?> <?php _e('Settings', 'cubepoints'); ?>
			</h2>

			<?php
				if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == true )
					echo '<div class="updated"><p>' . __( 'Settings updated.' ) . '</p></div>';
			?>

			<form name="cubepoints-settings" method="post" action="options.php">
				<?php settings_fields('cubepoints'); ?>

				<div class="ui-tabs">
					<ul class="ui-tabs-nav">
						<?php
						foreach ( $settings_pages as $settings_page_slug => $settings_page )
							echo '<li><a href="#cubepoints_' . $settings_page_slug . '">' . $settings_page . '</a></li>';
						?>
					</ul>
					<?php
					foreach ( $settings_pages as $settings_page_slug => $settings_page ) {
						echo '<div class="ui-tabs-panel ui-tabs-hide" id="cubepoints_' . $settings_page_slug . '">';
						echo '<h2>' . $settings_page . '</h2>';
						do_settings_sections('cubepoints_' . $settings_page_slug);
						echo '</div>';
					}
					?>
				</div>
				<p class="submit"><input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" /></p>
			</form>
		</div>
		<script type="text/javascript">
			jQuery(".ui-tabs").tabs({ fx: { opacity: "toggle", duration: "fast" } });
		</script>
		<?php
	}

}