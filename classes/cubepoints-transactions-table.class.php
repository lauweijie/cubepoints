<?php

if(!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CubePoints_Transactions_Table extends WP_List_Table {

	public $context = '';

    function __construct( $context = '' ) {
        global $status, $page;
		$this->context = $context;
        parent::__construct( array(
            'singular' => __('transaction', 'cubepoints'),
            'plural' => __('transactions', 'cubepoints'),
            'ajax' => false
        ) );
    }

    function get_columns() {
        $columns = array(
            'user' => __('User', 'cubepoints'),
            'points' => __('Points', 'cubepoints'),
			'description' => __('Description', 'cubepoints'),
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
		return $item->points;
    }

    function column_description($item) {
		$description = apply_filters( "cubepoints_txn_desc_{$item->type}", '', $item );
		if( has_filter( "cubepoints_txn_desc_{$item->type}" ) === false || $description == '' ) {
			$description = sprintf( '<i>%s</i>', __('no description', 'cubepoints') );
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
		if( $this->context == 'dashboard' ) {
			return array();
		}
        $sortable_columns = array(
            'user' => array('uid', false),
            'points' => array('points', false),
            'description' => array('type', false),
            'time' => array('timestamp', false),
        );
        return $sortable_columns;
    }

    function prepare_items() {
        global $wpdb;
		global $cubepoints;
		
		$cubepoints_table = $cubepoints->db('cubepoints');

		if( $this->context == 'dashboard' ) {
			$per_page = 10;
		} else {
			$user = get_current_user_id();
			$screen = get_current_screen();
			$screen_option = $screen->get_option('per_page', 'option');
			$per_page = get_user_meta($user, $screen_option, true);
		}

		if ( empty ( $per_page) || $per_page < 1 ) {
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
		
		if( $this->context == 'dashboard' ) {
			$limit_start = 0;
			$total_pages = 1;
			$total_items = $per_page;
		}

        $this->items = $wpdb->get_results( "SELECT *, UNIX_TIMESTAMP(timestamp) as unix_timestamp FROM {$cubepoints_table} {$filter} ORDER BY {$orderby} {$order} LIMIT {$limit_start}, {$per_page}" );

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => $total_pages
        ) );
    }

}