/**
 * Extends the default @wordpress/scripts config: blocks are still discovered
 * from block.json files in src/, and the document sidebar is added as an
 * extra entry point.
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: {
		...defaultConfig.entry(),
		'sidebar/index': './src/sidebar/index.js',
	},
};
