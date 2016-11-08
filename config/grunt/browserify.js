var config,
	_ = require( 'lodash' );

config = {
	options: {
		alias: {
			audiotheme: './includes/js/application.js'
		},
		watch: true
	},
	build: {
		options: {},
		files: [
			{
				src: 'admin/js/admin.js',
				dest: 'admin/js/admin.bundle.js'
			},
			{
				src: 'admin/js/gig-edit.js',
				dest: 'admin/js/gig-edit.bundle.min.js'
			},
			{
				src: 'admin/js/venue-edit.js',
				dest: 'admin/js/venue-edit.bundle.min.js'
			},
			{
				src: 'admin/js/venue-manager.js',
				dest: 'admin/js/venue-manager.bundle.min.js'
			}
		]
	}
};

config.develop = _.cloneDeep( config.build );
config.develop.options.keepAlive = true;

module.exports = config;
