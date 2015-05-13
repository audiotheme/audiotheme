<?php
/**
 * Generic utility functions.
 *
 * @package AudioTheme_Framework
 */

/**
 * Get localized image size names.
 *
 * The 'image_size_names_choose' filter exists in core and should be
 * hooked by plugin authors to provide localized labels for custom image
 * sizes added using add_image_size().
 *
 * @see image_size_input_fields()
 * @see https://core.trac.wordpress.org/ticket/20663
 *
 * @since 1.0.0
 *
 * @return array
 */
function audiotheme_image_size_names() {
	return apply_filters( 'image_size_names_choose', array(
		'thumbnail' => __( 'Thumbnail', 'audiotheme' ),
		'medium'    => __( 'Medium', 'audiotheme' ),
		'large'     => __( 'Large', 'audiotheme' ),
		'full'      => __( 'Full Size', 'audiotheme' ),
	) );
}

/**
 * Compare two version numbers.
 *
 * This function abstracts the logic for determining the current version
 * number for various packages, so the only version number that needs to be
 * known is the one to compare against.
 *
 * Basically serves as a wrapper for the native PHP version_compare()
 * function, but allows a known package to be passed as the first parameter.
 *
 * @since 1.0.0
 * @see PHP docs for version_compare()
 * @uses version_compare()
 *
 * @param string $version A package identifier or version number to compare against.
 * @param string $version2 The version number to compare to.
 * @param string $operator Optional. Relationship to test. <, lt, <=, le, >, gt, >=, ge, ==, =, eq, !=, <>, ne
 * @return mixed True or false if operator is supplied. -1, 0, or 1 if operator is empty.
 */
function audiotheme_version_compare( $version, $version2, $operator = null ) {
	switch ( $version ) {
		case 'audiotheme' :
			$version = AUDIOTHEME_VERSION;
			break;
		case 'php' :
			$version = phpversion();
			break;
		case 'stylesheet' : // Child theme if it exists, otherwise same as template.
			$theme = wp_get_theme();
			$version = $theme->get( 'Version' );
			break;
		case 'template' : // Parent theme.
			$theme = wp_get_theme( get_template() );
			$version = $theme->get( 'Version' );
			break;
		case 'wp' :
			$version = get_bloginfo( 'version' );
			break;
	}

	return version_compare( $version, $version2, $operator );
}

/**
 * Sort an array of objects by an objects properties.
 *
 * Ex: sort_objects( $gigs, array( 'venue', 'name' ), 'asc', true, 'gig_datetime' );
 *
 * @since 1.0.0
 * @uses Audiotheme_Sort_Objects
 *
 * @param array $objects An array of objects to sort.
 * @param string $orderby The object property to sort on.
 * @param string $order The sort order; ASC or DESC.
 * @param bool $unique Optional. If the objects have an ID property, it will be used for the array keys, thus they'll unique. Defaults to true.
 * @param string $fallback Optional. Comma-delimited string of properties to sort on if $orderby property is equal.
 * @return array The array of sorted objects.
 */
function audiotheme_sort_objects( $objects, $orderby, $order = 'ASC', $unique = true, $fallback = null ) {
	if ( ! is_array( $objects ) ) {
		return false;
	}

	usort( $objects, array( new Audiotheme_Sort_Objects( $orderby, $order, $fallback ), 'sort' ) );

	// Use object ids as the array keys.
	if ( $unique && count( $objects ) && isset( $objects[0]->ID ) ) {
		$objects = array_combine( wp_list_pluck( $objects, 'ID' ), $objects );
	}

	return $objects;
}

/**
 * Object list sorting class.
 *
 * @since 1.0.0
 * @access private
 */
class Audiotheme_Sort_Objects {
	var $fallback, $order, $orderby;

	// Fallback is limited to working with properties of the parent object.
	function __construct( $orderby, $order, $fallback = null ) {
		$this->order = ( 'desc' === strtolower( $order ) ) ? 'DESC' : 'ASC';
		$this->orderby = $orderby;
		$this->fallback = $fallback;
	}

	function sort( $a, $b ) {
		if ( is_string( $this->orderby ) ) {
			$a_value = $a->{$this->orderby};
			$b_value = $b->{$this->orderby};
		} elseif ( is_array( $this->orderby ) ) {
			$a_value = $a;
			$b_value = $b;

			foreach ( $this->orderby as $prop ) {
				$a_value = ( isset( $a_value->$prop ) ) ? $a_value->$prop : '';
				$b_value = ( isset( $b_value->$prop ) ) ? $b_value->$prop : '';
			}
		}

		if ( $a_value === $b_value ) {
			if ( ! empty( $this->fallback ) ) {
				$properties = explode( ',', $this->fallback );
				foreach ( $properties as $prop ) {
					if ( $a->$prop !== $b->$prop ) {
						#printf( '(%s - %s) - (%s - %s)<br>', $a_value, $a->$prop, $b_value, $b->$prop );
						return $this->compare( $a->$prop, $b->$prop );
					}
				}
			}

			return 0;
		}

		return $this->compare( $a_value, $b_value );
	}

