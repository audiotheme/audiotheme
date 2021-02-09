var autoprefixer = require( 'autoprefixer' );

module.exports = {
	options: {
		processors: [
			autoprefixer({
				cascade: false
			})
		]
	},
	build: {
		files: [
			{
				src: 'admin/css/admin.min.css'
			},
			{
				src: 'admin/css/dashboard.min.css'
			},
			{
				src: 'admin/css/venue-manager.min.css'
			},
			{
				src: 'includes/css/audiotheme.min.css'
			}
		]
	}
};
