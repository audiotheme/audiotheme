/*global jQuery:false */

(function( $, window, undefined ) {
	'use strict';

	jQuery(function( $ ) {
		var errors = $( 'div.audiotheme-settings-error' ),
			$navTabs = $( '.nav-tab-wrapper .nav-tab' ),
			$tabPanels = $( '.tab-panel' ),
			$refererField = $( 'input[name="_wp_http_referer"]' ),
			updateTabs;

		// Initialize color fields.
		$( '.audiotheme-settings-color' ).each(function() {
			$( this ).wpColorPicker({ palettes: false });
		});

		// Hide hidden setting rows.
		$( '.audiotheme-settings-hidden-field' ).closest( 'tr' ).hide();

		updateTabs = function() {
			var href,
				hash = window.location.hash;

			$navTabs.removeClass( 'nav-tab-active' ).filter( '[href="' + hash + '"]' ).addClass( 'nav-tab-active' );
			$tabPanels.removeClass( 'tab-panel-active' ).filter( hash ).addClass( 'tab-panel-active' ).trigger( 'showTabPanel' );

			if ( $navTabs.filter( '.nav-tab-active' ).length < 1 ) {
				href = $navTabs.eq( 0 ).addClass( 'nav-tab-active' ).attr( 'href' );
				$tabPanels.removeClass( 'tab-panel-active' ).filter( href ).addClass( 'tab-panel-active' );
			}

			// Makes wp-admin/options.php redirect to the appropriate tab.
			if ( -1 === $refererField.val().indexOf( '#' ) ) {
				$refererField.val( $refererField.val() + hash );
			} else {
				$refererField.val( $refererField.val().replace( /#.*/, hash ) );
			}
		};

		updateTabs();
		$( window ).on( 'hashchange', updateTabs );

		if ( errors.length ) {
			errors.each(function() {
				var $self = $( this ),
					field = $( '#' + $self.data( 'field-id' ) ),
					tabPanel = field.closest( 'div.tab-panel' );

				// Add 'audiotheme-settings-error' class to the field container.
				field.closest( 'tr' ).addClass( 'audiotheme-settings-error' );

				// Add 'audiotheme-settings-tab-has-error' class to tabs with errors.
				$navTabs.filter( 'a.nav-tab[href="#' + tabPanel.attr( 'id' ) + '"]' ).addClass( 'audiotheme-settings-tab-has-error' );

				// Prepend errors to the tab panel containing the field.
				$self.prependTo( tabPanel );
			});
		}

		// Scroll back to the top when a tab panel is reloaded or submitted.
		setTimeout(function() {
			if ( location.hash ) {
				window.scrollTo( 0, 1 );
			}
		}, 1 );
	});

})( jQuery, window );
