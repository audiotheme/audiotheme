module.exports = {
	package: {
		options: {
			archive: 'dist/<%= package.name %>-plugin-<%= package.version %>.zip'
		},
		files: [
			{
				src: [
					'**',
					'!admin/less/**',
					'!dist/**',
					'!node_modules/**',
					'!release/**',
					'!tests/**',
					'!.jshintrc',
					'!Gruntfile.js',
					'!package.json',
					'!phpcs.xml',
					'!phpunit.xml',
					'!README.md'
				],
				dest: '<%= package.name %>/'
			}
		]
	}
};
