/*global _audiothemeLicenseSettings:false, jQuery:false, wp:false */

(function( window, $, wp, undefined ) {
	'use strict';

	var $field   = $( '#audiotheme-license-key' ),
		$button  = $field.parent().find( '.button' ),
		$spinner = $field.parent().find( '.spinner' ),
		settings = _audiothemeLicenseSettings;

	$field.on( 'keyup', function() {
		if ( '' !== $field.val() ) {
			$button.attr( 'disabled', false );
		} else {
			$button.attr( 'disabled', true );
		}
	}).trigger( 'keyup' );

	$button.on( 'click', function( e ) {
		e.preventDefault();

		$spinner.addClass( 'is-active' );

		wp.ajax.post( 'audiotheme_ajax_activate_license', {
			license: $field.val(),
			nonce: settings.nonce
		}).done(function( data ) {
			var $response;

			data = data || {};

			if ( 'status' in data && 'ok' === data.status ) {
				$field.parent().find( '.audiotheme-response' ).remove();
				$button.hide().after( settings.activatedResponse );
			} else {
				$response = $field.parent().find( '.audiotheme-response' ).addClass( 'is-error' );

				if ( 'status' in data && data.status in settings.errorMessages ) {
					$response.html( settings.errorMessages[ data.status ] );
				} else {
					$response.html( settings.errorMessages.generic );
				}
			}

			$spinner.removeClass( 'is-active' );
		});
	});
})( window, jQuery, wp );
