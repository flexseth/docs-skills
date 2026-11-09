<?php
/**
 * Example inputs/outputs for every Smart Media Markup transform.
 *
 * Each data provider row is: input HTML, options, expected HTML. Markup is
 * compared structurally (tag names, attribute name/value sets, text), so the
 * position the HTML API chooses for a newly added attribute does not matter.
 *
 * @package SmartMediaMarkup
 */

/**
 * @covers Smart_Media_Markup_Processor
 * @covers Smart_Media_Markup
 */
class Test_Smart_Media_Markup_Processor extends WP_UnitTestCase {

	const HOST = 'example.org';

	/*
	 * ---------------------------------------------------------------------
	 * 1. decoding="async"
	 * ---------------------------------------------------------------------
	 */

	/**
	 * @return array[]
	 */
	public function data_decoding() {
		return array(
			'adds decoding to image'          => array(
				'<figure class="wp-block-image size-large"><img src="https://example.org/a.jpg" alt="A kite"/></figure>',
				'<figure class="wp-block-image size-large has-smart-media"><img decoding="async" src="https://example.org/a.jpg" alt="A kite"/></figure>',
			),
			'keeps an existing decoding'      => array(
				'<figure class="wp-block-image"><img src="a.jpg" alt="" decoding="sync"/></figure>',
				'<figure class="wp-block-image has-smart-media"><img src="a.jpg" alt="" decoding="sync"/></figure>',
			),
			'every image in a gallery'        => array(
				'<figure class="wp-block-gallery has-nested-images"><figure class="wp-block-image"><img src="1.jpg" alt=""/></figure><figure class="wp-block-image"><img src="2.jpg" alt=""/></figure></figure>',
				'<figure class="wp-block-gallery has-nested-images has-smart-media"><figure class="wp-block-image"><img decoding="async" src="1.jpg" alt=""/></figure><figure class="wp-block-image"><img decoding="async" src="2.jpg" alt=""/></figure></figure>',
			),
			'cover background image'          => array(
				'<div class="wp-block-cover"><img class="wp-block-cover__image-background" alt="" src="bg.jpg" data-object-fit="cover"/><div class="wp-block-cover__inner-container"></div></div>',
				'<div class="wp-block-cover"><img decoding="async" class="wp-block-cover__image-background" alt="" src="bg.jpg" data-object-fit="cover"/><div class="wp-block-cover__inner-container"></div></div>',
			),
		);
	}

	/**
	 * @dataProvider data_decoding
	 *
	 * @param string $input    Input HTML.
	 * @param string $expected Expected HTML.
	 */
	public function test_decoding( $input, $expected ) {
		$this->assertSameMarkup( $expected, Smart_Media_Markup_Processor::process( $input, array( 'site_host' => self::HOST ) ) );
	}

	/*
	 * ---------------------------------------------------------------------
	 * 2. Caption as alt
	 * ---------------------------------------------------------------------
	 */

	/**
	 * @return array[]
	 */
	public function data_caption_as_alt() {
		return array(
			'copies caption text, strips tags, decodes entities' => array(
				'<figure class="wp-block-image"><img src="a.jpg" alt=""/><figcaption class="wp-element-caption">A <strong>red</strong> &amp; blue   kite</figcaption></figure>',
				true,
				'<figure class="wp-block-image has-smart-media"><img decoding="async" src="a.jpg" alt="A red &amp; blue kite"/><figcaption class="wp-element-caption">A <strong>red</strong> &amp; blue   kite</figcaption></figure>',
			),
			'missing alt attribute counts as empty'              => array(
				'<figure class="wp-block-image"><img src="a.jpg"/><figcaption>Sunset</figcaption></figure>',
				true,
				'<figure class="wp-block-image has-smart-media"><img decoding="async" alt="Sunset" src="a.jpg"/><figcaption>Sunset</figcaption></figure>',
			),
			'quotes are escaped safely'                          => array(
				'<figure class="wp-block-image"><img src="a.jpg" alt=""/><figcaption>The "best" <em>view</em><br>ever</figcaption></figure>',
				true,
				'<figure class="wp-block-image has-smart-media"><img decoding="async" src="a.jpg" alt="The &quot;best&quot; view ever"/><figcaption>The "best" <em>view</em><br>ever</figcaption></figure>',
			),
			'linked image'                                       => array(
				'<figure class="wp-block-image"><a href="/page/"><img src="a.jpg" alt=""/></a><figcaption>Linked</figcaption></figure>',
				true,
				'<figure class="wp-block-image has-smart-media"><a href="/page/"><img decoding="async" src="a.jpg" alt="Linked"/></a><figcaption>Linked</figcaption></figure>',
			),
			'existing alt is never overwritten'                  => array(
				'<figure class="wp-block-image"><img src="a.jpg" alt="Author alt"/><figcaption>Caption</figcaption></figure>',
				true,
				'<figure class="wp-block-image has-smart-media"><img decoding="async" src="a.jpg" alt="Author alt"/><figcaption>Caption</figcaption></figure>',
			),
			'attribute off: alt stays empty'                     => array(
				'<figure class="wp-block-image"><img src="a.jpg" alt=""/><figcaption>Caption</figcaption></figure>',
				false,
				'<figure class="wp-block-image has-smart-media"><img decoding="async" src="a.jpg" alt=""/><figcaption>Caption</figcaption></figure>',
			),
			'no caption: alt stays empty'                        => array(
				'<figure class="wp-block-image"><img src="a.jpg" alt=""/></figure>',
				true,
				'<figure class="wp-block-image has-smart-media"><img decoding="async" src="a.jpg" alt=""/></figure>',
			),
		);
	}

