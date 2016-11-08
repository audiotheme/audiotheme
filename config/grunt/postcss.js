var autoprefixer = require( 'autoprefixer' );

module.exports = {
	options: {
		processors: [
			autoprefixer({
				browsers: [ '> 1%', 'last 2 versions', 'ff 17', 'opera 12.1', 'android 4' ],
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
