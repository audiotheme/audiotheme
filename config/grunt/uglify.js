module.exports = {
	build: {
		files: [
			{
				src: 'admin/js/admin.bundle.js',
				dest: 'admin/js/admin.bundle.min.js'
			},
			{
				src: 'admin/js/media.js',
				dest: 'admin/js/media.min.js'
			},
			{
				src: 'admin/js/settings.js',
				dest: 'admin/js/settings.min.js'
			},
			{
				src: 'modules/gigs/admin/js/gig-edit.bundle.min.js',
				dest: 'modules/gigs/admin/js/gig-edit.bundle.min.js'
			},
			{
				src: 'modules/gigs/admin/js/venue-edit.bundle.min.js',
				dest: 'modules/gigs/admin/js/venue-edit.bundle.min.js'
			},
			{
				src: 'modules/gigs/admin/js/venue-manager.bundle.min.js',
				dest: 'modules/gigs/admin/js/venue-manager.bundle.min.js'
			},
			{
				src: 'includes/js/audiotheme.js',
				dest: 'includes/js/audiotheme.min.js'
			}
		]
	}
};
