<div id="gig-ui">
	<table id="gig-fields">
		<tr>
			<th><label for="gig-date">Date</label></th>
			<td><input type="text" name="gig_date" id="gig-date" placeholder="MM/DD/YYY" value="<?php echo esc_attr( $gig_date ); ?>" autocomplete="off"></td>
		</tr>
		<tr>
			<th><label for="gig-time">Time</label></th>
			<td><input type="text" name="gig_time" id="gig-time" placeholder="HH:MM" value="<?php echo esc_attr( $gig_time ); ?>"></td>
		</tr>
		<tr>
			<th><label for="gig-venue">Venue</label></th>
			<?php // TODO: consider refactoring to use a dropdown for data integrity? ?>
			<td><input type="text" name="gig_venue" id="gig-venue" value="<?php echo esc_html( $gig_venue ); ?>"></td>
		</tr>
		<!--<tr>
			<th><label for="gig_price">Price</label></th>
			<td><input type="text" name="gig_price" value=""></td>
		</tr>
		<tr>
			<th><label for="gig-tickets-url">Tickets URL</label></th>
			<td><input type="text" name="gig_tickets_url" value="" class="widefat"></td>
		</tr>-->
		<tr>
			<th>Notes</th>
			<td><textarea name="excerpt" id="excerpt" cols="76" rows="3"></textarea></td>
		</tr>
	</table>
</div>

<?php
/*
TODO: set transient whenever a gig is saved so the date and time fields can be automatically adjusted
the date field will automatically show the next day
the time field will have the same value as the previously saved time
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
	//$('#gig-time').timepicker({ show24Hours: false, step: 15 });
	$('#gig-venue').autocomplete({
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
		minLength: 2
	});
});
</script>
<style type="text/css">
.wrap h2 { margin-bottom: 20px;}

#gig-date { }
#gig-ui { margin-bottom: 15px;}
#gig-ui input { padding: 3px 8px; font-size: 1.5em;}
#gig-ui input::-webkit-input-placeholder { padding: 3px 0;}
#gig-ui input:-moz-placeholder { }
#gig-ui .ui-datepicker-trigger { cursor: pointer; margin: 0 0 0 5px; vertical-align: text-bottom;}

#gig-fields { width: 100%; max-width: 600px;}
#gig-fields th { padding-right: 20px; width: 20%; font-size: 1.2em; font-weight: normal; text-align: left;}
</style>