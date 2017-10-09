const webpackConfig = require( process.cwd() + '/webpack.config' );

let config = {
	build: webpackConfig
};

config.develop = Object.assign({
	keepalive: true,
	watch: true
}, config.build );

module.exports = config;
