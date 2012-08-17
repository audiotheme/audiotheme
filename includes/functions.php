<?php
/**
 * Sort an Array of Objects
 *
 * Crazyness!
 *
 * Ex: sort_objects( $gigs, array( 'venue', 'name' ), 'asc', true, 'gig_datetime' );
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'sort_objects' ) && ! class_exists( 'Sort_Objects' ) ) :
function sort_objects( $objects, $orderby, $order = 'ASC', $unique = true, $fallback = NULL ) {
	if ( ! is_array( $objects ) ) {
		return false;
	}
	
	usort( $objects, array( new Sort_Objects( $orderby, $order, $fallback ), 'sort' ) );
	
	// Use object ids as the array keys
	if ( $unique && count( $objects ) && isset( $objects[0]->ID ) ) {
		$objects = array_combine( wp_list_pluck( $objects, 'ID' ), $objects );
	}
	
	return $objects;
}

class Audiotheme_Sort_Objects {
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
 * @since 1.0.0
 *
 * @param mixed $var
 */
if ( ! function_exists( 'vd' ) ) :
function vd( $var ) {
	echo '<pre style="font-size: 12px; text-align: left">'; print_r( $var ); echo '</pre>';
}
endif;
?>