<?php
class AudioThemeFunctionsTest extends WP_UnitTestCase {
	public function testShortcodeBool() {
		$this->assertFalse( audiotheme_shortcode_bool( 'false' ) );
		$this->assertFalse( audiotheme_shortcode_bool( '0' ) );
		$this->assertFalse( audiotheme_shortcode_bool( 'n' ) );
		$this->assertFalse( audiotheme_shortcode_bool( 'no' ) );
	}
}
