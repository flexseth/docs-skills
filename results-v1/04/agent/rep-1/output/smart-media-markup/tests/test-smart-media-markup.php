<?php
/**
 * Example inputs/outputs for each Smart Media Markup transform.
 *
 * Notes on the HTML API's output format, which the expectations below rely on:
 * - New attributes are inserted directly after the tag name.
 * - Existing attributes are updated in place.
 * - add_class() appends to an existing class attribute.
 * - Attribute values are escaped on write ("&" becomes "&amp;").
 *
 * The WordPress test suite's home_url() host is "example.org".
 *
 * @package SmartMediaMarkup
 */

/**
 * Tests for Smart_Media_Markup_Processor and the block filters.
 */
class Test_Smart_Media_Markup extends WP_UnitTestCase {

	/**
	 * Data provider: [ block name, caption-as-alt flag, input, expected output ].
	 *
	 * @return array[]
	 */
	public function data_transforms() {
		return array(

			// 1 + 3. decoding="async" is added; wrapper figure gets the class.
			'image: adds decoding and wrapper class'         => array(
				'core/image',
				false,
				'<figure class="wp-block-image size-large"><img src="https://example.org/a.jpg" alt="A cat"/></figure>',
				'<figure class="wp-block-image size-large has-smart-media"><img decoding="async" src="https://example.org/a.jpg" alt="A cat"/></figure>',
			),

			// 1. An existing decoding attribute is left alone.
			'image: keeps existing decoding value'           => array(
				'core/image',
				false,
				'<figure class="wp-block-image"><img decoding="sync" src="a.jpg" alt="A cat"/></figure>',
				'<figure class="wp-block-image has-smart-media"><img decoding="sync" src="a.jpg" alt="A cat"/></figure>',
			),

			// 2. Caption copied into empty alt; nested tags dropped, entities decoded then re-escaped.
			'image: caption as alt'                          => array(
				'core/image',
				true,
				'<figure class="wp-block-image"><img src="kite.jpg" alt=""/><figcaption class="wp-element-caption">A <strong>red</strong> &amp; blue   kite</figcaption></figure>',
				'<figure class="wp-block-image has-smart-media"><img decoding="async" src="kite.jpg" alt="A red &amp; blue kite"/><figcaption class="wp-element-caption">A <strong>red</strong> &amp; blue   kite</figcaption></figure>',
			),

			// 2. A non-empty alt is never overwritten.
			'image: caption as alt keeps existing alt'       => array(
				'core/image',
				true,
				'<figure class="wp-block-image"><img src="kite.jpg" alt="Kite"/><figcaption class="wp-element-caption">A red kite</figcaption></figure>',
				'<figure class="wp-block-image has-smart-media"><img decoding="async" src="kite.jpg" alt="Kite"/><figcaption class="wp-element-caption">A red kite</figcaption></figure>',
			),

			// 2. Attribute off: alt stays empty.
			'image: caption as alt disabled'                 => array(
				'core/image',
				false,
				'<figure class="wp-block-image"><img src="kite.jpg" alt=""/><figcaption class="wp-element-caption">A red kite</figcaption></figure>',
				'<figure class="wp-block-image has-smart-media"><img decoding="async" src="kite.jpg" alt=""/><figcaption class="wp-element-caption">A red kite</figcaption></figure>',
			),

			// 4. External link without rel gets rel="noopener".
			'image: external link gets rel'                  => array(
				'core/image',
				false,
				'<figure class="wp-block-image"><a href="https://wordpress.org/"><img src="a.jpg" alt="Logo"/></a></figure>',
				'<figure class="wp-block-image has-smart-media"><a rel="noopener" href="https://wordpress.org/"><img decoding="async" src="a.jpg" alt="Logo"/></a></figure>',
			),

			// 4. Existing rel values are kept.
			'image: external link keeps existing rel'        => array(
				'core/image',
				false,
				'<figure class="wp-block-image"><a href="https://wordpress.org/" rel="nofollow sponsored"><img src="a.jpg" alt="Logo"/></a></figure>',
				'<figure class="wp-block-image has-smart-media"><a href="https://wordpress.org/" rel="nofollow sponsored noopener"><img decoding="async" src="a.jpg" alt="Logo"/></a></figure>',
			),

			// 4. noopener already present: no duplicate.
			'image: external link already noopener'          => array(
				'core/image',
				false,
				'<figure class="wp-block-image"><a href="https://wordpress.org/" rel="NoOpener"><img src="a.jpg" alt="Logo"/></a></figure>',
				'<figure class="wp-block-image has-smart-media"><a href="https://wordpress.org/" rel="NoOpener"><img decoding="async" src="a.jpg" alt="Logo"/></a></figure>',
			),

			// 4. Same-host and relative links are untouched.
			'image: internal links untouched'                => array(
				'core/image',
				false,
				'<figure class="wp-block-image"><a href="https://example.org/about/"><img src="a.jpg" alt="A"/></a><figcaption><a href="/contact/">Contact</a></figcaption></figure>',
				'<figure class="wp-block-image has-smart-media"><a href="https://example.org/about/"><img decoding="async" src="a.jpg" alt="A"/></a><figcaption><a href="/contact/">Contact</a></figcaption></figure>',
			),

			// Gallery: every nested image gets decoding; only the outer figure gets the class here
			// (inner image figures receive it from their own render_block_core/image pass).
			'gallery: all images and outer figure'           => array(
				'core/gallery',
				false,
				'<figure class="wp-block-gallery has-nested-images"><figure class="wp-block-image"><img src="1.jpg" alt="One"/></figure><figure class="wp-block-image"><a href="https://cdn.example.net/2.jpg"><img src="2.jpg" alt="Two"/></a></figure></figure>',
				'<figure class="wp-block-gallery has-nested-images has-smart-media"><figure class="wp-block-image"><img decoding="async" src="1.jpg" alt="One"/></figure><figure class="wp-block-image"><a rel="noopener" href="https://cdn.example.net/2.jpg"><img decoding="async" src="2.jpg" alt="Two"/></a></figure></figure>',
			),

			// Cover: wrapper is a <div>; it receives the class too.
			'cover: background image and wrapper'            => array(
				'core/cover',
				false,
				'<div class="wp-block-cover"><img class="wp-block-cover__image-background" alt="" src="bg.jpg" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><p><a href="https://wordpress.org/">Go</a></p></div></div>',
				'<div class="wp-block-cover has-smart-media"><img decoding="async" class="wp-block-cover__image-background" alt="" src="bg.jpg" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><p><a rel="noopener" href="https://wordpress.org/">Go</a></p></div></div>',
			),

			// Idempotent: running twice (image inside gallery) changes nothing more.
			'image: already processed markup is unchanged'   => array(
				'core/image',
				false,
				'<figure class="wp-block-image has-smart-media"><a rel="noopener" href="https://wordpress.org/"><img decoding="async" src="a.jpg" alt="A"/></a></figure>',
				'<figure class="wp-block-image has-smart-media"><a rel="noopener" href="https://wordpress.org/"><img decoding="async" src="a.jpg" alt="A"/></a></figure>',
			),
		);
	}

