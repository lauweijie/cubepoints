<?php

class CubePointsAdminPageModules extends CubePointsModule {

	public static $module = array(
		'name' => 'Admin Page: Modules',
		'version' => '1.0',
		'author_name' => 'CubePoints',
		'description' => 'Admin page to modify plugin modules.',
		'_core' => true
	);

	/**
	 * Automatically triggered when module is active
	 */
	public function main() {
		$this->cubepoints->addAdminMenu( array(
			'page_title' => __('CubePoints', 'cubepoints') . ' &ndash; ' .  __('Modules', 'cubepoints'),
			'menu_title' => __('Modules', 'cubepoints'),
			'menu_slug' => 'cubepoints_modules',
			'function' => array($this, 'adminPageModules'),
			'position' => 100
		) );
	}

	/**
	 * HTML for the Settings page
	 */
	public function adminPageModules() {
		?>
		<div class="wrap">
			<div id="icon-plugins" class="icon32"></div>
			<h2>
				<?php _e('CubePoints', 'cubepoints'); ?> <?php _e('Modules', 'cubepoints'); ?>
				<a href="<?php echo CubePoints::$URL_CP_MODULES; ?>" class="add-new-h2"><?php _e('Get More Modules', 'cubepoints'); ?></a>
			</h2>

			<?php if( isset($_GET['activate']) && $_GET['activate'] == 'true' ) : ?>
				<div id="message" class="updated"><p><?php _e( 'Module', 'cubepoints' ); ?> <strong><?php _e( 'activated', 'cubepoints' ); ?></strong>.</p></div>
			<?php elseif( isset($_GET['deactivate']) && $_GET['deactivate'] == 'true' ) : ?>
				<div id="message" class="updated"><p><?php _e( 'Module', 'cubepoints' ); ?> <strong><?php _e( 'deactivated', 'cubepoints' ); ?></strong>.</p></div>
			<?php endif; ?>

			<?php
				$currUri = remove_query_arg( array('_wpnonce', 'action', 'module', 'activate', 'deactivate'), $_SERVER['REQUEST_URI'] );
				$modules = $this->cubepoints->availableModules();
				$activeModules = array();
				$inactiveModules = array();
				$allModules = array();
				foreach( $modules as $module ) {
					$moduleDetails = $this->cubepoints->moduleDetails( $module );
					if( isset($moduleDetails['_core']) && $moduleDetails['_core'] ) {
						continue;
					}
					if( $this->cubepoints->moduleActivated( $module ) ) {
						$activeModules[] = $module;
					} else {
						$inactiveModules[] = $module;
					}
					$allModules[] = $module;
				}
				$moduleStatus = isset($_GET['module_status']) ? $_GET['module_status'] : false;
				if( empty($moduleStatus) || ($moduleStatus == 'active' && count($activeModules) == 0) || ($moduleStatus == 'inactive' && count($inactiveModules) == 0) ) {
					$moduleStatus = 'all';
				}
				if( $moduleStatus == 'active' ) {
					$modulesToDisplay = $activeModules;
				} else if( $moduleStatus == 'inactive' ) {
					$modulesToDisplay = $inactiveModules;
				} else {
					$modulesToDisplay = $allModules;
				}
				
			?>

			<?php if( count($modules) > 0 ) : ?>
				<ul class="subsubsub">
					<li class="all"><a href="<?php echo add_query_arg('module_status', 'all', $currUri); ?>"<?php echo ($moduleStatus == 'all') ? ' class="current"' : ''; ?>><?php _e('All', 'cubepoints'); ?> <span class="count">(<?php echo count($allModules); ?>)</span></a></li>
					<?php if( count($activeModules) > 0 ) : ?>
						<li class="active">| <a href="<?php echo add_query_arg('module_status', 'active', $currUri); ?>"<?php echo ($moduleStatus == 'active') ? ' class="current"' : ''; ?>><?php _e('Active', 'cubepoints'); ?> <span class="count">(<?php echo count($activeModules); ?>)</span></a></li>
					<?php endif; ?>
					<?php if( count($inactiveModules) > 0 ) : ?>
					<li class="inactive">| <a href="<?php echo add_query_arg('module_status', 'inactive', $currUri); ?>"<?php echo ($moduleStatus == 'inactive') ? ' class="current"' : ''; ?>><?php _e('Inactive', 'cubepoints'); ?> <span class="count">(<?php echo count($inactiveModules); ?>)</span></a></li>
					<?php endif; ?>
				</ul>
			<?php endif; ?>

			<table class="wp-list-table widefat plugins" cellspacing="0">
				<thead>
					<tr>
						<th scope="col" id="name"><?php _e('Module', 'cubepoints'); ?></th>
						<th scope="col" id="description"><?php _e('Description', 'cubepoints'); ?></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th scope="col" id="name"><?php _e('Module', 'cubepoints'); ?></th>
						<th scope="col" id="description"><?php _e('Description', 'cubepoints'); ?></th>
					</tr>
				</tfoot>
				<tbody>
					<?php if( count($modulesToDisplay) == 0 ) : ?>
						<tr class="no-items"><td class="colspanchange" colspan="2"><?php _e('You do not appear to have any modules available at this time.', 'cubepoints'); ?></td></tr>
					<?php else : ?>

						<?php
							$modulesToDisplayDetails = array();
							$modulesToDisplayNames = array();
							foreach ( $modulesToDisplay as $module ) {
								$moduleDetails = $this->cubepoints->moduleDetails( $module );
								$modulesToDisplayDetails[ $module ] = $moduleDetails;
								$modulesToDisplayNames[] = $moduleDetails['name'];
							}
							array_multisort($modulesToDisplayNames, $modulesToDisplayDetails);
						?>

						<?php foreach ( $modulesToDisplayDetails as $module => $moduleDetails ) : ?>
						<?php
							$moduleActivated = $this->cubepoints->moduleActivated( $module );
						?>
							<tr id="<?php echo $module; ?>" class="<?php echo $moduleActivated ? 'active' : 'inactive'; ?>">
								<td class="plugin-title">
									<strong><?php echo $moduleDetails['name']; ?></strong>
									<div class="row-actions-visible">
										<?php if( ! $moduleActivated ) : ?>
											<span class="activate"><a href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'activate_module', 'module' => $module ) ), 'activate_module_' . $module ); ?>" title="<?php _e('Activate this module', 'cubepoints'); ?>" class="edit"><?php _e('Activate', 'cubepoints'); ?></a></span>
										<?php else : ?>
											<span class="deactivate"><a href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'deactivate_module', 'module' => $module ) ), 'deactivate_module_' . $module ); ?>" title="<?php _e('Deactivate this module', 'cubepoints'); ?>" class="edit"><?php _e('Deactivate', 'cubepoints'); ?></a></span>
											<?php if( method_exists($this->cubepoints->module($module), 'settings_link') ): ?>
												| <a href="<?php echo $this->cubepoints->module($module)->settings_link(); ?>">Settings</a>
											<?php endif; ?>
										<?php endif; ?>
									</div>
								</td>
								<td class="column-description">
									<div class="plugin-description"><p><?php echo $moduleDetails['description']; ?></p></div>
									<div class="second plugin-version-author-uri">
										<?php _e('Version', 'cubepoints'); ?> <?php echo $moduleDetails['version']; ?> |
										<?php if( ! empty ( $moduleDetails['author_uri'] ) ) : ?>
											<?php _e('By', 'cubepoints'); ?> <a href="<?php echo $moduleDetails['author_uri']; ?>" title="<?php _e('Visit author homepage', 'cubepoints'); ?>"><?php echo $moduleDetails['author_name']; ?></a>
										<?php else : ?>
											<?php _e('By', 'cubepoints'); ?> <?php echo $moduleDetails['author_name']; ?>
										<?php endif; ?>
										<?php if( ! empty ( $moduleDetails['module_uri'] ) ) : ?>
											| <a href="<?php echo $moduleDetails['module_uri']; ?>" title="<?php _e('Visit module site', 'cubepoints'); ?>">Visit module site</a>
										<?php endif; ?>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

}