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

function my_custom_menu_page(){
	echo 'hiworld';
}

add_meta_box( 
    'my-custom-meta-box',
    __( 'My Custom Meta Box' ),
    'my_custom_menu_page',
	'cubepoints_page_cubepoints_dashboard',
    'normal' );


add_meta_box( 
    'my-custom-meta-box-two',
    __( 'My Custom Meta Box Two' ),
    'my_custom_menu_page',
	'cubepoints_page_cubepoints_dashboard',
    'normal' );


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