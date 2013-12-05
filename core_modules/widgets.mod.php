<?php

class CubePointsWidgets extends CubePointsModule {

	public static $module = array(
		'name' => 'Widgets',
		'version' => '1.0',
		'author_name' => 'CubePoints',
		'description' => 'Adds widgets to display points.',
		'_core' => true
	);

	/**
	 * Automatically triggered when module is active
	 */
	public function main() {
		// Register the Points Widget
		add_action('widgets_init', create_function('', 'return register_widget("CubePoints_Points_Widget");') );
	}

}

class CubePoints_Points_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'cubepoints_points_widget',
			__('CubePoints', 'cubepoints'),
			array( 'description' => __( 'Display the points of the current logged in user.', 'cubepoints' ), )
		);
	}

	public function widget( $args, $instance ) {
		if( $instance['hide'] && ! is_user_logged_in() ) {
			return;
		}
		$title = apply_filters( 'widget_title', $instance['title'] );
		$text = $instance['text'];
		global $cubepoints;
		$text = $cubepoints->formatText($text);
		if($instance['autop']) {
			$text = wpautop($text);
		}
		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		echo $text;
		do_action('cubepoints_points_widget_after');
		echo $args['after_widget'];
	}

 	public function form( $instance ) {
 		$default_text = '[logged-out]' . __('Login to view your points!', 'cubepoints') . '[/logged-out]' . "\n\n" . '[logged-in]' . "\n" . '<strong>' . __('Balance:', 'cubepoints') . '</strong> %points%' . "\n" . '[/logged-in]';
 		$title = isset($instance['title']) ? $instance['title'] : __( 'Points', 'cubepoints' );
 		$text = isset($instance['text']) ? $instance['text'] : $default_text;
 		$autop = isset($instance['autop']) ? $instance['autop'] : true;
 		$hide = isset($instance['hide']) ? $instance['hide'] : false;
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'text' ); ?>"><?php _e( 'Text:', 'cubepoints' ); ?></label> 
		<textarea style="font-size: 11px;" class="widefat" rows="7" id="<?php echo $this->get_field_id( 'text' ); ?>" name="<?php echo $this->get_field_name( 'text' ); ?>"><?php echo esc_attr( $text ); ?></textarea>
		</p>
		<p><small><code>%points%</code> <?php _e('Number of points (with prefix &amp; suffix)' , 'cubepoints'); ?></small></p>
		<p><small><code>%npoints%</code> <?php _e('Number of points' , 'cubepoints'); ?></small></p>
		<p><small><code>%name%</code> <?php _e('Display name of logged in user' , 'cubepoints'); ?></small></p>
		<p><small><code>%firstname%</code> <?php _e('First name' , 'cubepoints'); ?></small></p>
		<p><small><code>%lastname%</code> <?php _e('Last name' , 'cubepoints'); ?></small></p>
		<p><small><code>%userid%</code> <?php _e('Login name of user' , 'cubepoints'); ?></small></p>
		<p><small><code>%emailhash%</code> <?php _e('MD5 hash of email (useful for displaying gravatars)' , 'cubepoints'); ?></small></p>
		<p><small><code>[logged-in][/logged-in]</code> <?php _e('Text wrapped within will be displayed when a user is logged in' , 'cubepoints'); ?></small></p>
		<p><small><code>[logged-out][/logged-out]</code> <?php _e('Text wrapped within will be displayed when no user is logged in' , 'cubepoints'); ?></small></p>
		<p>
		<label for="<?php echo $this->get_field_id( 'autop' ); ?>">
		<input id="<?php echo $this->get_field_id( 'autop' ); ?>" name="<?php echo $this->get_field_name( 'autop' ); ?>" type="checkbox" <?php echo $autop ? 'checked="checked"' : '' ?> />
		<?php _e( 'Automatically add paragraphs' ); ?></label> 
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'hide' ); ?>">
		<input id="<?php echo $this->get_field_id( 'hide' ); ?>" name="<?php echo $this->get_field_name( 'hide' ); ?>" type="checkbox" <?php echo $hide ? 'checked="checked"' : '' ?> />
		<?php _e( 'Show widget only if user is logged in', 'cubepoints' ); ?></label> 
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['text'] = ( ! empty( $new_instance['text'] ) ) ? $new_instance['text'] : '';
		$instance['autop'] = ( ! empty( $new_instance['autop'] ) );
		$instance['hide'] = ( ! empty( $new_instance['hide'] ) );

		return $instance;
	}
}