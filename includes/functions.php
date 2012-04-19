<?php
/**
 * Admin Redirect
 *
 * This function redirects the user to an admin page, and adds query args
 * to the URL string for alerts, etc.
 *
 * @since 1.0
 */
function audiotheme_admin_redirect( $page, $query_args = array() ) {

	if ( ! $page )
		return;

	$url = menu_page_url( $page, false );

	foreach ( ( array ) $query_args as $key => $value ) {
		if ( isset( $key ) && isset( $value ) ) {
			$url = add_query_arg( $key, $value, $url );
		}
	}

	wp_redirect( esc_url_raw( $url ) );

}

/**
 * Detect Plugin by constant, class or function existence.
 *
 * Detect Plugin from a list of constants, classes or functions added by plugins.
 *
 * @since 1.0
 *
 * @param array $plugins Array of Array for constants, classes and / or functions to check for plugin existence.
 * @return boolean True if plugin exists or false if plugin constant, class or function not detected.
 */
function audiotheme_detect_plugin( $plugins ) {

	// Check for classes
	if( isset( $plugins['classes'] ) ) {
		foreach ( $plugins['classes'] as $name ) {
			if ( class_exists( $name ) )
				return true;
		}
	}

	//Check for functions
	if ( isset( $plugins['functions'] ) ) {
		foreach ( $plugins['functions'] as $name ) {
			if ( function_exists( $name ) )
				return true;
		}
	}

	//Check for constants
	if ( isset( $plugins['constants'] ) ) {
		foreach ( $plugins['constants'] as $name ) {
			if ( defined( $name ) )
				return true;
		}
	}

	return false;
}

/**
 * Sort an Array of Objects
 *
 * Crazyness!
 *
 * Ex: sort_objects( $gigs, array( 'venue', 'name' ), 'asc', true, 'gig_datetime' );
 *
 * @since 1.0
 */
if ( ! function_exists( 'sort_objects' ) && ! class_exists( 'Sort_Objects' ) ) :
function sort_objects( $objects, $orderby, $order = 'ASC', $unique = true, $fallback = NULL ) {
	if ( ! is_array( $objects ) ) {
		return false;
	}
	
	usort( $objects, array( new Sort_Objects( $orderby, $order, $fallback ), 'sort' ) );
	
	// use object ids as the array keys
	if ( $unique && count( $objects ) && isset( $objects[0]->ID ) ) {
		$objects = array_combine( wp_list_pluck( $objects, 'ID' ), $objects );
	}
	
	return $objects;
}

class Sort_Objects {
	var $fallback, $order, $orderby;
	
	// Fallback is limited to working with properties of the parent object
	function __construct( $orderby, $order, $fallback = NULL ) {
		$this->order = ( 'desc' == strtolower( $order ) ) ? 'DESC' : 'ASC';
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
			
			foreach( $this->orderby as $prop ) {
				$a_value = ( isset( $a_value->$prop ) ) ? $a_value->$prop : '';
				$b_value = ( isset( $b_value->$prop ) ) ? $b_value->$prop : '';
			}
		}
		
		#echo $a_value . ' - ' . $b_value . '<br>';
		
		if ( $a_value == $b_value ) {
			if ( ! empty( $this->fallback ) ) {
				$properties = explode( ',', $this->fallback );
				foreach( $properties as $prop ) {
					if ( $a->$prop != $b->$prop ) {
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
			return ( 'ASC' == $this->order ) ? -1 : 1;
		} else {
			return ( 'ASC' == $this->order ) ? 1 : -1;
		}
	}
}
endif;

/**
 * Echo a variable for debugging
 *
 * Don't want vd in production code.
 *
 * @since 1.0
 *
 * @param mixed $var
 */
if ( ! function_exists( 'vd' ) ) :
function vd( $var ) {
	echo '<pre>'; print_r( $var ); echo '</pre>';
}
endif;
?>