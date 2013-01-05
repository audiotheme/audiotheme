<?php
/**
 * AudioTheme record widget class.
 *
 * Display a list of upcoming gigs in a widget area.
 *
 * @todo Implement some caching.
 *
 * @package AudioTheme_Framework
 * @subpackage Widgets
 *
 * @since 1.0.0
 */
class Audiotheme_Widget_Upcoming_Gigs extends WP_Widget {
	/**
	 * Setup widget options.
	 *
	 * @since 1.0.0
	 * @see WP_Widget::construct()
	 */
	function __construct() {
		$widget_options = array( 'classname' => 'widget_audiotheme_upcoming_gigs', 'description' => __( 'Display a list of upcoming gigs', 'audiotheme-i18n' ) );
		parent::__construct( 'audiotheme-upcoming-gigs', __( 'Upcoming Gigs (AudioTheme)', 'audiotheme-i18n' ), $widget_options );
	}

	/**
	 * Default widget front end display method.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Args specific to the widget area (sidebar).
	 * @param array $instance Widget instance settings.
	 */
	function widget( $args, $instance ) {
		extract( $args );

		$instance['title_raw'] = $instance['title'];
		$instance['title'] = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Upcoming Gigs', 'audiotheme-i18n' ) : $instance['title'], $instance, $this->id_base );
		$instance['title'] = apply_filters( 'audiotheme_widget_title', $instance['title'], $instance, $args, $this->id_base );

		$instance['date_format'] = apply_filters( 'audiotheme_widget_upcoming_gigs_date_format', get_option( 'date_format' ) );
		$instance['number'] = ( empty( $instance['number'] ) || ! absint( $instance['number'] ) ) ? 5 : absint( $instance['number'] );

		$loop = new WP_Query( apply_filters( 'audiotheme_widget_upcoming_gigs_loop_args', array(
			'post_type'      => 'audiotheme_gig',
			'no_found_rows'  => true,
			'post_status'    => 'publish',
			'posts_per_page' => $instance['number'],
			'orderby'        => 'meta_value',
			'order'          => 'asc',
			'ignore_sticky_posts' => true,
			'meta_query'     => array(
				array(
					'key'     => '_audiotheme_gig_datetime',
					'compare' => 'EXISTS'
				),
				array(
					'key'     => '_audiotheme_gig_datetime',
					'value'   => current_time( 'mysql' ),
					'compare' => '>=',
					'type'    => 'DATETIME'
				),
			),
		) ) );

		p2p_type( 'audiotheme_venue_to_gig' )->each_connected( $loop );

		// Add a class with the number of gigs to display.
		echo preg_replace( '/class="([^"]+)"/', 'class="$1 widget-items-' . $instance['number'] . '"', $before_widget );

			echo ( empty( $instance['title'] ) ) ? '' : $before_title . $instance['title'] . $after_title;

			if ( $loop->have_posts() ) :

				if ( ! $output = apply_filters( 'audiotheme_widget_upcoming_gigs_output', '', $instance, $args, $loop ) ) {
					while ( $loop->have_posts() ) :
						$loop->the_post();

						$output.= '<dl class="vevent" itemscope itemtype="http://schema.org/MusicEvent">';

							$gig = get_audiotheme_gig();
							$venue = get_audiotheme_venue( $gig->venue->ID );

							$output.= get_audiotheme_gig_link( $gig, array( 'before' => '<dt>', 'after' => '</dt>' ) );

							if ( audiotheme_gig_has_venue() ) {
								$output.= '<dd class="location">';
									$location = get_audiotheme_gig_location( $gig );
									$output.= '<a href="' . get_permalink( $gig->ID ) . '"><span class="gig-title">' . $location . '</span></a>';
								$output.= '</dd>';
							}

							$output.= '<dd class="date">';
								$output.= sprintf( '<meta content="%s" itemprop="startDate">', get_audiotheme_gig_time( 'c' ) );
								$output.= sprintf( '<time class="dtstart" datetime="%s">%s</time>',
									get_audiotheme_gig_time( 'c' ),
									$instance['date_format']
								);
							$output.= '</dd>';

							if ( ! empty( $gig->post_title ) && audiotheme_gig_has_venue() ) {
								$output.= '<dd class="venue">' . esc_html( $venue->name ) . '</dd>';
							}

							if ( $gig_description = get_audiotheme_gig_description() ) {
								$output.= '<dd class="description">' . wp_strip_all_tags( $gig_description ) . '</dd>';
							}

						$output.= '</dl>';
					endwhile;
				}

				wp_reset_postdata();
			endif;

			echo $output;

		echo $after_widget;
	}

	/**
	 * Form to modify widget instance settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $instance Current widget instance settings.
	 */
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array(
			'title' => '',
		) );

		$title = wp_strip_all_tags( $instance['title'] );
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'audiotheme-i18n' ); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" id="<?php echo $this->get_field_id( 'title' ); ?>" class="widefat" value="<?php echo $title; ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of gigs to show:', 'audiotheme-i18n' ); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'number' ); ?>" id="<?php echo $this->get_field_id( 'number' ); ?>" value="<?php echo $number; ?>" size="3">
		</p>
		<?php
	}

	/**
	 * Save widget settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $new_instance New widget settings.
	 * @param array $old_instance Old widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = wp_parse_args( $new_instance, $old_instance );

		$instance['title'] = wp_strip_all_tags( $new_instance['title'] );
		$instance['number'] = absint( $new_instance['number'] );

		return $instance;
	}
}
