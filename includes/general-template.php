<?php
/**
 * General template tags and functions.
 *
 * @package AudioTheme_Framework
 * @subpackage Template
 */

/**
 * Retrieve the name of the highest priority template file that exists.
 *
 * Searches in the STYLESHEETPATH before TEMPLATEPATH so that themes which
 * inherit from a parent theme can just overload one file. Falls back to
 * the built-in template.
 *
 * @since 1.1.0
 * @see locate_template()
 *
 * @param string|array $template_names Template file(s) to search for, in order.
 * @param bool $load If true the template file will be loaded if it is found.
 * @param bool $require_once Whether to require_once or require. Default true. Has no effect if $load is false.
 * @return string The template path if one is located.
 */
function audiotheme_locate_template( $template_names, $load = false, $require_once = true ) {
	$template = '';

	foreach ( (array) $template_names as $template_name ) {
		if ( ! $template_name ) {
			continue;
		}

		if ( file_exists( get_stylesheet_directory() . '/audiotheme/' . $template_name ) ) {
			$template = get_stylesheet_directory() . '/audiotheme/' . $template_name;
			break;
		} elseif ( is_child_theme() && file_exists( get_template_directory() . '/audiotheme/' . $template_name ) ) {
			$template = get_template_directory() . '/audiotheme/' . $template_name;
			break;
		} elseif ( file_exists( AUDIOTHEME_DIR . 'templates/' . $template_name ) ) {
			$template = AUDIOTHEME_DIR . 'templates/' . $template_name;
			break;
		}
	}

	if ( $load && ! empty( $template ) ) {
		load_template( $template, $require_once );
	}

	return $template;
}

/**
 * Load a template file.
 *
 * @since 1.5.0
 *
 * @param string|array $template_file Absolute path to a file or list of template parts.
 * @param array $data Optional. List of variables to extract into the template scope.
 * @param bool $locate Optional. Whether the $template_file argument should be located. Default false.
 * @param bool $require_once Optional. Whether to require_once or require. Default false.
 */
function audiotheme_load_template( $template_file, $data = array(), $locate = false, $require_once = false ) {
	if ( is_array( $data ) && ! empty( $data ) ) {
		extract( $data, EXTR_SKIP );
		unset( $data );
	}

	// Locate the template file specified as the first parameter.
	if ( $locate ) {
		$template_file = audiotheme_locate_template( $template_file );
	}

	if ( $require_once ) {
		require_once( $template_file );
	} else {
		require( $template_file );
	}
}

/**
 * Determine if a template file is being loaded from the plugin.
 *
 * @since 1.2.0
 *
 * @param string $template Template path.
 * @return bool
 */
function is_audiotheme_default_template( $template ) {
	return ( false !== strpos( $template, AUDIOTHEME_DIR ) );
}

/**
 * Display a post type archive title.
 *
 * Just a wrapper to the default post_type_archive_title for the sake of
 * consistency. This should only be used in AudioTheme-specific template files.
 *
 * @since 1.0.0
 *
 * @see post_type_archive_title()
 *
 * @param string $before Optional. Content to prepend to the title. Default empty.
 * @param string $after  Optional. Content to append to the title. Default empty.
 */
function the_audiotheme_archive_title( $before = '', $after = '' ) {
	$title = apply_filters( 'audiotheme_archive_title', post_type_archive_title( '', false ) );

	if ( ! empty( $title ) ) {
		echo $before . $title . $after;
	}
}

/**
 * Display a post type archive description.
 *
 * @since 1.0.0
 *
 * @param string $before Content to display before the description.
 * @param string $after Content to display after the description.
 */
function the_audiotheme_archive_description( $before = '', $after = '' ) {
	if ( is_post_type_archive() ) {
		$post_type_object = get_queried_object();

		if ( $archive_id = get_audiotheme_post_type_archive( $post_type_object->name ) ) {
			$archive = get_post( $archive_id );
			if ( ! empty( $archive->post_content ) ) {
				echo $before . apply_filters( 'the_content', $archive->post_content ) . $after;
			}
		}
	}

	if ( is_tax() && ! empty( get_queried_object()->description ) ) {
		echo $before . apply_filters( 'the_content', term_description() ) . $after;
	}
}

/**
 * Strip the protocol and trailing slash from a URL for display.
 *
 * @since 1.2.0
 *
 * @param string $url URL to simplify.
 * @return string
 */
