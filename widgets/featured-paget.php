<?php
/**
 * Adds the Featured Page Widget.
 *
 * @package Genesis
 */


/**
 * Genesis Featured Page widget class.
 *
 * @package Genesis
 * @subpackage Widgets
 * @since unknown
 */
class Genesis_Featured_Page extends WP_Widget {

	/**
	 * Constructor. Set the default widget options and create widget.
	 */
	function Genesis_Featured_Page() {
		$widget_ops = array( 'classname' => 'featuredpage', 'description' => __('Displays featured page with thumbnails', 'genesis') );
		$control_ops = array( 'width' => 200, 'height' => 250, 'id_base' => 'featured-page' );
		$this->WP_Widget( 'featured-page', __('Genesis - Featured Page', 'genesis'), $widget_ops, $control_ops );
	}

	/**
	 * Echo the widget content.
	 *
	 * @param array $args Display arguments including before_title, after_title, before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget
	 */
	function widget($args, $instance) {
		extract($args);

		$instance = wp_parse_args( (array) $instance, array(
			'title' => '',
			'page_id' => '',
			'show_image' => 0,
			'image_alignment' => '',
			'image_size' => '',
			'show_title' => 0,
			'show_byline' => 0,
			'show_content' => 0,
			'content_limit' => '',
			'more_text' => ''
		) );

		echo $before_widget;

			// Set up the author bio
			if (!empty($instance['title']))
				echo $before_title . apply_filters('widget_title', $instance['title']) . $after_title;

			$featured_page = new WP_Query(array('page_id' => $instance['page_id']));
			if($featured_page->have_posts()) : while($featured_page->have_posts()) : $featured_page->the_post();

				echo '<div '; post_class(); echo '>';

				if(!empty($instance['show_image'])) :
					printf( '<a href="%s" title="%s" class="%s">%s</a>', get_permalink(), the_title_attribute('echo=0'), esc_attr( $instance['image_alignment'] ), genesis_get_image( array( 'format' => 'html', 'size' => $instance['image_size'] ) ) );
				endif;

				if(!empty($instance['show_title'])) :
					printf( '<h2><a href="%s" title="%s">%s</a></h2>', get_permalink(), the_title_attribute('echo=0'), get_the_title() );
				endif;

				if(!empty($instance['show_byline'])) :
					echo '<p class="byline">';
					the_time('F j, Y');
					echo ' '.__('by', 'genesis').' ';
					the_author_posts_link();
					echo g_ent(' &middot; ');
					comments_popup_link(__('Leave a Comment', 'genesis'), __('1 Comment', 'genesis'), __('% Comments', 'genesis'));
					echo ' ';
					edit_post_link(__('(Edit)', 'genesis'), '', '');
					echo '</p>';
				endif;

				if(!empty($instance['show_content'])) :

					if(empty($instance['content_limit'])) :
						the_content($instance['more_text']);
					else :
						the_content_limit( (int)$instance['content_limit'], esc_html( $instance['more_text'] ) );
					endif;

				endif;

				echo '</div><!--end post_class()-->'."\n\n";

			endwhile; endif;

		echo $after_widget;
		wp_reset_query();
	}

	/** Update a particular instance.
	 *
	 * This function should check that $new_instance is set correctly.
	 * The newly calculated value of $instance should be returned.
	 * If "false" is returned, the instance won't be saved/updated.
	 *
	 * @param array $new_instance New settings for this instance as input by the user via form()
	 * @param array $old_instance Old settings for this instance
	 * @return array Settings to save or bool false to cancel saving
	 */
	function update($new_instance, $old_instance) {
		$new_instance['title'] = strip_tags( $new_instance['title'] );
		$new_instance['more_text'] = strip_tags( $new_instance['more_text'] );
		return $new_instance;
	}

	/** Echo the settings update form.
	 *
	 * @param array $instance Current settings
	 */
	function form($instance) {

		$instance = wp_parse_args( (array)$instance, array(
			'title' => '',
			'page_id' => '',
			'show_image' => 0,
			'image_alignment' => '',
			'image_size' => '',
			'show_title' => 0,
			'show_byline' => 0,
			'show_content' => 0,
			'content_limit' => '',
			'more_text' => __('[Read More...]', 'genesis')
		) );

?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'genesis'); ?>:</label>
		<input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" /></p>