	/**
	 * @dataProvider data_caption_as_alt
	 *
	 * @param string $input          Input HTML.
	 * @param bool   $caption_as_alt Option value.
	 * @param string $expected       Expected HTML.
	 */
	public function test_caption_as_alt( $input, $caption_as_alt, $expected ) {
		$actual = Smart_Media_Markup_Processor::process(
			$input,
			array(
				'caption_as_alt' => $caption_as_alt,
				'site_host'      => self::HOST,
			)
		);

		$this->assertSameMarkup( $expected, $actual );
	}

	/*
	 * ---------------------------------------------------------------------
	 * 3. has-smart-media on the outer <figure>
	 * ---------------------------------------------------------------------
	 */

	/**
	 * @return array[]
	 */
	public function data_figure_class() {
		return array(
			'figure without class'              => array(
				'<figure><img src="a.jpg" alt="x"/></figure>',
				'<figure class="has-smart-media"><img decoding="async" src="a.jpg" alt="x"/></figure>',
			),
			'class not duplicated'              => array(
				'<figure class="wp-block-image has-smart-media"><img src="a.jpg" alt="x" decoding="async"/></figure>',
				'<figure class="wp-block-image has-smart-media"><img src="a.jpg" alt="x" decoding="async"/></figure>',
			),
			'only the outer gallery figure'     => array(
				'<figure class="wp-block-gallery"><figure class="wp-block-image"><img src="1.jpg" alt="x" decoding="async"/></figure></figure>',
				'<figure class="wp-block-gallery has-smart-media"><figure class="wp-block-image"><img src="1.jpg" alt="x" decoding="async"/></figure></figure>',
			),
			'legacy aligned image wrapper'      => array(
				'<div class="wp-block-image"><figure class="aligncenter"><img src="a.jpg" alt="x" decoding="async"/></figure></div>',
				'<div class="wp-block-image"><figure class="aligncenter has-smart-media"><img src="a.jpg" alt="x" decoding="async"/></figure></div>',
			),
			'cover (no outer figure): untouched' => array(
				'<div class="wp-block-cover"><div class="wp-block-cover__inner-container"><figure class="wp-block-image"><img src="a.jpg" alt="x" decoding="async"/></figure></div></div>',
				'<div class="wp-block-cover"><div class="wp-block-cover__inner-container"><figure class="wp-block-image"><img src="a.jpg" alt="x" decoding="async"/></figure></div></div>',
			),
		);
	}

	/**
	 * @dataProvider data_figure_class
	 *
	 * @param string $input    Input HTML.
	 * @param string $expected Expected HTML.
	 */
	public function test_figure_class( $input, $expected ) {
		$this->assertSameMarkup( $expected, Smart_Media_Markup_Processor::process( $input, array( 'site_host' => self::HOST ) ) );
	}

	/*
	 * ---------------------------------------------------------------------
	 * 4. rel="noopener" on external links
	 * ---------------------------------------------------------------------
	 */

	/**
	 * @return array[]
	 */
	public function data_external_links() {
		$wrap = static function ( $a ) {
			return '<figure class="wp-block-image has-smart-media">' . $a . '<img src="a.jpg" alt="x" decoding="async"/></a></figure>';
		};

		return array(
			'external link gets noopener'         => array(
				$wrap( '<a href="https://wordpress.org/">' ),
				$wrap( '<a rel="noopener" href="https://wordpress.org/">' ),
			),
			'existing rel values are kept'        => array(
				$wrap( '<a href="https://wordpress.org/" rel="nofollow sponsored" target="_blank">' ),
				$wrap( '<a href="https://wordpress.org/" rel="nofollow sponsored noopener" target="_blank">' ),
			),
			'noopener not duplicated'             => array(
				$wrap( '<a href="https://wordpress.org/" rel="NoOpener">' ),
				$wrap( '<a href="https://wordpress.org/" rel="NoOpener">' ),
			),
			'protocol-relative external'          => array(
				$wrap( '<a href="//cdn.example.com/a.jpg">' ),
				$wrap( '<a rel="noopener" href="//cdn.example.com/a.jpg">' ),
			),
			'same host is internal'               => array(
				$wrap( '<a href="https://Example.org/page/">' ),
				$wrap( '<a href="https://Example.org/page/">' ),
			),
			'relative URL is internal'            => array(
				$wrap( '<a href="/wp-content/uploads/a.jpg">' ),
				$wrap( '<a href="/wp-content/uploads/a.jpg">' ),
			),
			'mailto is ignored'                   => array(
				$wrap( '<a href="mailto:hi@wordpress.org">' ),
				$wrap( '<a href="mailto:hi@wordpress.org">' ),
			),
		);
	}

