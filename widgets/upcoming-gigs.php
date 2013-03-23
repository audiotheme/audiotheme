<?php
/**
 * AudioTheme record widget class.
 *
 * Display a list of upcoming gigs in a widget area.
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
		
		add_action( 'save_post', array( $this, 'flush_group_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_group_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_group_cache' ) );
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
		$cache = (array) wp_cache_get( 'audiotheme_widget_upcoming_gigs', 'widget' );

		if ( isset( $cache[ $this->id ] ) ) {
			echo $cache[ $this->id ];
			return;
		}
		
		extract( $args );

		$instance['title_raw'] = $instance['title'];
		$instance['title'] = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Upcoming Gigs', 'audiotheme-i18n' ) : $instance['title'], $instance, $this->id_base );
		$instance['title'] = apply_filters( 'audiotheme_widget_title', $instance['title'], $instance, $args, $this->id_base );

		$instance['date_format'] = apply_filters( 'audiotheme_widget_upcoming_gigs_date_format', get_option( 'date_format' ) );
		$instance['number'] = ( empty( $instance['number'] ) || ! absint( $instance['number'] ) ) ? 5 : absint( $instance['number'] );

		$loop = new Audiotheme_Gig_Query( apply_filters( 'audiotheme_widget_upcoming_gigs_loop_args', array(
			'no_found_rows'  => true,
			'posts_per_page' => $instance['number'],
		) ) );

		// Add a class with the number of gigs to display.
		$output = preg_replace( '/class="([^"]+)"/', 'class="$1 widget-items-' . $instance['number'] . '"', $before_widget );

			$output .= ( empty( $instance['title'] ) ) ? '' : $before_title . $instance['title'] . $after_title;

			if ( $loop->have_posts() ) :

				if ( ! $inside = apply_filters( 'audiotheme_widget_upcoming_gigs_output', '', $instance, $args, $loop ) ) {
					while ( $loop->have_posts() ) :
						$loop->the_post();

						$inside .= '<dl class="vevent" itemscope itemtype="http://schema.org/MusicEvent">';

							$gig = get_audiotheme_gig();
							$venue = get_audiotheme_venue( $gig->venue->ID );

							$inside .= get_audiotheme_gig_link( $gig, array( 'before' => '<dt>', 'after' => '</dt>' ) );

							if ( audiotheme_gig_has_venue() ) {
								$inside .= '<dd class="location">';
									$location = get_audiotheme_gig_location( $gig );
									$inside .= '<a href="' . get_permalink( $gig->ID ) . '"><span class="gig-title">' . $location . '</span></a>';
								$inside .= '</dd>';
							}

							$inside .= '<dd class="date">';
								$inside .= sprintf( '<meta content="%s" itemprop="startDate">', get_audiotheme_gig_time( 'c' ) );
								$inside .= sprintf( '<time class="dtstart" datetime="%s">%s</time>',
									get_audiotheme_gig_time( 'c' ),
									get_audiotheme_gig_time( $instance['date_format'] )
								);
							$inside .= '</dd>';

							if ( ! empty( $gig->post_title ) && audiotheme_gig_has_venue() ) {
								$inside .= '<dd class="venue">' . esc_html( $venue->name ) . '</dd>';
							}

							if ( $gig_description = get_audiotheme_gig_description() ) {
								$inside .= '<dd class="description">' . wp_strip_all_tags( $gig_description ) . '</dd>';
							}

						$inside .= '</dl>';
					endwhile;
				}

				wp_reset_postdata();
			endif;

			$output .= $inside;

		$output .= $after_widget;
		echo $output;
		
		$cache[ $this->id ] = $output;
		wp_cache_set( 'audiotheme_widget_upcoming_gigs', $cache, 'widget' );
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
		$this->flush_widget_cache();

		return $instance;
	}

	/**
	 * Remove a single upcoming gigs widget from the cache.
	 *
	 * @since 1.0.0
	 */
	function flush_widget_cache() {
		$cache = (array) wp_cache_get( 'audiotheme_widget_upcoming_gigs', 'widget' );

		if ( isset( $cache[ $this->id ] ) ) {
			unset( $cache[ $this->id ] );
		}

		wp_cache_set( 'audiotheme_widget_upcoming_gigs', array_filter( $cache ), 'widget' );
	}

	/**
	 * Flush the cache for all upcoming gigs widgets.
	 *
	 * @since 1.0.0
	 */
	function flush_group_cache() {
		wp_cache_delete( 'audiotheme_widget_upcoming_gigs', 'widget' );
	}
}
