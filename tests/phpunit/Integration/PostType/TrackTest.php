<?php
namespace AudioTheme\Test\Integration\PostType;;

class TrackTest extends \WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function test_guid_is_uuid() {
		$post_id = $this->factory->post->create( [
			'post_title' => 'Track',
			'post_type'  => 'audiotheme_track',
		] );

		$this->assertStringStartsWith( 'urn:', get_post( $post_id )->guid );
	}
}
