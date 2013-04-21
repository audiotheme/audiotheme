<?php
/**
 * AudioTheme API for working with media and defines filters for modifying
 * WordPress behavior related to media.
 *
 * @package AudioTheme_Framework
 */

/**
 * Register custom oEmbed providers.
 *
 * Post content is filtered on display, so limited services should be
 * supported by default.
 *
 * @since 1.0.0
 * @link http://core.trac.wordpress.org/ticket/15734
 * @link http://core.trac.wordpress.org/ticket/21635#comment:8
 */
function audiotheme_add_default_oembed_providers() {
	#wp_oembed_add_provider( 'http://snd.sc/*', 'http://soundcloud.com/oembed' );
	#wp_oembed_add_provider( 'http://www.rdio.com/#artist/*album/*', 'http://www.rdio.com/api/oembed/' );
	#wp_oembed_add_provider( 'http://rd.io/*', 'http://www.rdio.com/api/oembed/' );
}

/**
 * Add an HTML wrapper to certain videos retrieved via oEmbed.
 *
 * The wrapper is useful as a styling hook and for responsive designs. Also
 * attempts to add the wmode parameter to YouTube videos and flash embeds.
 *
 * @since 1.0.0
 *
 * @param string $html HTML.
 * @param string $url oEmbed URL.
 * @param array $attr Embed attributes.
 * @param int $post_id Post ID.
 * @return string Embed HTML with wrapper.
 */
function audiotheme_oembed_html( $html, $url = null, $attr = null, $post_id = null ) {
	$wrapped = '<div class="audiotheme-video">' . $html . '</div>';

    if ( empty( $url ) && 'video_embed_html' == current_filter() ) { // Jetpack
        $html = $wrapped;
    } elseif ( ! empty( $url ) ) {
        $players = array( 'youtube', 'youtu.be', 'vimeo', 'dailymotion', 'hulu', 'blip.tv', 'wordpress.tv', 'viddler', 'revision3' );

        foreach ( $players as $player ) {
            if ( false !== strpos( $url, $player ) ) {
                if ( false !== strpos( $url, 'youtube' ) && false !== strpos( $html, '<iframe' ) && false === strpos( $html, 'wmode' ) ) {
                    $html = preg_replace_callback( '|https?://[^"]+|im', '_audiotheme_oembed_youtube_wmode_parameter', $html );
                }

                $html = $wrapped;
                break;
            }
        }
    }

    if ( false !== strpos( $html, '<embed' ) && false === strpos( $html, 'wmode' ) ) {
        $html = str_replace( '</param><embed', '</param><param name="wmode" value="opaque"></param><embed wmode="opaque"', $html );
    }

	return $html;
}

/**
 * Private callback.
 *
 * Adds wmode=transparent query argument to oEmbedded YouTube videos.
 *
 * @since 1.0.0
 * @access private
 *
 * @param array $matches
 * @return string
 */
function _audiotheme_oembed_youtube_wmode_parameter( $matches ) {
	return add_query_arg( 'wmode', 'transparent', $matches[0] );
}
