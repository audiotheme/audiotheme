<table class="form-table">
	<tr>
		<th><label for="venue-contact-name"><?php _e( 'Name', 'audiotheme' ) ?></label></th>
		<td><input type="text" name="audiotheme_venue[contact_name]" id="venue-contact-name" class="regular-text" value="<?php echo esc_attr( $contact_name ); ?>"></td>
	</tr>
	<tr>
		<th><label for="venue-contact-phone"><?php _e( 'Phone', 'audiotheme' ) ?></label></th>
		<td><input type="text" name="audiotheme_venue[contact_phone]" id="venue-contact-phone" class="regular-text" value="<?php echo esc_attr( $contact_phone ); ?>"></td>
	</tr>
	<tr>
		<th><label for="venue-contact-email"><?php _e( 'Email', 'audiotheme' ) ?></label></th>
		<td><input type="text" name="audiotheme_venue[contact_email]" id="venue-contact-email" class="regular-text" value="<?php echo esc_attr( $contact_email ); ?>"></td>
	</tr>
</table>