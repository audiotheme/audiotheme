<?php
/**
 * View to display the venue contact meta box.
 *
 * @package   AudioTheme\Gigs
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.9.0
 */
?>

<table class="form-table">
	<tr>
		<th><label for="venue-contact-name"><?php esc_html_e( 'Name', 'audiotheme' ) ?></label></th>
		<td><input type="text" name="audiotheme_venue[contact_name]" id="venue-contact-name" class="regular-text" value="<?php echo esc_attr( $venue->contact_name ); ?>"></td>
	</tr>
	<tr>
		<th><label for="venue-contact-phone"><?php esc_html_e( 'Phone', 'audiotheme' ) ?></label></th>
		<td><input type="text" name="audiotheme_venue[contact_phone]" id="venue-contact-phone" class="regular-text" value="<?php echo esc_attr( $venue->contact_phone ); ?>"></td>
	</tr>
	<tr>
		<th><label for="venue-contact-email"><?php esc_html_e( 'Email', 'audiotheme' ) ?></label></th>
		<td><input type="text" name="audiotheme_venue[contact_email]" id="venue-contact-email" class="regular-text" value="<?php echo esc_attr( $venue->contact_email ); ?>"></td>
	</tr>
</table>
