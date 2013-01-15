<?php
if ( ! function_exists( 'add_action' ) )
	die();
?>

<div class="wrap">
	<div id="icon-index" class="icon32"></div>
	<h2>
		<?php _e('CubePoints', 'cubepoints'); ?> <?php _e('Dashboard', 'cubepoints'); ?>
	</h2>


<?php

function cubepoints_dashboard_transactions(){
	$transactionsTable = new CubePoints_Transactions_Table('dashboard');
	$transactionsTable->prepare_items();
	$transactionsTable->display();
	printf( '<a href="admin.php?page=cubepoints_transactions" class="button">%s</a>', __('View More', 'cubepoints') );
}

function cubepoints_dashboard_overview(){
	global $cubepoints;
	printf( _n('Running CubePoints %s with %d module activated.', 'Running <strong>CubePoints %s</strong> with <strong>%d modules</strong> activated.', $cubepoints->loadedModulesCount(), 'cubepoints'), $cubepoints->getVersion(), $cubepoints->loadedModulesCount());
	echo '<ul>';
	echo '<li><a href="users.php">' . __('View and update the points of users.', 'cubepoints') . '</a></li>';
	echo '<li><a href="admin.php?page=cubepoints_settings">' . __('Change the way CubePoints work.', 'cubepoints') . '</a></li>';
	echo '<li><a href="admin.php?page=cubepoints_modules">' . __('Activate or deactivate a module.', 'cubepoints') . '</a></li>';
	echo '<li><a href="http://cubepoints.com/forums/?utm_source=plugin&utm_medium=dashboard&utm_campaign=cubepoints" target="_blank">' . __('Visit the CubePoints support forum.', 'cubepoints') . '</a></li>';
	echo '<li><a href="http://cubepoints.com/donate/?utm_source=plugin&utm_medium=dashboard&utm_campaign=cubepoints" target="_blank">' . __('Make a donation to CubePoints.', 'cubepoints') . '</a></li>';
	echo '</ul>';
}

add_meta_box( 
    'cubepoints_overview',
    __( 'CubePoints Overview' ),
    'cubepoints_dashboard_overview',
	'toplevel_page_cubepoints_dashboard',
    'normal' );

add_meta_box( 
    'cubepoints_dashboard_transactions',
    __( 'Latest Transactions' ),
    'cubepoints_dashboard_transactions',
	'toplevel_page_cubepoints_dashboard',
    'side' );

?>

<?php
	$screen = get_current_screen();
	$columns = $screen->get_columns();
?>

<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>

<div id="dashboard-widgets-wrap">
	<div id="dashboard-widgets" class="metabox-holder columns-<?php echo $columns; ?>">
		<div id='postbox-container-1' class='postbox-container'>
		<?php do_meta_boxes( $screen->id, 'normal', '' ); ?>
		</div>
		<div id='postbox-container-2' class='postbox-container'>
		<?php do_meta_boxes( $screen->id, 'side', '' ); ?>
		</div>
		<div id='postbox-container-3' class='postbox-container'>
		<?php do_meta_boxes( $screen->id, 'advanced	', '' ); ?>
		</div>
	</div>
</div>

</div>