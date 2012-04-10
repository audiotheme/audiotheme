<?php
/**
 * Adds the Page Navigation Menu widget.
 *
 * @package Genesis
 */


/**
 * Genesis Pages Menu widget class.
 *
 * @package Genesis
 * @subpackage Widgets
 * @since unknown
 */
class Genesis_Menu_Pages_Widget extends WP_Widget {

	/**
	 * Constructor. Set the default widget options and create widget.
	 */
	function Genesis_Menu_Pages_Widget() {
		$widget_ops = array( 'classname' => 'menupages', 'description' => __('Display page navigation for your header', 'genesis') );
		$control_ops = array( 'width' => 200, 'height' => 250, 'id_base' => 'menu-pages' );
		$this->WP_Widget( 'menu-pages', __('Genesis - Page Navigation Menu', 'genesis'), $widget_ops, $control_ops );
	}

	/**
	 * Echo the widget content.
	 *
	 * @param array $args Display arguments including before_title, after_title, before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget
	 */
	function widget($args, $instance) {
		extract($args);

		$instance = wp_parse_args( (array)$instance, array(
			'title' => '',
			'include' => array(),
			'order' => ''
		) );

		echo $before_widget;

		if ($instance['title']) echo $before_title . apply_filters('widget_title', $instance['title']) . $after_title;

		echo '<ul class="nav">'."\n";

		// Empty fallback (default)
		if(empty($instance['include'])) :
			$instance['include'][] = 'home';
			$pages = get_pages();
			foreach((array)$pages as $page) {
				$instance['include'][] = $page->ID;
			}
		endif;

		// Show Home Link?
		if(in_array('home', (array)$instance['include'])) {
			$active = (is_front_page()) ? 'class="current_page_item"' : '';
			echo '<li '.$active.'><a href="'. trailingslashit( home_url() ).'">'.__('Home', 'genesis').'</a></li>';
		}
		// Show Page Links?
		wp_list_pages(array('title_li' => '', 'include' => implode(',', $instance['include']), 'sort_column' => $instance['order']));

		echo '</ul>'."\n";

		echo $after_widget;
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
		return $new_instance;
	}

	/** Echo the settings update form.
	 *
	 * @param array $instance Current settings
	 */
	function form($instance) {

		$instance = wp_parse_args( (array)$instance, array(
			'title' => '',
			'include' => array(),
			'order' => ''
		) );

		// Empty fallback (default)
		if(empty($instance['include'])) {
			$instance['include'][] = 'home';
			$pages = get_pages();
			foreach((array)$pages as $page) {
				$instance['include'][] = $page->ID;
			}
		}
		?>

		<p><?php _e('NOTE: Leave title blank if using this widget in the header', 'genesis'); ?></p>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'genesis'); ?>:</label>
			<input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>

		<p><?php _e('Choose the order by which you would like to display your pages', 'genesis'); ?>:</p>

		<p><select name="<?php echo $this->get_field_name('order'); ?>">
			<?php
				printf( '<option value="menu_order" %s>%s</option>', selected( 'menu_order', $instance['order'], 0 ), __( 'Menu Order', 'genesis' ) );
				printf( '<option value="ID" %s>%s</option>', selected( 'ID', $instance['order'], 0 ), __( 'ID', 'genesis' ) );
				printf( '<option value="post_title" %s>%s</option>', selected( 'post_title', $instance['order'], 0 ), __( 'Title', 'genesis' ) );
				printf( '<option value="post_date" %s>%s</option>', selected( 'post_date', $instance['order'], 0 ), __( 'Date Created', 'genesis' ) );
				printf( '<option value="post_modified" %s>%s</option>', selected( 'post_modified', $instance['order'], 0 ), __( 'Date Modified', 'genesis' ) );
				printf( '<option value="post_author" %s>%s</option>', selected( 'post_author', $instance['order'], 0 ), __( 'Author', 'genesis' ) );
				printf( '<option value="post_name" %s>%s</option>', selected( 'post_name', $instance['order'], 0 ), __( 'Slug', 'genesis' ) );
			?>
		</select></p>

		<p><?php _e('Use the checklist below to choose which pages (and subpages) you want to include in your Navigation Menu', 'genesis'); ?></p>

		<p>
		<ul class="genesis-pagechecklist">
		<?php genesis_page_checklist($this->get_field_name('include'), $instance['include']); ?>
		</ul>
		</p>

	<?php
	}
}
?>