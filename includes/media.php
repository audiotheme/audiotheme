<?php
wp_oembed_add_provider( 'http://soundcloud.com/*', 'http://soundcloud.com/oembed' );
wp_oembed_add_provider( 'http://soundcloud.com/*/*', 'http://soundcloud.com/oembed' );
wp_oembed_add_provider( 'http://soundcloud.com/*/sets/*', 'http://soundcloud.com/oembed' );
wp_oembed_add_provider( 'http://soundcloud.com/groups/*', 'http://soundcloud.com/oembed' );
wp_oembed_add_provider( 'http://snd.sc/*', 'http://soundcloud.com/oembed' );
wp_oembed_add_provider( 'http://www.rdio.com/#artist/*album/*', 'http://www.rdio.com/api/oembed/' );
wp_oembed_add_provider( 'http://www.rdio.com/artist*/album/*', 'http://www.rdio.com/api/oembed/' );
wp_oembed_add_provider( 'http://rd.io/*', 'http://www.rdio.com/api/oembed/' );


/**
 * Filter oEmbed HTML
 *
 * Adds a wrapper to videos from the whitelisted services and attempts to add
 * the wmode parameter to YouTube videos and flash embeds.
 *
 * @since 1.0.0
 * @return string
 */
function audiotheme_oembed_html( $html, $url, $attr, $post_id ) {
	$players = array( 'youtube', 'vimeo', 'dailymotion', 'hulu', 'blip.tv', 'wordpress.tv', 'viddler', 'revision3' );
	
	foreach( $players as $player ) {
		if( false !== strpos( $url, $player ) ) {
			if ( false !== strpos( $url, 'youtube' ) && false !== strpos( $html, '<iframe' ) && false === strpos( $html, 'wmode' ) ) {
				$html = preg_replace_callback( '|https?://[^"]+|im', 'audiotheme_oembed_youtube_wmode_parameter', $html );
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

function audiotheme_oembed_youtube_wmode_parameter( $matches ) {
	return add_query_arg( 'wmode', 'transparent', $matches[0] );
}
?>