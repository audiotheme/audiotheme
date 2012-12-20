<?php
class Audiotheme_Widget_Twitter extends WP_Widget {
	var $transient_key;
	var $transient_key_error;
	
	function __construct() {
		$widget_ops = array( 'classname' => 'widget_audiotheme_twitter', 'description' => 'Display your latest tweets' );
		$control_ops = array(); // array( 'width' => 300 );
		$this->WP_Widget( 'audiotheme-twitter', 'Twitter (AudioTheme)', $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );

		$instance['title_filtered'] = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		
		$tweets = $this->fetch_tweets( $instance );
		if ( is_wp_error( $tweets ) ) {
			return '<!--error-->';
		}
		
		echo $before_widget;
		
			if ( ! empty( $instance['title_filtered'] ) ) {
				echo $before_title;
					echo $instance['title_filtered'];
					printf( ' <a href="%s" target="_blank" title="@%s">@%s</a>',
						esc_url( 'http://twitter.com/' . $instance['screen_name'] ),
						esc_attr( $instance['screen_name'] ),
						$instance['screen_name']
					);
				echo $after_title;
			}
			
			$output = '<ul>';
				for ( $i = 0; $i < $instance['count']; $i ++ ) {
					if ( ! isset( $tweets[ $i ] ) )
						break;
					
					$output.= sprintf( '<li>%1$s</li>', $tweets[ $i ]['html'] );
				}
			$output.= '</ul>';
			
			// Be sure to respect the count parameter
			echo apply_filters( 'audiotheme_widget_latest_tweets_output', $output, $instance, $args, $tweets );
		
		echo $after_widget;
	}
	