	/**
	 * @dataProvider data_transforms
	 *
	 * @param string $block_name     Block name.
	 * @param bool   $caption_as_alt Flag.
	 * @param string $input          Input HTML.
	 * @param string $expected       Expected HTML.
	 */
	public function test_transforms( $block_name, $caption_as_alt, $input, $expected ) {
		$this->assertSame(
			$expected,
			Smart_Media_Markup_Processor::process( $input, $block_name, $caption_as_alt )
		);
	}

	/**
	 * Caption-as-alt when the alt attribute is missing entirely. Asserted by
	 * reading attributes back, since the order of two newly inserted attributes
	 * is an implementation detail of the HTML API.
	 */
	public function test_caption_as_alt_when_alt_missing() {
		$output = Smart_Media_Markup_Processor::process(
			'<figure class="wp-block-image"><img src="kite.jpg"/><figcaption>Kite &lt;3</figcaption></figure>',
			'core/image',
			true
		);

		$processor = new WP_HTML_Tag_Processor( $output );
		$this->assertTrue( $processor->next_tag( 'img' ) );
		$this->assertSame( 'Kite <3', $processor->get_attribute( 'alt' ) );
		$this->assertSame( 'async', $processor->get_attribute( 'decoding' ) );
	}

	/**
	 * The attribute is registered on core/image on the server.
	 */
	public function test_attribute_registered_on_image_block() {
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'core/image' );

		$this->assertArrayHasKey( 'smartMediaCaptionAsAlt', $block_type->attributes );
		$this->assertSame( 'boolean', $block_type->attributes['smartMediaCaptionAsAlt']['type'] );
	}

	/**
	 * End to end through render_block(): saved content stays as-is, output is improved.
	 */
	public function test_render_block_integration() {
		$saved = '<!-- wp:image {"smartMediaCaptionAsAlt":true} -->'
			. '<figure class="wp-block-image"><img src="https://example.org/kite.jpg" alt=""/><figcaption class="wp-element-caption">Red kite</figcaption></figure>'
			. '<!-- /wp:image -->';

		$blocks = parse_blocks( $saved );
		$output = render_block( $blocks[0] );

		$processor = new WP_HTML_Tag_Processor( $output );
		$this->assertTrue( $processor->next_tag( 'figure' ) );
		$this->assertTrue( $processor->has_class( 'has-smart-media' ) );
		$this->assertTrue( $processor->next_tag( 'img' ) );
		$this->assertSame( 'Red kite', $processor->get_attribute( 'alt' ) );
		$this->assertSame( 'async', $processor->get_attribute( 'decoding' ) );
	}

	/**
	 * URL classification.
	 */
	public function test_is_external_url() {
		$this->assertTrue( Smart_Media_Markup_Processor::is_external_url( 'https://wordpress.org/' ) );
		$this->assertTrue( Smart_Media_Markup_Processor::is_external_url( '//cdn.example.net/x.jpg' ) );
		$this->assertFalse( Smart_Media_Markup_Processor::is_external_url( 'https://EXAMPLE.org/page/' ) );
		$this->assertFalse( Smart_Media_Markup_Processor::is_external_url( '/relative/' ) );
		$this->assertFalse( Smart_Media_Markup_Processor::is_external_url( '#top' ) );
		$this->assertFalse( Smart_Media_Markup_Processor::is_external_url( 'mailto:a@example.net' ) );
	}
}
