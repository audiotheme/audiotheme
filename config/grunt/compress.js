module.exports = {
	package: {
		options: {
			archive: 'dist/<%= package.name %>-<%= package.version %>.zip'
		},
		files: [
			{
				src: [
					'**',
					'!admin/less/**',
					'!config/**',
					'!dist/**',
					'!node_modules/**',
					'!release/**',
					'!tests/**',
					'!vendor/composer/installers/**',
					'!vendor/icit/**',
					'vendor/icit/wp-less/*.php',
					'!vendor/leafo/**',
					'vendor/leafo/lessphp/*.php',
					'!vendor/xrstf/**',
					'!.jshintrc',
					'!Gruntfile.js',
					'!package.json',
					'!phpcs.xml',
					'!phpunit.xml',
					'!README.md',
					'!shipitfile.js'
				],
				dest: '<%= package.name %>/'
			}
		]
	}
};
