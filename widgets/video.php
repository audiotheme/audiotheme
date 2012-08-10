<?php
add_action( 'widgets_init', 'audiotheme_register_widget_video' );
function audiotheme_register_widget_video() {
	register_widget( 'Audiotheme_Widget_Video' );
}


class Audiotheme_Widget_Video extends WP_Widget {
	function __construct() {
		$widget_ops = array( 'classname' => 'widget_audiotheme_video', 'description' => __( 'Display a video' ) );
		$control_ops = array();
		$this->WP_Widget( 'audiotheme-video', 'AudioTheme: Video', $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );
		
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? get_the_title( $instance['post_id'] ) : $instance['title'], $instance, $this->id_base );
		$instance['title_filtered'] = apply_filters( 'audiotheme_widget_title', $title, $instance, $this->id_base, $args );
		
		echo $before_widget;
		
			echo ( empty( $instance['title_filtered'] ) ) ? '' : $before_title . $instance['title_filtered'] . $after_title;
				
			if ( ! $output = apply_filters( 'audiotheme_widget_video_output', '', $instance, $args ) ) {
				$post = get_post( $instance['post_id'] );
				
				$image_size = apply_filters( 'audiotheme_widget_video_image_size', 'thumbnail', $instance, $args );
				$image_size = apply_filters( 'audiotheme_widget_video_image_size-' . $args['id'], $image_size, $instance, $args );
				
				$output.= sprintf( '<p class="featured-image"><a href="%s">%s</a></p>',
					get_permalink( $post->ID ),
					get_the_post_thumbnail( $post->ID, $image_size )
				);
				
				$output.= ( isset( $instance['text'] ) && ! empty( $instance['text'] ) ) ? wpautop( $instance['text'] ) : '';
			}
				
			echo $output;
			
		echo $after_widget;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array(
			'post_id' => '',
			'text' => '',
			'title' => ''
		));
		
		$videos = get_posts( 'post_type=audiotheme_video&order=asc&orderby=title&numberposts=-1' );
		$title = wp_strip_all_tags( $instance['title'] );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" id="<?php echo $this->get_field_id( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" class="widefat">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'post_id' ); ?>">Video:</label>
			<select name="<?php echo $this->get_field_name( 'post_id' ); ?>" id="<?php echo $this->get_field_id( 'post_id' ); ?>" class="widefat">
				<?php
				foreach ( $videos as $video ) {
					printf( '<option value="%s"%s>%s</option>',
						$video->ID,
						selected( $instance['post_id'], $video->ID, false ),
						esc_html( $video->post_title )
					);
				}
				?>
			</select>
		</p>
		<p>
			<textarea class="widefat" rows="5" cols="20" id="<?php echo $this->get_field_id( 'text' ); ?>" name="<?php echo $this->get_field_name( 'text' ); ?>"><?php echo esc_textarea( $instance['text'] ); ?></textarea>
		</p>
		<?php
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = wp_parse_args( $new_instance, $old_instance );
		
		$instance['title'] = wp_strip_all_tags( $new_instance['title'] );
		
		return $instance;
	}
}
?>