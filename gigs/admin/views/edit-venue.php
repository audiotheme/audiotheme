<div class="wrap columns-2" id="venue-edit">
	<div id="icon-audiotheme-venues" class="icon32"><br></div>
	<h2><?php
		if ( 'edit' == $action ) {
			echo $post_type_object->labels->edit_item;
			echo sprintf( ' <a class="add-new-h2" href="%s">%s</a>', esc_url( get_audiotheme_venue_admin_url() ), esc_html( $post_type_object->labels->add_new ) );
		} else {
			echo $post_type_object->labels->add_new_item;
		}
	?></h2>

	<?php
	if ( isset( $_REQUEST['message'] ) ) {
		$notices = array(); ?>
		<div id="message" class="updated">
			<p>
				<?php
				$messages = array(
					1 => __( 'Venue added.', 'audiotheme-i18n' ),
					2 => __( 'Venue updated.', 'audiotheme-i18n' ),
				);

				if ( ! empty( $_REQUEST['message'] ) && isset( $messages[ $_REQUEST['message'] ] ) ) {
					$notices[] = $messages[ $_REQUEST['message'] ];
				}

				if ( $notices ) {
					echo join( ' ', $notices );
				}

				unset( $notices );

				$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'message' ), $_SERVER['REQUEST_URI'] );
				?>
			</p>
		</div>
	<?php } ?>

	<form action="" method="post">
		<input type="hidden" name="page" value="audiotheme-venue">
		<input type="hidden" name="audiotheme_venue[ID]" id="venue-id" value="<?php echo esc_attr( $ID ); ?>">
		<?php
		echo $nonce_field;
		wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
		wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
		?>

		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">


				<div id="post-body-content">

					<div id="venuediv" class="stuffbox">
						<h3><?php echo $post_type_object->labels->singular_name; ?></h3>

						<div class="inside">
							<table class="form-table" >
								<tr>
									<th><label for="venue-name"><?php _e( 'Name', 'audiotheme-i18n' ) ?></label></th>
									<td><input type="text" name="audiotheme_venue[name]" id="venue-name" class="regular-text" value="<?php echo esc_attr( $name ); ?>"></td>
								</tr>
								<tr>
									<th><label for="venue-address"><?php _e( 'Address', 'audiotheme-i18n' ) ?></label></th>
									<td><textarea name="audiotheme_venue[address]" id="venue-address" cols="30" rows="2"><?php echo esc_textarea( $address ); ?></textarea></td>
								</tr>
								<tr>
									<th><label for="venue-city"><?php _e( 'City', 'audiotheme-i18n' ) ?></label></th>
									<td><input type="text" name="audiotheme_venue[city]" id="venue-city" class="regular-text" value="<?php echo esc_attr( $city ); ?>"></td>
								</tr>
								<tr>
									<th><label for="venue-state"><?php _e( 'State', 'audiotheme-i18n' ) ?></label></th>
									<td><input type="text" name="audiotheme_venue[state]" id="venue-state" class="regular-text" value="<?php echo esc_attr( $state ); ?>"></td>
								</tr>
								<tr>
									<th><label for="venue-postal-code"><?php _e( 'Postal Code', 'audiotheme-i18n' ) ?></label></th>
									<td><input type="text" name="audiotheme_venue[postal_code]" id="venue-postal-code" class="regular-text" value="<?php echo esc_attr( $postal_code ); ?>"></td>
								</tr>
								<tr>
									<th><label for="venue-country"><?php _e( 'Country', 'audiotheme-i18n' ) ?></label></th>
									<td><input type="text" name="audiotheme_venue[country]" id="venue-country" class="regular-text" value="<?php echo esc_attr( $country ); ?>"></td>
								</tr>
								<tr>
									<th><label for="venue-timezone-string"><?php _e( 'Time zone', 'audiotheme-i18n' ) ?></label></th>
									<td>
										<select id="venue-timezone-string" name="audiotheme_venue[timezone_string]">
											<?php echo audiotheme_timezone_choice( $timezone_string ); ?>
										</select>
										<span class="description"><?php _e( 'This is important.', 'audiotheme-i18n' ); ?></span>
									</td>
								</tr>
								<tr>
									<th><label for="venue-website"><?php _e( 'Website', 'audiotheme-i18n' ) ?></label></th>
									<td><input type="text" name="audiotheme_venue[website]" id="venue-website" class="regular-text" value="<?php echo esc_url( $website ); ?>"></td>
								</tr>
								<tr>
									<th><label for="venue-phone"><?php _e( 'Phone', 'audiotheme-i18n' ) ?></label></th>
									<td><input type="text" name="audiotheme_venue[phone]" id="venue-phone" class="regular-text" value="<?php echo esc_attr( $phone ); ?>"></td>
								</tr>
							</table>
						</div>
					</div>

					<?php do_meta_boxes( $screen->id, 'normal', '' ); ?>
				</div><!--end div#post-body-content-->


				<div id="postbox-container-1" class="postbox-container">
					<?php do_meta_boxes( $screen->id, 'side', get_post( $ID ) ); ?>
				</div>


			</div><!--end div#post-body-->
			<br class="clear" />
		</div><!--end div#poststuff-->
	</form>
</div><!--end div.wrap-->