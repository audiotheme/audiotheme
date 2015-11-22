module.exports = {
	package: {
		options: {
			exclude: [
				'.git/.*',
				'dist/.*',
				'includes/vendor/.*',
				'node_modules/.*',
				'tests/.*'
			],
			mainFile: 'audiotheme.php',
			potHeaders: {
				'report-msgid-bugs-to': 'https://audiotheme.com/support/',
				'poedit': true
			},
			type: 'wp-plugin',
			processPot: function( pot ) {
				var translation,
					excludedMeta = [
						'Plugin Name of the plugin/theme',
						'Plugin URI of the plugin/theme',
						'Author of the plugin/theme',
						'Author URI of the plugin/theme'
					];

				for ( translation in pot.translations[''] ) {
					if ( 'undefined' !== typeof pot.translations[''][ translation ].comments.extracted ) {
						if ( 0 <= excludedMeta.indexOf( pot.translations[''][ translation ].comments.extracted ) ) {
							console.log( 'Excluded meta: ' + pot.translations[''][ translation ].comments.extracted );
							delete pot.translations[''][ translation ];
						}
					}
				}

				return pot;
			}
		}
	}
};
