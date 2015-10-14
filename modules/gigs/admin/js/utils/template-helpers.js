module.exports = {
	isAddressEmpty: function() {
		return ! ( this.address || this.city || this.state || this.postal_code || this.country );
	}
};
