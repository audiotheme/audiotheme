/*global _:false, _audiothemeDashboardSettings:false, Backbone:false, wp:false */

(function( window, $, _, Backbone, wp, undefined ) {
	'use strict';

	var app = {},
		settings = _audiothemeDashboardSettings,
		l10n = settings.l10n;

	delete settings.l10n;

	_.extend( app, { controller: {}, model: {}, view: {} } );


	/**
	 * ========================================================================
	 * CONTROLLERS
	 * ========================================================================
	 */

	app.controller.ModalState = Backbone.Model.extend({
		defaults: {
			canActivateModules: settings.canActivateModules || false,
			current: {},
			modules: {},
		},

		next: function() {
			var modules = this.get( 'modules' ),
				currentIndex = modules.indexOf( this.get( 'current' ) ),
				nextIndex = modules.length - 1 === currentIndex ? 0 : currentIndex + 1;

			this.set( 'current', modules.at( nextIndex ) );
		},

		previous: function() {
			var modules = this.get( 'modules' ),
				currentIndex = modules.indexOf( this.get( 'current' ) ),
				previousIndex = 0 === currentIndex ? modules.length - 1 : currentIndex - 1;

			this.set( 'current', modules.at( previousIndex ) );
		}
	});


	/**
	 * ========================================================================
	 * MODELS
	 * ========================================================================
	 */

	app.model.Module = Backbone.Model.extend({
		defaults: {
			id: '',
			name: '',
			description: '',
			overview: '',
			isActive: true,
			toggleNonce: ''
		},

		toggleStatus: function() {
			var module = this;

			return wp.ajax.post( 'audiotheme_ajax_toggle_module', {
				module: this.get( 'id' ),
				nonce: this.get( 'toggleNonce' )
			}).done(function( response ) {
				module.set( 'isActive', response.isActive );
				$( '#' + response.adminMenuId ).toggle( response.isActive );
			}).fail(function() {

			});
		}
	});

	app.model.Modules = Backbone.Collection.extend({
		module: app.model.Module
	});


	/**
	 * ========================================================================
	 * VIEWS
	 * ========================================================================
	 */

	app.view.ModuleCard = wp.Backbone.View.extend({
		events: {
			'click .audiotheme-module-card-actions-secondary a': 'openModal',
			'click .js-toggle-module': 'toggleStatus'
		},

		initialize: function( options ) {
			this.controller = options.controller;
			this.modal = options.modal;
			this.model = options.model;

			this.listenTo( this.model, 'change:status change:isActive', this.updateStatus );

			this.render();
		},

		render: function() {
			this.$button = this.$el.find( '.button-activate' );
			this.$spinner = this.$el.find( '.spinner' );
			this.updateStatus();
			return this;
		},

		openModal: function( e ) {
			e.preventDefault();
			this.controller.set( 'current', this.model );
			this.modal.open();
		},

		toggleStatus: function() {
			var view = this;

			view.$button.attr( 'disabled', true );
			view.$spinner.addClass( 'is-active' );

			this.model.toggleStatus().done(function() {
				view.$button.attr( 'disabled', false );
				view.$spinner.removeClass( 'is-active' );
			});
		},

		updateStatus: function() {
			var isActive = this.model.get( 'isActive' );
			this.$el.toggleClass( 'is-active', isActive ).toggleClass( 'is-inactive', ! isActive );
		}
	});

	app.view.Modal = wp.Backbone.View.extend({
		className: 'audiotheme-overlay',
		tagName: 'div',

		events: {
			'click .js-close': 'close'
		},

		initialize: function( options ) {
			this.$backdrop = $();
			this.$body = $( 'body' );
			this.controller = options.controller;
			this.render();
		},

		render: function() {
			this.$el.appendTo( '#wpbody-content' );

			this.views.add([
				new app.view.ModalHeader({
					controller: this.controller,
					parent: this
				}),
				new app.view.ModalContent({
					controller: this.controller,
					parent: this
				})
			]);

			if ( this.controller.get( 'canActivateModules' ) ) {
				this.views.add([
					new app.view.ModalFooter({
						controller: this.controller,
						parent: this
					})
				]);
			}

			if ( ! this.$backdrop.length ) {
				this.$backdrop = this.$el.after( '<div class="audiotheme-overlay-backdrop" />' ).next();
			}

			return this;
		},

		close: function() {
			this.$el.hide();
			this.$backdrop.hide();
			this.$body.removeClass( 'modal-open' );
		},

		open: function() {
			this.$el.show();
			this.$backdrop.show();
			this.$body.addClass( 'modal-open' );
		}
	});

	app.view.ModalHeader = wp.Backbone.View.extend({
		tagName: 'div',
		className: 'audiotheme-overlay-header',
		template: wp.template( 'audiotheme-module-modal-header' ),

		events : {
			'click .js-next': 'next',
			'click .js-previous': 'previous',
			'keyup': 'routeKey'
		},

		initialize: function( options ) {
			this.controller = options.controller;
			this.parent = options.parent;
		},

		render: function() {
			this.$el.html( this.template() );
			return this;
		},

		next: function() {
			this.controller.next();
		},

		previous: function() {
			this.controller.previous();
		},

		routeKey: function( e ) {
			// Escape
			if ( 27 === e.keyCode ) {
				this.parent.close();
			}

			// Left arrow
			if ( 37 === e.keyCode ) {
				this.controller.previous();
			}

			// Right arrow
			if ( 39 === e.keyCode ) {
				this.controller.next();
			}
		}
	});

	app.view.ModalContent = wp.Backbone.View.extend({
		tagName: 'div',
		className: 'audiotheme-overlay-content',
		template: wp.template( 'audiotheme-module-modal-content' ),

		events : {
			'click .js-toggle-module': 'toggleModuleStatus'
		},

		initialize: function( options ) {
			this.controller = options.controller;
			this.listenTo( this.controller, 'change:current', this.render );
		},

		render: function() {
			this.$el.html( this.template( this.controller.get( 'current' ).toJSON() ) );
			return this;
		}
	});

	app.view.ModalFooter = wp.Backbone.View.extend({
		tagName: 'div',
		className: 'audiotheme-overlay-footer',
		template: wp.template( 'audiotheme-module-modal-footer' ),

		events : {
			'click .js-toggle-module': 'toggleModuleStatus'
		},

		initialize: function( options ) {
			this.controller = options.controller;
			this.listenTo( this.controller, 'change:current', this.updateToggleButton );
			this.listenTo( this.controller.get( 'modules' ), 'change:isActive', this.updateToggleButton );
		},

		render: function() {
			this.$button = $( '<button class="button button-secondary js-toggle-module" />' )
				.appendTo( this.$el );

			this.$spinner = $( '<span class="spinner" />' )
				.prependTo( this.$el );

			return this;
		},

		toggleModuleStatus: function() {
			var view = this;

			view.$button.attr( 'disabled', true );
			view.$spinner.addClass( 'is-active' );

			this.controller.get( 'current' ).toggleStatus().done(function() {
				view.$button.attr( 'disabled', false );
				view.$spinner.removeClass( 'is-active' );
			});
		},

		updateToggleButton: function() {
			var isActive = this.controller.get( 'current' ).get( 'isActive' ),
				text = isActive ? l10n.deactivate : l10n.activate;
			this.$button.text( text ).toggleClass( 'button-primary', ! isActive );
		}
	});


	/**
	 * ========================================================================
	 * SETUP
	 * ========================================================================
	 */

	$( document ).ready(function() {
		var controller, modal;

		controller = new app.controller.ModalState({
			current: new app.model.Module(),
			modules: new app.model.Modules()
		});

		modal = new app.view.Modal({
			controller: controller
		});

		$( '.audiotheme-module-card' ).each(function() {
			var $module = $( this ),
				model = new app.model.Module();

			model.set({
				id: $module.data( 'module-id' ),
				name: $module.find( '.audiotheme-module-card-name' ).text(),
				description: $module.find( '.audiotheme-module-card-description' ).text(),
				media: $module.find( '.audiotheme-module-card-overview-media' ).detach().prop( 'outerHTML' ),
				overview: $module.find( '.audiotheme-module-card-overview' ).html(),
				isActive: $module.hasClass( 'is-active' ),
				toggleNonce: $module.data( 'toggle-nonce' )
			});

			controller.get( 'modules' ).add( model );

			new app.view.ModuleCard({
				el: this,
				controller: controller,
				modal: modal,
				model: model
			});
		});
	});

})( window, jQuery, _, Backbone, wp );
