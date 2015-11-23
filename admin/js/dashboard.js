/*global _:false, _audiothemeDashboardSettings:false, Backbone:false, jQuery:false, wp:false */

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
			modules: {}
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
			adminMenuId: '',
			canToggle: false,
			isActive: true,
			nonces: []
		},

		toggleStatus: function() {
			var module = this;

			return wp.ajax.post( 'audiotheme_ajax_toggle_module', {
				module: this.get( 'id' ),
				nonce: this.get( 'nonces' ).toggle
			}).done(function( response ) {
				module.set( response ).toggleAdminMenu();
			}).fail(function() {

			});
		},

		toggleAdminMenu: function() {
			var selector = this.get( 'adminMenuId' );
			$( '#' + selector + ', .wp-submenu > .' + selector ).toggle( this.get( 'isActive' ) );
		}
	});

	app.model.Modules = Backbone.Collection.extend({
		model: app.model.Module
	});

	/**
	 * ========================================================================
	 * VIEWS
	 * ========================================================================
	 */

	app.view.ToggleButton = wp.Backbone.View.extend({
		className: 'button button-primary button-activate',
		tagName: 'button',

		events: {
			'click': 'toggleStatus'
		},

		initialize: function( options ) {
			this.model = options.model;

			this.listenTo( this.model, 'change:isActive', this.updateStatus );
		},

		render: function() {
			this.$spinner = $( '<span class="spinner" />' );
			this.updateStatus();
			return this;
		},

		toggleStatus: function( e ) {
			var view = this;

			e.preventDefault();

			view.$el.attr( 'disabled', true );
			view.$spinner.insertBefore( view.$el ).addClass( 'is-active' );

			this.model.toggleStatus().done(function() {
				view.$el.attr( 'disabled', false );
				view.$spinner.removeClass( 'is-active' );
			});
		},

		updateStatus: function() {
		var isActive = this.model.get( 'isActive' ),
			text = isActive ? l10n.deactivate : l10n.activate;

		this.$el.text( text ).toggleClass( 'button-primary', ! isActive );

		if ( ! this.model.get( 'canToggle' ) ) {
			this.$el.hide();
		} else {
			this.$el.css( 'display', '' );
		}
		}
	});

	app.view.ModuleCard = wp.Backbone.View.extend({
		events: {
			'click .audiotheme-module-card-actions-secondary a': 'openModal'
		},

		initialize: function( options ) {
			this.controller = options.controller;
			this.modal = options.modal;
			this.model = options.model;

			this.listenTo( this.model, 'change:isActive', this.updateStatusClass );
		},

		render: function() {
			this.buttonView = new app.view.ToggleButton({
				model: this.model
			});

			this.$el.find( '.audiotheme-module-card-actions-primary' )
				.prepend( this.buttonView.render().$el );

			this.updateStatusClass();
			return this;
		},

		openModal: function( e ) {
			e.preventDefault();
			this.controller.set( 'current', this.model );
			this.modal.open();
		},

		updateStatusClass: function() {
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

		events: {
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

		events: {
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

		events: {
			'click .js-toggle-module': 'toggleModuleStatus'
		},

		initialize: function( options ) {
			this.controller = options.controller;
			this.listenTo( this.controller, 'change:current', this.render );
			this.listenTo( this.controller, 'change:current', this.updateVisibility );
		},

		render: function() {
			if ( this.buttonView ) {
				this.buttonView.remove();
			}

			this.buttonView = new app.view.ToggleButton({
				model: this.controller.get( 'current' )
			});

			this.$el.append( this.buttonView.render().$el );

			return this;
		},

		updateVisibility: function() {
			var module = this.controller.get( 'current' );
			this.$el.toggle( !! module.get( 'canToggle' ) );
		}
	});

	/**
	 * ========================================================================
	 * SETUP
	 * ========================================================================
	 */

	$( document ).ready(function() {
		var controller, modal,
			modules = new app.model.Modules( _.where( settings.modules, { showInDashboard: true }) );

		controller = new app.controller.ModalState({
			current: new app.model.Module(),
			modules: modules
		});

		modal = new app.view.Modal({
			controller: controller
		});

		$( '.audiotheme-module-card' ).each(function() {
			var cardView,
				$module = $( this ),
				model = modules.get( $module.data( 'module-id' ) );

			model.set({
				description: $module.find( '.audiotheme-module-card-description' ).text(),
				media: $module.find( '.audiotheme-module-card-overview-media' ).detach().prop( 'outerHTML' ),
				overview: $module.find( '.audiotheme-module-card-overview' ).html()
			});

			cardView = new app.view.ModuleCard({
				el: this,
				controller: controller,
				modal: modal,
				model: model
			});

			cardView.render();
		});
	});

})( window, jQuery, _, Backbone, wp );
