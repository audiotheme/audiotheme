module.exports = {
	options: {
		jshintrc: 'config/.jshintrc'
	},
	build: [
		'Gruntfile.js',
		'admin/js/*.js',
		'!admin/js/*.bundle.js',
		'!admin/js/*.min.js',
		'modules/**/*.js',
		'!modules/**/*.bundle.js',
		'!modules/**/*.min.js',
		'includes/js/*.js',
		'!includes/js/*.min.js',
		'!includes/js/vendor/*.js'
	],
	grunt: {
		options: {
			jshintrc: 'config/.jshintnoderc'
		},
		src: [
			'Gruntfile.js',
			'config/grunt/*.js'
		]
	}
};
