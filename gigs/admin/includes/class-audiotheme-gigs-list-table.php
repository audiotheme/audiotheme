<?php
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class AudioTheme_Gigs_List_Table extends WP_List_Table {
	var $current_view;
	
	function __construct(){
		parent::__construct( array(
			'singular' => 'gig',
			'plural' => 'gigs',
			'ajax' => false
		) );
		
		if ( ( isset( $_REQUEST['gig_date'] ) && empty( $_REQUEST['m'] ) && ( empty( $_REQUEST['compare'] ) || false !== strpos( $_REQUEST['compare'], '>' ) ) ) || ( empty( $_REQUEST['post_status'] ) && empty( $_REQUEST['gig_date'] ) ) ) {
			$this->current_view = 'upcoming';
		} elseif ( isset( $_REQUEST['gig_date'] ) && isset( $_REQUEST['compare'] ) && '<' == $_REQUEST['compare'] && empty( $_REQUEST['m'] ) ) {
			$this->current_view = 'past';
		} elseif ( ! empty( $_REQUEST['post_status'] ) ) {
			$this->current_view = $_REQUEST['post_status'];
		}
		
		$this->is_trash = isset( $_REQUEST['post_status'] ) && $_REQUEST['post_status'] == 'trash';
	}
	
	function prepare_items() {
		global $wp_query;
		
		$screen = get_current_screen();
		
		#$user = wp_get_current_user();
		$per_page = get_user_option( 'toplevel_page_gigs_per_page' );
		$per_page = ( empty( $per_page ) ) ? 20 : $per_page;
		
		$columns = $this->get_columns();
		$hidden = get_hidden_columns( $screen->id );
		$sortable = $this->get_sortable_columns();
		
		$this->_column_headers = array( $columns, $hidden, $sortable );
		
		
		$args = array(
			'post_type' => 'audiotheme_gig',
			'order' => ( isset( $_REQUEST['order'] ) && 'asc' == strtolower( $_REQUEST['order'] ) ) ? 'asc' : 'desc',
			'post_status' => ( isset( $_REQUEST['post_status'] ) ) ? $_REQUEST['post_status'] : 'publish,draft',
			'posts_per_page' => $per_page
		);
		
		
		if ( empty( $_REQUEST['m'] ) && ( 'upcoming' == $this->current_view || 'past' == $this->current_view ) ) {
			$args['meta_query'][] = array(
				'key' => '_audiotheme_gig_datetime',
				'value' => ( isset( $_REQUEST['gig_date'] ) ) ? urldecode( $_REQUEST['gig_date'] ) : current_time( 'mysql' ),
				'compare' => ( isset( $_REQUEST['compare'] ) ) ? urldecode( $_REQUEST['compare'] ) : '>=',
				'type' => 'DATETIME'
			);
			
			// sort upcoming in ascending order
			$args['order'] = ( 'upcoming' == $this->current_view && ! isset( $_REQUEST['order'] ) ) ? 'asc' : $args['order'];
		} elseif ( ! empty( $_REQUEST['m'] ) ) {
			$m = absint( substr( $_REQUEST['m'], 4 ) );
			$y = absint( substr( $_REQUEST['m'], 0, 4 ) );
			
			$start = sprintf( '%s-%s-01 00:00:00', $y, zeroise( $m, 2 ) );
			$end = sprintf( '%s 23:59:59', date( 'Y-m-t', mktime( 0, 0, 0, $m, 1, $y ) ) );
			
			$args['meta_query'][] = array(
				'key' => '_audiotheme_gig_datetime',
				'value' => array( $start, $end ),
				'compare' => 'BETWEEN',
				'type' => 'DATETIME'
			);
			
			$args['order'] = ( isset( $_REQUEST['order'] ) ) ? $args['order'] : 'asc';
		}
		
		
		if ( ! empty( $_REQUEST['venue'] ) ) {
			$args['connected_type'] = 'audiotheme_venue_to_gig';
			$args['connected_items'] = absint( $_REQUEST['venue'] );
		}
		
		
		if ( isset( $_REQUEST['orderby'] ) ) {
			switch( $_REQUEST['orderby'] ) {
				case 'title':
					$args['orderby'] = 'title';
					break;
				case 'venue':
					// handled after query
					break;
				default:
					$args['meta_key'] = '_audiotheme_' . $_REQUEST['orderby'];
					$args['orderby'] = 'meta_value';
					break;
			}
		} else {
			$args['meta_key'] = '_audiotheme_gig_datetime';
			$args['orderby'] = 'meta_value';
		}
		
		if ( isset( $_REQUEST['s'] ) ) {
			$args['s'] = stripslashes( $_REQUEST['s'] );
		}
		
		$args['paged'] = $this->get_pagenum();
		
		
		$items = array();
		$wp_query = new WP_Query( $args );
		p2p_type( 'audiotheme_venue_to_gig' )->each_connected( $wp_query );
		
		if ( isset( $wp_query->posts ) && count( $wp_query->posts ) ) {
			foreach ( $wp_query->posts as $post ) {
				$items[ $post->ID ] = get_audiotheme_gig( $post->ID );
			}
			
			// Sort by venue
			if ( ! empty( $_GET['orderby'] ) && 'venue' == $_GET['orderby'] ) {
				$items = sort_objects( $items, array( 'venue', 'name' ), $args['order'], true, 'gig_datetime' );
			}
		}
		$this->items = $items;
		
		
		$this->set_pagination_args( array(
			'total_items' => $wp_query->found_posts,
			'per_page' => $per_page,
			'total_pages' => $wp_query->max_num_pages
		) );
	}
	
	function get_views() {
		global $wpdb;
		
		$post_type = 'audiotheme_gig';
		$post_type_object = get_post_type_object( $post_type );
		$avail_post_stati = get_available_post_statuses( $post_type );
		
		$base_url = 'admin.php?page=gigs';
		$status_links = array();
		$num_posts = wp_count_posts( $post_type, 'readable' );
		$allposts = '';

		$current_user_id = get_current_user_id();
		
		/*
		// TODO: could be useful in a multiple artist context (for a label)
		if ( $this->user_posts_count ) {
			if ( isset( $_GET['author'] ) && ( $_GET['author'] == $current_user_id ) )
				$class = ' class="current"';
			$status_links['mine'] = "<a href='edit.php?post_type=$post_type&author=$current_user_id'$class>" . sprintf( _nx( 'Mine <span class="count">(%s)</span>', 'Mine <span class="count">(%s)</span>', $this->user_posts_count, 'posts' ), number_format_i18n( $this->user_posts_count ) ) . '</a>';
			$allposts = '&all_posts=1';
		}
		*/

		$total_posts = array_sum( (array) $num_posts );
		
		// Subtract post types that are not included in the admin all list.
		foreach ( get_post_stati( array( 'show_in_admin_all_list' => false ) ) as $status )
			$total_posts -= $num_posts->$status;
		
		
		$class = ( 'upcoming' == $this->current_view ) ? ' class="current"' : '';
		$upcoming_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*)
			FROM $wpdb->posts p, $wpdb->postmeta pm
			WHERE p.post_type='audiotheme_gig' AND p.post_status!='auto-draft' AND p.ID=pm.post_id AND pm.meta_key='_audiotheme_gig_datetime' AND pm.meta_value>=%s",
			current_time( 'mysql' ) ) );
		$status_links['upcoming'] = sprintf( '<a href="%s"%s>%s <span class="count">(%d)</span></a>', $base_url, $class, 'Upcoming', $upcoming_count );
		
		$class = ( 'past' == $this->current_view ) ? ' class="current"' : '';
		$past_url = add_query_arg( array( 'gig_date' => current_time( 'mysql' ), 'compare' => '<' ), $base_url );
		$past_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*)
			FROM $wpdb->posts p, $wpdb->postmeta pm
			WHERE p.post_type='audiotheme_gig' AND p.post_status!='auto-draft' AND p.ID=pm.post_id AND pm.meta_key='_audiotheme_gig_datetime' AND pm.meta_value<%s",
			current_time( 'mysql' ) ) );
		$status_links['past'] = sprintf( '<a href="%s"%s>%s <span class="count">(%d)</span></a>', $past_url, $class, 'Past', $past_count );
		
		$class = ( 'any' == $this->current_view ) ? ' class="current"' : '';
		$all_url = add_query_arg( 'post_status', 'any', $base_url );
		$status_links['all'] = "<a href='$all_url{$allposts}'$class>" . sprintf( _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $total_posts, 'posts' ), number_format_i18n( $total_posts ) ) . '</a>';
		
		
		foreach ( get_post_stati ( array( 'show_in_admin_status_list' => true ), 'objects' ) as $status ) {
			$class = '';
			$status_name = $status->name;

			if ( ! in_array( $status_name, $avail_post_stati ) )
				continue;

			if ( empty( $num_posts->$status_name ) )
				continue;

			if ( $status_name == $this->current_view )
				$class = ' class="current"';
			
			$status_url = add_query_arg( array( 'post_status' => $status_name, 'post_type' => $post_type ), $base_url );
			$status_links[ $status_name ] = "<a href='$status_url'$class>" . sprintf( translate_nooped_plural( $status->label_count, $num_posts->$status_name ), number_format_i18n( $num_posts->$status_name ) ) . '</a>';
		}

		return $status_links;
	}
	
	function extra_tablenav( $which ) {
		global $wpdb;
		?>
		<div class="alignleft actions">
			<?php
			if ( 'top' == $which ) {
	
				$this->months_dropdown( 'audiotheme_gig' );
				
				
				$sql = $wpdb->prepare( "SELECT p.ID, p.post_title
					FROM $wpdb->posts p	
					INNER JOIN $wpdb->p2p p2p ON p.ID=p2p.p2p_from AND p2p.p2p_type='audiotheme_venue_to_gig'
					WHERE p.post_type='audiotheme_venue'
					GROUP BY p.ID
					ORDER BY p.post_title ASC" );
				$venues = $wpdb->get_results( $sql );
				?>
				<select name="venue">
					<option value="">Show all venues</option>
					<?php
					if ( $venues ) {
						$selected = ( ! empty( $_REQUEST['venue'] ) ) ? absint( $_REQUEST['venue'] ) : '';
						foreach ( $venues as $venue ) {
							printf( '<option value="%s"%s>%s</option>',
								$venue->ID,
								selected( $selected, $venue->ID, false ),
								esc_html( $venue->post_title )
							);
						}
					}
					?>
				</select>
				<?php
				
				submit_button( __( 'Filter', 'audiotheme-i18n' ), 'secondary', false, false, array( 'id' => 'post-query-submit' ) );
			}
			?>
		</div>
		<?php
	}
	
	function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox">',
			'title' => 'Date',
			'post_title' => 'Title',
			'venue' => 'Venue'
		);
		
		// the screen id is used when managing column visibility
		$columns = apply_filters( 'manage_toplevel_page_gigs_posts_columns', $columns );
		
		return $columns;
	}
	
	function get_sortable_columns() {
		$sortable_columns = array(
			'title' => array( 'gig_datetime', true ), // true means its already sorted
			'post_title' => array( 'title', false ),
			'venue' => array( 'venue', false )
		);
		
		return $sortable_columns;
	}
	
	function get_bulk_actions() {
		$actions = array();

		if ( $this->is_trash )
			$actions['untrash'] = __( 'Restore', 'audiotheme-i18n' );

		if ( $this->is_trash || ! EMPTY_TRASH_DAYS )
			$actions['delete'] = __( 'Delete Permanently', 'audiotheme-i18n' );
		else
			$actions['trash'] = __( 'Move to Trash', 'audiotheme-i18n' );

		return $actions;
	}
	
	/**
	 * @see /wp-admin/edit.php
	 */
	function process_actions() {
		global $wpdb;
	
		$action = '';
		$current_user = wp_get_current_user();
		$post_type_object = get_post_type_object( 'audiotheme_gig' );
		
		if ( ! empty( $_REQUEST['ids'] ) ) {
			$post_ids = ( is_array( $_REQUEST['ids'] ) ) ? $_REQUEST['ids'] : explode( ',', $_REQUEST['ids'] );
			$post_ids = array_map( 'absint', $post_ids );
			$action = $this->current_action();
		}
		
		$sendback = remove_query_arg( array( 'trashed', 'untrashed', 'deleted', 'ids' ), wp_get_referer() );
		if ( ! $sendback )
			$sendback = get_audiotheme_gig_admin_url();
		$sendback = add_query_arg( 'paged', $this->get_pagenum(), $sendback );
		
		
		if ( ! empty( $action ) ) {
			check_admin_referer( 'bulk-' . $this->_args['plural'] );
			
			switch( $action ) {
				case 'trash':
					$trashed = 0;
					foreach( (array) $post_ids as $post_id ) {
						if ( ! current_user_can( $post_type_object->cap->delete_post, $post_id ) )
							wp_die( __( 'You are not allowed to move this item to the Trash.', 'audiotheme-i18n' ) );
						
						if ( ! wp_trash_post( $post_id ) )
							wp_die( __( 'Error moving to Trash.', 'audiotheme-i18n' ) );
						$trashed++;
					}
					$sendback = add_query_arg( array( 'trashed' => $trashed, 'ids' => join( ',', $post_ids ) ), $sendback );
					break;
				case 'untrash':
					$untrashed = 0;
					foreach( (array) $post_ids as $post_id ) {
						if ( ! current_user_can( $post_type_object->cap->delete_post, $post_id ) )
							wp_die( __( 'You are not allowed to restore this item from the Trash.', 'audiotheme-i18n' ) );
		
						if ( ! wp_untrash_post( $post_id ) )
							wp_die( __( 'Error in restoring from Trash.', 'audiotheme-i18n' ) );
		
						$untrashed++;
					}
					$sendback = add_query_arg( 'untrashed', $untrashed, $sendback );
					break;
				
				case 'delete':
					$deleted = 0;
					foreach ( $post_ids as $post_id ) {
						if ( ! current_user_can( $post_type_object->cap->delete_post, $post_id ) )
							wp_die( __( 'You are not allowed to delete this item.', 'audiotheme-i18n' ) );
						
						if ( ! wp_delete_post( $post_id ) )
							wp_die( __( 'Error in deleting...', 'audiotheme-i18n' ) );
						$deleted++;
					}
					$sendback = add_query_arg( 'deleted', $deleted, $sendback );
				default:
					break;
			}
			
			$sendback = remove_query_arg( array( 'action', 'action2' ), $sendback );
			wp_redirect( $sendback );
			exit;
		}
		
		
		if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
			 wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), stripslashes( $_SERVER['REQUEST_URI'] ) ) );
			 exit;
		}
	}
	
	function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="ids[]" value="%s">', esc_attr( $item->ID ) );
	}
	
	function column_title( $item ) {
		$statuses = get_post_statuses();
		$status = ( 'publish' != $item->post_status && array_key_exists( $item->post_status, $statuses ) ) ? ' - <strong>' . esc_html( $statuses[ $item->post_status ] ) . '</strong>' : '';
		
		$date = ( empty( $item->gig_datetime ) ) ? __( '(no date)', 'audiotheme-i18n' ) : mysql2date( get_option( 'date_format' ), $item->gig_datetime );
		$out = sprintf( '<strong><a href="%1$s" class="row-title">%2$s</a></strong> - %3$s%4$s<br>', 
			esc_url( get_edit_post_link( $item->ID ) ),
			esc_html( $date ),
			esc_html( empty( $item->gig_time ) ? 'TBD' : $item->gig_time ),
			$status 
		);
		
		#$actions['edit'] = sprintf( '<a href="%s">Edit</a>', get_edit_post_link( $item->ID ) );
		
		
		$post_type_object = get_post_type_object( $item->post_type );
		$can_edit_post = current_user_can( $post_type_object->cap->edit_post, $item->ID );
		#$actions = array();
		if ( $can_edit_post && 'trash' != $item->post_status ) {
			$actions['edit'] = '<a href="' . get_edit_post_link( $item->ID, true ) . '" title="' . esc_attr( __( 'Edit this item' ) ) . '">' . __( 'Edit' ) . '</a>';
			#$actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="' . esc_attr( __( 'Edit this item inline' ) ) . '">' . __( 'Quick&nbsp;Edit' ) . '</a>';
		}
		
		if ( current_user_can( $post_type_object->cap->delete_post, $item->ID ) ) {
			#$onclick = " onclick=\"return confirm('" . esc_js( sprintf( __( 'Are you sure you want to delete this %s?', 'audiotheme-i18n' ), strtolower( $post_type_object->labels->singular_name ) ) ) . "');\"";
			#$actions['delete'] = "<a class='submitdelete' title='" . esc_attr( __( 'Delete this item permanently', 'audiotheme-i18n' ) ) . "' href='" . get_delete_post_link( $item->ID, '', true ) . "'$onclick>" . __( 'Delete Permanently', 'audiotheme-i18n' ) . "</a>";
			if ( 'trash' == $item->post_status )
				$actions['untrash'] = "<a title='" . esc_attr( __( 'Restore this item from the Trash', 'audiotheme-i18n' ) ) . "' href='" . wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $item->ID ) ), 'untrash-' . $item->post_type . '_' . $item->ID ) . "'>" . __( 'Restore', 'audiotheme-i18n' ) . "</a>";
			elseif ( EMPTY_TRASH_DAYS )
				$actions['trash'] = "<a class='submitdelete' title='" . esc_attr( __( 'Move this item to the Trash', 'audiotheme-i18n' ) ) . "' href='" . get_delete_post_link( $item->ID ) . "'>" . __( 'Trash', 'audiotheme-i18n' ) . "</a>";
			if ( 'trash' == $item->post_status || !EMPTY_TRASH_DAYS )
				$actions['delete'] = "<a class='submitdelete' title='" . esc_attr( __( 'Delete this item permanently', 'audiotheme-i18n' ) ) . "' href='" . get_delete_post_link( $item->ID, '', true ) . "'>" . __( 'Delete Permanently', 'audiotheme-i18n' ) . "</a>";
		}
		
		if ( $post_type_object->public ) {
			if ( in_array( $item->post_status, array( 'pending', 'draft', 'future' ) ) ) {
				if ( $can_edit_post )
					$actions['view'] = '<a href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $item->ID ) ) ) . '" title="' . esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;', 'audiotheme-i18n' ), $item->post_title ) ) . '" rel="permalink">' . __( 'Preview', 'audiotheme-i18n' ) . '</a>';
			} elseif ( 'trash' != $item->post_status ) {
				$actions['view'] = '<a href="' . get_permalink( $item->ID ) . '" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'audiotheme-i18n' ), $item->post_title ) ) . '" rel="permalink">' . __( 'View', 'audiotheme-i18n' ) . '</a>';
			}
		}
		
		$out.= $this->row_actions( $actions );
		
		return $out;
	}
	
	function column_default( $item, $column_name ) {
		switch($column_name){
			case 'gig_time':
				return mysql2date( get_option( 'time_format' ), $item->gig_datetime );
			case 'post_title':
				return $item->post_title;
			case 'venue':
				return ( isset( $item->venue->name ) ) ? $item->venue->name : '';
			default:
				return print_r( $item, true ); // show the whole array for troubleshooting purposes
		}
	}
	
	function column_venue( $item ) {
		$out = '';
		
		if ( ! empty( $item->venue ) ) {
			$out = sprintf( '<a href="%1$s">%2$s</a>',
				esc_url( get_edit_post_link( $item->venue->ID ) ),
				esc_html( $item->venue->name ) 
			);
		}
		
		return $out;
	}
	
	function months_dropdown( $post_type ) {
		global $wpdb, $wp_locale;
		
		$months = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT YEAR( meta_value ) AS year, MONTH( meta_value ) AS month
			FROM $wpdb->posts p, $wpdb->postmeta pm
			WHERE p.post_type='audiotheme_gig' AND p.post_status!='auto-draft' AND p.ID=pm.post_id AND pm.meta_key='_audiotheme_gig_datetime'
			ORDER BY meta_value DESC" ) );
		
		$month_count = count( $months );
		
		if ( ! $month_count || ( 1 == $month_count && 0 == $months[0]->month ) )
		return;
		
		$m = isset( $_GET['m'] ) ? (int) $_GET['m'] : 0;
		?>
		<select name="m">
			<option value="0" <?php selected( $m, 0 ); ?>><?php _e( 'Show all dates' ); ?></option>
			<?php
			foreach ( $months as $arc_row ) {
				if ( 0 == $arc_row->year )
					continue;
				
				$month = zeroise( $arc_row->month, 2 );
				$year = $arc_row->year;
				
				printf( "<option %s value='%s'>%s</option>\n",
					selected( $m, $year . $month, false ),
					esc_attr( $arc_row->year . $month ),
					$wp_locale->get_month( $month ) . " $year"
				);
			}
			?>
		</select>
		<?php
	}
}
?>