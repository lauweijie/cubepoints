<?php

class CubePointsPostPoints extends CubePointsModule {

	public static $module = array(
		'name' => 'Post Points',
		'version' => '1.0',
		'author_name' => 'CubePoints',
		'description' => 'Awards points to users for publishing posts.',
		'_core' => true
	);

	/**
	 * Automatically triggered when module is activated
	 */
	public function activate() {
		$this->cubepoints->addOption( 'post_points' , 10 );
	}

	/**
	 * Automatically triggered when module is active
	 */
	public function main() {
		add_action( 'publish_post', array($this, 'newPost'), 10, 2 );
		add_action( 'admin_init', array($this, 'admin_init') );
		$this->cubepoints->registerTransactionType( 'post', __('Posts', 'cubepoints'), array($this, 'txnDescPost'), 10 );
	}

	/**
	 * Triggers when a post is posted
	 */
	public function newPost( $post_id ) {
		$post = get_post( $post_id );
		$post_author_id = $post->post_author;
		global $wpdb;
		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM `{$this->cubepoints->db('cubepoints')}` LEFT JOIN `{$this->cubepoints->db('cubepoints_meta')}` ON {$this->cubepoints->db('cubepoints')}.id = {$this->cubepoints->db('cubepoints_meta')}.txn_id WHERE type = 'post' AND meta_key = 'post_id' AND meta_value = %d",
				$post_id
			)
		);
		// prevents double crediting of points whenever a post is updated
		if($count == 0) {
			$this->cubepoints->addPoints( 'post', $post_author_id, $this->cubepoints->getOption('post_points'), array('post_id', $post_id) );
		}
	}

	/**
	 * Hook triggered when user accesses the admin area
	 */
	public function admin_init() {
		add_settings_section('posts', __('Posts', 'cubepoints'), array($this, 'postsSectionDescription'), 'cubepoints_points');
		add_settings_field('cubepoints_post_points', __('Points Per Post', 'cubepoints'), array($this, 'postPointsField'), 'cubepoints_points', 'posts');
		register_setting( 'cubepoints', 'cubepoints_post_points', 'intval' );
	}

	/**
	 * Description for the points display section
	 */
	public function postsSectionDescription() {
		echo '<p>' . __('Award points to post authors when posts are published.', 'cubepoints') . '</p>';
	}

	/**
	 * HTML form element for the points per post field
	 */
	public function postPointsField() {
		echo "<input id='cubepoints_post_points' name='cubepoints_post_points' size='40' type='text' value='{$this->cubepoints->getOption('post_points')}' />";
	}

	/**
	 * Transaction Description: post
	 */
	public function txnDescPost( $description, $details, $admin_display ) {
		$post_id = $this->cubepoints->getTransactionMeta($details->id, 'post_id');
		$post = get_post( $post_id );
		if( $post !== null ) {
			$post_title = $post->post_title;
			$post_permalink = get_permalink( $post_id );
			return sprintf( __('Published a post: <a href="%s">%s</a>', 'cubepoints'), $post_permalink, $post_title );
		} else {
			return __('Published a post', 'cubepoints');
		}
	}

}