<div class="wrap">
	<div id="icon-audiotheme-venues" class="icon32"><br></div>
	<h2>
		<?php
		echo $post_type_object->labels->name;

		if ( current_user_can( $post_type_object->cap->create_posts ) ) {
			printf( ' <a class="add-new-h2" href="%s">%s</a>', esc_url( get_audiotheme_venue_admin_url() ), esc_html( $post_type_object->labels->add_new ) );
		}

		if ( ! empty( $_REQUEST['s'] ) ) {
			printf( ' <span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;', 'audiotheme-i18n' ) . '</span>', get_search_query() );
		}
		?>
	</h2>

	<?php
	if ( isset( $_REQUEST['deleted'] ) || isset( $_REQUEST['message'] ) || isset( $_REQUEST['updated'] ) ) {
		$notices = array();
		?>
		<div id="message" class="updated">
			<p>
				<?php
				$messages = array(
					1 => __( 'Venue added.', 'audiotheme-i18n' )
				);

				if ( ! empty( $_REQUEST['message'] ) && isset( $messages[ $_REQUEST['message'] ] ) ) {
					$notices[] = $messages[ $_GET['message'] ];
				}

				if ( isset( $_REQUEST['updated'] ) && (int) $_REQUEST['updated'] ) {
					$notices[] = sprintf( _n( '%s venue updated.', '%s venues updated.', $_REQUEST['updated'] ), number_format_i18n( $_REQUEST['updated'] ) );
					unset( $_REQUEST['updated'] );
				}

				if ( isset( $_REQUEST['deleted'] ) && (int) $_REQUEST['deleted'] ) {
					$notices[] = sprintf( _n( 'Venue permanently deleted.', '%s venues permanently deleted.', $_REQUEST['deleted'] ), number_format_i18n( $_REQUEST['deleted'] ) );
					unset( $_REQUEST['deleted'] );
				}

				if ( $notices )
					echo join( ' ', $notices );
				unset( $notices );

				$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'deleted', 'message', 'updated' ), $_SERVER['REQUEST_URI'] );
				?>
			</p>
		</div>
	<?php } ?>

	<form action="" method="get">
		<input type="hidden" name="page" value="audiotheme-venues">
		<?php $venues_list_table->search_box( $post_type_object->labels->search_items, $post_type_object->name ); ?>

		<?php $venues_list_table->display(); ?>
	</form>

</div><!--end div.wrap-->