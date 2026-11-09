/**
 * Front-end submission handler for the Event RSVP form.
 *
 * Config (`window.eventRsvp`) is added with wp_add_inline_script() in PHP.
 */

/* global FormData */

const config = window.eventRsvp || {};
const i18n = config.i18n || {};

/**
 * Clears field error messages.
 *
 * @param {HTMLFormElement} form Form element.
 */
function clearErrors( form ) {
	form.querySelectorAll( '[data-error-for]' ).forEach( ( el ) => {
		el.textContent = '';
	} );
	form.querySelectorAll( '[aria-invalid]' ).forEach( ( el ) => {
		el.removeAttribute( 'aria-invalid' );
	} );
}

/**
 * Shows field error messages returned by the server.
 *
 * @param {HTMLFormElement} form   Form element.
 * @param {Object}          errors Map of field name to message.
 */
function showErrors( form, errors ) {
	Object.keys( errors || {} ).forEach( ( field ) => {
		const target = form.querySelector( `[data-error-for="${ field }"]` );
		const input = form.elements.namedItem( field );

		if ( target ) {
			target.textContent = String( errors[ field ] );
		}
		if ( input && input.setAttribute ) {
			input.setAttribute( 'aria-invalid', 'true' );
		}
	} );
}

/**
 * Handles a form submission.
 *
 * @param {SubmitEvent} event Submit event.
 */
async function onSubmit( event ) {
	event.preventDefault();

	const form = event.currentTarget;
	const button = form.querySelector( '.event-rsvp__submit' );
	const message = form.querySelector( '.event-rsvp__message' );
	const buttonLabel = button.textContent;

	clearErrors( form );
	message.textContent = i18n.sending || '';
	message.classList.remove( 'is-error', 'is-success' );
	button.disabled = true;

	const body = new FormData( form );
	body.append( 'action', config.action );
	body.append( 'nonce', config.nonce );

	try {
		const response = await window.fetch( config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body,
		} );

		let json = null;
		try {
			json = await response.json();
		} catch ( e ) {
			// check_ajax_referer() failure returns "-1", not JSON.
		}

		if ( json && json.success ) {
			message.textContent = json.data.message;
			message.classList.add( 'is-success' );
			form.reset();
			return;
		}

		const data = ( json && json.data ) || {};
		showErrors( form, data.errors );
		message.textContent = data.message || i18n.error || '';
		message.classList.add( 'is-error' );
	} catch ( e ) {
		message.textContent = i18n.error || '';
		message.classList.add( 'is-error' );
	} finally {
		button.disabled = false;
		button.textContent = buttonLabel;
	}
}

function init() {
	document.querySelectorAll( '.event-rsvp__form' ).forEach( ( form ) => {
		form.addEventListener( 'submit', onSubmit );
	} );
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', init );
} else {
	init();
}
