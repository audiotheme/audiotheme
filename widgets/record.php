<?php
/**
 * AudioTheme record widget class.
 *
 * Display a selected record in a widget area.
 *
 * @package AudioTheme_Framework
 * @subpackage Widgets
 *
 * @since 1.0.0
 */
class Audiotheme_Widget_Record extends WP_Widget {
	/**
	 * Setup widget options.
	 *
	 * @since 1.0.0
	 * @see WP_Widget::construct()
	 */
	function __construct() {
		$widget_options = array( 'classname' => 'widget_audiotheme_record', 'description' => __( 'Display a selected record', 'audiotheme-i18n' ) );
		parent::__construct( 'audiotheme-record', __( 'Record (AudioTheme)', 'audiotheme-i18n' ), $widget_options );
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
		$instance['title'] = apply_filters( 'widget_title', empty( $instance['title'] ) ? get_the_title( $instance['post_id'] ) : $instance['title'], $instance, $this->id_base );
		$instance['title'] = apply_filters( 'audiotheme_widget_title', $instance['title'], $instance, $args, $this->id_base );
		
		if ( isset( $instance['show_link'] ) && $instance['show_link'] && empty( $instance['link_text'] ) ) {
			$instance['link_text'] = apply_filters( 'audiotheme_widget_record_default_link_text', __( 'View Details &rarr;', 'audiotheme-i18n' ) );
		}
		
		echo $before_widget;
			
			echo ( empty( $instance['title'] ) ) ? '' : $before_title . $instance['title'] . $after_title;
			
			if ( ! $output = apply_filters( 'audiotheme_widget_record_output', '', $instance, $args ) ) {
				$post = get_post( $instance['post_id'] );
				
				$image_size = apply_filters( 'audiotheme_widget_record_image_size', 'thumbnail', $instance, $args );
				$image_size = apply_filters( 'audiotheme_widget_record_image_size-' . $args['id'], $image_size, $instance, $args );
				
				$output .= sprintf( '<p class="featured-image"><a href="%s">%s</a></p>',
					get_permalink( $post->ID ),
					get_the_post_thumbnail( $post->ID, $image_size )
				);
				
				$output .= ( empty( $instance['text'] ) ) ? '' : wpautop( $instance['text'] );
				
				if ( isset( $instance['show_link'] ) && $instance['show_link'] && ! empty( $instance['link_text'] ) ) {
					$output .= sprintf( '<p class="more"><a href="%s">%s</a></p>',
						get_permalink( $post->ID ),
						$instance['link_text']
					);
				}
			}
			
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
			'link_text' => '',
			'post_id'   => '',
			'show_link' => false,
			'text'      => '',
			'title'     => ''
		) );
		
		$records = get_posts( array(
			'post_type'      => 'audiotheme_record',
			'orderby'        => 'title',
			'order'          => 'asc',
			'posts_per_page' => -1,
			'cache_results'  => false
		) );
		
		$title = wp_strip_all_tags( $instance['title'] );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'audiotheme-i18n' ); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" id="<?php echo $this->get_field_id( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" class="widefat">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'post_id' ); ?>"><?php _e( 'Record:', 'audiotheme-i18n' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'post_id' ); ?>" id="<?php echo $this->get_field_id( 'post_id' ); ?>" class="widefat">
				<?php
				foreach ( $records as $record ) {
					printf( '<option value="%s"%s>%s</option>',
						$record->ID,
						selected( $instance['post_id'], $record->ID, false ),
						esc_html( $record->post_title )
					);
				}
				?>
			</select>
		</p>
		<p>
			<textarea name="<?php echo $this->get_field_name( 'text' ); ?>" id="<?php echo $this->get_field_id( 'text' ); ?>" cols="20" rows="5" class="widefat"><?php echo esc_textarea( $instance['text'] ); ?></textarea>
		</p>
		<p style="margin-bottom: 0.5em">
			<label for="<?php echo $this->get_field_id( 'link_text' ); ?>"><?php _e( 'More Link Text:', 'audiotheme-i18n' ); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'link_text' ); ?>" id="<?php echo $this->get_field_id( 'link_text' ); ?>" value="<?php echo esc_attr( $instance['link_text'] ); ?>" class="widefat">
		</p>
		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'show_link' ); ?>" id="<?php echo $this->get_field_id( 'show_link' ); ?>" value="1"<?php checked( $instance['show_link'] ); ?>>
			<label for="<?php echo $this->get_field_id( 'show_link' ); ?>"><?php _e( 'Show more link?', 'audiotheme-i18n' ); ?></label>
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
		$instance['text'] = wp_kses_data( $new_instance['text'] );
		$instance['link_text'] = wp_kses_data( $new_instance['link_text'] );
		$instance['show_link'] = ( isset( $new_instance['show_link'] ) ) ? 1 : 0;
		
		return $instance;
	}
}
?>