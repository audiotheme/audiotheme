'use strict';

module.exports = function(grunt) {

	var exec = require('child_process').exec,
		pkg = grunt.file.readJSON('package.json');

	grunt.loadNpmTasks('grunt-contrib-compress');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-less');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-shell');
	grunt.loadNpmTasks('grunt-string-replace');

	grunt.initConfig({
		version: '<%= pkg.version %>',

		/**
		 * Check JavaScript for errors and warnings.
		 */
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
					AudiothemeJplayer: true,
					AudiothemeMediaControl: true,
					AudiothemeTracks: true,
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

		/**
		 * Compile LESS style sheets.
		 */
		less: {
			dist: {
				options: {
					yuicompress: true
				},
				files: [
					{ src: 'includes/css/less/audiotheme.less', dest: 'includes/css/audiotheme.min.css' },
				]
			}
		},

		/**
		 * Minify JavaScript source files.
		 */
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

		/**
		 * Watch sources files and compile when they're changed.
		 */
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

		/**
		 * Archive the plugin in the /release directory, excluding development
		 * and build related files.
		 *
		 * The zip archive will be named: audiotheme-plugin-{{version}}.zip
		 */
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
		},

		shell: {
			/**
			 * The custom AudioTheme version of WordPress' i18n tools needs to
			 * exist at '/wp-content/i18n-tools/', with php-cli and gettext in
			 * the system path to run this target.
			 *
			 * @link http://github.com/AudioTheme/i18n-tools/
			 */
			makepot: {
				command: 'php ../../i18n-tools/makepot-audiotheme.php wp-plugin . languages/audiotheme.pot'
			}
		},

		/**
		 * Replace version numbers in the main audiotheme.php file with the
		 * version defined in package.json.
		 */
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
		}

	});

	/**
	 * Register the default task.
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
	 * excluding unncessary dev/build files in the process.
	 *
	 * @todo generate pot files
	 *       bump/verify version numbers
	 *       git tag, commit, and push
	 *       zip to release directory, cleaning dev/build files in the process
	 *       push to remote server
	 */
	grunt.registerTask('release', function(arg1) {
		grunt.config.set('version', 0 === arguments.length ? pkg.version : arg1);
		grunt.task.run('string-replace:dist');
		grunt.task.run('compress:dist');
	});

	/**
	 * PHP Code Sniffer using WordPress Coding Standards
	 *
	 * @link https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards
	 */
	grunt.registerTask('phpcs', function() {
		var cmd = 'phpcs -p -s --standard=WordPress --extensions=php --ignore=*/node_modules/*,*/release/*,*/includes/lib/* --report-file=release/codesniffs.txt .',
			done = this.async();

		exec(cmd, function(error, stdout, stderr) {
			if (stdout) {
				grunt.log.write(stdout);
			}

			if (null !== error) {
				grunt.fatal(error);
			}

			done();
		});
	});

};
