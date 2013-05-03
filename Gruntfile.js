'use strict';

module.exports = function(grunt) {

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		version: '<%= pkg.version %>',

		// Check JavaScript for errors.
		jshint: {
			options: {
				bitwise: true,
				browser: true,
				curly: true,
				eqeqeq: true,
				eqnull: true,
				es5: true,
				esnext: true,
				immed: true,
				jquery: true,
				latedef: true,
				newcap: true,
				noarg: true,
				node: true,
				strict: false,
				trailing: true,
				undef: true,
				globals: {
					_: true,
					ajaxurl: true,
					audiotheme: true,
					audiothemePointers: true,
					audiothemeGigsL10n: true,
					AudiothemeMediaControl: true,
					isRtl: true,
					jQuery: true,
					tb_remove: true,
					wp: true,
					wpPointerL10n: true
				},
			},
			all: [
				'Gruntfile.js',
				'admin/js/*.js',
				'!admin/js/*.min.js',
				'gigs/admin/js/*.js',
				'!gigs/admin/js/*.min.js',
				'includes/js/*.js',
				'!includes/js/*.min.js'
			]
		},

		// Compile LESS files.
		less: {
			dist: {
				options: {
					yuicompress: true
				},
				files: [
					{ src: 'includes/css/less/*.less', dest: 'includes/css/audiotheme.min.css' },
				]
			}
		},

		// Minify (could concatenate, too).
		uglify: {
			dist: {
				files: [
					{ src: 'admin/js/admin.js', dest: 'admin/js/admin.min.js' },
					{ src: 'admin/js/media.js', dest: 'admin/js/media.min.js' },
					{ src: 'admin/js/pointer.js', dest: 'admin/js/pointer.min.js' },
					{ src: 'admin/js/settings.js', dest: 'admin/js/settings.min.js' },
					{ src: 'gigs/admin/js/gig-edit.js', dest: 'gigs/admin/js/gig-edit.min.js' },
					{ src: 'gigs/admin/js/venue-edit.js', dest: 'gigs/admin/js/venue-edit.min.js' },
					{ src: 'includes/js/audiotheme.js', dest: 'includes/js/audiotheme.min.js' }
				]
			}
		},

		// Watch for changes.
		watch: {
			js: {
				files: ['<%= jshint.all %>'],
				tasks: ['jshint', 'uglify']
			},
			less: {
				files: ['includes/css/less/*.less'],
				tasks: ['less']
			}
		},

		// Replace version numbers in audiotheme.php with the version in package.json.
		"string-replace": {
			dist: {
				options: {
					replacements: [{
						pattern: /Version: .+/,
						replacement: "Version: <%= version %>"
					}, {
						pattern: /@version .+/,
						replacement: "@version <%= version %>"
					}, {
						pattern: /'AUDIOTHEME_VERSION', '[^']+'/,
						replacement: "'AUDIOTHEME_VERSION', '<%= version %>'"
					}]
				},
				files: {
					'audiotheme.php': 'audiotheme.php',
					'style.css': 'style.css'
				}
			}
		},

		// Zip the plugin into an audiotheme-{{version}}.zip archive in the /release directory.
		compress: {
			dist: {
				options: {
					archive: 'release/<%= pkg.slug %>-plugin-<%= version %>.zip',
				},
				files: [
					{
						src: [
							'**',
							'!node_modules/**',
							'!release/**',
							'!Gruntfile.js',
							'!package.json',
							'!README.md',
							'!includes/lib/wp-less/**',
							'includes/lib/wp-less/lessc/lessc.inc.php',
							'includes/lib/wp-less/wp-less.php',
						],
						dest: '<%= pkg.slug %>/'
					}
				]
			}
		}

	});

	/**
	 * Load tasks.
	 */
	grunt.loadNpmTasks('grunt-contrib-compress');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-less');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-string-replace');

	/**
	 * Register default task.
	 */
	grunt.registerTask('default', [
		'jshint',
		'less',
		'uglify',
		'watch'
	]);

	/**
	 * Build a release.
	 *
	 * Bumps the version numbers in audiotheme.php. Defaults to the version set
	 * in package.json, but a specific version number can be passed as the first
	 * argument. Ex: grunt release:1.2.3
	 *
	 * The project is then zipped into an archive in the release directory,
	 * excluding unncessary source files in the process.
	 *
	 * @todo generate pot files
	 *       bump/verify version numbers
	 *       git tag, commit, and push
	 *       zip to release directory, cleaning source files in the process
	 *       push to remote server
	 */
	grunt.registerTask('release', function(arg1) {
		var pkg = grunt.file.readJSON('package.json');

		grunt.config.set('version', 0 === arguments.length ? pkg.version : arg1);
		grunt.task.run('string-replace:dist');
		grunt.task.run('compress:dist');
	});

};