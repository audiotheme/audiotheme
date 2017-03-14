/* jshint browserify: true */

'use strict';

module.exports = {
	isAddressEmpty: function() {
		return ! ( this.address || this.city || this.state || this.postal_code || this.country );
	},

	formatCityStatePostalCode: function() {
		var location = '';

		if ( this.city ) {
			location += this.city;
		}

		if ( this.state ) {
			location = ( '' === location ) ? this.state : location + ', ' + this.state;
		}

		if ( this.postal_code ) {
			location = ( '' === location ) ? this.postal_code : location + ' ' + this.postal_code;
		}

		return location;
	}
};