	function compare( $a, $b ) {
		if ( $a < $b ) {
			return ( 'ASC' === $this->order ) ? -1 : 1;
		} else {
			return ( 'ASC' === $this->order ) ? 1 : -1;
		}
	}
}

/**
 * Gives a nicely formatted list of timezone strings.
 *
 * Strips the manual offsets from the default WordPress list.
 *
 * @since 1.0.0
 * @uses wp_timezone_choice()
 *
 * @param string $selected_zone Selected Zone.
 * @return string
 */
function audiotheme_timezone_choice( $selected_zone = null ) {
	$selected = ( empty( $selected_zone ) ) ? get_option( 'timezone_string' ) : $selected_zone;
	$choices = wp_timezone_choice( $selected );

	// Remove the manual offsets optgroup.
	$pos = strrpos( $choices, '<optgroup' );
	if ( false !== $pos ) {
		$choices = substr( $choices, 0, $pos );
	}

	return apply_filters( 'audiotheme_timezone_dropdown', $choices, $selected );
}

/**
 * Display a variable for debugging.
 *
 * @since 1.0.0
 *
 * @param mixed $var
 */
if ( ! function_exists( 'vd' ) ) :
	function vd( $var ) {
		echo '<pre style="font-size: 12px; text-align: left">' . print_r( $var, true ) . '</pre>';
	}
endif;

/**
 * Remove a portion of an associative array, optionally replace it with something else
 * and maintain the keys.
 *
 * Can produce unexpected behavior with numeric indexes. Use array_splice() if
 * keys don't need to be preserved, although exact behavior of offset and
 * length is not duplicated.
 *
 * @since 1.0.0
 *
 * @version 1.0.0
 * @see array_splice()
 *
 * @param array $input The input array.
 * @param int $offset The position to start from.
 * @param int $length Optional. The number of elements to remove. Defaults to 0.
 * @param mixed $replacement Optional. Item(s) to replace removed elements.
 * @param string $primary Optiona. input|replacement Defaults to input. Which array should take precedence if there is a key collision.
 * @return array The modified array.
 */
function audiotheme_array_asplice( $input, $offset, $length = 0, $replacement = null, $primary = 'input' ) {
	$input = (array) $input;
	$replacement = (array) $replacement;

	$start = array_slice( $input, 0, $offset, true );
	// $remove = array_slice( $input, $offset, $length, true );
	$end = array_slice( $input, $offset + $length, null, true );

	// Discard elements in $replacement whose keys match keys in $input.
	if ( 'input' === $primary ) {
		$replacement = array_diff_key( $replacement, $input );
	}

	// Discard elements in $start and $end whose keys match keys in $replacement.
	// Could change the size of $input, so this is done after slicing the start and end.
	elseif ( 'replacement' === $primary ) {
		$start = array_diff_key( $start, $replacement );
		$end = array_diff_key( $end, $replacement );
	}

	// Which is faster?
	// return $start + $replacement + $end;
	return array_merge( $start, $replacement, $end );
}

/**
 * Insert an element(s) after a particular value if it exists in an array.
 *
 * @since 1.0.0
 *
 * @version  1.0.0
 * @uses audiotheme_array_find()
 * @uses audiotheme_array_asplice()
 *
 * @param array $input The input array.
 * @param mixed $needle Value to insert new elements after.
 * @param mixed $insert The element(s) to insert.
 * @return array|bool Modified array or false if $needle couldn't be found.
 */
function audiotheme_array_insert_after( $input, $needle, $insert ) {
	$input = (array) $input;
	$insert = (array) $insert;

	$position = audiotheme_array_find( $needle, $input );
	if ( false === $position ) {
		return false;
	}

	return audiotheme_array_asplice( $input, $position + 1, 0, $insert );
}

/**
 * Insert an element(s) after a certain key if it exists in an array.
 *
 * Use array_splice() if keys don't need to be maintained.
 *
 * @since 1.0.0
 *
 * @version 1.0.0
 * @uses audiotheme_array_key_find()
 * @uses audiotheme_array_asplice()
 *
 * @param array $input The input array.
 * @param mixed $needle Value to insert new elements after.
 * @param mixed $insert The element(s) to insert.
 * @return array|bool Modified array or false if $needle couldn't be found.
 */
function audiotheme_array_insert_after_key( $input, $needle, $insert ) {
	$input = (array) $input;
	$insert = (array) $insert;

	$position = audiotheme_array_key_find( $needle, $input );
	if ( false === $position ) {
		return false;
	}

	return audiotheme_array_asplice( $input, $position + 1, 0, $insert );
}

/**
 * Find the position (not index) of a value in an array.
 *
 * @since 1.0.0
 *
 * @version 1.0.0
 * @see array_search()
 * @uses audiotheme_array_key_find()
 *
 * @param mixed $needle The value to search for.
 * @param array $haystack The array to search.
 * @param bool $strict Whether to search for identical (types) values.
 * @return int|bool Position of the first matching element or false if not found.
 */
