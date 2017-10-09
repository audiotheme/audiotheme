module.exports = function( grunt, options ) {
	return {
		'default': [
			'build',
			'watch'
		],
		'build': [
			'check',
			'build:css',
			'build:js'
		],
		'build:css': [
			'less',
			'postcss',
			'cssmin'
		],
		'build:js': [
			'webpack:build',
			'uglify'
		],
		'check': [
			'jshint',
			'jscs'
		],
		'develop:js': [
			'webpack:develop'
		],
		'package': [
			'check',
			'string-replace:package',
			'build:css',
			'build:js',
			'makepot',
			'compress:package'
		]
	};
};
