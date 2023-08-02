<?php
/**
 * Generic utility functions.
 *
 * @package   AudioTheme
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.0.0
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
 * Sort an array of objects by an objects properties.
 *
 * Ex: sort_objects( $gigs, array( 'venue', 'name' ), 'asc', true, 'gig_datetime' );
 *
 * @since 1.0.0
 * @uses AudioTheme_Sort_Objects
 *
 * @param array  $objects An array of objects to sort.
 * @param string $orderby The object property to sort on.
 * @param string $order The sort order; ASC or DESC.
 * @param bool   $unique Optional. If the objects have an ID property, it will be used for the array keys, thus they'll unique. Defaults to true.
 * @param string $fallback Optional. Comma-delimited string of properties to sort on if $orderby property is equal.
 * @return array The array of sorted objects.
 */
function audiotheme_sort_objects( $objects, $orderby, $order = 'ASC', $unique = true, $fallback = null ) {
	if ( ! is_array( $objects ) ) {
		return false;
	}

	usort( $objects, array( new AudioTheme_Sort_Objects( $orderby, $order, $fallback ), 'sort' ) );

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
class AudioTheme_Sort_Objects {
	/**
	 * Fallback property to sort by if primary is equal.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $fallback;

	/**
	 * Sort direction.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $order;

	/**
	 * Property to sort by.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $orderby;

	/**
	 * Constructor method.
	 *
	 * @since 1.0.0
	 *
	 * @param string $orderby  Property to sort by.
	 * @param string $order    Sort direction.
	 * @param string $fallback Fallback property to sort by. Limited to properties of the parent object.
	 */
	public function __construct( $orderby, $order, $fallback = null ) {
		$this->order = ( 'desc' === strtolower( $order ) ) ? 'DESC' : 'ASC';
		$this->orderby = $orderby;
		$this->fallback = $fallback;
	}

	/**
	 * Sort objects.
	 *
	 * @since 1.0.0
	 *
	 * @param  object $a Object 1.
	 * @param  object $b Object 2.
	 * @return int
	 */
	public function sort( $a, $b ) {
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
						// @todo printf( '(%s - %s) - (%s - %s)<br>', $a_value, $a->$prop, $b_value, $b->$prop );
						return $this->compare( $a->$prop, $b->$prop );
					}
				}
			}

			return 0;
		}

		return $this->compare( $a_value, $b_value );
	}

	/**
	 * Compare two values for equality.
	 *
	 * @since 1.0.0
	 *
	 * @param  mixed $a Value 1.
	 * @param  mixed $b Value 2.
	 * @return int
	 */
	public function compare( $a, $b ) {
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
	$selected = empty( $selected_zone ) ? get_option( 'timezone_string' ) : $selected_zone;
	$choices  = wp_timezone_choice( $selected );

	// Remove the manual offsets optgroup.
	$pos = strrpos( $choices, '<optgroup' );
	if ( false !== $pos ) {
		$choices = substr( $choices, 0, $pos );
	}

	return apply_filters( 'audiotheme_timezone_dropdown', $choices, $selected );
}

if ( ! function_exists( 'vd' ) ) :
/**
 * Display a variable for debugging.
 *
 * @since 1.0.0
 *
 * @param mixed $value Value.
 */
function vd( $value ) {
	echo '<pre style="font-size: 12px; text-align: left">' . print_r( $value, true ) . '</pre>';
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
 * @param array  $input The input array.
 * @param int    $offset The position to start from.
 * @param int    $length Optional. The number of elements to remove. Defaults to 0.
 * @param mixed  $replacement Optional. Item(s) to replace removed elements.
 * @param string $primary Optiona. input|replacement Defaults to input. Which array should take precedence if there is a key collision.
 * @return array The modified array.
 */
function audiotheme_array_asplice( $input, $offset, $length = 0, $replacement = null, $primary = 'input' ) {
	$input = (array) $input;
	$replacement = (array) $replacement;

	$start = array_slice( $input, 0, $offset, true );
	// @todo $remove = array_slice( $input, $offset, $length, true );
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
	// @todo return $start + $replacement + $end;
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
 * @param bool  $strict Whether to search for identical (types) values.
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
 * @param string|int $key The key to search for.
 * @param array      $search The array to search.
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
 * @param array  $array The array to sort.
 * @param array  $order Array used for sorting. Values should match keys in $array.
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
 * Spaces in directory or filenames are stripped by esc_url() and can cause
 * issues when requesting a URL programmatically. This method encodes spaces
 * and other characters.
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
 * @param array  $data Array of properties.
 * @param string $arg_separator Separator between arguments.
 * @param string $value_separator Separator between keys and values.
 * @return array string
 */
function audiotheme_build_query( $data, $arg_separator = '|', $value_separator = ':' ) {
	$output = http_build_query( $data, '', $arg_separator );
	return str_replace( '=', $value_separator, $output );
}

/**
 * Remove letterbox matte from an image attachment.
 *
 * Overwrites the existing attachment and regenerates all sizes.
 *
 * @since 2.0.0
 *
 * @param int $attachment_id Attachment ID.
 */
function audiotheme_trim_image_letterbox( $attachment_id ) {
	require_once( ABSPATH . 'wp-admin/includes/image.php' );

	$file  = get_attached_file( $attachment_id );

	$image = wp_get_image_editor( $file, array(
		'methods' => array( 'trim' )
	) );

	if ( is_wp_error( $image ) ) {
		return;
	}

	// Delete intermediate sizes.
	$meta  = wp_get_attachment_metadata( $attachment_id );
	foreach ( $meta['sizes'] as $size ) {
		$path = path_join( dirname( $file ), $size['file'] );
		wp_delete_file( $path );
	}

	$image->trim( 10 );
	$saved = $image->save( $file );

	$meta = wp_generate_attachment_metadata( $attachment_id, $saved['path'] );
	wp_update_attachment_metadata( $attachment_id, $meta );
}

/**
 * Parse a date string into a formatted string.
 *
 * @since 2.1.0
 *
 * @param  string $date Date string.
 * @param  string $time Time string.
 * @return string
 */
function audiotheme_parse_date( $date, $time = '' ) {
	$result = '';
	$parts  = date_parse( $date . ' ' . $time );

	// Date and time are always stored local to the venue.
	// If GMT, or time in another locale is needed, use the venue time zone to calculate.
	// Other functions should be aware that time is optional; check for the presence of gig_time.
	if ( checkdate( $parts['month'], $parts['day'], $parts['year'] ) ) {
		$result = sprintf(
			'%d-%s-%s %s:%s:%s',
			$parts['year'],
			zeroise( $parts['month'], 2 ),
			zeroise( $parts['day'], 2 ),
			zeroise( $parts['hour'], 2 ),
			zeroise( $parts['minute'], 2 ),
			zeroise( $parts['second'], 2 )
		);
	}

	return $result;
}

/**
 * Parse a time string into a formatted string.
 *
 * @since 2.1.0
 *
 * @param  string $time Time string.
 * @return string
 */
function audiotheme_parse_time( $time ) {
	$result = '';
	$parts  = date_parse( $time );

	if ( empty( $parts['errors'] ) ) {
		$result = sprintf(
			'%s:%s:%s',
			zeroise( $parts['hour'], 2 ),
			zeroise( $parts['minute'], 2 ),
			zeroise( $parts['second'], 2 )
		);
	}

	return $result;
}
