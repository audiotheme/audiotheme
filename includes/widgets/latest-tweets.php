<?php
/**
 * Adds the Latest tweets widget.
 *
 * @package Genesis
 */


/**
 * Genesis Latest Tweets widget class.
 *
 * @package Genesis
 * @subpackage Widgets
 * @since unknown
 */
class Genesis_Latest_Tweets_Widget extends WP_Widget {

	/**
	 * Constructor. Set the default widget options and create widget.
	 */
	function Genesis_Latest_Tweets_Widget() {
		$widget_ops = array( 'classname' => 'latest-tweets', 'description' => __('Display a list of your latest tweets', 'genesis') );
		$control_ops = array( 'width' => 200, 'height' => 250, 'id_base' => 'latest-tweets' );
		$this->WP_Widget( 'latest-tweets', __('Genesis - Latest Tweets', 'genesis'), $widget_ops, $control_ops );
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
			'twitter_id' => '',
			'twitter_num' => '',
			'twitter_duration' => '',
			'twitter_hide_replies' => 0,
			'follow_link_show' => 0,
			'follow_link_text' => ''
		) );

		echo $before_widget;

			if ($instance['title']) echo $before_title . apply_filters('widget_title', $instance['title']) . $after_title;
			echo '<ul>' . "\n";

					$tweets = get_transient($instance['twitter_id'].'-'.$instance['twitter_num'].'-'.$instance['twitter_duration']);

					if( !$tweets ) {

						$count = isset( $instance['twitter_hide_replies'] ) ? (int)$instance['twitter_num'] + 100 : (int)$instance['twitter_num'];
						$twitter = wp_remote_retrieve_body( wp_remote_request( sprintf( 'http://api.twitter.com/1/statuses/user_timeline.json?screen_name=%s&count=%s&trim_user=1', $instance['twitter_id'], $count ), array('timeout' => 100) ) );

						$json = json_decode($twitter);

						if( !$twitter ) {
							$tweets[] = '<li>' . __('The Twitter API is taking too long to respond. Please try again later.', 'genesis') . '</li>' . "\n";
						}
						elseif ( is_wp_error($twitter) ) {
							$tweets[] = '<li>' . __('There was an error while attempting to contact the Twitter API. Please try again.', 'genesis') . '</li>' . "\n";
						}
						elseif ( is_object( $json ) && $json->error ) {
							$tweets[] = '<li>' . __('The Twitter API returned an error while processing your request. Please try again.', 'genesis') . '</li>' . "\n";
						}
						else {

							// Build the tweets array
							foreach( (array)$json as $tweet ) {

								// don't include @ replies (if applicable)
								if( $instance['twitter_hide_replies'] && $tweet->in_reply_to_user_id )
									continue;

								// stop the loop if we've got enough tweets
								if( !empty( $tweets[(int)$instance['twitter_num'] - 1] ) )
									break;

								// add tweet to array
								$timeago = sprintf(__('about %s ago', 'genesis'), human_time_diff(strtotime($tweet->created_at)));
								$timeago_link = sprintf( '<a href="%s" rel="nofollow">%s</a>', esc_url( sprintf( 'http://twitter.com/%s/status/%s', $instance['twitter_id'], $tweet->id_str ) ), esc_html( $timeago ) );

								$tweets[] = '<li>' . genesis_tweet_linkify( $tweet->text ) . ' <span style="font-size: 85%;">' . $timeago_link . '</span></li>' . "\n";

							}

							// just in case
							$tweets = array_slice((array)$tweets, 0, (int)$instance['twitter_num']);

							if( $instance['follow_link_show'] && $instance['follow_link_text'] )
								$tweets[] = '<li class="last"><a href="' . esc_url( 'http://twitter.com/'.$instance['twitter_id'] ).'">'. esc_html( $instance['follow_link_text'] ) .'</a></li>';

							$time = ( absint($instance['twitter_duration']) * 60 );

							// Save them in transient
							set_transient($instance['twitter_id'].'-'.$instance['twitter_num'].'-'.$instance['twitter_duration'], $tweets, $time);

						}

					}

					foreach( (array)$tweets as $tweet ) {
						echo $tweet;
					}

			echo '</ul>' . "\n";

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

		// Force the transient to refresh
		delete_transient($old_instance['twitter_id'].'-'.$old_instance['twitter_num'].'-'.$old_instance['twitter_duration']);
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
			'twitter_id' => '',
			'twitter_num' => '',
			'twitter_duration' => '',
			'twitter_hide_replies' => 0,
			'follow_link_show' => 0,
			'follow_link_text' => ''
		) );

