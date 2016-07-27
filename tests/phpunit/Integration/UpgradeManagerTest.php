<?php
namespace AudioTheme\Test\Integration;

use AudioTheme_UpgradeManager;

class UpgradeManagerTest extends \WP_UnitTestCase {
	public function setUp() {
		parent::setUp();

		$this->classname = 'AudioTheme_UpgradeManager';
	}

	public function test_copy_venue_ids_to_gig_meta() {
		$gig_id = $this->factory->post->create( [
			'post_title' => 'Gig',
			'post_type'  => 'audiotheme_gig',
		] );

		$venue_id = $this->factory->post->create( [
			'post_title' => 'Venue',
			'post_type'  => 'audiotheme_venue',
		] );

		$venue_guid = get_post( $venue_id )->guid;

		p2p_create_connection(
			'audiotheme_venue_to_gig',
			array(
				'from' => $venue_id,
				'to'   => $gig_id,
			)
		);

		$method = new \ReflectionMethod( $this->classname, 'upgrade_200' );
		$method->setAccessible( true );
		$manager = $method->invoke( new $this->classname );

		$this->assertEquals( $venue_id, get_post_meta( $gig_id, '_audiotheme_venue_id', true ) );
		$this->assertEquals( $venue_guid, get_post_meta( $gig_id, '_audiotheme_venue_guid', true ) );
	}
}
