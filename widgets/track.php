<?php
class Audiotheme_Widget_Track extends WP_Widget {
	function __construct() {
		$widget_ops = array( 'classname' => 'widget_audiotheme_track', 'description' => __( 'Display a selected track' ) );
		$control_ops = array();
		$this->WP_Widget( 'audiotheme-track', 'Track (AudioTheme)', $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );
		
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? get_the_title( $instance['post_id'] ) : $instance['title'], $instance, $this->id_base );
		$instance['title_filtered'] = apply_filters( 'audiotheme_widget_title', $title, $instance, $this->id_base, $args );
		
		echo $before_widget;
		
			echo ( empty( $instance['title_filtered'] ) ) ? '' : $before_title . $instance['title_filtered'] . $after_title;
				
			if ( ! $output = apply_filters( 'audiotheme_widget_track_output', '', $instance, $args ) ) {
				$post = get_post( $instance['post_id'] );
				
				$image_size = apply_filters( 'audiotheme_widget_video_image_size', 'thumbnail', $instance, $args );
				$image_size = apply_filters( 'audiotheme_widget_video_image_size-' . $args['id'], $image_size, $instance, $args );
				
				$output.= sprintf( '<p class="featured-image"><a href="%s">%s</a></p>',
					get_permalink( $post->ID ),
					get_the_post_thumbnail( $post->post_parent, $image_size )
				);
				
				$output.= ( isset( $instance['text'] ) && ! empty( $instance['text'] ) ) ? wpautop( $instance['text'] ) : '';
				$output.= sprintf( '<p class="more"><a href="%s">View Details &rarr;</a></p>', get_permalink( $post->ID ) );
			}
				
			echo $output;
			
		echo $after_widget;
	}

	function form( $instance ) {
		global $wpdb;
		
		$instance = wp_parse_args( (array) $instance, array(
			'post_id' => '',
			'text' => '',
			'title' => ''
		));
		
		$title = wp_strip_all_tags( $instance['title'] );
		
		$tracks = $wpdb->get_results( $wpdb->prepare( "SELECT p.ID, p.post_title, p2.post_title AS record
			FROM wp_posts p
			INNER JOIN wp_posts p2 ON (p.post_parent=p2.ID)
			WHERE p.post_type='audiotheme_track'
			ORDER BY p2.post_title ASC, p.post_title ASC" ) );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" id="<?php echo $this->get_field_id( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" class="widefat">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'post_id' ); ?>">Track:</label>
			<select name="<?php echo $this->get_field_name( 'post_id' ); ?>" id="<?php echo $this->get_field_id( 'post_id' ); ?>" class="widefat">
				<?php
				$last_record = '';
				foreach ( $tracks as $key => $track ) {
					echo ( 0 !== $key && $last_record != $track->record ) ? '</optgroup>' : '';
					echo ( $last_record != $track->record ) ? '<optgroup label="' . esc_attr( $track->record ) . '">' : '';
					$last_record = $track->record;
					
					printf( '<option value="%s"%s>%s</option>',
						$track->ID,
						selected( $instance['post_id'], $track->ID, false ),
						esc_html( $track->post_title )
					);
				}
				echo '</optgroup>';
				?>
			</select>
		</p>
		<p>
			<textarea class="widefat" rows="5" cols="20" id="<?php echo $this->get_field_id( 'text' ); ?>" name="<?php echo $this->get_field_name( 'text' ); ?>"><?php echo esc_textarea( $instance['text'] ); ?></textarea>
		</p>
		<style type="text/css">
		optgroup option { margin-left: 12px;}
		</style>
		<?php
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = wp_parse_args( $new_instance, $old_instance );
		
		$instance['title'] = wp_strip_all_tags( $new_instance['title'] );
		
		return $instance;
	}
}
?>