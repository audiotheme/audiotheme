module.exports = {
	js: {
		files: [ '<%= jshint.check %>' ],
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