?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'genesis'); ?>:</label>
			<input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('twitter_id'); ?>"><?php _e('Twitter Username', 'genesis'); ?>:</label>
			<input type="text" id="<?php echo $this->get_field_id('twitter_id'); ?>" name="<?php echo $this->get_field_name('twitter_id'); ?>" value="<?php echo esc_attr( $instance['twitter_id'] ); ?>" class="widefat" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('twitter_num'); ?>"><?php _e('Number of Tweets to Show', 'genesis'); ?>:</label>
			<input type="text" id="<?php echo $this->get_field_id('twitter_num'); ?>" name="<?php echo $this->get_field_name('twitter_num'); ?>" value="<?php echo esc_attr( $instance['twitter_num'] ); ?>" size="3" />
		</p>

		<p><input id="<?php echo $this->get_field_id('twitter_hide_replies'); ?>" type="checkbox" name="<?php echo $this->get_field_name('twitter_hide_replies'); ?>" value="1" <?php checked(1, $instance['twitter_hide_replies']); ?>/> <label for="<?php echo $this->get_field_id('twitter_hide_replies'); ?>"><?php _e('Hide @ Replies', 'genesis'); ?></label></p>

		<p>
			<label for="<?php echo $this->get_field_id('twitter_duration'); ?>"><?php _e('Load new Tweets every', 'genesis'); ?></label>
			<select name="<?php echo $this->get_field_name('twitter_duration'); ?>" id="<?php echo $this->get_field_id('twitter_duration'); ?>">
				<option value="5" <?php selected(5, $instance['twitter_duration']); ?>><?php _e( '5 Min.' , 'genesis' ); ?></option>
				<option value="15" <?php selected(15, $instance['twitter_duration']); ?>><?php _e( '15 Minutes' , 'genesis' ); ?></option>
				<option value="30" <?php selected(30, $instance['twitter_duration']); ?>><?php _e( '30 Minutes' , 'genesis' ); ?></option>
				<option value="60" <?php selected(60, $instance['twitter_duration']); ?>><?php _e( '1 Hour' , 'genesis' ); ?></option>
				<option value="120" <?php selected(120, $instance['twitter_duration']); ?>><?php _e( '2 Hours' , 'genesis' ); ?></option>
				<option value="240" <?php selected(240, $instance['twitter_duration']); ?>><?php _e( '4 Hours' , 'genesis' ); ?></option>
				<option value="720" <?php selected(720, $instance['twitter_duration']); ?>><?php _e( '12 Hours' , 'genesis' ); ?></option>
				<option value="1440" <?php selected(1440, $instance['twitter_duration']); ?>><?php _e( '24 Hours' , 'genesis' ); ?></option>
			</select>
		</p>

		<p><input id="<?php echo $this->get_field_id('follow_link_show'); ?>" type="checkbox" name="<?php echo $this->get_field_name('follow_link_show'); ?>" value="1" <?php checked(1, $instance['follow_link_show']); ?>/> <label for="<?php echo $this->get_field_id('follow_link_show'); ?>"><?php _e('Include link to twitter page?', 'genesis'); ?></label></p>

		<p>
			<label for="<?php echo $this->get_field_id('follow_link_text'); ?>"><?php _e('Link Text (required)', 'genesis'); ?>:</label>
			<input type="text" id="<?php echo $this->get_field_id('follow_link_text'); ?>" name="<?php echo $this->get_field_name('follow_link_text'); ?>" value="<?php echo esc_attr( $instance['follow_link_text'] ); ?>" class="widefat" />
		</p>

	<?php
	}
}
