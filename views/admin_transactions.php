<?php
if ( ! function_exists( 'add_action' ) )
	die();
?>

<div class="wrap">
	<div id="icon-edit-pages" class="icon32"></div>
	<h2>
		<?php _e('CubePoints', 'cubepoints'); ?> <?php _e('Transactions', 'cubepoints'); ?>
	</h2>

	<?php
	    $transactionsTable = new CubePoints_Transactions_Table();
		$transactionsTable->prepare_items();
	?>

	<form id="transactions-user-filter" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php $transactionsTable->search_box( __('Filter by User', 'cubepoints'), 'search_user' ); ?>
	</form>

	<form id="transactions-filter" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php $transactionsTable->display(); ?>
	</form>
</div>

<style type="text/css">
	.wp-list-table.transactions td {
		padding: 5px 8px;
	}
</style>