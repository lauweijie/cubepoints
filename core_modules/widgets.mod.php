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
			'cubepoints_points_widget', // Base ID
			__('CubePoints', 'cubepoints'), // Name
			array( 'description' => __( 'Display the points of the current logged in user.', 'cubepoints' ), ) // Args
		);
	}

	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];
		echo __( 'Points', 'cubepoints' ) . ': ';
		global $cubepoints;
		$cubepoints->displayPoints();
		do_action('cubepoints_points_widget_after');
		echo $args['after_widget'];
	}

 	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		} else {
			$title = __( 'Points', 'cubepoints' );
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}
}