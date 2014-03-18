<?php
/**
 * AudioTheme recent posts widget class.
 *
 * An improved recent posts widget to allow for more control over display and post type.
 *
 * @package AudioTheme_Framework
 * @subpackage Widgets
 *
 * @since 1.0.0
 */
class Audiotheme_Widget_Recent_Posts extends WP_Widget {
	/**
	 * Setup widget options.
	 *
	 * @since 1.0.0
	 * @see WP_Widget::construct()
	 */
	function __construct() {
		$widget_options = array( 'classname' => 'widget_recent_posts', 'description' => __( 'Display a list of recent posts', 'audiotheme' ) );
		parent::__construct( 'recent-posts', __( 'Recent Posts', 'audiotheme' ), $widget_options );
		$this->alt_option_name = 'widget_recent_entries';

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
		$cache = (array) wp_cache_get( 'audiotheme_widget_recent_posts', 'widget' );

		if ( isset( $cache[ $this->id ] ) ) {
			echo $cache[ $this->id ];
			return;
		}

		// Sanitize some of the instance values.
		$instance['post_type'] = ( empty( $instance['post_type'] ) ) ? 'post' : $instance['post_type'];
		$instance['number'] = ( empty( $instance['number'] ) || ! absint( $instance['number'] ) ) ? 5 : absint( $instance['number'] );

		$instance['title_raw'] = $instance['title'];
		$instance['title'] = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Recent Posts', 'audiotheme' ) : $instance['title'], $instance, $this->id_base );

		$instance['date_format'] = apply_filters( 'audiotheme_widget_recent_posts_date_format', get_option( 'date_format' ), $instance, $this->id_base );
		$instance['excerpt_length'] = apply_filters( 'audiotheme_widget_recent_posts_excerpt_length', 100, $instance, $this->id_base );

		// Add classes based on the widget options.
		preg_match( '/class=["\']([^"\']+)["\']/', $args['before_widget'], $classes );
		if ( isset( $classes[1] ) ) {
			$classes = preg_split( '#\s+#', $classes[1] );
			$classes = array_map( 'trim', $classes );

			$classes[] = 'post-type_' . $instance['post_type'];

			if ( isset( $instance['show_date'] ) && ! empty( $instance['show_date'] ) ) {
				$classes[] = 'show-date';
			}

			if ( isset( $instance['show_excerpts'] ) && ! empty( $instance['show_excerpts'] ) ) {
				$classes[] = 'show-excerpts';
			}

			$args['before_widget'] = preg_replace( '/class=["\']([^"\']+)["\']/', 'class="' . join( ' ', $classes ) . '"', $args['before_widget'] );
		}

		$instance['loop_args'] = apply_filters( 'widget_post_args', array(
			'post_type'           => $instance['post_type'],
			'post_status'         => 'publish',
			'posts_per_page'      => $instance['number'],
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		) );

		$output = $this->render( $args, $instance );
		echo $output;

		$cache[ $this->id ] = $output;
		wp_cache_set( 'audiotheme_widget_recent_posts', $cache, 'widget' );
	}

