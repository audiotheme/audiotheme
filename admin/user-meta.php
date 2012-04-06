<?php
add_filter( 'user_contactmethods', 'audiotheme_edit_user_contact_info' );

function audiotheme_edit_user_contact_info( $contactmethods ) {
	/* Remove contact options */
	unset( $contactmethods['aim'] );
	unset( $contactmethods['yim'] );
	unset( $contactmethods['jabber'] );
	
	/* Add Contact Options */
	$contactmethods['twitter'] = __( 'Twitter <span class="description">(username)</span>', 'audiotheme' );
	$contactmethods['facebook'] = __( 'Facebook  <span class="description">(link)</span>', 'audiotheme' );
	
	return $contactmethods;
}
?>