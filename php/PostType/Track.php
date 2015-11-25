<?php
/**
 * Track post type registration and integration.
 *
 * @package   AudioTheme\Discography
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.9.0
 */

/**
 * Class for registering the track post type and integration.
 *
 * @package AudioTheme\Discography
 * @since   1.9.0
 */
class AudioTheme_PostType_Track extends AudioTheme_PostType_AbstractPostType {
	/**
	 * Discography module.
	 *
	 * @since 1.9.0
	 * @var AudioTheme_Module_Discography
	 */
	protected $module;

	/**
	 * Post type name.
	 *
	 * @since 1.9.0
	 * @var string
	 */
	protected $post_type = 'audiotheme_track';

	/**
	 * Constructor method.
	 *
	 * @since 1.9.0
	 *
	 * @param AudioTheme_Module_Discography $module Gigs module.
	 */
	public function __construct( AudioTheme_Module_Discography $module ) {
		$this->module = $module;
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.0
	 */
	public function register_hooks() {
		add_action( 'init',                    array( $this, 'register_post_type' ) );
		add_action( 'pre_get_posts',           array( $this, 'track_query' ) );
		add_filter( 'post_type_archive_link',  array( $this, 'archive_permalink' ), 10, 2 );
		add_filter( 'post_type_link',          array( $this, 'post_permalink' ), 10, 4 );
		add_filter( 'wp_unique_post_slug',     array( $this, 'get_unique_slug' ), 10, 6 );
		add_action( 'wp_print_footer_scripts', array( $this, 'print_tracks_js' ) );
		add_filter( 'post_updated_messages',   array( $this, 'post_updated_messages' ) );
	}

	/**
	 * Filter track requests.
	 *
	 * Tracks must belong to a record, so the parent record is set for track
	 * requests.
	 *
	 * @since 1.9.0
	 *
	 * @global wpdb $wpdb
	 *
	 * @param WP_Query $wp_query The main WP_Query object. Passed by reference.
	 */
	public function track_query( $wp_query ) {
		global $wpdb;

		if ( is_admin() || ! $wp_query->is_main_query() ) {
			return;
		}

		if ( ! is_single() || 'audiotheme_track' !== $wp_query->get( 'post_type' ) ) {
			return;
		}

		// Limit requests for single tracks to the context of the parent record.
		if ( get_option( 'permalink_structure' ) ) {
			$record_id = $wpdb->get_var( $wpdb->prepare(
				"SELECT ID
				FROM $wpdb->posts
				WHERE post_type = 'audiotheme_record' AND post_name = %s
				LIMIT 1",
				$wp_query->get( 'audiotheme_record' )
			) );
		} elseif ( ! empty( $_GET['post_parent'] ) ) {
			$record_id = absint( $_GET['post_parent'] );
		}

		if ( ! empty( $record_id ) ) {
			$wp_query->set( 'post_parent', $record_id );
		}
	}

	/**
	 * Filter the permalink for track archives.
	 *
	 * @since 1.9.0
	 *
	 * @param string $link The default archive URL.
	 * @param string $post_type Post type.
	 * @return string The discography archive URL.
	 */
	public function archive_permalink( $link, $post_type ) {
		$permalink = get_option( 'permalink_structure' );
		if ( ! empty( $permalink ) && 'audiotheme_track' === $post_type ) {
			$link = home_url( '/' . $this->module->get_rewrite_base() . '/' );
		}

		return $link;
	}

	/**
	 * Filter track permalinks to match the custom rewrite rules.
	 *
	 * Allows the standard WordPress API function get_permalink() to return the
	 * correct URL when used with a track post type.
	 *
	 * @since 1.9.0
	 *
	 * @see get_post_permalink()
	 *
	 * @param string  $post_link The default permalink.
	 * @param WP_Post $post The track post object to get the permalink for.
	 * @param bool    $leavename Whether to keep the post name.
	 * @param bool    $sample Is it a sample permalink.
	 * @return string
	 */
	public function post_permalink( $post_link, $post, $leavename, $sample ) {
		if ( $this->is_draft_or_pending( $post ) || 'audiotheme_track' !== get_post_type( $post ) ) {
			return $post_link;
		}

		$permalink = get_option( 'permalink_structure' );

		if ( ! empty( $permalink ) && ! empty( $post->post_parent ) ) {
			$base   = $this->module->get_rewrite_base();
			$slug   = $leavename ? '%postname%' : $post->post_name;
			$record = get_post( $post->post_parent );

			if ( $record ) {
				$post_link = home_url( sprintf( '/%s/%s/track/%s/', $base, $record->post_name, $slug ) );
			}
		} elseif ( empty( $permalink ) && ! empty( $post->post_parent ) ) {
			$post_link = add_query_arg( 'post_parent', $post->post_parent, $post_link );
		}

		return $post_link;
	}

	/**
	 * Ensure track slugs are unique.
	 *
	 * Tracks should always be associated with a record so their slugs only need
	 * to be unique within the context of a record.
	 *
	 * @since 1.9.0
	 *
	 * @see wp_unique_post_slug()
	 * @global wpdb $wpdb
	 * @global WP_Rewrite $wp_rewrite
	 *
	 * @param string  $slug The desired slug (post_name).
	 * @param integer $post_id Post ID.
	 * @param string  $post_status No uniqueness checks are made if the post is still draft or pending.
	 * @param string  $post_type Post type.
	 * @param integer $post_parent Post parent ID.
	 * @param string  $original_slug Slug passed to the uniqueness method.
	 * @return string
	 */
	public function get_unique_slug( $slug, $post_id, $post_status, $post_type, $post_parent, $original_slug = null ) {
		global $wpdb, $wp_rewrite;

		if ( 'audiotheme_track' === $post_type ) {
			$slug = $original_slug;

			$feeds = $wp_rewrite->feeds;
			if ( ! is_array( $feeds ) ) {
				$feeds = array();
			}

			// Make sure the track slug is unique within the context of the record only.
			$check_sql = "SELECT post_name FROM $wpdb->posts WHERE post_name = %s AND post_type = %s AND post_parent = %d AND ID != %d LIMIT 1";
			$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, $slug, $post_type, $post_parent, $post_id ) );

			if ( $post_name_check || apply_filters( 'wp_unique_post_slug_is_bad_flat_slug', false, $slug, $post_type ) ) {
				$suffix = 2;
				do {
					$alt_post_name = substr( $slug, 0, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";
					$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, $alt_post_name, $post_type, $post_parent, $post_id ) );
					$suffix++;
				} while ( $post_name_check );
				$slug = $alt_post_name;
			}
		}

		return $slug;
	}

	/**
	 * Transform a track id or array of data into the expected format for use as
	 * a JavaScript object.
	 *
	 * @since 1.9.0
	 *
	 * @param int|array $track Track ID or array of expected track properties.
	 * @return array
	 */
	public function prepare_track_for_js( $track ) {
		$data = array(
			'artist'  => '',
			'artwork' => '',
			'mp3'     => '',
			'record'  => '',
			'title'   => '',
		);

		// Enqueue a track post type.
		if ( 'audiotheme_track' === get_post_type( $track ) ) {
			$track  = get_post( $track );
			$record = get_post( $track->post_parent );

			$data['artist'] = get_audiotheme_track_artist( $track->ID );
			$data['mp3']    = get_audiotheme_track_file_url( $track->ID );
			$data['record'] = $record->post_title;
			$data['title']  = $track->post_title;

			// WP playlist format.
			$data['format']                   = 'mp3';
			$data['meta']['artist']           = $data['artist'];
			$data['meta']['length_formatted'] = '0:00';
			$data['src']                      = $data['mp3'];

			$thumbnail_id = get_audiotheme_track_thumbnail_id( $track );
			if ( ! empty( $thumbnail_id ) ) {
				$image = wp_get_attachment_image_src( $thumbnail_id, apply_filters( 'audiotheme_track_js_artwork_size', 'thumbnail' ) );
				$data['artwork'] = $image[0];
			}
		}

		// Add the track data directly.
		elseif ( is_array( $track ) ) {
			if ( isset( $track['artwork'] ) ) {
				$data['artwork'] = esc_url( $track['artwork'] );
			}

			if ( isset( $track['file'] ) ) {
				$data['mp3'] = esc_url_raw( audiotheme_encode_url_path( $track['file'] ) );
			}

			if ( isset( $track['mp3'] ) ) {
				$data['mp3'] = esc_url_raw( audiotheme_encode_url_path( $track['mp3'] ) );
			}

			if ( isset( $track['title'] ) ) {
				$data['title'] = wp_strip_all_tags( $track['title'] );
			}

			$data = array_merge( $track, $data );
		}

		$data = apply_filters( 'audiotheme_track_js_data', $data, $track );

		return $data;
	}

	/**
	 * Convert enqueued track lists into an array of tracks prepared for
	 * JavaScript and output the JSON-encoded object in the footer.
	 *
	 * @todo The track & record ids should be collected at some point so they
	 *       can all be fetched in a single query.
	 *
	 * @since 1.9.0
	 */
	public function print_tracks_js() {
		global $audiotheme_enqueued_tracks;

		if ( empty( $audiotheme_enqueued_tracks ) || ! is_array( $audiotheme_enqueued_tracks ) ) {
			return;
		}

		$lists = array();

		foreach ( $audiotheme_enqueued_tracks as $list => $tracks ) {
			if ( empty( $tracks ) || ! is_array( $tracks ) ) {
				continue;
			}

			do_action( 'audiotheme_prepare_tracks', $list );

			foreach ( $tracks as $track ) {
				if ( 'audiotheme_record' === get_post_type( $track ) ) {
					$record_tracks = get_audiotheme_record_tracks( $track, array( 'has_file' => true ) );

					if ( $record_tracks ) {
						foreach ( $record_tracks as $record_track ) {
							if ( $track_data = $this->prepare_track_for_js( $record_track ) ) {
								$lists[ $list ][] = $track_data;
							}
						}
					}
				} elseif ( $track_data = $this->prepare_track_for_js( $track ) ) {
					$lists[ $list ][] = $track_data;
				}
			}
		}

		// Print a JavaScript object.
		if ( ! empty( $lists ) ) {
			?>
			<script type="text/javascript">
			/* <![CDATA[ */
			window.AudiothemeTracks = window.AudiothemeTracks || {};

			(function( window ) {
				var tracks = <?php echo wp_json_encode( $lists ); ?>,
					i;

				for ( i in tracks ) {
					window.AudiothemeTracks[ i ] = tracks[ i ];
				}
			})( this );
			/* ]]> */
			</script>
			<?php
		}
	}

	/**
	 * Retrieve post type registration argments.
	 *
	 * @since 1.9.0
	 *
	 * @return array
	 */
	protected function get_args() {
		return array(
			'has_archive'        => false,
			'hierarchical'       => false,
			'labels'             => $this->get_labels(),
			'public'             => true,
			'publicly_queryable' => true,
			'rewrite'            => false,
			'show_ui'            => true,
			'show_in_admin_bar'  => true,
			'show_in_menu'       => 'edit.php?post_type=audiotheme_record',
			'show_in_nav_menus'  => true,
			'supports'           => array( 'title', 'editor', 'thumbnail' ),
		);
	}

	/**
	 * Retrieve post type labels.
	 *
	 * @return array
	 */
	protected function get_labels() {
		return array(
			'name'                  => esc_html_x( 'Tracks', 'post type general name', 'audiotheme' ),
			'singular_name'         => esc_html_x( 'Track', 'post type singular name', 'audiotheme' ),
			'add_new'               => esc_html_x( 'Add New', 'track', 'audiotheme' ),
			'add_new_item'          => esc_html__( 'Add New Track', 'audiotheme' ),
			'edit_item'             => esc_html__( 'Edit Track', 'audiotheme' ),
			'new_item'              => esc_html__( 'New Track', 'audiotheme' ),
			'view_item'             => esc_html__( 'View Track', 'audiotheme' ),
			'search_items'          => esc_html__( 'Search Tracks', 'audiotheme' ),
			'not_found'             => esc_html__( 'No tracks found', 'audiotheme' ),
			'not_found_in_trash'    => esc_html__( 'No tracks found in Trash', 'audiotheme' ),
			'parent_item_colon'     => esc_html__( 'Parent Track:', 'audiotheme' ),
			'all_items'             => esc_html__( 'All Tracks', 'audiotheme' ),
			'menu_name'             => esc_html_x( 'Tracks', 'admin menu name', 'audiotheme' ),
			'name_admin_bar'        => esc_html_x( 'Tracks', 'add new on admin bar', 'audiotheme' ),
			'archives'              => esc_html__( 'Post Archives', 'audiotheme' ),
			'insert_into_item'      => esc_html__( 'Insert into track', 'audiotheme' ),
			'uploaded_to_this_item' => esc_html__( 'Uploaded to this track', 'audiotheme' ),
			'featured_image'        => esc_html__( 'Featured Image', 'audiotheme' ),
			'set_featured_image'    => esc_html__( 'Set featured image', 'audiotheme' ),
			'remove_featured_image' => esc_html__( 'Remove featured image', 'audiotheme' ),
			'use_featured_image'    => esc_html__( 'Use as featured image', 'audiotheme' ),
			'filter_items_list'     => esc_html__( 'Filter tracks list', 'audiotheme' ),
			'items_list_navigation' => esc_html__( 'Tracks list navigation', 'audiotheme' ),
			'items_list'            => esc_html__( 'Tracks list', 'audiotheme' ),
		);
	}

	/**
	 * Retrieve post updated messages.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Post $post Post object.
	 * @return array
	 */
	protected function get_updated_messages( $post ) {
		return array(
			0  => '', // Unused. Messages start at index 1.
			1  => esc_html__( 'Track updated.', 'audiotheme' ),
			2  => esc_html__( 'Custom field updated.', 'audiotheme' ),
			3  => esc_html__( 'Custom field deleted.', 'audiotheme' ),
			4  => esc_html__( 'Track updated.', 'audiotheme' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( esc_htmlesc_html__( 'Track restored to revision from %s.' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => esc_html__( 'Track published.', 'audiotheme' ),
			7  => esc_html__( 'Track saved.', 'audiotheme' ),
			8  => esc_html__( 'Track submitted.', 'audiotheme' ),
			9  => sprintf(
				esc_html__( 'Track scheduled for: %s.', 'audiotheme' ),
				/* translators: Publish box date format, see http://php.net/date */
				'<strong>' . date_i18n( esc_html__( 'M j, Y @ H:i', 'audiotheme' ), strtotime( $post->post_date ) ) . '</strong>'
			),
			10 => esc_html__( 'Track draft updated.', 'audiotheme' ),
			'preview' => esc_html__( 'Preview track', 'audiotheme' ),
			'view'    => esc_html__( 'View track', 'audiotheme' ),
		);
	}
}
