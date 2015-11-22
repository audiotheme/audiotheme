module.exports = {
	js: {
		files: [ '<%= jshint.build %>' ],
		tasks: [ 'jshint', 'uglify' ]
	},
	less: {
		files: [
			'includes/less/*.less',
			'admin/less/*.less',
			'admin/less/**/*.less'
		],
		tasks: [ 'less', 'postcss', 'cssmin' ]
	}
};
