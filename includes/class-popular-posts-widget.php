<?php

/**
 * Widget that displays popular posts.
 *
 * @package   ng-popular-posts
 * @copyright Copyright (c) 2015, Nose Graze Ltd.
 * @license   GPL2+
 */
class NG_Popular_Posts_Widget extends WP_Widget {

	/**
	 * Array of allowed time frames. Used in validation.
	 *
	 * @var array
	 */
	private $allowed_time_frames = array(
		'all',
		'month',
		'week'
	);

	/**
	 * Sets up the widget's name, etc.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		parent::__construct(
			'ng_popular_posts', // Base ID
			__( 'NG Popular Posts', 'ng-popular-posts' ), // Name
			array( 'description' => __( 'Displays the featured image of your most popular posts', 'ng-popular-posts' ), ) // Args
		);

	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$title      = empty( $instance['title'] ) ? '' : apply_filters( 'widget_title', $instance['title'] );
		$numposts   = is_numeric( $instance['numposts'] ) ? intval( $instance['numposts'] ) : 5;
		$time_frame = in_array( $instance['timeframe'], $this->allowed_time_frames ) ? $instance['timeframe'] : 'all';

		// Perform our query. Get our number of posts, ordered by most commented.
		$q_args = array(
			'posts_per_page' => $numposts,
			'orderby'        => 'comment_count'
		);

		if ( $time_frame != 'all' ) {

			// Get the time frame for the query.
			if ( $time_frame == 'month' ) {
				$date_query = '1 month ago';
			} else {
				$date_query = '1 week ago';
			}

			$q_args['date_query'] = array(
				array(
					'after' => $date_query
				)
			);

		}

		$popular_posts = get_posts( apply_filters( 'ng_popular_posts_query_args', $q_args, $args ) );

		// If there are no results - let's bail now and not show anything in the sidebar.
		if ( ! $popular_posts || ! is_array( $popular_posts ) ) {
			return;
		}

		echo $args['before_widget'];

		// Title of the widget.
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		// Loop through each post and display the featured image.
		foreach ( $popular_posts as $popular_post ) {
			// If this post doesn't have a featured image - skip it.
			if ( ! has_post_thumbnail( $popular_post->ID ) ) {
				continue;
			}

			// Display the featured image (medium size);
			?>
			<div class="ng-popular-post-entry">
				<?php
				// Uncomment the below line to show the post title. You could also wrap this
				// in a link tag to make it clickable.
				//
				// echo get_the_title( $popular_post );
				?>
				<a href="<?php echo esc_url( get_permalink( $popular_post ) ); ?>">
					<?php echo get_the_post_thumbnail( $popular_post->ID, apply_filters( 'ng_popular_posts_featured_image_size', 'medium' ) ); ?>
				</a>
			</div>
			<?php
		}

		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array(
			'title'     => __( 'Popular Posts', 'ng-popular-posts' ),
			'numposts'  => 5,
			'timeframe' => 'all'
		) );

		$title      = $instance['title'];
		$numposts   = $instance['numposts'];
		$time_frame = $instance['timeframe'];

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'ng-popular-posts' ); ?></label>
			<br>
			<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>" id="<?php echo $this->get_field_id( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>">
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'numposts' ); ?>"><?php _e( 'Number of Posts:', 'ng-popular-posts' ); ?></label>
			<br>
			<input type="number" class="widefat" name="<?php echo $this->get_field_name( 'numposts' ); ?>" id="<?php echo $this->get_field_id( 'numposts' ); ?>" value="<?php echo esc_attr( $numposts ); ?>">
		</p>

		<legend><?php _e( 'Choose the time frame from which to display posts:', 'ng-popular-posts' ); ?></legend>
		<p>
			<input type="radio" <?php checked( $time_frame, 'all' ); ?> name="<?php echo $this->get_field_name( 'timeframe' ); ?>" id="<?php echo $this->get_field_id( 'timeframe' ); ?>_all" value="all">
			<label for="<?php echo $this->get_field_id( 'timeframe' ); ?>_all"><?php _e( 'All Time', 'ng-popular-posts' ); ?></label>
			<br>

			<input type="radio" <?php checked( $time_frame, 'month' ); ?> name="<?php echo $this->get_field_name( 'timeframe' ); ?>" id="<?php echo $this->get_field_id( 'timeframe' ); ?>_month" value="month">
			<label for="<?php echo $this->get_field_id( 'timeframe' ); ?>_month"><?php _e( 'Last 30 Days', 'ng-popular-posts' ); ?></label>
			<br>

			<input type="radio" <?php checked( $time_frame, 'week' ); ?> name="<?php echo $this->get_field_name( 'timeframe' ); ?>" id="<?php echo $this->get_field_id( 'timeframe' ); ?>_week" value="week">
			<label for="<?php echo $this->get_field_id( 'timeframe' ); ?>_week"><?php _e( 'Last 7 Days', 'ng-popular-posts' ); ?></label>
			<br>
		</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance              = array();
		$instance['title']     = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['numposts']  = intval( strip_tags( $new_instance['numposts'] ) );
		$instance['timeframe'] = in_array( $new_instance['timeframe'], $this->allowed_time_frames ) ? strip_tags( $new_instance['timeframe'] ) : 'all';

		return $instance;
	}

}
