<div id="audiotheme-venue-details-panel" class="audiotheme-panel">
	<div class="audiotheme-panel-header">
		<h3 class="audiotheme-panel-title" style="padding: 0"><?php esc_html_e( 'Details', 'audiotheme' ); ?></h3>
	</div>
	<div class="audiotheme-panel-body">
		<?php wp_nonce_field( 'save-venue_' . $venue->ID, 'audiotheme_venue_nonce', true ); ?>

		<table class="form-table" >
			<tr>
				<th><label for="venue-address"><?php esc_html_e( 'Address', 'audiotheme' ) ?></label></th>
				<td><textarea name="audiotheme_venue[address]" id="venue-address" cols="30" rows="2"><?php echo esc_textarea( $venue->address ); ?></textarea></td>
			</tr>
			<tr>
				<th><label for="venue-city"><?php esc_html_e( 'City', 'audiotheme' ) ?></label></th>
				<td><input type="text" name="audiotheme_venue[city]" id="venue-city" class="regular-text" value="<?php echo esc_attr( $venue->city ); ?>"></td>
			</tr>
			<tr>
				<th><label for="venue-state"><?php esc_html_e( 'State', 'audiotheme' ) ?></label></th>
				<td><input type="text" name="audiotheme_venue[state]" id="venue-state" class="regular-text" value="<?php echo esc_attr( $venue->state ); ?>"></td>
			</tr>
			<tr>
				<th><label for="venue-postal-code"><?php esc_html_e( 'Postal Code', 'audiotheme' ) ?></label></th>
				<td><input type="text" name="audiotheme_venue[postal_code]" id="venue-postal-code" class="regular-text" value="<?php echo esc_attr( $venue->postal_code ); ?>"></td>
			</tr>
			<tr>
				<th><label for="venue-country"><?php esc_html_e( 'Country', 'audiotheme' ) ?></label></th>
				<td><input type="text" name="audiotheme_venue[country]" id="venue-country" class="regular-text" value="<?php echo esc_attr( $venue->country ); ?>"></td>
			</tr>
			<tr>
				<th><label for="venue-timezone-string"><?php esc_html_e( 'Time zone', 'audiotheme' ) ?></label></th>
				<td>
					<select id="venue-timezone-string" name="audiotheme_venue[timezone_string]">
						<?php echo audiotheme_timezone_choice( $venue->timezone_string ); ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="venue-website"><?php esc_html_e( 'Website', 'audiotheme' ) ?></label></th>
				<td><input type="text" name="audiotheme_venue[website]" id="venue-website" class="regular-text" value="<?php echo esc_url( $venue->website ); ?>"></td>
			</tr>
			<tr>
				<th><label for="venue-phone"><?php esc_html_e( 'Phone', 'audiotheme' ) ?></label></th>
				<td><input type="text" name="audiotheme_venue[phone]" id="venue-phone" class="regular-text" value="<?php echo esc_attr( $venue->phone ); ?>"></td>
			</tr>
		</table>
	</div>
</div>
