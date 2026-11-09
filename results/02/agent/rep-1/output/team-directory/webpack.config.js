/**
 * Extends the default @wordpress/scripts config with the sidebar entry.
 * Blocks in src/ are still discovered through their block.json files.
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: {
		...( typeof defaultConfig.entry === 'function'
			? defaultConfig.entry()
			: defaultConfig.entry ),
		'sidebar/index': path.resolve( __dirname, 'src/sidebar/index.js' ),
	},
};
