/**
 * Extends the default @wordpress/scripts config with the member sidebar entry.
 * Blocks in src/ are still discovered automatically via their block.json.
 */
const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: {
		...defaultConfig.entry(),
		'member-sidebar/index': path.resolve(
			process.cwd(),
			'src/member-sidebar',
			'index.js'
		),
	},
};
