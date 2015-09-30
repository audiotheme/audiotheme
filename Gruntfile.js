/*jshint node:true */

module.exports = function( grunt ) {
	'use strict';

	require( 'matchdep' ).filterDev( 'grunt-*' ).forEach( grunt.loadNpmTasks );

	grunt.initConfig({
		pkg: grunt.file.readJSON( 'package.json' ),
		version: '<%= pkg.version %>',

		/**
		 * Autoprefix CSS files.
		 */
		autoprefixer: {
			options: {
				browsers: [ '> 1%', 'last 2 versions', 'ff 17', 'opera 12.1', 'android 4' ]
			},
			dist: {
				files: [
					{ src: 'admin/css/admin.min.css' },
					{ src: 'admin/css/venue-manager.min.css' },
					{ src: 'includes/css/audiotheme.min.css' }
				]
			}
		},

		browserify: {
			options: {
				alias: {
					audiotheme: './includes/js/application.js'
				}
			},
			build: {
				files: [
					{ src: 'admin/js/admin.js', dest: 'admin/js/admin.bundle.js' },
					{ src: 'modules/gigs/admin/js/gig-edit.js', dest: 'modules/gigs/admin/js/gig-edit.bundle.min.js' },
					{ src: 'modules/gigs/admin/js/venue-manager.js', dest: 'modules/gigs/admin/js/venue-manager.bundle.min.js' }
				]
			}
		},

		/**
		 * Check JavaScript for errors and warnings.
		 */
		jshint: {
			options: {
				jshintrc: '.jshintrc'
			},
			all: [
				'Gruntfile.js',
				'admin/js/*.js',
				'!admin/js/*.bundle.js',
				'!admin/js/*.min.js',
				'gigs/admin/js/*.js',
				'!gigs/admin/js/*.min.js',
				'includes/js/*.js',
				'!includes/js/*.min.js'
			]
		},

		/**
		 * Minimize CSS files.
		 */
		cssmin: {
			dist: {
				files: [
					{ src: 'admin/css/admin.min.css', dest: 'admin/css/admin.min.css' },
					{ src: 'admin/css/venue-manager.min.css', dest: 'admin/css/venue-manager.min.css' },
					{ src: 'includes/css/audiotheme.min.css', dest: 'includes/css/audiotheme.min.css' }
				]
			}
		},

		/**
		 * Compile LESS style sheets.
		 */
		less: {
			dist: {
				files: [
					{ src: 'includes/css/less/audiotheme.less', dest: 'includes/css/audiotheme.min.css' },
					{ src: 'admin/css/less/admin.less', dest: 'admin/css/admin.min.css' },
					{ src: 'admin/css/less/venue-manager.less', dest: 'admin/css/venue-manager.min.css' }
				]
			}
		},

		/**
		 * Minify JavaScript source files.
		 */
		uglify: {
			dist: {
				files: [
					{ src: 'admin/js/admin.bundle.js', dest: 'admin/js/admin.bundle.min.js' },
					{ src: 'admin/js/media.js', dest: 'admin/js/media.min.js' },
					{ src: 'admin/js/pointer.js', dest: 'admin/js/pointer.min.js' },
					{ src: 'admin/js/settings.js', dest: 'admin/js/settings.min.js' },
					{ src: 'modules/gigs/admin/js/gig-edit.bundle.min.js', dest: 'modules/gigs/admin/js/gig-edit.bundle.min.js' },
					{ src: 'modules/gigs/admin/js/venue-edit.js', dest: 'modules/gigs/admin/js/venue-edit.min.js' },
					{ src: 'modules/gigs/admin/js/venue-manager.bundle.min.js', dest: 'modules/gigs/admin/js/venue-manager.bundle.min.js' },
					{ src: 'includes/js/audiotheme.js', dest: 'includes/js/audiotheme.min.js' }
				]
			}
		},

		/**
		 * Watch sources files and compile when they're changed.
		 */
		watch: {
			js: {
				files: [ '<%= jshint.all %>' ],
				tasks: [ 'jshint', 'uglify' ]
			},
			less: {
				files: [
					'includes/css/less/*.less',
					'admin/css/less/*.less',
					'admin/css/less/**/*.less'
				],
				tasks: [ 'less', 'autoprefixer', 'cssmin' ]
			}
		},

		/**
		 * Archive the plugin in the /dist directory, excluding development
		 * and build related files.
		 *
		 * The zip archive will be named: audiotheme-plugin-{{version}}.zip
		 */
		compress: {
			build: {
				options: {
					archive: 'dist/<%= pkg.name %>-plugin-<%= version %>.zip'
				},
				files: [
					{
						src: [
							'**',
							'!admin/css/less/**',
							'!dist/**',
							'!node_modules/**',
							'!release/**',
							'!tests/**',
							'!.jshintrc',
							'!Gruntfile.js',
							'!package.json',
							'!phpcs.xml',
							'!phpunit.xml',
							'!README.md',
							'!includes/vendor/lessphp/**',
							'!includes/vendor/wp-less/**',
							'includes/vendor/lessphp/lessc.inc.php',
							'includes/vendor/wp-less/wp-less.php'
						],
						dest: '<%= pkg.name %>/'
					}
				]
			}
		},

		makepot: {
			build: {
				options: {
					exclude: [ '.git/.*', 'dist/.*', 'node_modules/.*', 'release/.*', 'tests/.*' ],
					mainFile: 'audiotheme.php',
					potHeaders: {
						'report-msgid-bugs-to': 'https://audiotheme.com/support/',
						'poedit': true
					},
					type: 'wp-plugin'
				}
			}
		},

		/**
		 * Replace version numbers during a build.
		 */
		'string-replace': {
			build: {
				options: {
					replacements: [{
						pattern: /Version: .+/,
						replacement: 'Version: <%= version %>'
					}, {
						pattern: /@version .+/,
						replacement: '@version <%= version %>'
					}, {
						pattern: /'AUDIOTHEME_VERSION', '[^']+'/,
						replacement: '\'AUDIOTHEME_VERSION\', \'<%= version %>\''
					}]
				},
				files: {
					'audiotheme.php': 'audiotheme.php',
					'style.css': 'style.css'
				}
			}
		},

	});

	/**
	 * Default task.
	 */
	grunt.registerTask( 'default', [ 'jshint', 'browserify', 'uglify', 'less', 'autoprefixer', 'cssmin', 'watch' ] );

	/**
	 * Build a release.
	 *
	 * Bumps version numbers. Defaults to the version set in package.json, but a
	 * specific version number can be passed as the first argument.
	 * Ex: grunt release:1.2.3
	 *
	 * The project is then zipped into an archive in the /dist directory,
	 * excluding unnecessary source files in the process.
	 */
	grunt.registerTask( 'package', function( arg1 ) {
		var pkg = grunt.file.readJSON( 'package.json' ),
			version = 0 === arguments.length ? pkg.version : arg1;

		grunt.config.set( 'version', version );

		grunt.task.run([
			'string-replace:build',
			'jshint',
			'less',
			'autoprefixer',
			'cssmin',
			'browserify',
			'uglify',
			'makepot',
			'compress:build'
		]);
	});

};
