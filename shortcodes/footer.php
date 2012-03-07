<?php
/**
 * This file defines return functions to be used as shortcodes
 * in the site footer.
 * 
 * @package Genesis
 * 
 * @example <code>[footer_something]</code>
 * @example <code>[footer_something before="<em>" after="</em>" foo="bar"]</code>
 */

add_shortcode( 'footer_backtotop', 'genesis_footer_backtotop_shortcode' );
/**
 * This function produces the "Return to Top" link
 * 
 * @since Unknown
 * 
 * @param array $atts Shortcode attributes
 * @return string 
 */
function genesis_footer_backtotop_shortcode( $atts ) {

	$defaults = array( 
		'text'     => __( 'Return to top of page', 'genesis' ),
		'href'     => '#wrap',
		'nofollow' => true,
		'before'   => '',
		'after'    => ''
	);
	$atts = shortcode_atts( $defaults, $atts );

	$nofollow = $atts['nofollow'] ? 'rel="nofollow"' : '';

	$output = sprintf( '%s<a href="%s" %s>%s</a>%s', $atts['before'], esc_url( $atts['href'] ), $nofollow, $atts['text'], $atts['after'] );

	return apply_filters( 'genesis_footer_backtotop_shortcode', $output, $atts );

}

add_shortcode( 'footer_copyright', 'genesis_footer_copyright_shortcode' );
/**
 * Adds the visual copyright notice
 * 
 * @since Unknown
 *
 * @param array $atts Shortcode attributes
 * @return string
 */
function genesis_footer_copyright_shortcode( $atts ) {

	$defaults = array( 
		'copyright' => g_ent( '&copy;' ),
		'first'     => '',
		'before'    => '',
		'after'     => ''
	);
	$atts = shortcode_atts( $defaults, $atts );

	$output = $atts['before'] . $atts['copyright'] . ' ';
	if ( '' != $atts['first'] && date( 'Y' ) != $atts['first'] )
		$output .= $atts['first'] . g_ent( '&ndash;' );
	$output .= date( 'Y' ) . $atts['after'];

	return apply_filters( 'genesis_footer_copyright_shortcode', $output, $atts );

}

add_shortcode( 'footer_childtheme_link', 'genesis_footer_childtheme_link_shortcode' );
/**
 * Adds the link to the child theme
 * 
 * @since Unknown
 *
 * @param array $atts Shortcode attributes
 * @return string
 */
function genesis_footer_childtheme_link_shortcode( $atts ) {

	$defaults = array( 
		'before' => g_ent( '&middot; ' ),
		'after'  => ''
	);
	$atts = shortcode_atts( $defaults, $atts );

	if ( ! is_child_theme() || ! defined( 'CHILD_THEME_NAME' ) || ! defined( 'CHILD_THEME_URL' ) )
		return;

	$output = sprintf( '%s<a href="%s" title="%s">%s</a>%s', $atts['before'], esc_url( CHILD_THEME_URL ), esc_attr( CHILD_THEME_NAME ), esc_html( CHILD_THEME_NAME ), $atts['after'] );

	return apply_filters( 'genesis_footer_childtheme_link_shortcode', $output, $atts );

}

add_shortcode( 'footer_genesis_link', 'genesis_footer_genesis_link_shortcode' );
/**
 * Adds the link to Genesis page on StudioPress website
 * 
 * @since Unknown
 *
 * @param array $atts Shortcode attributes
 * @return string
 */
function genesis_footer_genesis_link_shortcode( $atts ) {

	$defaults = array( 
		'before' => '',
		'after'  => ''
	);
	$atts = shortcode_atts( $defaults, $atts );

	$output = $atts['before'] . '<a href="http://www.studiopress.com/themes/genesis" title="Genesis Framework">Genesis Framework</a>' . $atts['after'];

	return apply_filters( 'genesis_footer_genesis_link_shortcode', $output, $atts );

}

add_shortcode( 'footer_studiopress_link', 'genesis_footer_studiopress_link_shortcode' );
/**
 * Adds the link to StudioPress home page
 * 
 * @since Unknown
 *
 * @param array $atts Shortcode attributes
 * @return string
 */
function genesis_footer_studiopress_link_shortcode( $atts ) {

	$defaults = array( 
		'before' => __( 'by ', 'genesis' ),
		'after'  => ''
	);
	$atts = shortcode_atts( $defaults, $atts );

	$output = $atts['before'] . '<a href="http://www.studiopress.com/">StudioPress</a>' . $atts['after'];

	return apply_filters( 'genesis_footer_studiopress_link_shortcode', $output, $atts );

}

add_shortcode('footer_wordpress_link', 'genesis_footer_wordpress_link_shortcode');
/**
 * Adds link to WordPress
 * 
 * @since Unknown
 *
 * @param array $atts Shortcode attributes
 * @return string
 */
function genesis_footer_wordpress_link_shortcode( $atts ) {

	$defaults = array( 
		'before' => '',
		'after'  => ''
	);
	$atts = shortcode_atts( $defaults, $atts );

	$output = sprintf( '%s<a href="%s" title="%s">%s</a>%s', $atts['before'], 'http://wordpress.org/', 'WordPress', 'WordPress', $atts['after'] );

	return apply_filters( 'genesis_footer_wordpress_link_shortcode', $output, $atts );

}

add_shortcode('footer_loginout', 'genesis_footer_loginout_shortcode');
/**
 * Adds admin login / logout link
 * 
 * @since Unknown
 *
 * @param array $atts Shortcode attributes
 * @return string
 */
function genesis_footer_loginout_shortcode( $atts ) {
	
	$defaults = array(
		'redirect' => '',
		'before'   => '',
		'after'    => ''
	);
	$atts = shortcode_atts( $defaults, $atts );
	
	if ( ! is_user_logged_in() )
		$link = '<a href="' . esc_url( wp_login_url($atts['redirect'] ) ) . '">' . __( 'Log in', 'genesis' ) . '</a>';
	else
		$link = '<a href="' . esc_url( wp_logout_url($atts['redirect'] ) ) . '">' . __( 'Log out', 'genesis' ) . '</a>';

	$output = $atts['before'] . apply_filters( 'loginout', $link ) . $atts['after'];

	return apply_filters( 'genesis_footer_loginout_shortcode', $output, $atts );

}