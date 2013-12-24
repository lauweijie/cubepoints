<?php

class CubePointsCommentPoints extends CubePointsModule {

	public static $module = array(
		'name' => 'Comment Points',
		'version' => '1.0',
		'author_name' => 'CubePoints',
		'description' => 'Awards points to users for making comments.',
		'_core' => true
	);

	/**
	 * Automatically triggered when module is activated
	 */
	public function activate() {
		$this->cubepoints->addOption( 'comment_points' , 5 );
	}

	/**
	 * Automatically triggered when module is active
	 */
	public function main() {
		add_action( 'comment_post', array($this, 'newComment'), 10, 2 );
		add_action( 'admin_init', array($this, 'admin_init') );
		$this->cubepoints->registerTransactionType( 'comment', __('Comments', 'cubepoints'), array($this, 'txnDescComment') );
	}

	/**
	 * Triggers when a comment is posted
	 */
	public function newComment( $comment_id, $status ) {
		$comment = get_comment( $comment_id );
		if($status == 1){
			$this->cubepoints->addPoints( 'comment', $this->cubepoints->currentUserId(), $this->cubepoints->getOption('comment_points'), array('comment_id', $comment_id) );
		}
	}

	/**
	 * Hook triggered when user accesses the admin area
	 */
	public function admin_init() {
		add_settings_section('comments', __('Comments', 'cubepoints'), array($this, 'commentsSectionDescription'), 'cubepoints_points');
		add_settings_field('cubepoints_comment_points', __('Points Per Comment', 'cubepoints'), array($this, 'commentPointsField'), 'cubepoints_points', 'comments');
		register_setting('cubepoints_points', 'cubepoints_comment_points', 'intval');
	}

	/**
	 * Description for the points display section
	 */
	public function commentsSectionDescription() {
		echo '<a name="comments"></a>';
		echo '<p>' . __('Award points to users for making comments on your site.', 'cubepoints') . '</p>';
	}

	/**
	 * HTML form element for the points per comment field
	 */
	public function commentPointsField() {
		echo "<input id='cubepoints_comment_points' name='cubepoints_comment_points' size='40' type='text' value='{$this->cubepoints->getOption('comment_points')}' />";
	}

	/**
	 * Transaction Description: comment
	 */
	public function txnDescComment( $description, $details, $admin_display ) {
		$comment_id = $this->cubepoints->getTransactionMeta($details->id, 'comment_id');
		$comment = get_comment( $comment_id );
		if( $comment !== null ) {
			$post_id = $comment->comment_post_ID;
			$post = get_post( $post_id );
			$post_title = $post->post_title;
			$comment_permalink = get_comment_link( $comment_id );
			return sprintf( __('Posted a comment on <a href="%s">%s</a>', 'cubepoints'), $comment_permalink, $post_title );
		} else {
			return __('Posted a comment', 'cubepoints');
		}
	}

}