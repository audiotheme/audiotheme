const webpack = require( 'webpack' );

const config = {
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
		loaders: [
			{
				test: /\.js$/,
				exclude: /node_modules/,
				loader: 'babel-loader'
			}
		]
	},
	plugins: []
};

// switch ( process.env.NODE_ENV ) {
// 	case 'production':
// 		config.plugins.push( new webpack.optimize.UglifyJsPlugin() );
// 		break;
//
// 	default:
// 		config.devtool = 'source-map';
// }

module.exports = config;
