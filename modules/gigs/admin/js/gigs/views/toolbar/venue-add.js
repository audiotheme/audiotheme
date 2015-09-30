var VenueAddToolbar,
	_ = require( 'underscore' ),
	Venue = require( '../../models/venue' ),
	wp = require( 'wp' );

VenueAddToolbar = wp.media.view.Toolbar.extend({
	initialize: function( options ) {
		_.bindAll( this, 'saveVenue' );

		// This is a button.
		this.options.items = _.defaults( this.options.items || {}, {
			save: {
				text: this.controller.state().get( 'button' ).text,
				style: 'primary',
				priority: 80,
				requires: false,
				click: this.saveVenue
			},
			spinner: new wp.media.view.Spinner({
				priority: 60
			})
		});

		this.options.items.spinner.delay = 0;
		this.listenTo( this.model, 'change:name', this.toggleButtonState );

		wp.media.view.Toolbar.prototype.initialize.apply( this, arguments );
	},

	render: function() {
		this.$button = this.get( 'save' ).$el;
		this.toggleButtonState();
		return this;
	},

	saveVenue: function() {
		var controller = this.controller,
			model = controller.state().get( 'model' ),
			spinner = this.get( 'spinner' ).show();

		model.save().done(function( response ) {
			var selectController = controller.state( 'audiotheme-venues' );

			// Insert into the venues collection and update the selection.
			selectController.get( 'venues' ).add( model );
			selectController.get( 'selection' ).reset( model );
			selectController.set( 'mode', 'view' );
			controller.state().set( 'model', new Venue() );

			// Switch to the select view.
			controller.setState( 'audiotheme-venues' );

			spinner.hide();
		});
	},

	toggleButtonState: function() {
		this.$button.attr( 'disabled', '' === this.model.get( 'name' ) );
	}
});

module.exports = VenueAddToolbar;
