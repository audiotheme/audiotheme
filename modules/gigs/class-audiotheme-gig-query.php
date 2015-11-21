<?php
/**
 * Gig query.
 *
 * @package   AudioTheme\Gigs
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.9.0
 */

/**
 * Extend WP_Query and set some default arguments when querying for gigs.
 *
 * @package AudioTheme\Gigs
 * @since   1.0.0
 * @link    http://bradt.ca/blog/extending-wp_query/
 */
class AudioTheme_Gig_Query extends WP_Query {
	/**
	 * Build the query args.
	 *
	 * @since 1.0.0
	 * @uses p2p_type()
	 *
	 * @todo Add context arg.
	 * @see audiotheme_gig_query()
	 *
	 * @param array $args WP_Query args.
	 */
	public function __construct( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'post_status'         => 'publish',
			'posts_per_page'      => get_option( 'posts_per_page' ),
			'meta_key'            => '_audiotheme_gig_datetime',
			'orderby'             => 'meta_value',
			'order'               => 'asc',
			'ignore_sticky_posts' => true,
			'meta_query'          => array(
				array(
					'key'     => '_audiotheme_gig_datetime',
					'value'   => date( 'Y-m-d', current_time( 'timestamp' ) ),
					'compare' => '>=',
					'type'    => 'DATETIME',
				),
			),
		) );

		$args = apply_filters( 'audiotheme_gig_query_args', $args );
		$args['post_type'] = 'audiotheme_gig';

		parent::__construct( $args );

		p2p_type( 'audiotheme_venue_to_gig' )->each_connected( $this );
	}
}
