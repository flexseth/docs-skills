/**
 * Extends the default @wordpress/scripts config with the document
 * settings panel entry. Blocks in src/ are still discovered automatically.
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

const defaultEntry =
	typeof defaultConfig.entry === 'function'
		? defaultConfig.entry()
		: defaultConfig.entry;

module.exports = {
	...defaultConfig,
	entry: {
		...defaultEntry,
		sidebar: './src/sidebar/index.js',
	},
};
