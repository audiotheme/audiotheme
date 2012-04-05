<?php
/**
 * This file defines return functions to be used as shortcodes
 * in the site footer.
 * 
 * @package audiotheme
 * 
 * @example <code>[footer_something]</code>
 * @example <code>[footer_something before="<em>" after="</em>" foo="bar"]</code>
 */


add_shortcode( 'footer_backtotop', 'audiotheme_footer_backtotop_shortcode' );
/**
 * This function produces the "Return to Top" link
 * 
 * @since 1.0
 * 
 * @param array $atts Shortcode attributes
 * @return string 
 */
function audiotheme_footer_backtotop_shortcode( $atts ) {

	$defaults = array( 
		'text'     => __( 'Return to top of page', 'audiotheme' ),
		'href'     => '#top',
		'nofollow' => true,
		'before'   => '',
		'after'    => ''
	 );
	$atts = shortcode_atts( $defaults, $atts );

	$nofollow = $atts['nofollow'] ? 'rel="nofollow"' : '';

	$output = sprintf( '%s<a href="%s" %s>%s</a>%s', $atts['before'], esc_url( $atts['href'] ), $nofollow, $atts['text'], $atts['after'] );

	return apply_filters( 'audiotheme_footer_backtotop_shortcode', $output, $atts );

}


add_shortcode( 'footer_copyright', 'audiotheme_footer_copyright_shortcode' );
/**
 * Adds the visual copyright notice
 * 
 * @since 1.0
 *
 * @param array $atts Shortcode attributes
 * @return string
 */
function audiotheme_footer_copyright_shortcode( $atts ) {

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

	return apply_filters( 'audiotheme_footer_copyright_shortcode', $output, $atts );

}


add_shortcode( 'footer_audiotheme_link', 'audiotheme_footer_audiotheme_link_shortcode' );
/**
 * Adds the link to audiotheme page on AudioTheme website
 * 
 * @since 1.0
 *
 * @param array $atts Shortcode attributes
 * @return string
 */
function audiotheme_footer_audiotheme_link_shortcode( $atts ) {

	$defaults = array( 
		'before' => '',
		'after'  => ''
	);
	
	$atts = shortcode_atts( $defaults, $atts );

	$output = $atts['before'] . '<a href="http://www.audiotheme.com/framework" title="AudioTheme Framework">AudioTheme Framework</a>' . $atts['after'];

	return apply_filters( 'audiotheme_footer_audiotheme_link_shortcode', $output, $atts );

}


add_shortcode( 'footer_luke_mcdonald_link', 'audiotheme_footer_luke_mcdonald_link_shortcode' );
/**
 * Adds the link to Luke McDonald home page
 * 
 * @since 1.0
 *
 * @param array $atts Shortcode attributes
 * @return string
 */
function audiotheme_footer_luke_mcdonald_link_shortcode( $atts ) {

	$defaults = array( 
		'before' => __( 'by ', 'audiotheme' ),
		'after'  => ''
	 );
	$atts = shortcode_atts( $defaults, $atts );

	$output = sprintf( '%1$s<a href="%2$s" title="%3$s">%3$s</a>%4$s', 
		$atts['before'], 
		'http://lukemcdonald.com/', 
		'Luke McDonald', 
		$atts['after'] 
	);

	return apply_filters( 'audiotheme_footer_luke_mcdonald_link_shortcode', $output, $atts );

}


add_shortcode( 'footer_shaken_stirred_link', 'audiotheme_footer_shaken_stirred_link_shortcode' );
/**
 * Adds the link to Shaken & Stirred Web (Sawyer Hollenshead) home page
 * 
 * @since 1.0
 *
 * @param array $atts Shortcode attributes
 * @return string
 */
