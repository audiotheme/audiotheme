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
				src: 'modules/gigs/admin/js/gig-edit.js',
				dest: 'modules/gigs/admin/js/gig-edit.bundle.min.js'
			},
			{
				src: 'modules/gigs/admin/js/venue-edit.js',
				dest: 'modules/gigs/admin/js/venue-edit.bundle.min.js'
			},
			{
				src: 'modules/gigs/admin/js/venue-manager.js',
				dest: 'modules/gigs/admin/js/venue-manager.bundle.min.js'
			}
		]
	}
};