	/**
	 * Helper method to generate widget output.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Args specific to the widget area (sidebar).
	 * @param array $instance Widget instance settings.
	 */
	function render( $args, $instance ) {
		$output = $args['before_widget'];

		// Allow the output to be filtered.
		if ( $inside = apply_filters( 'audiotheme_widget_recent_posts_output', '', $instance, $args ) ) {
			$output .= $inside;
		} else {
			if ( ! empty( $instance['title'] ) ) {
				$output .= $args['before_title'];
					$output .= $instance['title'];

					if ( ! empty( $instance['show_feed_link'] ) ) {
						$post_type_archive_feed_link = ( 'post' == $instance['post_type'] ) ? get_bloginfo( 'rss2_url' ) : get_post_type_archive_feed_link( $instance['post_type'] );
						if ( $post_type_archive_feed_link ) {
							$output .= sprintf( ' <a href="%s" target="_blank">%s</a>',
								esc_url( $post_type_archive_feed_link ),
								__( 'Feed', 'audiotheme' )
							);
						}
					}
				$output .= $args['after_title'];
			}

			$output .= '<ul>';
				$loop = new WP_Query( $instance['loop_args'] );

				if ( $loop->have_posts() ) :
					while ( $loop->have_posts() ) :
						$loop->the_post();

						$output .= '<li>';
							$output .= sprintf( '<h5><a href="%1$s" title="%2$s">%3$s</a></h5>',
								esc_url( get_permalink() ),
								esc_attr( get_the_title() ? get_the_title() : get_the_ID() ),
								get_the_title()
							);

							if ( $instance['show_date'] ) {
								$date_html = sprintf( '<time datetime="%s" pubdate="pubdate" class="published">%s</time>',
									get_post_time( 'c', true ),
									get_the_time( $instance['date_format'] )
								);

								$output.= apply_filters( 'audiotheme_widget_recent_posts_date_html', $date_html, $instance );
							}

							if ( ! empty( $instance['show_excerpts'] ) ) {
								$excerpt = wpautop( wp_html_excerpt( get_the_excerpt(), $instance['excerpt_length'] ) . '...' );
								$output .= apply_filters( 'audiotheme_widget_recent_posts_excerpt', $excerpt, $loop->post, $instance );
							}
						$output .= '</li>';
					endwhile;
				endif;
				wp_reset_postdata();
			$output .= '</ul>';
		}

		$output .= $args['after_widget'];

		return $output;
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
			'post_type'      => 'post',
			'show_date'      => 0,
			'show_excerpts'  => 0,
			'show_feed_link' => 1,
			'title'          => '',
		));

		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		$post_types = apply_filters( 'audiotheme_widget_recent_posts_post_types', $post_types );

		$title = wp_strip_all_tags( $instance['title'] );
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		$selected_post_type = ( array_key_exists( $instance['post_type'], $post_types ) || 'any' == $instance['post_type'] ) ? $instance['post_type'] : 'post';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'audiotheme' ); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" id="<?php echo $this->get_field_id( 'title' ); ?>" class="widefat" value="<?php echo $title; ?>">
		</p>
		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'show_feed_link' ); ?>" id="<?php echo $this->get_field_id( 'show_feed_link' ); ?>" <?php checked( $instance['show_feed_link'] ); ?>>
			<label for="<?php echo $this->get_field_id( 'show_feed_link' ); ?>"><?php _e( 'Show feed link in title?', 'audiotheme' ); ?></label>
		</p>

		<?php if ( apply_filters( 'audiotheme_widget_recent_posts_show_post_type_dropdown', false ) ) : ?>
			<p>
				<label for="<?php echo $this->get_field_id( 'post_type' ); ?>"><?php _e( 'Post Type:', 'audiotheme' ); ?></label>
				<select name="<?php echo $this->get_field_name( 'post_type' ); ?>" id="<?php echo $this->get_field_id( 'post_type' ); ?>">
					<!--<option value="any">Any</option>-->
					<?php
					foreach ( $post_types as $post_type => $post_type_object ) {
						printf( '<option value="%s"%s>%s</option>',
							$post_type,
							selected( $selected_post_type, $post_type, false ),
							esc_html( $post_type_object->labels->name )
						);
					}
					?>
				</select>
			</p>
		<?php endif; ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:', 'audiotheme' ); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'number' ); ?>" id="<?php echo $this->get_field_id( 'number' ); ?>" value="<?php echo $number; ?>" size="3">
		</p>
		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'show_date' ); ?>" id="<?php echo $this->get_field_id( 'show_date' ); ?>" <?php checked( $instance['show_date'] ); ?>>
			<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Show date?', 'audiotheme' ); ?></label>
		</p>
		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'show_excerpts' ); ?>" id="<?php echo $this->get_field_id( 'show_excerpts' ); ?>" <?php checked( $instance['show_excerpts'] ); ?>>
			<label for="<?php echo $this->get_field_id( 'show_excerpts' ); ?>"><?php _e( 'Show excerpts?', 'audiotheme' ); ?></label>
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
		$instance['show_date'] = isset( $new_instance['show_date'] );
		$instance['show_excerpts'] = isset( $new_instance['show_excerpts'] );
		$instance['show_feed_link'] = isset( $new_instance['show_feed_link'] );
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions['widget_recent_entries' ]) ) {
			delete_option( 'widget_recent_entries' );
		}

		return $instance;
	}

	/**
	 * Remove a single recent posts widget from the cache.
	 *
	 * @since 1.0.0
	 */
	function flush_widget_cache() {
		$cache = (array) wp_cache_get( 'audiotheme_widget_recent_posts', 'widget' );

		if ( isset( $cache[ $this->id ] ) ) {
			unset( $cache[ $this->id ] );
		}

		wp_cache_set( 'audiotheme_widget_recent_posts', array_filter( $cache ), 'widget' );
	}

	/**
	 * Flush the cache for all recent posts widgets.
	 *
	 * @since 1.0.0
	 */
	function flush_group_cache() {
		wp_cache_delete( 'audiotheme_widget_recent_posts', 'widget' );
	}
}
