<div class="wrap">
	<div id="icon-audiotheme-gigs" class="icon32"><br></div>
	<h1>
		<?php
		echo $post_type_object->labels->name;

		if ( current_user_can( $post_type_object->cap->create_posts ) ) {
			printf(
				' <a href="post-new.php?post_type=audiotheme_gig" class="page-title-action add-new-h2">%s</a>',
				esc_html( $post_type_object->labels->add_new )
			);
		}

		if ( ! empty( $_REQUEST['s'] ) ) {
			printf(
				' <span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;', 'audiotheme' ) . '</span>',
				esc_html( get_search_query() )
			);
		}
		?>
	</h1>

	<?php
	if ( isset( $_REQUEST['locked'] ) || isset( $_REQUEST['skipped'] ) || isset( $_REQUEST['updated'] ) || isset( $_REQUEST['deleted'] ) || isset( $_REQUEST['trashed'] ) || isset( $_REQUEST['untrashed'] ) ) {
		$messages = array(); ?>
		<div id="message" class="updated">
			<p>
				<?php
				if ( isset( $_REQUEST['updated'] ) && (int) $_REQUEST['updated'] ) {
					$messages[] = sprintf( _n( '%s post updated.', '%s posts updated.', $_REQUEST['updated'] ), number_format_i18n( $_REQUEST['updated'] ) );
					unset( $_REQUEST['updated'] );
				}

				if ( isset( $_REQUEST['skipped'] ) && (int) $_REQUEST['skipped'] ) {
					unset( $_REQUEST['skipped'] ); }

				if ( isset( $_REQUEST['locked'] ) && (int) $_REQUEST['locked'] ) {
					$messages[] = sprintf( _n( '%s item not updated, somebody is editing it.', '%s items not updated, somebody is editing them.', $_REQUEST['locked'] ), number_format_i18n( $_REQUEST['locked'] ) );
					unset( $_REQUEST['locked'] );
				}

				if ( isset( $_REQUEST['deleted'] ) && (int) $_REQUEST['deleted'] ) {
					$messages[] = sprintf( _n( 'Item permanently deleted.', '%s items permanently deleted.', $_REQUEST['deleted'] ), number_format_i18n( $_REQUEST['deleted'] ) );
					unset( $_REQUEST['deleted'] );
				}

				if ( isset( $_REQUEST['trashed'] ) && (int) $_REQUEST['trashed'] ) {
					$messages[] = sprintf( _n( 'Item moved to the Trash.', '%s items moved to the Trash.', $_REQUEST['trashed'] ), number_format_i18n( $_REQUEST['trashed'] ) );
					$ids = isset( $_REQUEST['ids'] ) ? $_REQUEST['ids'] : 0;
					$messages[] = '<a href="' . esc_url( wp_nonce_url( get_audiotheme_gig_admin_url( "action=untrash&ids=$ids" ), 'bulk-gigs' ) ) . '">' . __( 'Undo', 'audiotheme' ) . '</a>';
					unset( $_REQUEST['trashed'] );
				}

				if ( isset( $_REQUEST['untrashed'] ) && (int) $_REQUEST['untrashed'] ) {
					$messages[] = sprintf( _n( 'Item restored from the Trash.', '%s items restored from the Trash.', $_REQUEST['untrashed'] ), number_format_i18n( $_REQUEST['untrashed'] ) );
					unset( $_REQUEST['undeleted'] );
				}

				if ( $messages ) {
					echo join( ' ', $messages );
				}

				unset( $messages );

				$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'locked', 'skipped', 'updated', 'deleted', 'trashed', 'untrashed' ), $_SERVER['REQUEST_URI'] );
				?>
			</p>
		</div>
	<?php } ?>

	<?php $gigs_list_table->views(); ?>

	<form action="" method="get">
		<?php $gigs_list_table->search_box( $post_type_object->labels->search_items, $post_type_object->name ); ?>

		<input type="hidden" name="page" value="audiotheme-gigs">
		<input type="hidden" name="post_status" value="<?php echo ! empty( $_REQUEST['post_status'] ) ? esc_attr( $_REQUEST['post_status'] ) : 'any'; ?>">
		<input type="hidden" name="post_type" value="<?php echo $post_type_object->name; ?>">

		<?php if ( 'upcoming' === $gigs_list_table->current_view || 'past' === $gigs_list_table->current_view ) : ?>
			<input type="hidden" name="gig_date" value="<?php echo esc_attr( current_time( 'mysql' ) ); ?>">
		<?php endif; ?>

		<?php if ( isset( $_REQUEST['compare'] ) ) : ?>
			<input type="hidden" name="compare" value="<?php echo esc_attr( $_REQUEST['compare'] ); ?>">
		<?php endif; ?>

		<?php $gigs_list_table->display(); ?>
	</form>
</div><!--end div.wrap-->
