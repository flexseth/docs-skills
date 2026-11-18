<?php
/**
 * Fixture-based tests for Smart Media Markup.
 *
 * Each case pairs tests/fixtures/{case}.input.html with
 * tests/fixtures/{case}.expected.html.
 *
 * @package SmartMediaMarkup
 */

use Smart_Media_Markup\Markup_Processor;
use Smart_Media_Markup\Plugin;

/**
 * Tests the rendered-markup transformations.
 */
class Test_Smart_Media_Markup extends WP_UnitTestCase {

	/**
	 * Uses https://mysite.test as the site URL so fixtures are predictable.
	 */
	public function set_up() {
		parent::set_up();

		add_filter( 'pre_option_home', array( $this, 'site_url' ) );
		add_filter( 'pre_option_siteurl', array( $this, 'site_url' ) );
	}

	/**
	 * Site URL used by the fixtures.
	 *
	 * @return string
	 */
	public function site_url() {
		return 'https://mysite.test';
	}

	/**
	 * Fixture cases: case name => [ block name, block attributes ].
	 *
	 * @return array
	 */
	public function data_fixtures() {
		return array(
			'1. async decoding added'               => array( 'decoding-async', 'core/image', array() ),
			'1. existing decoding kept'             => array( 'decoding-existing', 'core/image', array() ),
			'2. outer class on gallery'             => array( 'outer-class-gallery', 'core/gallery', array() ),
			'1-3. cover block'                      => array( 'cover', 'core/cover', array() ),
			'3. external links get noopener'        => array( 'external-links', 'core/image', array() ),
			'4. caption becomes alt'                => array( 'caption-alt', 'core/image', array( 'smartMediaCaptionAlt' => true ) ),
			'4. existing alt is kept'               => array( 'caption-alt-existing-alt', 'core/image', array( 'smartMediaCaptionAlt' => true ) ),
			'4. toggle off leaves alt empty'        => array( 'caption-alt-disabled', 'core/image', array() ),
		);
	}

	/**
	 * Runs a fixture through the render filter.
	 *
	 * @dataProvider data_fixtures
	 *
	 * @param string $fixture    Fixture name.
	 * @param string $block_name Block name.
	 * @param array  $attrs      Block attributes.
	 */
	public function test_fixture( $fixture, $block_name, $attrs ) {
		$dir      = __DIR__ . '/fixtures/';
		$input    = file_get_contents( $dir . $fixture . '.input.html' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$expected = file_get_contents( $dir . $fixture . '.expected.html' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		$actual = Plugin::filter_block_output(
			$input,
			array(
				'blockName' => $block_name,
				'attrs'     => $attrs,
			)
		);

		$this->assertSame( trim( $expected ), trim( $actual ) );
	}

	/**
	 * Processing twice gives the same result (Gallery re-processes inner Images).
	 */
	public function test_processing_is_idempotent() {
		$input = file_get_contents( __DIR__ . '/fixtures/external-links.input.html' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$once  = Markup_Processor::process( $input );

		$this->assertSame( $once, Markup_Processor::process( $once ) );
	}

	/**
	 * Front-end rendering changes, stored post content does not.
	 */
	public function test_saved_content_is_unchanged() {
		$content = '<!-- wp:image {"smartMediaCaptionAlt":true} -->' . "\n"
			. '<figure class="wp-block-image"><a href="https://example.com/"><img src="https://mysite.test/cat.jpg" alt=""/></a><figcaption class="wp-element-caption">A cat</figcaption></figure>' . "\n"
			. '<!-- /wp:image -->';

		$post_id  = self::factory()->post->create( array( 'post_content' => $content ) );
		$rendered = do_blocks( get_post( $post_id )->post_content );

		$this->assertStringContainsString( 'has-smart-media', $rendered );
		$this->assertStringContainsString( 'decoding="async"', $rendered );
		$this->assertStringContainsString( 'alt="A cat"', $rendered );
		$this->assertStringContainsString( 'rel="noopener"', $rendered );
		$this->assertSame( $content, get_post( $post_id )->post_content );
	}
}
