module.exports = {
	package: {
		options: {
			replacements: [
				{
					pattern: /(Version:[\s]+).+/,
					replacement: '$1<%= package.version %>'
				},
				{
					pattern: /@version .+/,
					replacement: '@version <%= package.version %>'
				},
				{
					pattern: /'AUDIOTHEME_VERSION', '[^']+'/,
					replacement: '\'AUDIOTHEME_VERSION\', \'<%= package.version %>\''
				}
			]
		},
		files: {
			'audiotheme.php': 'audiotheme.php',
			'style.css': 'style.css'
		}
	}
};
