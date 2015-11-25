module.exports = {
	options: {
		alias: {
			audiotheme: './includes/js/application.js'
		}
	},
	build: {
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
