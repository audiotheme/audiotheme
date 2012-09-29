<?php
/**
 * Custom oEmbed Providers
 *
 * Post content is filtered on display, so limited services should be
 * supported by default.
 *
 * @since 1.0.0
 * @todo SoundCloud can be dropped when 3.4 support is dropped.
 * @link http://core.trac.wordpress.org/ticket/15734
 * @link http://core.trac.wordpress.org/ticket/21635#comment:8
 */
function audiotheme_add_default_oembed_providers() {
	if ( version_compare( get_bloginfo( 'version' ), '3.5-beta-1', '<' ) )
		wp_oembed_add_provider( '#https?://(www\.)?soundcloud\.com/.*#i', 'http://soundcloud.com/oembed', true );
	
	#wp_oembed_add_provider( 'http://snd.sc/*', 'http://soundcloud.com/oembed' );
	#wp_oembed_add_provider( 'http://www.rdio.com/#artist/*album/*', 'http://www.rdio.com/api/oembed/' );
	#wp_oembed_add_provider( 'http://rd.io/*', 'http://www.rdio.com/api/oembed/' );
}

/**
 * Filter oEmbed HTML
 *
 * Adds a wrapper to videos from the whitelisted services and attempts to add
 * the wmode parameter to YouTube videos and flash embeds.
 *
 * @since 1.0.0
 * @todo Remove the preg_replace_callback() when WP 3.5 support is dropped and
 *       use the filter introduced in ticket 16996.
 * @link http://core.trac.wordpress.org/ticket/16996
 * 
 * @return string
 */
function audiotheme_oembed_html( $html, $url, $attr, $post_id ) {
	$players = array( 'youtube', 'vimeo', 'dailymotion', 'hulu', 'blip.tv', 'wordpress.tv', 'viddler', 'revision3' );
	
	foreach( $players as $player ) {
		if( false !== strpos( $url, $player ) ) {
			if ( false !== strpos( $url, 'youtube' ) && false !== strpos( $html, '<iframe' ) && false === strpos( $html, 'wmode' ) ) {
				$html = preg_replace_callback( '|https?://[^"]+|im', '_audiotheme_oembed_youtube_wmode_parameter', $html );
			}
		
			$html = '<div class="audiotheme-video">' . $html . '</div>';
			break;
		}
	}
	
	if ( false !== strpos( $html, '<embed' ) && false === strpos( $html, 'wmode' ) ) {
		$html = str_replace( '</param><embed', '</param><param name="wmode" value="opaque"></param><embed wmode="opaque"', $html );
	}
	
	return $html;
}

/**
 * Private Callback
 *
 * Adds wmode=transparent query argument to oEmbedded YouTube videos.
 *
 * @since 1.0.0
 */
function _audiotheme_oembed_youtube_wmode_parameter( $matches ) {
	return add_query_arg( 'wmode', 'transparent', $matches[0] );
}
?>