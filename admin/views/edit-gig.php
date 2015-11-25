<?php
/**
 * View to display gig date, time, venue and notes fields.
 *
 * @package   AudioTheme\Gigs
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.9.0
 */

wp_nonce_field( 'save-gig_' . $post->ID, 'audiotheme_save_gig_nonce' );
?>

<?php
/*
if ( empty( $timezone_string ) && 'auto-draft' !== get_post_status() ) : ?>
	<div class="">
		This event doesn't have a time zone. Choose one now or <a href="">read more about the importance of time zones</a>:<br>

		<select name="audiotheme_venue[timezone_string]" id="gig-venue-timezone" data-setting="timezone">
			<?php echo audiotheme_timezone_choice( $timezone_string ); ?>
		</select>
	</div>
<?php endif;
*/
?>

<div class="audiotheme-gig-editor">

	<div class="audiotheme-gig-editor-primary">
		<div class="audiotheme-gig-date-picker audiotheme-gig-date-picker-start">
			<div id="audiotheme-gig-start-date-picker"></div>
			<div class="audiotheme-gig-date-picker-footer">
				<input type="text" name="gig_date" id="gig-date" value="<?php echo esc_attr( $gig_date ); ?>" placeholder="YYYY-MM-DD" autocomplete="off">
			</div>
		</div>
	</div>

	<div class="audiotheme-gig-editor-secondary">

		<div class="audiotheme-panel">
			<div class="audiotheme-panel-header">
				<h4 class="audiotheme-panel-title"><?php esc_html_e( 'Time', 'audiotheme' ); ?></h4>
			</div>
			<div class="audiotheme-panel-body">
				<div class="audiotheme-gig-time-picker audiotheme-input-group">
					<input type="text" name="gig_time" id="gig-time" value="<?php echo esc_attr( $gig_time ); ?>" placeholder="HH:MM" class="audiotheme-input-group-field ui-autocomplete-input">
					<label for="gig-time" id="gig-time-select" class="audiotheme-input-group-trigger dashicons dashicons-clock"></label>
				</div>
			</div>
		</div>

		<div id="audiotheme-gig-venue-meta-box" class="audiotheme-panel">
			<div class="audiotheme-panel-header">
				<h4 class="audiotheme-panel-title"><?php esc_html_e( 'Venue', 'audiotheme' ); ?></h4>
			</div>
			<div class="audiotheme-panel-body">
				<input type="hidden" name="gig_venue_id" id="gig-venue-id" value="<?php echo absint( $venue_id ); ?>">
			</div>
		</div>

	</div>

	<div id="audiotheme-gig-note-meta-box" class="audiotheme-panel">
		<div class="audiotheme-panel-header">
			<h4 class="audiotheme-panel-title"><?php esc_html_e( 'Note', 'audiotheme' ) ?></h4>
		</div>
		<div class="audiotheme-panel-body">
			<textarea name="excerpt" id="excerpt" cols="76" rows="3"><?php echo $post->post_excerpt; ?></textarea>
			<span class="description"><?php esc_html_e( 'A description of the gig to display within the list of gigs. Who is the opening act, special guests, etc? Keep it short.', 'audiotheme' ); ?></span>
		</div>
	</div>

</div>