	/**
	 * @dataProvider data_external_links
	 *
	 * @param string $input    Input HTML.
	 * @param string $expected Expected HTML.
	 */
	public function test_external_links( $input, $expected ) {
		$this->assertSameMarkup( $expected, Smart_Media_Markup_Processor::process( $input, array( 'site_host' => self::HOST ) ) );
	}

	/*
	 * ---------------------------------------------------------------------
	 * Integration: registration and render filters.
	 * ---------------------------------------------------------------------
	 */

	public function test_attribute_is_registered_on_core_image() {
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'core/image' );

		$this->assertInstanceOf( 'WP_Block_Type', $block_type );
		$this->assertArrayHasKey( 'smartMediaCaptionAsAlt', $block_type->attributes );
		$this->assertSame( 'boolean', $block_type->attributes['smartMediaCaptionAsAlt']['type'] );
		$this->assertFalse( $block_type->attributes['smartMediaCaptionAsAlt']['default'] );
	}

	public function test_render_block_image_filter() {
		$html   = '<figure class="wp-block-image"><a href="https://wordpress.org/"><img src="https://example.org/a.jpg" alt=""/></a><figcaption class="wp-element-caption">Hello world</figcaption></figure>';
		$output = render_block(
			array(
				'blockName'    => 'core/image',
				'attrs'        => array( 'smartMediaCaptionAsAlt' => true ),
				'innerBlocks'  => array(),
				'innerHTML'    => $html,
				'innerContent' => array( $html ),
			)
		);

		$p = new WP_HTML_Tag_Processor( $output );

		$this->assertTrue( $p->next_tag( 'figure' ) );
		$this->assertTrue( $p->has_class( 'has-smart-media' ) );

		$this->assertTrue( $p->next_tag( 'a' ) );
		$this->assertSame( 'noopener', $p->get_attribute( 'rel' ) );

		$this->assertTrue( $p->next_tag( 'img' ) );
		$this->assertSame( 'async', $p->get_attribute( 'decoding' ) );
		$this->assertSame( 'Hello world', $p->get_attribute( 'alt' ) );
	}

	public function test_saved_content_is_not_changed() {
		$html    = '<figure class="wp-block-image"><img src="a.jpg" alt=""/><figcaption>Caption</figcaption></figure>';
		$content = '<!-- wp:image {"smartMediaCaptionAsAlt":true} -->' . $html . '<!-- /wp:image -->';

		$post_id = self::factory()->post->create( array( 'post_content' => $content ) );
		do_blocks( get_post_field( 'post_content', $post_id ) );

		$this->assertSame( $content, get_post_field( 'post_content', $post_id, 'raw' ) );
	}

	public function test_caption_text_reader() {
		$this->assertSame(
			'A b & c',
			Smart_Media_Markup_Processor::get_caption_text( '<figure><img src="a.jpg"/><figcaption>  A <em>b</em> &amp; c </figcaption></figure>' )
		);
	}

	/*
	 * ---------------------------------------------------------------------
	 * Helpers.
	 * ---------------------------------------------------------------------
	 */

	/**
	 * Asserts two HTML strings are structurally equal.
	 *
	 * @param string $expected Expected HTML.
	 * @param string $actual   Actual HTML.
	 */
	private function assertSameMarkup( $expected, $actual ) {
		$this->assertSame( $this->tokenize( $expected ), $this->tokenize( $actual ) );
	}

	/**
	 * Serializes HTML into a canonical list of tokens using the HTML API.
	 *
	 * @param string $html HTML.
	 * @return string[]
	 */
	private function tokenize( $html ) {
		$p      = new WP_HTML_Tag_Processor( $html );
		$tokens = array();

		while ( $p->next_token() ) {
			$type = $p->get_token_type();

			if ( '#tag' === $type ) {
				if ( $p->is_tag_closer() ) {
					$tokens[] = '/' . $p->get_tag();
					continue;
				}

				$names = $p->get_attribute_names_with_prefix( '' );
				$names = is_array( $names ) ? $names : array();
				sort( $names );

				$attrs = array();
				foreach ( $names as $name ) {
					$value   = $p->get_attribute( $name );
					$attrs[] = $name . '=' . ( true === $value ? '' : wp_json_encode( $value ) );
				}

				$tokens[] = $p->get_tag() . ( $attrs ? ' ' . implode( ' ', $attrs ) : '' );
			} elseif ( '#text' === $type ) {
				$tokens[] = '#text:' . $p->get_modifiable_text();
			}
		}

		return $tokens;
	}
}
