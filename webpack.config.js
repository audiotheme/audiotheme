const webpack = require( 'webpack' );
const mode = process.env.NODE_ENV || 'production';

const config = {
	mode: mode,
	entry: {
		'admin': './admin/js/admin.js',
		'gig-edit': './admin/js/gig-edit.js'
	},
	output: {
		filename: '[name].bundle.js',
		path: __dirname + '/admin/js'
	},
	externals: {
		backbone: 'Backbone',
		jquery: 'jQuery',
		mediaelementjs: 'mejs',
		underscore: '_',
		wp: 'wp'
	},
	resolve: {
		alias: {
			audiotheme: __dirname + '/includes/js/application.js'
		}
	},
	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /node_modules/,
				use: 'babel-loader'
			}
		]
	},
	plugins: []
};

module.exports = config;
