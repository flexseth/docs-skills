/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';

/**
 * Internal dependencies
 */
import MaintenanceBannerSidebar from './sidebar';

registerPlugin( 'maintenance-banner', {
	render: MaintenanceBannerSidebar,
	icon: 'megaphone',
} );
