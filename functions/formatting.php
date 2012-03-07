<?php
/**
 * Text formatting functions.
 *
 * @package Genesis
 */

/**
 * Return a phrase shortened in length to a maximum number of characters.
 *
 * Result will be truncated at the last white space in the original
 * string. In this function the word separator is a single space (' ').
 * Other white space characters (like newlines and tabs) are ignored.
 *
 * If the first $max_characters of the string do contain a space
 * character, an empty string will be returned.
 *
 * @since 1.4
 *
 * @param string $phrase A string to be shortened.
 * @param integer $max_characters The maximum number of characters to return.
 * @return string
 */
function genesis_truncate_phrase($phrase, $max_characters) {

	$phrase = trim( $phrase );

	if ( strlen($phrase) > $max_characters ) {

		// Truncate $phrase to $max_characters + 1
		$phrase = substr($phrase, 0, $max_characters + 1);

		// Truncate to the last space in the truncated string.
		$phrase = trim(substr($phrase, 0, strrpos($phrase, ' ')));
	}

	return $phrase;
}

/**
 * This function strips out tags and shortcodes,
 * limits the output to $max_char characters,
 * and appends an ellipses and more link to the end.
 *
 * @since 0.1
 */
function get_the_content_limit($max_char, $more_link_text = '(more...)', $stripteaser = 0) {

	$content = get_the_content('', $stripteaser);

	// Strip tags and shortcodes
	$content = strip_tags(strip_shortcodes($content), apply_filters('get_the_content_limit_allowedtags', '<script>,<style>'));

	// Inline styles/scripts
	$content = trim(preg_replace('#<(s(cript|tyle)).*?</\1>#si', '', $content));

	// Truncate $content to $max_char
	$content = genesis_truncate_phrase($content, $max_char);

	// More Link?
	if ( $more_link_text ) {
		$link = apply_filters( 'get_the_content_more_link', sprintf( '%s <a href="%s" class="more-link">%s</a>', g_ent('&hellip;'), get_permalink(), $more_link_text ), $more_link_text );

		$output = sprintf('<p>%s %s</p>', $content, $link);
	}
	else {
		$output = sprintf('<p>%s</p>', $content);
	}

	return apply_filters('get_the_content_limit', $output, $content, $link, $max_char);

}
function the_content_limit($max_char, $more_link_text = '(more...)', $stripteaser = 0) {

	$content = get_the_content_limit($max_char, $more_link_text, $stripteaser);
	echo apply_filters('the_content_limit', $content);

}

/**
 *
 */
function genesis_rel_nofollow($xhtml) {
	$xhtml = genesis_strip_attr($xhtml, array('a'), array('rel'));
	$xhtml = stripslashes(wp_rel_nofollow($xhtml));

	return $xhtml;
}

/**
 * This function accepts a string of xHTML, parses it for any elements in the
 * $elements array, then parses that element for any attributes in the $attributes
 * array, and strips the attribute and its value(s).
 *
 * @author Charles Clarkson
 * @link http://studiopress.com/support/showthread.php?t=20633
 *
 * @example genesis_strip_attr('<a class="class" href="http://google.com/">Google</a>', array('a'), array('class'));
 *
 * @param string $xhtml A string of xHTML formatted code
 * @param array|string $elements Elements that $attributes should be stripped from
 * @param array|string $attributes Attributes that should be stripped from $elements
 * @param bool $two_passes Whether the function should allow two passes
 *
 * @return string
 *
 * @since 1.0
 */
function genesis_strip_attr($xhtml, $elements, $attributes, $two_passes = true) {

	// Cache elements pattern
	$elements_pattern = join('|', $elements);

	// Build patterns
	$patterns = array();
	foreach ( (array) $attributes as $attribute ) {

		// Opening tags
		$patterns[] = sprintf('~(<(?:%s)[^>]*)\s+%s=[\\\'"][^\\\'"]+[\\\'"]([^>]*[^>]*>)~', $elements_pattern, $attribute);

		// Self closing tags
		$patterns[] = sprintf('~(<(?:%s)[^>]*)\s+%s=[\\\'"][^\\\'"]+[\\\'"]([^>]*[^/]+/>)~', $elements_pattern, $attribute);

	}

	// First pass
	$xhtml = preg_replace($patterns, '$1$2', $xhtml);

	if ( $two_passes ) // Second pass
		$xhtml = preg_replace($patterns, '$1$2', $xhtml);

	return $xhtml;

}

/**
 * This function takes the content of a tweet, detects @replies,
 * #hashtags, and http://links, and links them appropriately.
 *
 * @author Snipe.net
 * @link http://www.snipe.net/2009/09/php-twitter-clickable-links/
 *
 * @param string $tweet A string representing the content of a tweet
 *
 * @return string
 *
 * @since 1.1
 */
