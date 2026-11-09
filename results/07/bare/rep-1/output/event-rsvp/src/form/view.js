/**
 * Front-end behaviour for the RSVP form.
 *
 * Settings (ajaxUrl, action, nonce, i18n) are provided on `window.eventRsvp`
 * by wp_add_inline_script() in the plugin's main file.
 */

const settings = window.eventRsvp || {};
const i18n = settings.i18n || {};

/**
 * Clears and optionally sets per-field error messages.
 *
 * @param {HTMLFormElement} form   Form element.
 * @param {Object}          errors Map of field name => message.
 */
function showFieldErrors( form, errors = {} ) {
	form.querySelectorAll( '[data-error-for]' ).forEach( ( el ) => {
		const field = el.getAttribute( 'data-error-for' );
		const input = form.elements.namedItem( field );
		const message = errors[ field ] || '';

		el.textContent = message;

		if ( input ) {
			if ( message ) {
				input.setAttribute( 'aria-invalid', 'true' );
			} else {
				input.removeAttribute( 'aria-invalid' );
			}
		}
	} );
}

/**
 * Shows the form-level status message.
 *
 * @param {HTMLFormElement} form    Form element.
 * @param {string}          message Message text.
 * @param {string}          type    'success' or 'error'.
 */
function showMessage( form, message, type ) {
	const el = form.querySelector( '.event-rsvp-form__message' );
	if ( ! el ) {
		return;
	}
	el.textContent = message;
	el.classList.toggle( 'is-success', type === 'success' );
	el.classList.toggle( 'is-error', type === 'error' );
}

/**
 * Handles form submission.
 *
 * @param {SubmitEvent} event Submit event.
 */
async function onSubmit( event ) {
	event.preventDefault();

	const form = event.currentTarget;
	const button = form.querySelector( '.event-rsvp-form__submit' );
	const originalLabel = button ? button.textContent : '';

	if ( ! settings.ajaxUrl || ! settings.nonce ) {
		showMessage( form, i18n.error || '', 'error' );
		return;
	}

	const data = new FormData( form );
	data.append( 'action', settings.action );
	data.append( 'nonce', settings.nonce );

	showFieldErrors( form );
	showMessage( form, '', '' );

	if ( button ) {
		button.disabled = true;
		button.textContent = i18n.sending || originalLabel;
	}

	try {
		const response = await fetch( settings.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: data,
		} );

		let json = null;
		try {
			json = await response.json();
		} catch ( e ) {
			json = null;
		}

		if ( json && json.success ) {
			form.reset();
			showMessage( form, json.data.message, 'success' );
			return;
		}

		const payload = ( json && json.data ) || {};
		showFieldErrors( form, payload.errors || {} );
		showMessage( form, payload.message || i18n.error || '', 'error' );
	} catch ( e ) {
		showMessage( form, i18n.error || '', 'error' );
	} finally {
		if ( button ) {
			button.disabled = false;
			button.textContent = originalLabel;
		}
	}
}

function init() {
	document
		.querySelectorAll( '.event-rsvp-form__form' )
		.forEach( ( form ) => form.addEventListener( 'submit', onSubmit ) );
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', init );
} else {
	init();
}
