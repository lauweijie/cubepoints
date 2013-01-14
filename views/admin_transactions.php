<?php
if ( ! function_exists( 'add_action' ) )
	die();
?>

<div class="wrap">
	<div id="icon-edit-pages" class="icon32"></div>
	<h2>
		<?php _e('CubePoints', 'cubepoints'); ?> <?php _e('Transactions', 'cubepoints'); ?>
	</h2>

	<table class="wp-list-table widefat plugins" cellspacing="0">
		<thead>
			<tr>
				<th scope="col" id="name"><?php _e('User', 'cubepoints'); ?></th>
				<th scope="col" id="description"><?php _e('Description', 'cubepoints'); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th scope="col" id="name"><?php _e('User', 'cubepoints'); ?></th>
				<th scope="col" id="description"><?php _e('Description', 'cubepoints'); ?></th>
			</tr>
		</tfoot>
		<tbody>
			<?php if( count($modulesToDisplay) == 0 ) : ?>
				<tr class="no-items"><td class="colspanchange" colspan="2"><?php _e('No transactions found.', 'cubepoints'); ?></td></tr>
			<?php else : ?>

				<?php
					$modulesToDisplayDetails = array();
					$modulesToDisplayNames = array();
					foreach ( $modulesToDisplay as $module ) {
						$moduleDetails = $this->moduleDetails( $module );
						$modulesToDisplayDetails[ $module ] = $moduleDetails;
						$modulesToDisplayNames[] = $moduleDetails['name'];
					}
					array_multisort($modulesToDisplayNames, $modulesToDisplayDetails);
				?>

				<?php foreach ( $modulesToDisplayDetails as $module => $moduleDetails ) : ?>
				<?php
					$moduleActivated = $this->moduleActivated( $module );
				?>
					<tr id="<?php echo $module; ?>" class="<?php echo $moduleActivated ? 'active' : 'inactive'; ?>">
						<td class="plugin-title">
							<strong><?php echo $moduleDetails['name']; ?></strong>
							<div class="row-actions-visible">
								<?php if( ! $moduleActivated ) : ?>
									<span class="activate"><a href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'activate_module', 'module' => $module ) ), 'activate_module_' . $module ); ?>" title="<?php _e('Activate this module', 'cubepoints'); ?>" class="edit"><?php _e('Activate', 'cubepoints'); ?></a></span>
								<?php else : ?>
									<span class="deactivate"><a href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'deactivate_module', 'module' => $module ) ), 'deactivate_module_' . $module ); ?>" title="<?php _e('Deactivate this module', 'cubepoints'); ?>" class="edit"><?php _e('Deactivate', 'cubepoints'); ?></a></span>
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