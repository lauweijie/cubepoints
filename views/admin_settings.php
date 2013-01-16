<?php
if ( ! function_exists( 'add_action' ) )
	die();
?>

<?php global $cubepoints; ?>

<div class="wrap">
	<div id="icon-options-general" class="icon32"></div>
	<h2>
		<?php _e('CubePoints', 'cubepoints'); ?> <?php _e('Settings', 'cubepoints'); ?>
	</h2>
	
	<form name="cubepoints-settings" method="post" action="admin.php?page=cubepoints_settings">

		<?php wp_nonce_field( 'cubepoints-settings' ); ?>

		<h3><?php _e('Points Display', 'cubepoints'); ?></h3>

		<table class="form-table"><tbody>
			<tr>
				<th><label for="cubepoints_prefix">Points Prefix</label></th>
				<td><input name="cubepoints_prefix" id="cubepoints_prefix" type="text" value="<?php echo $cubepoints->getOption('prefix'); ?>" class="regular-text code"></td>
			</tr>
			<tr>
				<th><label for="cubepoints_suffix">Points Suffix</label></th>
				<td><input name="cubepoints_suffix" id="cubepoints_suffix" type="text" value="<?php echo $cubepoints->getOption('suffix'); ?>" class="regular-text code"></td>
			</tr>
			<tr>
				<th><label for="cubepoints_display_example">Points Display Example</label></th>
				<td><input name="cubepoints_display_example" id="cubepoints_display_example" type="text" value="<?php echo $cubepoints->getOption('prefix'); ?>150<?php echo $cubepoints->getOption('suffix'); ?>" class="regular-text code" readonly></td>
			</tr>
		</tbody></table>
		
		<?php do_action('cubepoints_settings_form'); ?>

	</form>
	
	<script type="text/javascript">
	function cubepointsUpdateDisplayExample(){
		jQuery('#cubepoints_display_example').val( jQuery('#cubepoints_prefix').val() + '150' + jQuery('#cubepoints_suffix').val() );
	}
	jQuery(function() {
		jQuery('#cubepoints_prefix').bind( 'keydown keyup change click mouseover mouseout', cubepointsUpdateDisplayExample );
		jQuery('#cubepoints_suffix').bind( 'keydown keyup change click mouseover mouseout', cubepointsUpdateDisplayExample );
		cubepointsUpdateDisplayExample();
	});
	</script>

</div>