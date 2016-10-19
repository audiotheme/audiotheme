<?php
/**
 * View to display the venue contact meta box.
 *
 * @package   AudioTheme\Gigs
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */
?>

<p>
	<?php esc_html_e( "Notes and contact information below are for personal use and won't be displayed on the front end.", 'audiotheme' ); ?>
</p>

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
	<tr>
		<th><label for=""><?php esc_html_e( 'Notes', 'audiotheme' ); ?></label></th>
		<td>
			<?php
			wp_editor( $notes, 'venuenotes', array(
				'editor_css'    => '<style type="text/css" scoped="true">.mceIframeContainer { background-color: #fff;}</style>',
				'media_buttons' => false,
				'textarea_name' => 'audiotheme_venue[notes]',
				'textarea_rows' => 6,
				'teeny'         => true,
			) );
			?>
		</td>
	</tr>
</table>
