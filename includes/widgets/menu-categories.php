<?php
/**
 * undocumented
 *
 * @package Genesis
 */


/**
 * Genesis Categories Menu widget class.
 *
 * @package Genesis
 * @subpackage Widgets
 * @since unknown
 */
class Genesis_Widget_Menu_Categories extends WP_Widget {

	/**
	 * Constructor. Set the default widget options and create widget.
	 */
	function Genesis_Widget_Menu_Categories() {
		$widget_ops = array('classname' => 'menu-categories', 'description' => __('Display category navigation for your header', 'genesis') );
		$this->WP_Widget('menu-categories', __('Genesis - Category Navigation Menu', 'genesis'), $widget_ops);
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
		if( empty( $instance['include'] ) ) {
			$categories = get_categories( 'hide_empty=0' );
			foreach( (array) $categories as $category ) {
				$instance['include'][] = $category->cat_ID;
			}
		}

		// Show Home Link?
		if(in_array('home', (array)$instance['include'])) {
			$active = (is_front_page()) ? 'class="current_page_item"' : '';
			echo '<li '.$active.'><a href="'. trailingslashit( home_url() ) .'">'.__('Home', 'genesis').'</a></li>';
		}
		// Show Category Links?
		wp_list_categories(array('title_li' => '', 'include' => implode(',', (array)$instance['include']), 'orderby' => $instance['order'], 'hide_empty' => FALSE));

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
		if(empty($instance['include'])) :
			$cats = get_categories('hide_empty=0');
			foreach((array)$cats as $cat) {
				$instance['include'][] = $cat->cat_ID;
			}
		endif;
		?>

		<p><?php _e('NOTE: Leave title blank if using this widget in the header', 'genesis'); ?></p>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">
			<?php _e('Title', 'genesis'); ?>:
			</label>
			<input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>

		<p><?php _e('Choose the order by which you would like to display your categories', 'genesis'); ?>:</p>

		<p><select name="<?php echo $this->get_field_name('order'); ?>">
			<?php
				printf( '<option value="ID" %s>%s</option>', selected( 'ID', $instance['order'], 0 ), __( 'ID', 'genesis' ) );
				printf( '<option value="name" %s>%s</option>', selected( 'name', $instance['order'], 0 ), __( 'Name', 'genesis' ) );
				printf( '<option value="slug" %s>%s</option>', selected( 'slug', $instance['order'], 0 ), __( 'Slug', 'genesis' ) );
				printf( '<option value="count" %s>%s</option>', selected( 'count', $instance['order'], 0 ), __( 'Count', 'genesis' ) );
				printf( '<option value="term_group" %s>%s</option>', selected( 'term_group', $instance['order'], 0 ), __( 'Term Group', 'genesis' ) );
			?>
		</select></p>

		<p><?php _e('Use the checklist below to choose which categories (and subcategories) you want to include in your Navigation Menu', 'genesis'); ?></p>

		<div id="categorydiv">
		<ul class="categorychecklist">
		<?php genesis_category_checklist($this->get_field_name('include'), $instance['include']); ?>
		</ul>
		</div>

	<?php
	}
}
?>