function genesis_tweet_linkify($tweet) {

	$tweet = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $tweet);
	$tweet = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $tweet);
	$tweet = preg_replace("/@(\w+)/", "<a href=\"http://www.twitter.com/\\1\" target=\"_blank\">@\\1</a>", $tweet);
	$tweet = preg_replace("/#(\w+)/", "<a href=\"http://search.twitter.com/search?q=\\1\" target=\"_blank\">#\\1</a>", $tweet);

	return $tweet;

}

/**
 * This is a helper function. It passes text through the g_ent filter
 * so that entities can be converted on-the-fly.
 *
 * @since 1.5
 */
function g_ent( $text = '' ) {

	return apply_filters('g_ent', $text);

}

/**
 * This function returns an array of allowed tags for output formatting.
 * Mainly used by wp_kses() for sanitizing output.
 *
 * @since 1.6
 */
function genesis_formatting_allowedtags() {

	return apply_filters( 'genesis_formatting_allowedtags', array(
		//	<p>, <span>, <div>
		'p' => array( 'align' => array(), 'class' => array(), 'style' => array() ),
		'span' => array( 'align' => array(), 'class' => array(), 'style' => array() ),
		'div' => array( 'align' => array(), 'class' => array(), 'style' => array() ),

		// <img src="" class="" alt="" title="" width="" height="" />
		//'img' => array( 'src' => array(), 'class' => array(), 'alt' => array(), 'width' => array(), 'height' => array(), 'style' => array() ),

		//	<a href="" title="">Text</a>
		'a' => array( 'href' => array(), 'title' => array() ),

		//	<b>, </i>, <em>, <strong>
		'b' => array(), 'strong' => array(),
		'i' => array(), 'em' => array(),

		//	<blockquote>, <br />
		'blockquote' => array(),
		'br' => array()
	) );

}

/**
 * Calculate the time difference - a replacement for human_time_diff() until it
 * is improved.
 *
 * Based on BuddyPress function bp_core_time_since(), which in turn is based on
 * functions created by Dunstan Orchard - http://1976design.com
 *
 * This function will return an text representation of the time elapsed since a
 * given date, giving the two largest units e.g.:
 *  - 2 hours and 50 minutes
 *  - 4 days
 *  - 4 weeks and 6 days
 *
 * @since 1.7
 *
 * @param $older_date int Unix timestamp of date you want to calculate the time since for
 * @param $newer_date int Unix timestamp of date to compare older date to. Default false (current time)
 * @return str The time difference
 */
function genesis_human_time_diff( $older_date, $newer_date = false ) {

	// If no newer date is given, assume now
	$newer_date = $newer_date ? $newer_date : time();

	// Difference in seconds
	$since = absint( $newer_date - $older_date );

	if ( ! $since )
		return '0 ' . _x( 'seconds', 'time difference', 'genesis' );

	// Hold units of time in seconds, and their pluralised strings (not translated yet)
	$units = array(
		array( 31536000, _nx_noop( '%s year', '%s years', 'time difference' ) ),  // 60 * 60 * 24 * 365
		array( 2592000, _nx_noop( '%s month', '%s months', 'time difference' ) ), // 60 * 60 * 24 * 30
		array( 604800, _nx_noop( '%s week', '%s weeks', 'time difference' ) ),    // 60 * 60 * 24 * 7
		array( 86400, _nx_noop( '%s day', '%s days', 'time difference' ) ),       // 60 * 60 * 24
		array( 3600, _nx_noop( '%s hour', '%s hours', 'time difference' ) ),      // 60 * 60
		array( 60, _nx_noop( '%s minute', '%s minutes', 'time difference' ) ),
		array( 1, _nx_noop( '%s second', '%s seconds', 'time difference' ) ),
	);

	// Step one: the first unit
	for ( $i = 0, $j = count( $units ); $i < $j; $i++ ) {
		$seconds = $units[$i][0];

		// Finding the biggest chunk (if the chunk fits, break)
		if ( ( $count = floor( $since / $seconds ) ) != 0 )
			break;
	}

	// Translate unit string, and add to the output
	$output = sprintf( translate_nooped_plural( $units[$i][1], $count, 'genesis' ), $count );

	// Note the next unit
	$ii = $i + 1;

	// Step two: the second unit
	if ( $ii <= $j ) {
		$seconds2 = $units[$ii][0];

		// Check if this second unit has a value > 0
		if ( ( $count2 = floor( ( $since - ( $seconds * $count ) ) / $seconds2 ) ) != 0 )

			// Add translated separator string, and translated unit string
			$output .= sprintf( ' %s ' . translate_nooped_plural( $units[$ii][1], $count2, 'genesis' ),	_x( 'and', 'separator in time difference', 'genesis' ),	$count2	);

	}

	return $output;

}