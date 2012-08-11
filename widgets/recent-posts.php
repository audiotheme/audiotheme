<?php
class Audiotheme_Widget_Recent_Posts extends WP_Widget {
	function __construct() {
		$widget_ops = array( 'classname' => 'widget_recent_posts', 'description' => __( 'Display a list of recent posts' ) );
		$control_ops = array();
		parent::__construct( 'recent-posts', __( 'Recent Posts' ), $widget_ops, $control_ops );
		$this->alt_option_name = 'widget_recent_entries';
		
		add_action( 'save_post', array( $this, 'flush_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_cache' ) );
	}

	function widget( $args, $instance ) {
		$cache = (array) wp_cache_get( 'audiotheme_widget_recent_posts' );
		
		if ( isset( $cache[ $this->id ] ) ) {
			echo $cache[ $this->id ];
			return;
		}
		
		extract( $args );
		
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? 'Recent Posts' : $instance['title'], $instance, $this->id_base );
		$post_type = ( empty( $instance['post_type'] ) ) ? 'post' : $instance['post_type'];
		if ( empty( $instance['number'] ) || ! absint( $instance['number'] ) ) {
 			$instance['number'] = 10;
		}
		
		$output = $before_widget;
			
			if ( ! empty( $title ) ) {
				$output.= $before_title;
					$output.= $title;
					
					if ( isset( $instance['show_feed_link'] ) && $instance['show_feed_link'] ) {
						$post_type_archive_feed_link = ( 'post' == $post_type ) ? get_bloginfo( 'rss2_url' ) : get_post_type_archive_feed_link( $post_type );
						if ( $post_type_archive_feed_link ) {
							$output.= sprintf( ' <a href="%s" target="_blank">%s</a>',
								esc_url( $post_type_archive_feed_link ), 
								'Feed'
							);
						}
					}
					
				$output.= $after_title;
			}
			
			$output.= '<ul>';
				$loop = new WP_Query( apply_filters( 'widget_post_args', array(
					'post_type' => $post_type,
					'no_found_rows' => true,
					'post_status' => 'publish',
					'posts_per_page' => $instance['number'],
					'ignore_sticky_posts' => true
				) ) );
				
				$date_format = apply_filters( 'audiotheme_widget_recent_posts_date_format', get_option( 'date_format' ) );
				$excerpt_length = apply_filters( 'audiotheme_widget_recent_posts_excerpt_length', 100, $instance );
				
				if ( $loop->have_posts() ) :
					while ( $loop->have_posts() ) :
						$loop->the_post();
						
						$output.= '<li>';
							$output.= sprintf( '<h5><a href="%1$s" title="%2$s">%3$s</a></h5>',
								esc_url( get_permalink() ),
								esc_attr( get_the_title() ? get_the_title() : get_the_ID() ),
								get_the_title()
							);
							
							if ( $instance['show_date'] ) {
								$date_html = sprintf( '<time datetime="%s" pubdate="pubdate" class="published">%s</time>',
									get_post_time( 'c', true ),
									get_the_time( $date_format )
								);
								
								$output.= apply_filters( 'audiotheme_widget_recent_posts_date_html', $date_html, $instance );
							}
							
							if ( isset( $instance['show_excerpts'] ) && $instance['show_excerpts'] == '1' ) {
								$excerpt = wpautop( wp_html_excerpt( get_the_excerpt(), $excerpt_length ) . '...' );
								$output.= apply_filters( 'audiotheme_widget_recent_posts_excerpt', $excerpt, $loop->post, $instance );
							}
						$output.= '</li>';
					endwhile;
				endif;
				wp_reset_postdata();
			$output.= '</ul>';
		
		$output.= $after_widget;
		
		$instance['date_format'] = $date_format;
		$instance['excerpt_length'] = $excerpt_length;
		$output = apply_filters( 'audiotheme_widget_recent_posts_html', $output, $instance, $loop );
		
		$cache[ $this->id ] = $output;
		wp_cache_set( 'audiotheme_widget_recent_posts', $cache );
		echo $output;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array(
			'post_type' => 'post',
			'show_date' => 0,
			'show_excerpts' => 0,
			'show_feed_link' => 1,
			'title' => ''
		));
		
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		$post_types = apply_filters( 'audiotheme_widget_recent_posts_post_types', $post_types );
		
		$title = wp_strip_all_tags( $instance['title'] );
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		$selected_post_type = ( array_key_exists( $instance['post_type'], $post_types ) || 'any' == $instance['post_type'] ) ? $instance['post_type'] : 'post';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
			<input type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" id="<?php echo $this->get_field_id( 'title' ); ?>" class="widefat" value="<?php echo $title; ?>">
		</p>
		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'show_feed_link' ); ?>" id="<?php echo $this->get_field_id( 'show_feed_link' ); ?>" <?php checked( $instance['show_feed_link'] ); ?>>
			<label for="<?php echo $this->get_field_id( 'show_feed_link' ); ?>">Show feed link in title?</label>
		</p>
		
		<?php if ( apply_filters( 'audiotheme_widget_recent_posts_show_post_type_dropdown', false ) ) : ?>
			<p>
				<label for="<?php echo $this->get_field_id( 'post_type' ); ?>">Post Type:</label>
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
			<label for="<?php echo $this->get_field_id( 'number' ); ?>">Number of posts to show:</label>
			<input type="text" name="<?php echo $this->get_field_name( 'number' ); ?>" id="<?php echo $this->get_field_id( 'number' ); ?>" value="<?php echo $number; ?>" size="3">
		</p>
		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'show_date' ); ?>" id="<?php echo $this->get_field_id( 'show_date' ); ?>" <?php checked( $instance['show_date'] ); ?>>
			<label for="<?php echo $this->get_field_id( 'show_date' ); ?>">Show date?</label>
		</p>
		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'show_excerpts' ); ?>" id="<?php echo $this->get_field_id( 'show_excerpts' ); ?>" <?php checked( $instance['show_excerpts'] ); ?>>
			<label for="<?php echo $this->get_field_id( 'show_excerpts' ); ?>">Show excerpts?</label>
		</p>
		<?php
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = wp_parse_args( $new_instance, $old_instance );
		
		$instance['title'] = wp_strip_all_tags( $new_instance['title'] );
		$instance['number'] = absint( $new_instance['number'] );
		$instance['show_date'] = isset( $new_instance['show_date'] );
		$instance['show_excerpts'] = isset( $new_instance['show_excerpts'] );
		$instance['show_feed_link'] = isset( $new_instance['show_feed_link'] );
		$this->flush_cache();
		
		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions['widget_recent_entries' ]) ) {
			delete_option( 'widget_recent_entries' );
		}
		
		return $instance;
	}
	
	function flush_cache() {
		wp_cache_delete( 'audiotheme_widget_recent_posts' );
	}
}
?>