function audiotheme_array_find( $needle, $haystack, $strict = false ) {
	if ( ! is_array( $haystack ) ) {
		return false;
	}

	$key = array_search( $needle, $haystack, $strict );

	return ( $key ) ? audiotheme_array_key_find( $key, $haystack ) : false;
}

/**
 * Find the position (not index) of a key in an array.
 *
 * @since 1.0.0
 *
 * @version 1.0.0
 * @see array_key_exists()
 *
 * @param $key string|int The key to search for.
 * @param $search The array to search.
 * @return int|bool Position of the key or false if not found.
 */
function audiotheme_array_key_find( $key, $search ) {
	$key = ( is_int( $key ) ) ? $key : (string) $key;

	if ( ! is_array( $search ) ) {
		return false;
	}

	$keys = array_keys( $search );

	return array_search( $key, $keys );
}

/**
 * Use an ordered array to sort another array (the $order array values match
 * $input's keys).
 *
 * @since 1.0.0
 *
 * @version 1.0.1
 *
 * @param array $array The array to sort.
 * @param array $order Array used for sorting. Values should match keys in $array.
 * @param string $keep_diff Optional. Whether to keep the difference of the two arrays if they don't exactly match and where to place the difference.
 * @param string $diff_sort Optional. @todo Implement.
 * @return array The sorted array.
 */
function audiotheme_array_sort_array( $array, $order, $keep_diff = 'bottom', $diff_sort = 'stable' ) {
	$order = array_flip( $order );

	// The difference should be tacked back on after sorting.
	if ( 'discard' !== $keep_diff ) {
		$diff = array_diff_key( $array, $order );
	}

	$sorted = array();
	foreach ( $order as $key => $val ) {
		$sorted[ $key ] = $array[ $key ];
	}

	if ( 'discard' !== $keep_diff ) {
		$sorted = ( 'top' === $keep_diff ) ? $diff + $sorted : $sorted + $diff;
	}

	return $sorted;
}

/**
* Helper function to determine if a shortcode attribute is true or false.
*
* @since 1.0.0
*
* @param string|int|bool $var Attribute value.
* @return bool
*/
function audiotheme_shortcode_bool( $var ) {
	$falsey = array( 'false', '0', 'no', 'n' );
	return ( ! $var || in_array( strtolower( $var ), $falsey ) ) ? false : true;
}

/**
 * Return a base64 encoded SVG icon for use as a data URI.
 *
 * @since 1.4.3
 *
 * @param string $path Path to SVG icon.
 * @return string
 */
function audiotheme_encode_svg( $path ) {
	$path = path_is_absolute( $path ) ? $path : AUDIOTHEME_DIR . $path;

	if ( ! file_exists( $path ) || 'svg' !== pathinfo( $path, PATHINFO_EXTENSION ) ) {
		return '';
	}

	return 'data:image/svg+xml;base64,' . base64_encode( file_get_contents( $path ) );
}

/**
 * Encode the path portion of a URL.
 *
 * Spaces in directory or filenames are stripped by esc_url() and can cause issues when requesting a URL programmatically. This method encodes spaces and other characters.
 *
 * @since 1.4.4
 *
 * @param string $url A URL.
 * @return string
 */
function audiotheme_encode_url_path( $url ) {
	$parts = parse_url( $url );

	$return  = isset( $parts['scheme'] ) ? $parts['scheme'] . '://' : '';
	$return .= isset( $parts['host'] ) ? $parts['host'] : '';
	$return .= isset( $parts['port'] ) ? ':' . $parts['port'] : '';
	$user = isset( $parts['user'] ) ? $parts['user'] : '';
	$pass = isset( $parts['pass'] ) ? ':' . $parts['pass']  : '';
	$return .= ( $user || $pass ) ? "$pass@" : '';

	if ( isset( $parts['path'] ) ) {
		$path = implode( '/', array_map( 'rawurlencode', explode( '/', $parts['path'] ) ) );
		$return .= $path;
	}

	$return .= isset( $parts['query'] ) ? '?' . $parts['query'] : '';
	$return .= isset( $parts['fragment'] ) ? '#' . $parts['fragment'] : '';

	return $return;
}

/**
 * Return key value pairs with argument and operation separators.
 *
 * @since 1.6.0
 *
 * @param array $data Array of properties.
 * @param string $arg_separator Separator between arguments.
 * @param string $value_separator Separator between keys and values.
 * @return array string
 */
function audiotheme_build_query( $data, $arg_separator = '|', $value_separator = ':' ) {
	$output = http_build_query( $data, null, $arg_separator );
	return str_replace( '=', $value_separator, $output );
}

/**
 * Attempt to make custom time formats more compatible between JavaScript and PHP.
 *
 * If the time format option has an escape sequences, use a default format
 * determined by whether or not the option uses 24 hour format or not.
 *
 * @since 1.7.0
 *
 * @return string
 */
function audiotheme_compatible_time_format() {
	$time_format = get_option( 'time_format' );

	if ( false !== strpos( $time_format, '\\' ) ) {
		$time_format = false !== strpbrk( $time_format, 'GH' ) ? 'G:i' : 'g:i a';
	}

	return $time_format;
}
