<?php

class CubePointsAdminPageTransactions extends CubePointsModule {

	public static $module = array(
		'name' => 'Admin Page: Transactions',
		'version' => '1.0',
		'author_name' => 'CubePoints',
		'description' => 'Admin page to show the history of points transactions.',
		'_core' => true
	);

	/**
	 * Automatically triggered when module is active
	 */
	public function main() {
		$this->cubepoints->addAdminMenu( array(
			'page_title' => __('CubePoints', 'cubepoints') . ' &ndash; ' .  __('Transactions', 'cubepoints'),
			'menu_title' => __('Transactions', 'cubepoints'),
			'menu_slug' => 'cubepoints_transactions',
			'function' => array($this, 'adminPageTransactions'),
			'position' => 1
		) );
		add_filter( 'set-screen-option', array($this, 'adminPageTransactionsScreenOptionsSet'), 10, 3 );
		add_action( 'cubepoints_admin_menus_loaded', array($this, 'adminMenusLoaded') );
	}

	/**
	 * Runs when all admin pages are loaded
	 */
	public function adminMenusLoaded( $admin_pages ) {
		$page = $admin_pages['cubepoints_transactions'];
		add_action( "load-{$page}", array($this, 'adminPageTransactionsScreenOptions') );
	}

	/**
	 * HTML for the Transactions page
	 */
	public function adminPageTransactions() {
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
			.column-user {
				width: 20%;
			}
			.column-points {
				width: 10%;
			}
			.column-time {
				width: 18%;
			}
		</style>
		<?php
	}

	/**
	 * Screen options for the Transactions admin page
	 */
	public function adminPageTransactionsScreenOptions() {
		$option = 'per_page';
		$args = array(
			 'label' => 'Transactions',
			 'default' => 10,
			 'option' => 'cubepoints_transactions_per_page'
			 );
		add_screen_option( $option, $args );
	}

	/**
	 * Filter for saving screen options in the Transactions admin page
	 */
	public function adminPageTransactionsScreenOptionsSet($status, $option, $value) {
		if ( 'cubepoints_transactions_per_page' == $option ) {
			return $value;
		}
	}

}

/**
 * WP_List_Table class to generate the table of transactions
 */

if(!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CubePoints_Transactions_Table extends WP_List_Table {

    function __construct() {
        global $status, $page;
        parent::__construct( array(
            'singular' => __('transaction', 'cubepoints'),
            'plural' => __('transactions', 'cubepoints'),
            'ajax' => false
        ) );
    }

    function get_columns() {
        $columns = array(
            'user' => __('User', 'cubepoints'),
			'description' => __('Description', 'cubepoints'),
            'points' => __('Points', 'cubepoints'),
			'time' => __('Time', 'cubepoints')
        );
        return $columns;
    }

    function column_default($item, $column_name) {
		return print_r($item, true);
    }

    function column_user($item) {
		$user = get_userdata( $item->uid );
		if( $user ) {
			return sprintf( '<a href="user-edit.php?user_id=%d">%s</a>', $user->id, $user->user_login );
		} else {
			return sprintf( '<i>%s #%d</i>', __('user', 'cubepoints'), $item->uid );
		}
    }

    function column_points($item) {
		global $cubepoints;
		return $cubepoints->formatPoints($item->points);
    }

    function column_description($item) {
		$description = apply_filters( "cubepoints_txn_desc_{$item->type}", '', $item, 1 );
		if( has_filter( "cubepoints_txn_desc_{$item->type}" ) === false || $description == '' ) {
			$description = sprintf( '<span style="font-style: italic;" title="%s: %s">%s</span>', __('Transaction Type', 'cubepoints'), $item->type, __('no description', 'cubepoints') );
		}
		return $description;
    }

    function column_time($item) {
		global $cubepoints;
		$unix_time = $item->unix_timestamp;
		$relative_time = $cubepoints->relativeTime( $unix_time );
		return sprintf('<span title="%s">%s</span>', $item->timestamp, $relative_time );
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'user' => array('uid', false),
            'description' => array('type', false),
            'points' => array('points', false),
            'time' => array('timestamp', false),
        );
        return $sortable_columns;
    }

    function prepare_items() {
        global $wpdb;
		global $cubepoints;

		$cubepoints_table = $cubepoints->db('cubepoints');

		$user = get_current_user_id();
		$screen = get_current_screen();
		$screen_option = $screen->get_option('per_page', 'option');
		$per_page = get_user_meta($user, $screen_option, true);

		if ( empty ($per_page) || $per_page < 1 ) {
			$per_page = $screen->get_option( 'per_page', 'default' );
		}

        $current_page = $this->get_pagenum();
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
		$filter = '';

        $this->_column_headers = array($columns, $hidden, $sortable);

		if( !empty($_REQUEST['s']) ) {
			$filter_user = get_user_by('login', $_REQUEST['s']);
				$filter = "WHERE `uid` = {$filter_user->ID}";
		}

		$orderby_allowed = array('id', 'uid', 'points', 'type', 'timestamp');
		$order_allowed = array('asc', 'desc');

		if( empty($_REQUEST['orderby']) || ! in_array($_REQUEST['orderby'], $orderby_allowed) ) {
			$orderby = 'id';
		} else {
			$orderby = $_REQUEST['orderby'];
		}

		if( empty($_REQUEST['order']) || ! in_array($_REQUEST['order'], $order_allowed) ) {
			$order = 'desc';
		} else {
			$order = $_REQUEST['order'];
		}

        $total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$cubepoints_table} {$filter}" );
		$total_pages = ceil( $total_items / $per_page );

		if( $current_page > $total_pages ) {
			$current_page = $total_pages;
		}

		$limit_start = ($current_page - 1) * $per_page;
		if( $limit_start < 0 ) {
			$limit_start = 0;
		}

        $this->items = $wpdb->get_results( "SELECT *, UNIX_TIMESTAMP(timestamp) as unix_timestamp FROM {$cubepoints_table} {$filter} ORDER BY {$orderby} {$order} LIMIT {$limit_start}, {$per_page}" );

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => $total_pages
        ) );
    }

}