module.exports = {
	build: {
		files: [
			{
				src: 'includes/less/audiotheme.less',
				dest: 'includes/css/audiotheme.min.css'
			},
			{
				src: 'admin/less/admin.less',
				dest: 'admin/css/admin.min.css'
			},
			{
				src: 'admin/less/dashboard.less',
				dest: 'admin/css/dashboard.min.css'
			},
			{
				src: 'admin/less/venue-manager.less',
				dest: 'admin/css/venue-manager.min.css'
			}
		]
	}
};
