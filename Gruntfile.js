/*jshint node:true */

module.exports = function(grunt) {
	'use strict';

	require('matchdep').filterDev('grunt-*').forEach( grunt.loadNpmTasks );

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		config: grunt.file.readJSON('config.json'),
		version: '<%= pkg.version %>',

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
					{ src: 'includes/css/less/audiotheme.less', dest: 'includes/css/audiotheme.min.css' }
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
			build: {
				options: {
					archive: 'release/<%= pkg.slug %>-plugin-<%= version %>.zip'
				},
				files: [
					{
						src: [
							'**',
							'!node_modules/**',
							'!release/**',
							'!tests/**',
							'!.jshintrc',
							'!config.json',
							'!Gruntfile.js',
							'!package.json',
							'!phpunit.xml',
							'!README.md',
							'!includes/lib/wp-less/**',
							'includes/lib/wp-less/lessc/lessc.inc.php',
							'includes/lib/wp-less/wp-less.php'
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
			},
			release: {
				options: {
					replacements: [{
						pattern: /@since x\.x\.x/g,
						replacement: '@since <%= version %>'
					}]
				},
				files: [
					{
						src: [
							'*.php',
							'**/*.php'
						],
						dest: './'
					}
				]
			}
		},

		/**
		 * Upload a release build to the production server.
		 */
		sftp: {
			release: {
				options: {
					path: '<%= config.production.releasePath %>',
					srcBasePath: 'release/',
					host: '<%= config.production.host %>',
					username: '<%= config.production.username %>',
					password: '<%= config.production.password %>'
				},
				files: [
					{
						src: [ 'release/<%= pkg.slug %>-plugin-<%= version %>.zip' ],
						dest: './'
					}
				]
			}
		}

	});

	/**
	 * Default task.
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
	 * Bumps version numbers. Defaults to the version set in package.json, but a
	 * specific version number can be passed as the first argument.
	 * Ex: grunt release:1.2.3
	 *
	 * The project is then zipped into an archive in the release directory,
	 * excluding unnecessary source files in the process.
	 */
	grunt.registerTask('build', function(arg1) {
		var pkg = grunt.file.readJSON('package.json'),
			version = 0 === arguments.length ? pkg.version : arg1;

		grunt.config.set('version', version);
		grunt.task.run('string-replace:build');
		grunt.task.run('jshint');
		grunt.task.run('less');
		grunt.task.run('uglify');
		// @todo generate pot file
		grunt.task.run('compress:build');
	});

	/**
	 * Release a new version.
	 *
	 * Builds a release and pushes it to the remote git repo and uploads it to
	 * the production server.
	 *
	 * @todo "@since x.x.x" tags are also replaced with the new version number.
	 */
	grunt.registerTask('release', function(arg1) {
		var pkg = grunt.file.readJSON('package.json'),
			version = 0 === arguments.length ? pkg.version : arg1;

		grunt.config.set('version', version);
		grunt.task.run('build:' + version);
		//grunt.task.run('string-replace:release');
		// @todo git tag, commit, and push to origin
		grunt.task.run('sftp:release');
	});

	/**
	 * PHP Code Sniffer using WordPress Coding Standards.
	 *
	 * @link https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards
	 */
	grunt.registerTask('phpcs', function() {
		var done = this.async();

		grunt.util.spawn({
			cmd: 'phpcs',
			args: [
				'-p',
				'-s',
				'--standard=WordPress',
				'--extensions=php',
				'--ignore=*/node_modules/*,*/release/*,*/includes/lib/*',
				'--report-file=release/codesniffs.txt',
				'.'
			],
			opts: { stdio: 'inherit' }
		}, done);
	});

};