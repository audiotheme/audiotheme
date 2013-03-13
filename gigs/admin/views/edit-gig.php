<?php
/**
 * @todo Set a transient whenever a gig is saved so the date and time fields can be automatically adjusted.
 * The date field will automatically show the next day
 * The time field will have the same value as the previously saved time
 */
?>
<div id="gig-ui" class="audiotheme-edit-after-title">
	<?php wp_nonce_field( 'save-gig_' . $post->ID, 'audiotheme_save_gig_nonce' ); ?>
	<table id="gig-fields">
		<tr>
			<th><label for="gig-date"><?php _e( 'Date', 'audiotheme-i18n' ) ?></label></th>
			<td>
				<div class="audiotheme-input-append">
					<input type="text" name="gig_date" id="gig-date" value="<?php echo esc_attr( $gig_date ); ?>" placeholder="MM/DD/YYY" autocomplete="off">
				</div>
			</td>
		</tr>
		<tr>
			<th><label for="gig-time"><?php _e( 'Time', 'audiotheme-i18n' ) ?></label></th>
			<td>
				<div class="audiotheme-input-append">
					<input type="text" name="gig_time" id="gig-time" value="<?php echo esc_attr( $gig_time ); ?>" placeholder="HH:MM" class="ui-autocomplete-input"><label for="gig-time" id="gig-time-select" class="audiotheme-input-append-trigger"><img src="<?php echo AUDIOTHEME_URI; ?>/admin/images/clock.png" width="12" height="12"></label>
				</div>
			</td>
		</tr>
		<tr>
			<th><label for="gig-venue"><?php _e( 'Venue', 'audiotheme-i18n' ) ?></label></th>
			<td>
				<div class="audiotheme-input-append">
					<input type="text" name="gig_venue" id="gig-venue" value="<?php echo esc_html( $gig_venue ); ?>"><label for="gig-venue" id="gig-venue-select" class="audiotheme-input-append-trigger"><img src="<?php echo AUDIOTHEME_URI; ?>/admin/images/arrow-down.png" width="12" height="12" title="<?php esc_attr_e( 'Select Venue', 'audiotheme-i18n' ); ?>" alt="<?php esc_attr_e( 'Select Venue', 'audiotheme-i18n' ); ?>"></label>
				</div>

				<select name="audiotheme_venue[timezone_string]" id="gig-venue-timezone">
					<?php echo audiotheme_timezone_choice( $timezone_string ); ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><?php _e( 'Note', 'audiotheme-i18n' ) ?></th>
			<td>
				<textarea name="excerpt" id="excerpt" cols="76" rows="3"><?php echo esc_textarea( $post->post_excerpt ); ?></textarea><br>
				<span class="description">A description of the gig to display within the list of gigs. Who's the opening act, special guests, etc? Keep it short.</span>
			</td>
		</tr>
	</table>
</div>