		<p><label for="<?php echo $this->get_field_id('page_id'); ?>"><?php _e('Page', 'genesis'); ?>:</label>
		<?php wp_dropdown_pages(array('name' => $this->get_field_name('page_id'), 'selected' => $instance['page_id'])); ?></p>

		<hr class="div" />

		<p><input id="<?php echo $this->get_field_id('show_image'); ?>" type="checkbox" name="<?php echo $this->get_field_name('show_image'); ?>" value="1" <?php checked(1, $instance['show_image']); ?>/> <label for="<?php echo $this->get_field_id('show_image'); ?>"><?php _e('Show Featured Image', 'genesis'); ?></label></p>

		<p><label for="<?php echo $this->get_field_id('image_size'); ?>"><?php _e('Image Size', 'genesis'); ?>:</label>
		<?php $sizes = genesis_get_additional_image_sizes(); ?>
		<select id="<?php echo $this->get_field_id('image_size'); ?>" name="<?php echo $this->get_field_name('image_size'); ?>">
			<option value="thumbnail">thumbnail (<?php echo get_option('thumbnail_size_w'); ?>x<?php echo get_option('thumbnail_size_h'); ?>)</option>
			<?php
			foreach((array)$sizes as $name => $size) :
			echo '<option value="'.$name.'" '.selected($name, $instance['image_size'], FALSE).'>'.$name.' ('.$size['width'].'x'.$size['height'].')</option>';
			endforeach;
			?>
		</select></p>

		<p><label for="<?php echo $this->get_field_id('image_alignment'); ?>"><?php _e('Image Alignment', 'genesis'); ?>:</label>
		<select id="<?php echo $this->get_field_id('image_alignment'); ?>" name="<?php echo $this->get_field_name('image_alignment'); ?>">
			<option value="">- <?php _e('None', 'genesis'); ?> -</option>
			<option value="alignleft" <?php selected('alignleft', $instance['image_alignment']); ?>><?php _e('Left', 'genesis'); ?></option>
			<option value="alignright" <?php selected('alignright', $instance['image_alignment']); ?>><?php _e('Right', 'genesis'); ?></option>
		</select></p>

		<hr class="div" />

		<p><input id="<?php echo $this->get_field_id('show_title'); ?>" type="checkbox" name="<?php echo $this->get_field_name('show_title'); ?>" value="1" <?php checked(1, $instance['show_title']); ?>/> <label for="<?php echo $this->get_field_id('show_title'); ?>"><?php _e('Show Page Title', 'genesis'); ?></label></p>

		<p><input id="<?php echo $this->get_field_id('show_byline'); ?>" type="checkbox" name="<?php echo $this->get_field_name('show_byline'); ?>" value="1" <?php checked(1, $instance['show_byline']); ?>/> <label for="<?php echo $this->get_field_id('show_byline'); ?>"><?php _e('Show Page Byline', 'genesis'); ?></label></p>

		<p><input id="<?php echo $this->get_field_id('show_content'); ?>" type="checkbox" name="<?php echo $this->get_field_name('show_content'); ?>" value="1" <?php checked(1, $instance['show_content']); ?>/> <label for="<?php echo $this->get_field_id('show_content'); ?>"><?php _e('Show Page Content', 'genesis'); ?></label></p>

		<p><label for="<?php echo $this->get_field_id('content_limit'); ?>"><?php _e('Content Character Limit', 'genesis'); ?>:</label>
		<input type="text" id="<?php echo $this->get_field_id('content_limit'); ?>" name="<?php echo $this->get_field_name('content_limit'); ?>" value="<?php echo esc_attr( $instance['content_limit'] ); ?>" size="3" /></p>

		<p><label for="<?php echo $this->get_field_id('more_text'); ?>"><?php _e('More Text', 'genesis'); ?>:</label>
		<input type="text" id="<?php echo $this->get_field_id('more_text'); ?>" name="<?php echo $this->get_field_name('more_text'); ?>" value="<?php echo esc_attr( $instance['more_text'] ); ?>" /></p>

	<?php
	}
}