	/**
	 * Widget Form
	 *
	 * @TODO: intro, show profile, include time/format, include media, last successful refresh
	 */
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array(
			'count' => 5,
			'exclude_replies' => false,
			'include_rts' => false,
			'screen_name' => '',
			'title' => ''
		) );
		extract( $instance );
		
		$error = get_transient( 'audiotheme_twitter_widget_error-' . $this->number );
		if ( ! empty( $error ) ) {
			printf( '<div class="error">%1$s</div>', wpautop( $error ) );
		}
		
		$title = wp_strip_all_tags( $instance['title'] );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" id="<?php echo $this->get_field_id( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" class="widefat">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'screen_name' ); ?>"><?php _e( 'Twitter username:' ); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'screen_name' ); ?>" id="<?php echo $this->get_field_id( 'screen_name' ); ?>" value="<?php echo esc_attr( $screen_name ); ?>" class="widefat">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Tweets to show:' ); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'count' ); ?>" id="<?php echo $this->get_field_id( 'count' ); ?>" value="<?php echo esc_attr( $count ); ?>" class="small-text">
		</p>
		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'exclude_replies' ); ?>" id="<?php echo $this->get_field_id( 'exclude_replies' ); ?>" <?php checked( $exclude_replies ); ?>>
			<label for="<?php echo $this->get_field_id( 'exclude_replies' ); ?>"><?php _e( 'Hide replies?' ); ?></label>
		</p>
		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'include_rts' ); ?>" id="<?php echo $this->get_field_id( 'include_rts' ); ?>" <?php checked( $include_rts ); ?>>
			<label for="<?php echo $this->get_field_id( 'include_rts' ); ?>"><?php _e( 'Include retweets?' ); ?></label>
		</p>
		<style type="text/css">
		.widget .widget-inside div.error p { margin: .25em 0; padding: 2px;}
		</style>
		<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = wp_parse_args( $new_instance, $old_instance );
		
		$instance['title'] = wp_strip_all_tags( $new_instance['title'] );
		$instance['filter'] = isset( $new_instance['filter'] );
		$instance['exclude_replies'] = isset( $new_instance['exclude_replies'] );
		$instance['include_rts'] = isset( $new_instance['include_rts'] );
		$instance['count'] = min( max( absint( $new_instance['count'] ), 1 ), 200 );
		
		// @todo Fetch tweets in order to discover errors and display message
		// @todo Error messages seem to bleed into general admin notices.
		$tweets = $this->fetch_tweets( array_merge( $instance, array( 'force_refresh' => true ) ) );
		
		return $instance;
	}
	
	function fetch_tweets( $args ) {
		$key = 'audiotheme_twitter_widget-' . $this->number;
		$error = null;
		$error_key = 'audiotheme_twitter_widget_error-' . $this->number;
		
		
		if ( empty( $args['screen_name'] ) ) {
			set_transient( $error_key, 'Twitter username cannot be empty.', 60*5 );
			return new WP_Error( 'empty_screen_name', 'The screen name cannot be empty.' );	
		}
		
		
		$tweets = get_transient( 'audiotheme_twitter_widget-' . $this->number );
		if ( ! $tweets || ( isset( $args['force_refresh'] ) && $args['force_refresh'] ) ) {
			$args['screen_name'] = rawurlencode( $args['screen_name'] );
			
			$remote_args = shortcode_atts( array(
				'screen_name' => '',
				'exclude_replies' => false,
				'include_entities' => true,
				'include_rts' => false,
				'trim_user' => true
			), $args );
			
			$remote_args['count'] = 200;
			
			$response = wp_remote_get( add_query_arg( $remote_args, 'http://api.twitter.com/1/statuses/user_timeline.json' ) );
			
			if ( 200 == wp_remote_retrieve_response_code( $response ) ) {
				$results = json_decode( wp_remote_retrieve_body( $response ), true );
				if ( is_array( $results ) && ! isset( $results['errors'] ) ) { // make sure there's not a twitter error
					$tweets = array();
					foreach( $results as $tweet ) {
						$tweets[] = array(
							'id_str' => $tweet['id_str'],
							'created_at' => $tweet['created_at'],
							'html' => $this->parse_tweet( $tweet ),
							'text' => $tweet['text']
						);
					}
					
					set_transient( $key, $tweets, 60 * 15 );
					update_option( $key, $tweets ); // update failsafe
					delete_transient( $error_key ); // delete any existing error messages
				} elseif ( isset( $results['errors'] ) ) {
					$error = $results['errors'][0]['message'];
				} else {
					$error = 'Unknown response format received from Twitter.';
				}
			} else {
				if ( is_wp_error( $response ) ) {
					$error = $response->get_error_message();
				} elseif ( $code = wp_remote_retrieve_response_code( $response ) ) {
					$error = 'Remote response code: ' . $code;
				} else {
					$error = 'Twitter did not respond. Please wait awhile and try again.';
				}
			}
			
			if ( ! empty( $error ) ) {
				$tweets = get_option( $key );
				set_transient( $key, $tweets, 60*5 ); // check again in 5 minutes
				set_transient( $error_key, $error, 60*5 );
			}
			
			if ( empty( $tweets ) ) {
				// TODO: suggested, check authorization, wait a little while
				return new WP_Error( 'no_tweets', 'Uhh, there weren\'t any tweets.' );
			}
		}
		
		return $tweets;
	}
	
	function parse_tweet( $tweet ) {
		$text = $tweet['text'];
		
		if ( isset( $tweet['entities'] ) ) {
			$entities = array();
			// Flatten entity array so we can sort them by starting indice
			foreach( $tweet['entities'] as $type => $type_entities ) {
				if ( ! empty( $type_entities ) ) {
					foreach( $type_entities as $key => $entity ) {
						$entity['type'] = $type;
						$entities[] = $entity;
					}
				}
			}
			usort( $entities, array( $this, 'sort_tweet_entities' ) );
			
			$shift = 0;
			foreach( $entities as $entity ) {
				$start = $entity['indices'][0] + $shift;
				$length = $entity['indices'][1] - $entity['indices'][0];
				$match = mb_substr( $text, $start, $length );
				
				$before = ( 0 !== $start ) ? mb_substr( $text, 0, $start ) : '';
				$after = ( $length ) ? mb_substr( $text, $start + $length ) : '';
				
				switch( $entity['type'] ) {
					case 'hashtags' :
						$replace = sprintf( '<a href="' . esc_url( 'http://twitter.com/search/#' . $entity['text'] ) . '" target="_blank">%s</a>',
							$match
						);
						break;
					case 'urls' :
						$replace = sprintf( '<a href="%1$s" target="_blank">%2$s</a>',
							esc_url( $entity['expanded_url'] ),
							esc_url( $entity['display_url'] )
						);
						break;
					case 'user_mentions' :
						$replace = sprintf( '<a href="' . esc_url( 'http://twitter.com/' . $entity['screen_name'] ) . '" target="_blank">%s</a>',
							$match
						);
						break;
					default :
						$replace = $match;
						break;
				}
				
				$text = $before . $replace . $after;
				$shift += mb_strlen( $replace ) - $length;
			}
		}
		
		return $text;
	}
	
	function sort_tweet_entities( $a, $b ) {
		if ( $a['indices'][0] == $b['indices'][0] ) {
        	return 0;
    	}
   		
		return ( $a['indices'][0] < $b['indices'][0] ) ? -1 : 1;
	}
}
?>