function audiotheme_footer_shaken_stirred_link_shortcode( $atts ) {

	$defaults = array( 
		'before' => __( 'by ', 'audiotheme' ),
		'after'  => ''
	);
	
	$atts = shortcode_atts( $defaults, $atts );

	$output = sprintf( '%1$s<a href="%2$s" title="%3$s">%3$s</a>%4$s', 
		$atts['before'], 
		'http://shakenandstirredweb.com/', 
		'Shaken &amp; Stirred', 
		$atts['after'] 
	);
	
	return apply_filters( 'audiotheme_footer_shaken_stirred_link_shortcode', $output, $atts );

}


add_shortcode( 'footer_blazersix_link', 'audiotheme_footer_blazersix_link_shortcode' );
/**
 * Adds the link to BlazerSix home page
 * 
 * @since 1.0
 *
 * @param array $atts Shortcode attributes
 * @return string
 */
function audiotheme_footer_blazersix_link_shortcode( $atts ) {

	$defaults = array( 
		'before' => __( 'by ', 'audiotheme' ),
		'after'  => ''
	 );
	$atts = shortcode_atts( $defaults, $atts );

	$output = sprintf( '%1$s<a href="%2$s" title="%3$s">%3$s</a>%4$s', 
		$atts['before'], 
		'http://blazersix.com/', 
		'Blazersix', 
		$atts['after'] 
	);

	return apply_filters( 'audiotheme_footer_blazersix_link_shortcode', $output, $atts );

}


add_shortcode( 'footer_wap8_link', 'audiotheme_footer_wap8_link_shortcode' );
/**
 * Adds the link to We Are Pixel 8 home page
 * 
 * @since 1.0
 *
 * @param array $atts Shortcode attributes
 * @return string
 */
function audiotheme_footer_wap8_link_shortcode( $atts ) {

	$defaults = array( 
		'before' => __( 'by ', 'audiotheme' ),
		'after'  => ''
	);
	
	$atts = shortcode_atts( $defaults, $atts );

	$output = sprintf( '%1$s<a href="%2$s" title="%3$s">%3$s</a>%4$s', 
		$atts['before'], 
		'http://wearepixel8.com/', 
		'We Are Pixel 8', 
		$atts['after'] 
	);

	return apply_filters( 'audiotheme_footer_wap8_link_shortcode', $output, $atts );

}


add_shortcode( 'footer_wordpress_link', 'audiotheme_footer_wordpress_link_shortcode' );
/**
 * Adds link to WordPress
 * 
 * @since 1.0
 *
 * @param array $atts Shortcode attributes
 * @return string
 */
function audiotheme_footer_wordpress_link_shortcode( $atts ) {

	$defaults = array( 
		'before' => '',
		'after'  => ''
	);
	
	$atts = shortcode_atts( $defaults, $atts );

	$output = sprintf( '%1$s<a href="%2$s" title="%3$s">%3$s</a>%4$s', 
		$atts['before'], 
		'http://wordpress.org/', 
		'WordPress', 
		$atts['after'] 
	);

	return apply_filters( 'audiotheme_footer_wordpress_link_shortcode', $output, $atts );

}


add_shortcode( 'footer_loginout', 'audiotheme_footer_loginout_shortcode' );
/**
 * Adds admin login / logout link
 * 
 * @since Unknown
 *
 * @param array $atts Shortcode attributes
 * @return string
 */
function audiotheme_footer_loginout_shortcode( $atts ) {
	
	$defaults = array( 
		'redirect' => '',
		'before'   => '',
		'after'    => ''
	 );
	$atts = shortcode_atts( $defaults, $atts );
	
	if ( ! is_user_logged_in() )
		$link = sprintf( '<a href="%1$s">%2$s</a>', 
			esc_url( wp_login_url( $atts['redirect'] ) ),
			esc_html( __( 'Log in', 'audiotheme' ) )
		);
	else
		$link = sprintf( '<a href="%1$s">%2$s</a>',
			esc_url( wp_logout_url( $atts['redirect'] ) ),
			esc_html( __( 'Log out', 'audiotheme' ) )
		);

	$output = $atts['before'] . apply_filters( 'loginout', $link ) . $atts['after'];

	return apply_filters( 'audiotheme_footer_loginout_shortcode', $output, $atts );

}