function audiotheme_simplify_url( $url ) {
	return untrailingslashit( preg_replace( '|^https?://(www\.)?|i', '', esc_url( $url ) ) );
}

/**
 * Retrieve CSS classes that mimic nth-child selectors for compatibility
 * across browsers.
 *
 * @since 1.2.0
 *
 * @param array $args Arguments to control the class names.
 * @return array
 */
function audiotheme_nth_child_classes( $args ) {
	$args = wp_parse_args( $args, array(
		'base'    => 'item',
		'current' => 1, // Current item in the loop. Index starts at 1 to match CSS.
		'max'     => 3, // Number of columns.
	) );

	$classes = array( $args['base'] );

	for ( $i = 2; $i <= $args['max']; $i ++ ) {
		$classes[] = ( $args['current'] % $i ) ? $args['base'] . '-' . $i . 'np' . ( $args['current'] % $i ) : $args['base'] . '-' . $i . 'n';
	}

	return $classes;
}

/**
 * Displays navigation to next/previous pages when applicable in archive
 * templates
 *
 * @since 1.2.0
 */
function audiotheme_archive_nav() {
	global $wp_query;

	if ( $wp_query->max_num_pages > 1 ) :
		?>
		<div class="audiotheme-paged-nav audiotheme-clearfix" role="navigation">
			<?php if ( get_previous_posts_link() ) : ?>
				<span class="audiotheme-paged-nav-prev"><?php previous_posts_link( __( '&larr; Previous', 'audiotheme' ) ); ?></span>
			<?php endif; ?>

			<?php if ( get_next_posts_link() ) : ?>
				<span class="audiotheme-paged-nav-next"><?php next_posts_link( __( 'Next &rarr;', 'audiotheme' ) ) ?></span>
			<?php endif; ?>
		</div>
		<?php
	endif;
}

/**
 * Template tag to allow for CSS classes to be easily filtered
 * across templates.
 *
 * @since 1.2.1
 * @link http://www.blazersix.com/blog/wordpress-class-template-tag/
 *
 * @param string $id Element identifier.
 * @param array|string $classes Optional. List of default classes as an array or space-separated string.
 * @param array|string $args Optional. Override defaults.
 * @return array
 */
function audiotheme_class( $id, $classes = array(), $args = array() ) {
	$id = sanitize_key( $id );

	$args = wp_parse_args( (array) $args, array(
		'echo'    => true,
		'post_id' => null
	) );

	if ( ! empty( $classes ) && ! is_array( $classes ) ) {
		// Split a string.
		$classes = preg_split( '#\s+#', $classes );
	} elseif ( empty( $classes ) ) {
		// If the function call didn't pass any classes, use the id as a default class.
		// Otherwise, the calling function can pass the id as a class along with any others.
		$classes = array( $id );
	}

	// Add support for the body element.
	if ( 'body' === $id ) {
		$classes = array_merge( get_body_class(), $classes );
	}

	// Add support for post classes.
	if ( 'post' === $id ) {
		$classes = array_merge( get_post_class( '', $args['post_id'] ), $classes );
	}

	// A page template should set modifier classes all at once in the form of an array.
	$class_mods = apply_filters( 'audiotheme_class', array(), $id, $args );

	if ( ! empty( $class_mods ) && isset( $class_mods[ $id ] ) ) {
		$mods = $class_mods[ $id ];

		// Split a string.
		if ( ! is_array( $mods ) ) {
			$mods = preg_split( '#\s+#', $mods );
		}

		foreach ( $mods as $key => $mod ) {
			// If the class starts with a double minus, remove it from both arrays.
			if ( 0 === strpos( $mod, '--' ) ) {
				$unset_class = substr( $mod, 2 );
				unset( $mods[ $key ] );
				unset( $classes[ array_search( $unset_class, $classes ) ] );
			}
		}

		$classes = array_merge( $classes, $mods );
	}

	// Last chance to modify.
	$classes = apply_filters( 'audiotheme_classes', $classes, $id, $args );
	$classes = apply_filters( 'audiotheme_classes-' . $id, $classes, $args );

	if ( $args['echo'] ) {
		echo 'class="' . join( ' ', array_map( 'sanitize_html_class', $classes ) ) . '"';
	}

	return $classes;
}
