<?php
namespace AudioTheme\Test\Integration\PostType;;

class GigTest extends \WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function test_guid_is_uuid() {
		$post_id = $this->factory->post->create( [
			'post_title' => 'Gig',
			'post_type'  => 'audiotheme_gig',
		] );

		$this->assertStringStartsWith( 'urn:', get_post( $post_id )->guid );
	}

	public function test_venue_guid_synced_to_gig_meta() {
		$gig_id = $this->factory->post->create( [
			'post_title' => 'Gig',
			'post_type'  => 'audiotheme_gig',
		] );

		$venue_id = $this->factory->post->create( [
			'post_title' => 'Venue',
			'post_type'  => 'audiotheme_venue',
		] );

		update_post_meta( $gig_id, '_audiotheme_venue_id', $venue_id );

		$venue_guid = get_post( $venue_id )->guid;
		$gig_meta = get_post_meta( $gig_id, '_audiotheme_venue_guid', true );

		$this->assertSame( $venue_guid, $gig_meta );
	}
}
