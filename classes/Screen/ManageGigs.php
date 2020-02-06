<?php
/**
 * Manage Gigs administration screen integration.
 *
 * @package   AudioTheme\Gigs
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */

/**
 * Class providing integration with the Manage Gigs administration screen.
 *
 * @package AudioTheme\Gigs
 * @since   2.0.0
 */
class AudioTheme_Screen_ManageGigs extends AudioTheme_Screen_AbstractScreen{
	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_filter( 'parse_query',   array( $this, 'parse_admin_query' ) );
		add_action( 'load-edit.php', array( $this, 'load_screen' ) );
	}

	/**
	 * Set up the screen.
	 *
	 * @since 2.0.0
	 */
	public function load_screen() {
		if ( 'edit-audiotheme_gig' !== get_current_screen()->id ) {
			return;
		}

		add_action( 'load-audiotheme_gig',                         array( $this, 'handle_request' ) );
		add_action( 'admin_enqueue_scripts',                       array( $this, 'enqueue_assets' ) );
		add_filter( 'views_edit-audiotheme_gig',                   array( $this, 'filter_views' ) );
		add_action( 'restrict_manage_posts',                       array( $this, 'display_months_filter' ) );
		add_action( 'restrict_manage_posts',                       array( $this, 'display_venues_filter' ) );
		add_filter( 'manage_audiotheme_gig_posts_columns',         array( $this, 'register_columns' ), 9 );
		add_filter( 'list_table_primary_column',                   array( $this, 'register_primary_column' ) );
		add_action( 'manage_edit-audiotheme_gig_sortable_columns', array( $this, 'register_sortable_columns' ) );
		add_action( 'post_row_actions',                            array( $this, 'register_row_actions' ), 10, 2 );
		add_action( 'manage_posts_custom_column',                  array( $this, 'display_columns' ), 10, 2 );
		add_action( 'admin_footer',                                array( $this, 'print_templates' ) );
	}

	/**
	 * Sort posts on the screen.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Query $wp_query The main WP_Query object passed by reference.
	 */
	public function parse_admin_query( $wp_query ) {
		// Ensure this only affects requests in the admin panel.
		if (
			! is_admin() ||
			! $wp_query->is_main_query() ||
			empty( $_GET['post_type'] ) ||
			'audiotheme_gig' !== $_GET['post_type']
		) {
			return;
		}

		$current_view = $this->get_current_view();

		// Sort in descending order by default.
		$order = isset( $_REQUEST['order'] ) && 'asc' === strtolower( $_REQUEST['order'] ) ? 'asc' : 'desc';
		$wp_query->set( 'order', $order );

		// Upcoming and past views.
		if ( empty( $_REQUEST['m'] ) && ( 'upcoming' === $current_view || 'past' === $current_view ) ) {
			$wp_query->set( 'meta_query', array(
				array(
					'key'     => '_audiotheme_gig_datetime',
					'value'   => isset( $_REQUEST['gig_date'] ) ? urldecode( $_REQUEST['gig_date'] ) : current_time( 'mysql', true ),
					'compare' => isset( $_REQUEST['compare'] ) ? urldecode( $_REQUEST['compare'] ) : '>=',
					'type'    => 'DATETIME',
				),
			) );

			// Sort upcoming in ascending order by default.
			if ( 'upcoming' === $current_view && empty( $_REQUEST['order'] ) ) {
				$wp_query->set( 'order', 'asc' );
			}
		}

		// Monthly views.
		elseif ( ! empty( $_REQUEST['m'] ) ) {
			$m     = absint( substr( $_REQUEST['m'], 4 ) );
			$y     = absint( substr( $_REQUEST['m'], 0, 4 ) );
			$start = sprintf( '%s-%s-01', $y, zeroise( $m, 2 ) );
			$end   = sprintf( '%s', date( 'Y-m-t', mktime( 0, 0, 0, $m, 1, $y ) ) );

			$wp_query->set( 'm', null );
			$wp_query->set( 'meta_query', array(
				array(
					'key'     => '_audiotheme_gig_datetime',
					'value'   => array( $start, $end ),
					'compare' => 'BETWEEN',
					'type'    => 'DATE',
				),
			) );

			// Sort in ascending order.
			if ( empty( $_REQUEST['order'] ) ) {
				$wp_query->set( 'order', 'asc' );
			}
		}

		// Filter by venue.
		if ( ! empty( $_REQUEST['venue'] ) ) {
			$wp_query->set( 'connected_type', 'audiotheme_venue_to_gig' );
			$wp_query->set( 'connected_items', absint( $_REQUEST['venue'] ) );
		}

		if ( isset( $_REQUEST['orderby'] ) ) {
			switch ( $_REQUEST['orderby'] ) {
				case 'title':
					$wp_query->set( 'orderby', 'title' );
					break;
				default:
					$wp_query->set( 'meta_key', '_audiotheme_' . sanitize_text_field( $_REQUEST['orderby'] ) );
					$wp_query->set( 'orderby', 'meta_value' );
					break;
			}
		} elseif ( empty( $_REQUEST['post_status'] ) || 'draft' !== $_REQUEST['post_status'] ) {
			$wp_query->set( 'meta_key', '_audiotheme_gig_datetime' );
			$wp_query->set( 'orderby', 'meta_value' );
		}
	}

	/**
	 * Process the request to duplicate a gig.
	 *
	 * @since 2.1.0
	 */
	public function handle_request() {
		$post_id = empty( $_GET['post'] ) ? 0 : absint( $_GET['post'] );

		// Bail if the action shouldn't be executed or intention can't be verified.
		if ( empty( $_GET['action'] ) || 'duplicate-gig' !== $_GET['action'] ) {
			return;
		}

		check_admin_referer( 'duplicate-gig_' . $post_id );

		$duplicate_id = audiotheme_duplicate_gig( $post_id );

		if ( $duplicate_id ) {
			// @todo Display an admin notice.
			$redirect_url = get_edit_post_link( $duplicate_id, 'redirect' );
			wp_safe_redirect( $redirect_url );
			exit;
		}

		// @todo Display an admin notice about the failure.
		wp_safe_redirect( admin_url( 'edit.php?post_type=audiotheme_gig' ) );
		exit;
	}

	/**
	 * Enqueue assets.
	 *
	 * @since 2.1.0
	 */
	public function enqueue_assets( $hook_suffix ) {
		wp_enqueue_script(
			'audiotheme-duplicate-gig',
			$this->plugin->get_url( 'admin/js/duplicate-gig.js' ),
			array( 'jquery', 'jquery-timepicker', 'pikaday', 'wp-backbone', 'wp-util' ),
			'1.0.0',
			true
		);

		wp_localize_script( 'audiotheme-duplicate-gig', '_audiothemeDuplicateGigSettings', array(
			'l10n'       => array(
				'duplicate'           => esc_html__( 'Duplicate', 'audiotheme' ),
				'duplicateModalTitle' => esc_html__( 'Gig Date and Time', 'audiotheme' ),
			),
			'timeFormat' => AudioTheme_Module_Gigs::get_time_format(),
		) );
	}

	/**
	 * Filter the view links.
	 *
	 * @since 2.0.0
	 *
	 * @param array $views Array of view links.
	 * @return array
	 */
	public function filter_views( $views ) {
		global $wpdb;

		$post_type    = 'audiotheme_gig';
		$current_view = $this->get_current_view();
		$base_url     = admin_url( sprintf( 'edit.php?post_type=%s', $post_type ) );

		/*
		 * Add post status query arg to the 'all' link to differentiate from the
		 * default 'upcoming' view and manage the 'current' class.
		 */
		if ( preg_match( '/<a.*?>(?P<text>.+?)<\/a>/', $views['all'], $matches ) ) {
			$views['all'] = sprintf(
				'<a href="%s"%s>%s</a>',
				esc_url( add_query_arg( 'post_status', 'any', $base_url ) ),
				'any' === $current_view ? ' class="current"' : '',
				$matches['text']
			);
		}

		$sql = "SELECT COUNT( DISTINCT p.ID )
			FROM $wpdb->posts p
			INNER JOIN $wpdb->postmeta pm ON p.ID = pm.post_id
			WHERE
				p.post_type = 'audiotheme_gig' AND
				p.post_status NOT IN ( 'auto-draft', 'trash' ) AND
				pm.meta_key = '_audiotheme_gig_datetime'";

		$current_time   = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) - DAY_IN_SECONDS );
		$upcoming_count = $wpdb->get_var( $wpdb->prepare( $sql . ' AND pm.meta_value >= %s', $current_time ) );
		$past_count     = $wpdb->get_var( $wpdb->prepare( $sql . ' AND pm.meta_value < %s', $current_time ) );

		// @todo Translate the numbers?
		$new_views['upcoming'] = sprintf(
			'<a href="%s"%s>%s <span class="count">(%d)</span></a>',
			esc_url( $base_url ),
			'upcoming' === $current_view ? ' class="current"' : '',
			__( 'Upcoming', 'audiotheme' ),
			$upcoming_count
		);

		$new_views['past'] = sprintf(
			'<a href="%s"%s>%s <span class="count">(%d)</span></a>',
			esc_url( add_query_arg( array( 'gig_date' => current_time( 'mysql' ), 'compare' => rawurlencode( '<' ) ), $base_url ) ),
			'past' === $current_view ? ' class="current"' : '',
			__( 'Past', 'audiotheme' ),
			$past_count
		);

		return array_merge( $new_views, $views );
	}

	/**
	 * Display the months filter dropdown.
	 *
	 * @since 2.0.0
	 */
	public function display_months_filter() {
		global $wpdb, $wp_locale;

		$months = $wpdb->get_results(
			"SELECT
				DISTINCT YEAR( meta_value ) AS year,
				MONTH( meta_value ) AS month
			FROM
				$wpdb->posts p,
				$wpdb->postmeta pm
			WHERE
				p.post_type = 'audiotheme_gig' AND
				p.post_status NOT IN ( 'auto-draft' ) AND
				p.ID = pm.post_id AND
				pm.meta_key = '_audiotheme_gig_datetime'
			ORDER BY meta_value DESC"
		);

		$month_count = count( $months );

		if ( ! $month_count || ( 1 === $month_count && 0 === $months[0]->month ) ) {
			return;
		}

		$m = isset( $_GET['m'] ) ? (int) $_GET['m'] : 0;
		?>
		<select name="m">
			<option value="0" <?php selected( $m, 0 ); ?>><?php esc_html_e( 'All dates', 'audiotheme' ); ?></option>
			<?php
			foreach ( $months as $arc_row ) {
				if ( empty( $arc_row->year ) ) {
					continue;
				}

				$month = zeroise( $arc_row->month, 2 );
				$year  = $arc_row->year;

				printf(
					'<option value="%s"%s>%s</option>',
					esc_attr( $year . $month ),
					selected( $m, $year . $month, false ),
					esc_html( sprintf( '%s %s', $wp_locale->get_month( $month ), $year ) )
				);
			}
			?>
		</select>
		<?php
	}

	/**
	 * Display the venues filter dropdown.
	 *
	 * @since 2.0.0
	 */
	public function display_venues_filter() {
		global $wpdb;

		$venues = $wpdb->get_results(
			"SELECT p.ID, p.post_title
			FROM $wpdb->posts p
			INNER JOIN $wpdb->p2p p2p ON p.ID = p2p.p2p_from AND p2p.p2p_type = 'audiotheme_venue_to_gig'
			WHERE p.post_type = 'audiotheme_venue'
			GROUP BY p.ID
			ORDER BY p.post_title ASC"
		);
		?>
		<select name="venue">
			<option value=""><?php esc_html_e( 'All venues', 'audiotheme' ); ?></option>
			<?php
			if ( $venues ) {
				$selected = ! empty( $_REQUEST['venue'] ) ? absint( $_REQUEST['venue'] ) : '';
				foreach ( $venues as $venue ) {
					printf( '<option value="%s"%s>%s</option>',
						absint( $venue->ID ),
						selected( $selected, $venue->ID, false ),
						esc_html( $venue->post_title )
					);
				}
			}
			?>
		</select>
		<?php
	}

	/**
	 * Register list table columns.
	 *
	 * @since 2.0.0
	 *
	 * @param array $columns An array of the column names to display.
	 * @return array Filtered array of column names.
	 */
	public function register_columns( $columns ) {
		$title_column = $columns['title'];

		$columns['gig_date'] = esc_html_x( 'Date', 'column name', 'audiotheme' );

		// Move the title column after the date column.
		if ( version_compare( get_bloginfo( 'version' ), '4.3.0', '>=' ) ) {
			unset( $columns['title'] );
			$columns['title'] = $title_column;
		}

		$columns['venue'] = esc_html__( 'Venue', 'audiotheme' );
		unset( $columns['date'] );

		return $columns;
	}

	/**
	 * Register the primary column.
	 *
	 * @since 2.0.0
	 *
	 * @param  string $column_id Column name.
	 * @return string
	 */
	public function register_primary_column( $column_id ) {
		return 'gig_date';
	}

	/**
	 * Register sortable list table columns.
	 *
	 * @since 2.0.0
	 *
	 * @param array $columns Column query vars with their corresponding column id as the key.
	 * @return array
	 */
	public function register_sortable_columns( $columns ) {
		$columns['title']    = 'title';
		$columns['gig_date'] = array( 'gig_datetime', true );

		return $columns;
	}

	/**
	 * Register row actions.
	 *
	 * @since 2.1.0
	 */
	public function register_row_actions( $actions, $item ) {
		if ( ! current_user_can( 'publish_posts' ) ) {
			return $actions;
		}

		$base_url = admin_url( 'edit.php?post_type=audiotheme_gig' );

		$url = add_query_arg( array(
			'action'   => 'duplicate-gig',
			'post'     => $item->ID,
			'_wpnonce' => wp_create_nonce( 'duplicate-gig_' . $item->ID ),
		), $base_url );

		$actions['duplicate hide-if-no-js'] = sprintf(
			'<a href="%1$a" title="%2$s" data-nonce="%3$s">%4$s</a>',
			'#',
			esc_attr__( 'Duplicate this gig', 'audiotheme' ),
			wp_create_nonce( 'duplicate-gig_' . $item->ID ),
			esc_html__( 'Duplicate', 'audiotheme' )
		);

		return $actions;
	}

	/**
	 * Display custom list table columns.
	 *
	 * @since 2.0.0
	 *
	 * @param string $column_id The id of the column to display.
	 * @param int    $post_id Post ID.
	 */
	public function display_columns( $column_id, $post_id ) {
		$gig = get_audiotheme_gig( $post_id );

		switch ( $column_id ) {
			case 'gig_date' :
				$this->display_date_column( $gig );
				break;
			case 'venue' :
				if ( isset( $gig->venue ) ) {
					echo esc_html( $gig->venue->name );
				}
				break;
		}
	}

	/**
	 * Print Underscore.js templates.
	 *
	 * @since 2.1.0
	 */
	public function print_templates() {
		?>
		<script type="text/html" id="tmpl-audiotheme-modal">
			<div class="audiotheme-modal-header">
				<h1 class="audiotheme-modal-title">{{ data.title }}</h1>
				<button type="button" class="audiotheme-modal-close dashicons dashicons-no js-close"><span class="screen-reader-text"><?php esc_html_e( 'Close overlay', 'audiotheme' ); ?></span></button>
			</div>
			<div class="audiotheme-modal-content"></div>
			<div class="audiotheme-modal-footer"></div>
		</script>

		<script type="text/html" id="tmpl-audiotheme-duplicate-gig-form">
			<table>
				<tr>
					<th><label for="gig-date"><?php esc_html_e( 'Date', 'audiotheme' ) ?></label></th>
					<td>
						<div class="audiotheme-input-group">
							<input type="text" name="gig_date" id="gig-date" data-setting="date" value="{{ data.date }}" placeholder="YYYY/MM/DD" class="audiotheme-input-group-field ui-autocomplete-input">
							<label for="gig-date" id="gig-date-select" class="audiotheme-input-group-trigger dashicons dashicons-calendar"></label>
						</div>
					</td>
				</tr>
				<tr>
					<th><label for="gig-time"><?php esc_html_e( 'Time', 'audiotheme' ) ?></label></th>
					<td>
						<div class="audiotheme-input-group">
							<input type="text" name="gig_time" id="gig-time" data-setting="time" value="{{ data.time }}" placeholder="HH:MM" class="audiotheme-input-group-field ui-autocomplete-input">
							<label for="gig-time" id="gig-time-select" class="audiotheme-input-group-trigger dashicons dashicons-clock"></label>
						</div>
					</td>
				</tr>
			</table>
		</script>
		<?php
	}

	/**
	 * Display the date column value.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $gig Gig post object.
	 */
	protected function display_date_column( $gig ) {
		$title = esc_html__( '(no date)', 'audiotheme' );
		if ( ! empty( $gig->gig_datetime ) ) {
			$title = mysql2date( get_option( 'date_format' ), $gig->gig_datetime );
		}

		$time = esc_html__( 'TBD', 'audiotheme' );
		if ( ! empty( $gig->gig_time ) ) {
			$time = mysql2date( get_option( 'time_format' ), $gig->gig_datetime );
		}

		printf(
			'<a href="%1$s" class="row-title">%2$s</a> - %3$s',
			esc_url( get_edit_post_link( $gig->ID ) ),
			esc_html( $title ),
			esc_html( $time )
		);
	}

	/**
	 * Retrieve the current view.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_current_view() {
		$view = 'upcoming';

		if (
			( isset( $_REQUEST['gig_date'] ) && empty( $_REQUEST['m'] ) && ( empty( $_REQUEST['compare'] ) || false !== strpos( $_REQUEST['compare'], '>' ) ) ) ||
			( empty( $_REQUEST['post_status'] ) && empty( $_REQUEST['gig_date'] ) )
		) {
			$view = 'upcoming';
		} elseif (
			isset( $_REQUEST['gig_date'] ) &&
			isset( $_REQUEST['compare'] ) &&
			'<' === $_REQUEST['compare'] &&
			empty( $_REQUEST['m'] )
		) {
			$view = 'past';
		} elseif ( ! empty( $_REQUEST['post_status'] ) ) {
			$view = sanitize_text_field( $_REQUEST['post_status'] );
		}

		return $view;
	}
}
