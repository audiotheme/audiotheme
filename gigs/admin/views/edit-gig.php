<div id="gig-ui">
	<table id="gig-fields">
		<tr>
			<th><label for="gig-date"><?php _e( 'Date', 'audiotheme-i18n' ) ?></label></th>
			<td><input type="text" name="gig_date" id="gig-date" placeholder="MM/DD/YYY" value="<?php echo esc_attr( $gig_date ); ?>" autocomplete="off"></td>
		</tr>
		<tr>
			<th><label for="gig-time"><?php _e( 'Time', 'audiotheme-i18n' ) ?></label></th>
			<td>
				<input type="text" name="gig_time" id="gig-time" placeholder="HH:MM" value="<?php echo esc_attr( $gig_time ); ?>" style="vertical-align: middle">
				<label for="gig-time" id="gig-time-select"><img src="<?php echo AUDIOTHEME_URI; ?>/admin/images/clock.png" width="16" height="16" style="vertical-align: middle"></label>
			</td>
		</tr>
		<tr>
			<th><label for="gig-venue"><?php _e( 'Venue', 'audiotheme-i18n' ) ?></label></th>
			<?php // TODO: consider refactoring to use a dropdown for data integrity? ?>
			<td>
				<input type="text" name="gig_venue" id="gig-venue" value="<?php echo esc_html( $gig_venue ); ?>">
				<label for="gig-venue" id="gig-venue-select">Select</label>
				<select name="audiotheme_venue[timezone_string]" id="gig-venue-timezone">
					<?php
					$tzstring = ( empty( $timezone_string ) ) ? get_option( 'timezone_string' ) : $timezone_string;
					echo wp_timezone_choice( $tzstring );
					?>
				</select>
			</td>
		</tr>
		<tr>
			<th><?php _e( 'Note', 'audiotheme-i18n' ) ?></th>
			<td>
				<textarea name="excerpt" id="excerpt" cols="76" rows="3"></textarea><br>
				<span class="description">A description of the gig to display within the list of gigs. Who's the opening act, special guests, etc? Keep it short.</span>
			</td>
		</tr>
	</table>
</div>

<?php
/**
 * TODO: set transient whenever a gig is saved so the date and time fields can be automatically adjusted.
 * The date field will automatically show the next day
 * The time field will have the same value as the previously saved time
 */
?>
<script type="text/javascript">
jQuery('#gig-ui').insertBefore('#postdivrich');

jQuery(function($) {
	$('#gig-date').datepicker({ showOn: 'both', buttonImage: '<?php echo AUDIOTHEME_URI . 'admin/images/calendar.png'; ?>', buttonImageOnly: true });
	$('#gig-time').timepicker({ 'timeFormat': '<?php echo get_option( 'time_format' ); ?>' }).on('focus', function() {
		var $this = $(this);
		
		$this.next('.ui-timepicker-list').width( $this.outerWidth() );
	});
	$('#gig-time-select').on('click', function(e) {
		$('#gig-time').focus();
	});
	//$('#gig-time').timepicker({ show24Hours: false, step: 15 });
	
	$('#gig-venue').autocomplete({
		change: function() {
			var $this = $(this);
		
			if ('' != $this.val()) {
				$.ajax({
					url: ajaxurl,
					data: {
						action: 'ajax_is_new_audiotheme_venue',
						name: $this.val()
					},
					dataType: 'JSON',
					success: function( data ) {
						if ( data.length ) {
							$('#gig-venue-timezone').hide();
						} else {
							$('#gig-venue-timezone').show();
						}
					}
				});
			} else {
				$('#gig-venue-timezone').hide();
			}
		},
		select: function() {
			$('#gig-venue-timezone').hide();
		},
		source: function( request, response ) {
			$.ajax({
				url: ajaxurl,
				data: {
					action: 'ajax_get_audiotheme_venue_matches',
					name: request.term
				},
				dataType: 'JSON',
				success: function( data ) { response( data ); }
			});
		},
		minLength: 0
	});
	
	$('#gig-venue-select').on('click', function(e) {
		e.preventDefault();
		$('#gig-venue').focus().autocomplete('search','');
	});
});
</script>
<style type="text/css">
.wrap h2 { margin-bottom: 20px;}

#gig-date { }
#gig-ui { margin-bottom: 15px;}
#gig-ui input { padding: 3px 8px; font-size: 1.5em; vertical-align: middle;}
#gig-ui input::-webkit-input-placeholder { padding: 3px 0;}
#gig-ui input:-moz-placeholder { }
#gig-ui select { padding: 5px 5px 5px 8px; font-size: 1.2em;}
#gig-ui .ui-datepicker-trigger { cursor: pointer; margin: 0 0 0 5px; vertical-align: middle;}

#gig-venue-timezone { display: none;}

#gig-fields { width: 100%; max-width: 600px;}
#gig-fields th { padding-right: 20px; width: 20%; font-size: 1.2em; font-weight: normal; text-align: left;}
</style>