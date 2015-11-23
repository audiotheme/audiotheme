module.exports = {
	options: {
		config: 'config/.jscsrc'
	},
	check: {
		files: {
			src: [
				'admin/js/*.js',
				'!admin/js/*.bundle.js',
				'!admin/js/*.min.js',
				'modules/**/*.js',
				'!modules/**/*.bundle.js',
				'!modules/**/*.min.js',
				'includes/js/*.js',
				'!includes/js/*.min.js',
				'!includes/js/vendor/*.js'
			]
		}
	}
};
