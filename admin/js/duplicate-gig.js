/*global _, _audiothemeDuplicateGigSettings, Backbone, jQuery, _pikadayL10n, isRtl, Pikaday, wp */

(function( window, $, _, Backbone, wp, undefined ) {
	'use strict';

	var formView, duplicateGigButton, modal, model,
		app = {},
		settings = _audiothemeDuplicateGigSettings;

	_.extend( app, { controller: {}, collection: {}, model: {}, view: {} } );

	/**
	 * ========================================================================
	 * CONTROLLERS
	 * ========================================================================
	 */

	app.controller.ModalState = Backbone.Model.extend({
		defaults: {
			status: 'closed'
		},

		close: function() {
			this.set( 'status', 'closed' );
		},

		open: function() {
			this.set( 'status', 'open' );
		}
	});

	/**
	 * ========================================================================
	 * VIEWS
	 * ========================================================================
	 */

	app.view.Modal = wp.Backbone.View.extend({
		className: 'audiotheme-modal wp-core-ui',
		tagName: 'div',
		template: wp.template( 'audiotheme-modal' ),

		events: {
			'click .js-close': 'handleCloseButton',
			'keyup': 'routeKey'
		},

		initialize: function( options ) {
			this.contentView = options.contentView;
			this.controller = options.controller || null;
			this.footerView = options.footerView || null;

			this.listenTo( this.controller, 'change:status', this.toggleVisibility );
			this.$backdrop = $();
		},

		render: function() {
			var view = this;

			this.$body = $( 'body' );
			this.$html = $( 'html' );

			this.$el
				.empty()
				.html( this.template( this.controller.toJSON() ) )
				.appendTo( 'body' );

			this.views.set( '.audiotheme-modal-content', this.contentView );

			if ( this.footerView ) {
				this.views.set( '.audiotheme-modal-footer', this.footerView );
			}

			if ( ! this.$backdrop.length ) {
				this.$backdrop = this.$el
					.after( '<div class="audiotheme-modal-backdrop" />' )
					.next()
					.on( 'click', function( e ) {
						view.controller.close();
					});
			}

			return this;
		},

		close: function() {
			this.$el.hide();
			this.$backdrop.hide();
			this.$body.removeClass( 'modal-open' );
			this.$html.css( 'overflow', '' );
		},

		open: function() {
			this.$el.show();
			this.$backdrop.show();
			this.$body.addClass( 'modal-open' );
			this.$html.css( 'overflow', 'hidden' );
		},

		handleCloseButton: function( e ) {
			e.preventDefault();
			this.controller.close();
		},

		routeKey: function( e ) {
			// Escape
			if ( 27 === e.keyCode ) {
				this.controller.close();
			}
		},

		toggleVisibility: function() {
			if ( 'open' === this.controller.get( 'status' ) ) {
				this.open();
			} else {
				this.close();
			}
		}
	});

	app.view.DuplicateGigForm = wp.Backbone.View.extend({
		tagName: 'div',
		className: 'audiotheme-duplicate-gig-form',
		template: wp.template( 'audiotheme-duplicate-gig-form' ),

		events: {
			'change [data-setting]': 'updateAttribute'
		},

		initialize: function( options ) {
			this.model = options.model;

			this.listenTo( this.model, 'change', this.render );
		},

		render: function() {
			var $time;

			this.$el.html( this.template( this.model.toJSON() ) );
			$time = this.$( '#gig-time' );

			// Initialize the date picker.
			new Pikaday({
				field: this.$( '#gig-date' )[0],
				format: 'YYYY/MM/DD',
				i18n: _pikadayL10n || {},
				isRTL: isRtl,
				theme: 'audiotheme-pikaday audiotheme-pikaday-dropdown'
			});

			// Initialize the time picker.
			$time.timepicker({
				'timeFormat': settings.timeFormat,
				'className': 'ui-autocomplete'
			}).on( 'showTimepicker', function() {
				$( this ).addClass( 'open' );
				$( '.ui-timepicker-list' ).width( $( this ).outerWidth() );
			}) .on( 'hideTimepicker', function() {
				$( this ).removeClass( 'open' );
			}) .next().on( 'click', function() {
				$time.focus();
			});

			return this;
		},

		/**
		 * Update a model attribute when a field is changed.
		 *
		 * Fields with a 'data-setting="{{key}}"' attribute whose value
		 * corresponds to a model attribute will be automatically synced.
		 *
		 * @param {Object} e Event object.
		 */
		updateAttribute: function( e ) {
			var $target = $( e.target ),
				attribute = $target.data( 'setting' ),
				value = e.target.value;

			this.model.set( attribute, value );
		}
	});

	app.view.DuplicateGigButton = wp.Backbone.View.extend({
		tagName: 'div',

		events: {
			'click button': 'handleClick'
		},

		initialize: function( options ) {
			this.modal = options.modal;
			this.model = options.model;
		},

		render: function() {
			this.$spinner = this.$el.append( '<span class="spinner"></span>' ).find( '.spinner' );
			this.$button = this.$el.append( '<button class="button button-primary">Duplicate</button>' ).find( 'button' ).text( settings.l10n.duplicate );

			return this;
		},

		handleClick: function( e ) {
			var view = this;

			e.preventDefault();

			this.$button.attr( 'disabled', true );
			this.$spinner.addClass( 'is-active' );

			wp.ajax.post( 'audiotheme_ajax_duplicate_gig', {
				post_id: view.model.get( 'id' ),
				date: view.model.get( 'date' ),
				time: view.model.get( 'time' ),
				_wpnonce: view.model.get( 'duplicateNonce' )
			}).done(function() {
				window.location.reload();
			});
		}
	});

	/**
	 * ========================================================================
	 * SETUP
	 * ========================================================================
	 */

	modal = new app.controller.ModalState({ title: settings.l10n.duplicateModalTitle });
	model = new Backbone.Model();

	formView = new app.view.DuplicateGigForm({
		model: model
	});

	duplicateGigButton = new app.view.DuplicateGigButton({
		modal: modal,
		model: model
	});

	new app.view.Modal({
		contentView: formView,
		controller: modal,
		footerView: duplicateGigButton
	}).render();

	// Clear the form when the modal is closed.
	modal.on( 'change:status', function() {
		if ( 'closed' === this.get( 'status' ) ) {
			model.clear();
		}
	});

	$( '.row-actions' ).on( 'click', '.duplicate a', function( e ) {
		var $this = $( this ),
			postId = parseInt( $this.closest( 'tr' ).attr( 'id' ).replace( 'post-', '' ), 10 );

		e.preventDefault();

		wp.ajax.post( 'audiotheme_ajax_get_gig_data', {
			post_id: postId
		}).done(function( response ) {
			model.set( response );
			model.set( 'duplicateNonce', $this.data( 'nonce' ), { silent: true });
			modal.open();
		});
	});

})( window, jQuery, _, Backbone, wp );
