- [ ] Claude todo: 

Determine where there are JS Doc or other inline/formatting standards for variables, function names, etc, in Core and Gutenberg.

---

https://github.com/WordPress/gutenberg/pull/82741#discussion_r3986533130

> Optional for these. JS Doc will pick up @example if there's a best practice way of using these exports, e.g.

https://jsdoc.app/

```JS
/**
 * @example
 * ```js
 * const state = { viewport: '@mobile' };
 * const stateStyle = getStyleForState( attributes.style, state ) || {};
 * setAttributes( {
 * 	style: setStyleForState( attributes.style, state, {
 * 		...stateStyle,
 * 		dimensions: { ...stateStyle.dimensions, minHeight: '50vh' },
 * 	} ),
 * } );
 * ```
 */
 ```
