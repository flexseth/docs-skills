<?php
/**
 * Fixture-based tests for Smart Media Markup.
 *
 * Each case in tests/fixtures/ has a `{name}.input.html` file (block HTML as
 * rendered by core) and a `{name}.expected.html` file (HTML sent to visitors).
 * The WordPress test suite uses http://example.org as home URL, so links to
 * example.org are internal and every other host is external.
 *
 * @package SmartMediaMarkup
 */

/**
 * Class Test_Smart_Media_Markup
 */
class Test_Smart_Media_Markup extends WP_UnitTestCase {

	/**
	 * Fixture cases: name => [ blockName, block attributes ].
	 *
	 * @return array
	 */
	public function data_fixtures() {
		return array(
			'1. async decoding added'           => array( 'image-decoding-async', 'core/image', array() ),
			'1. existing decoding kept'         => array( 'image-decoding-existing', 'core/image', array() ),
			'2. gallery wrapper class'          => array( 'gallery-wrapper-class', 'core/gallery', array() ),
			'1-3. cover block'                  => array( 'cover-block', 'core/cover', array() ),
			'3. external links get noopener'    => array( 'external-links', 'core/image', array() ),
			'4. caption used as alt'            => array( 'caption-alt-enabled', 'core/image', array( 'smartMediaCaptionAsAlt' => true ) ),
			'4. existing alt kept'              => array( 'caption-alt-existing-alt', 'core/image', array( 'smartMediaCaptionAsAlt' => true ) ),
			'4. toggle off leaves alt empty'    => array( 'caption-alt-disabled', 'core/image', array() ),
		);
	}

	/**
	 * Runs a fixture through the block filter.
	 *
	 * @dataProvider data_fixtures
	 *
	 * @param string $fixture    Fixture name.
	 * @param string $block_name Block name.
	 * @param array  $attrs      Block attributes.
	 */
	public function test_fixture( $fixture, $block_name, $attrs ) {
		$dir      = __DIR__ . '/fixtures/';
		$input    = trim( file_get_contents( $dir . $fixture . '.input.html' ) );
		$expected = trim( file_get_contents( $dir . $fixture . '.expected.html' ) );

		$block = array(
			'blockName'    => $block_name,
			'attrs'        => $attrs,
			'innerBlocks'  => array(),
			'innerHTML'    => $input,
			'innerContent' => array( $input ),
		);

		$this->assertSame( $expected, apply_filters( "render_block_{$block_name}", $input, $block, null ) );
	}

	/**
	 * Processing twice yields the same output (nested Gallery images are filtered twice).
	 *
	 * @dataProvider data_fixtures
	 *
	 * @param string $fixture Fixture name.
	 */
	public function test_idempotent( $fixture ) {
		$expected = trim( file_get_contents( __DIR__ . '/fixtures/' . $fixture . '.expected.html' ) );

		$this->assertSame( $expected, Smart_Media_Markup::process( $expected ) );
	}

	/**
	 * Saved post content is never modified; only the rendered output is.
	 */
	public function test_saved_content_unchanged() {
		$content = '<!-- wp:image {"id":12} --><figure class="wp-block-image"><img src="https://example.org/a.jpg" alt="" class="wp-image-12"/></figure><!-- /wp:image -->';
		$post_id = self::factory()->post->create( array( 'post_content' => $content ) );

		$rendered = apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) );

		$this->assertStringContainsString( 'has-smart-media', $rendered );
		$this->assertSame( $content, get_post_field( 'post_content', $post_id ) );
	}

	/**
	 * Caption text extraction strips tags and decodes entities.
	 */
	public function test_get_caption_text() {
		$html = '<figure><img src="x.jpg" alt=""/><figcaption>Tom <em>&amp;</em>  Jerry<br>at&nbsp;home</figcaption></figure>';

		$this->assertSame( "Tom & Jerry at\u{00A0}home", Smart_Media_Markup::get_caption_text( $html ) );
	}
}
