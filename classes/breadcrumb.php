<?php
/**
 * Genesis Breadcrumbs class and related functions.
 *
 * @author Gary Jones
 *
 * @package Genesis
 */


/**
 * Class to control breadcrumbs display.
 *
 * Private properties will be set to private when WordPress requires PHP 5.2.
 * If you change a private property expect that change to break Genesis in the future.
 *
 * @since 1.5
 */
class Genesis_Breadcrumb {

	/**
	 * Settings array, a merge of provided values and defaults. Private.
	 *
	 * @since 1.5
	 *
	 * @var array
	 */
	var $args = array();

	/**
	 * Cache get_option call. Private.
	 *
	 * @since 1.5
	 *
	 * @var string
	 */
	var $on_front;

	/**
	 * Constructor. Set up cacheable values and settings.
	 *
	 * @since 1.5
	 *
	 * @param array $args
	 */
	function genesis_breadcrumb() {
		$this->on_front = get_option( 'show_on_front' );

		/** Default arguments **/
		$this->args = array(
			'home'						=> __( 'Home', 'genesis' ),
			'sep'						=> ' / ',
			'list_sep'					=> ', ',
			'prefix'					=> '<div class="breadcrumb">',
			'suffix'					=> '</div>',
			'heirarchial_attachments'	=> true,
			'heirarchial_categories'	=> true,
			'display'					=> true,
			'labels' => array(
				'prefix'	=> __( 'You are here: ', 'genesis' ),
				'author'	=> __( 'Archives for ', 'genesis' ),
				'category'	=> __( 'Archives for ', 'genesis' ),
				'tag'		=> __( 'Archives for ', 'genesis' ),
				'date'		=> __( 'Archives for ', 'genesis' ),
				'search'	=> __( 'Search for ', 'genesis' ),
				'tax'		=> __( 'Archives for ', 'genesis' ),
				'post_type'	=> __( 'Archives for ', 'genesis' ),
				'404'		=> __( 'Not found: ', 'genesis' )
			)
		);

	}

	/**
	 * Return the final completed breadcrumb in markup wrapper. Public.
	 *
	 * @since 1.5
	 *
	 * @return string HTML markup
	 */
	function get_output( $args = array() ) {

		/** Merge and Filter user and default arguments **/
		$this->args = apply_filters( 'genesis_breadcrumb_args', wp_parse_args( $args, $this->args ) );

		return $this->args['prefix'] . $this->args['labels']['prefix'] . $this->build_crumbs() . $this->args['suffix'];

	}

	/**
	 * Echo the final completed breadcrumb in markup wrapper. Public.
	 *
	 * @since 1.5
	 *
	 * @return string HTML markup
	 */
	function output( $args = array() ) {

		echo $this->get_output( $args );

	}

	/**
	 * Return home breadcrumb. Private.
	 *
	 * Default is Home, linked on all occasions except when is_home() is true.
	 *
	 * @since 1.5
	 *
	 * @return string HTML markup
	 */
	function get_home_crumb() {

		$url = 'page' == $this->on_front ? get_permalink( get_option( 'page_on_front' ) ) : trailingslashit( home_url() );
		$crumb = ( is_home() && is_front_page() ) ? $this->args['home'] : $this->get_breadcrumb_link( $url, sprintf( __( 'View %s', 'genesis' ), $this->args['home'] ), $this->args['home'] );

		return apply_filters( 'genesis_home_crumb', $crumb, $this->args );

	}

	/**
	 * Return blog posts page breadcrumb. Private.
	 *
	 * Defaults to the home crumb (later removed as a duplicate). If using a
	 * static front page, then the title of the Page is returned.
	 *
	 * @since 1.5
	 *
	 * @return string HTML markup
	 */
	function get_blog_crumb() {

		$crumb = $this->get_home_crumb();
		if ( 'page' == $this->on_front )
			$crumb = get_the_title( get_option( 'page_for_posts' ) );

		return apply_filters( 'genesis_blog_crumb', $crumb, $this->args );

	}

	/**
	 * Return search results page breadcrumb. Private.
	 *
	 * @since 1.5
	 *
	 * @return string HTML markup
	 */
	function get_search_crumb() {

		$crumb = $this->args['labels']['search'] . '"' . esc_html( apply_filters( 'the_search_query', get_search_query() ) ) . '"';

		return apply_filters( 'genesis_search_crumb', $crumb, $this->args );

	}

	/**
	 * Return 404 (page not found) breadcrumb. Private.
	 *
	 * @since 1.5
	 *
	 * @return string HTML markup
	 */
	function get_404_crumb() {

		global $wp_query;

		$crumb = $this->args['labels']['404'];

		return apply_filters( 'genesis_404_crumb', $crumb, $this->args );

	}

	/**
	 * Return content page breadcrumb. Private.
	 *
	 * @since 1.5
	 *
	 * @global mixed $wp_query
	 * @return string HTML markup
	 */
	function get_page_crumb() {

		global $wp_query;

		if ( 'page' == $this->on_front && is_front_page() ) {
			// Don't do anything - we're on the front page and we've already dealt with that elsewhere.
			$crumb = $this->get_home_crumb();
		} else {
			$post = $wp_query->get_queried_object();

			// If this is a top level Page, it's simple to output the breadcrumb
			if ( 0 == $post->post_parent ) {
				$crumb = get_the_title();
			} else {
				if ( isset( $post->ancestors ) ) {
					if ( is_array( $post->ancestors ) )
						$ancestors = array_values( $post->ancestors );
					else
						$ancestors = array( $post->ancestors );
				} else {
					$ancestors = array( $post->post_parent );
				}

				$crumbs = array( );
				foreach ( $ancestors as $ancestor ) {
					array_unshift( $crumbs, $this->get_breadcrumb_link(
									get_permalink( $ancestor ), sprintf( __( 'View %s', 'genesis' ), get_the_title( $ancestor ) ), get_the_title( $ancestor )
							)
					);
				}

				// Add the current page title
				$crumbs[] = get_the_title( $post->ID );

				$crumb = join( $this->args['sep'], $crumbs );
			}
		}

		return apply_filters( 'genesis_page_crumb', $crumb, $this->args );

	}

	/**
	 * Return archive breadcrumb. Private
	 *
	 * @since 1.5
	 *
	 * @global mixed $wp_query The page query object
	 * @global mixed $wp_locale The locale object, used for getting the
	 * auto-translated name of the month for month or day archives
	 * @return string HTML markup
	 * @todo Heirarchial, and multiple, cats and taxonomies
	 * @todo redirect taxonomies to plural pages.
	 */
	function get_archive_crumb() {

		global $wp_query, $wp_locale;

		if ( is_category() ) {
			$crumb = $this->args['labels']['category'] . $this->get_term_parents( get_query_var( 'cat' ), 'category' );
			$crumb .= edit_term_link( __( '(Edit)', 'genesis' ), ' ', '', null, false );
		}
		elseif ( is_tag() ) {
			$crumb = $this->args['labels']['tag'] . single_term_title( '', false );
			$crumb .= edit_term_link( __( '(Edit)', 'genesis' ), ' ', '', null, false );
		}
		elseif ( is_tax() ) {
			$term = $wp_query->get_queried_object();
			$crumb = $this->args['labels']['tax'] . $this->get_term_parents( $term->term_id, $term->taxonomy );
			$crumb .= edit_term_link( __( '(Edit)', 'genesis' ), ' ', '', null, false );
		}
		elseif ( is_year() ) {
			$crumb = $this->args['labels']['date'] . get_query_var( 'year' );
		}
		elseif ( is_month() ) {
			$crumb = $this->get_breadcrumb_link(
							get_year_link( get_query_var( 'year' ) ), sprintf( __( 'View archives for %s', 'genesis' ), get_query_var( 'year' ) ), get_query_var( 'year' ), $this->args['sep']
			);
			$crumb .= $this->args['labels']['date'] . single_month_title( ' ', false );
		}
		elseif ( is_day() ) {
			$crumb = $this->get_breadcrumb_link(
							get_year_link( get_query_var( 'year' ) ), sprintf( __( 'View archives for %s', 'genesis' ), get_query_var( 'year' ) ), get_query_var( 'year' ), $this->args['sep']
			);
			$crumb .= $this->get_breadcrumb_link(
							get_month_link( get_query_var( 'year' ), get_query_var( 'monthnum' ) ), sprintf( __( 'View archives for %s %s', 'genesis' ), $wp_locale->get_month( get_query_var( 'monthnum' ) ), get_query_var( 'year' ) ), $wp_locale->get_month( get_query_var( 'monthnum' ) ), $this->args['sep']
			);
			$crumb .= $this->args['labels']['date'] . get_query_var( 'day' ) . date( 'S', mktime( 0, 0, 0, 1, get_query_var( 'day' ) ) );
		}
		elseif ( is_author() ) {
			$crumb = $this->args['labels']['author'] . esc_html( $wp_query->queried_object->display_name );
		}
		elseif ( is_post_type_archive() ) {
			$crumb = $this->args['labels']['post_type'] . esc_html( post_type_archive_title( '', false ) );
		}

		return apply_filters( 'genesis_archive_crumb', $crumb, $this->args );

	}

	/**
	 * Get single breadcrumb, including any parent crumbs. Private.
	 *
	 * @since 1.5
	 *
	 * @global mixed $post Current post object
	 * @return string HTML markup
	 */
	function get_single_crumb() {

		global $post;

		if ( is_attachment() ) {
			$crumb = '';
			if ( $this->args['heirarchial_attachments'] ) { // if showing attachment parent
				$attachment_parent = get_post( $post->post_parent );
				$crumb = $this->get_breadcrumb_link(
								get_permalink( $post->post_parent ), sprintf( __( 'View %s', 'genesis' ), $attachment_parent->post_title ), $attachment_parent->post_title, $this->args['sep']
				);
			}
			$crumb .= single_post_title( '', false );
		} elseif ( is_singular( 'post' ) ) {
			$categories = get_the_category( $post->ID );

			if ( 1 == count( $categories ) ) { // if in single category, show it, and any parent categories
				$crumb = $this->get_term_parents( $categories[0]->cat_ID, 'category', true ) . $this->args['sep'];
			}
			if ( count( $categories ) > 1 ) {
				if ( ! $this->args['heirarchial_categories'] ) { // Don't show parent categories (unless the post happen to be explicitely in them)
					foreach ( $categories as $category ) {
						$crumbs[] = $this->get_breadcrumb_link(
										get_category_link( $category->term_id ), sprintf( __( 'View all posts in %s', 'genesis' ), $category->name ), $category->name
						);
					}
					$crumb = join( $this->args['list_sep'], $crumbs ) . $this->args['sep'];
				} else { // Show parent categories - see if one is marked as primary and try to use that.
					$primary_category_id = get_post_meta( $post->ID, '_category_permalink', true ); // Support for sCategory Permalink plugin
					if ( $primary_category_id ) {
						$crumb = $this->get_term_parents( $primary_category_id, 'category', true ) . $this->args['sep'];
					} else {
						$crumb = $this->get_term_parents( $categories[0]->cat_ID, 'category', true ) . $this->args['sep'];
					}
				}
			}
			$crumb .= single_post_title( '', false );
		} else {
			$post_type = get_query_var( 'post_type' );
			$post_type_object = get_post_type_object( $post_type );

			$crumb = $this->get_breadcrumb_link( get_post_type_archive_link( $post_type ), sprintf( __( 'View all %s', 'genesis' ), $post_type_object->labels->name ), $post_type_object->labels->name );

			$crumb .= $this->args['sep'] . single_post_title( '', false );
		}

		return apply_filters( 'genesis_single_crumb', $crumb, $this->args );

	}

	/**
	 * Return the correct crumbs for this query, combined together. Private.
	 *
	 * @since 1.5
	 *
	 * @return string HTML markup
	 */
	function build_crumbs() {

		$crumbs[] = $this->get_home_crumb();

		if ( is_home() )
			$crumbs[] = $this->get_blog_crumb();
		elseif ( is_search() )
			$crumbs[] = $this->get_search_crumb();
		elseif ( is_404() )
			$crumbs[] = $this->get_404_crumb();
		elseif ( is_page() )
			$crumbs[] = $this->get_page_crumb();
		elseif ( is_archive() )
			$crumbs[] = $this->get_archive_crumb();
		elseif ( is_singular() )
			$crumbs[] = $this->get_single_crumb();

		return join( $this->args['sep'], array_filter( array_unique( $crumbs ) ) );

	}

	/**
	 * Return recursive linked crumbs of category, tag or custom taxonomy parents. Private.
	 *
	 * @param int $parent_id Initial ID of object to get parents of
	 * @param string $taxonomy Name of the taxnomy. May be 'category', 'post_tag' or something custom
	 * @param boolean $link. Whether to link last item in chain. Default false
	 * @param array $visited Array of IDs already included in the chain
	 * @return string HTML markup of crumbs
	 */
	function get_term_parents( $parent_id, $taxonomy, $link = false, $visited = array( ) ) {

		$parent = &get_term( (int)$parent_id, $taxonomy );

		if ( is_wp_error( $parent ) )
			return array();

		if ( $parent->parent && ( $parent->parent != $parent->term_id ) && ! in_array( $parent->parent, $visited ) ) {
			$visited[] = $parent->parent;
			$chain[] = $this->get_term_parents( $parent->parent, $taxonomy, true, $visited );
		}

		if ( $link && !is_wp_error( get_term_link( get_term( $parent->term_id, $taxonomy ), $taxonomy ) ) ) {
			$chain[] = $this->get_breadcrumb_link( get_term_link( get_term( $parent->term_id, $taxonomy ), $taxonomy ), sprintf( __( 'View all items in %s', 'genesis' ), $parent->name ), $parent->name );
		} else {
			$chain[] = $parent->name;
		}

		return join( $this->args['sep'], $chain );

	}

	/**
	 * Return anchor link for a single crumb. Private.
	 *
	 * @param string $url URL for href attribute
	 * @param string $title title attribute
	 * @param string $content linked content
	 * @param type $sep Separator
	 * @return type HTML markup for anchor link and optional separator
	 */
	function get_breadcrumb_link( $url, $title, $content, $sep = false ) {

		$link = sprintf( '<a href="%s" title="%s">%s</a>', esc_attr( $url ), esc_attr( $title ), esc_html( $content ) );

		if ( $sep )
			$link .= $sep;

		return $link;

	}

}

/**
 * Helper function for the Genesis Breadcrumb Class
 *
 * @since 0.1.6
 */
function genesis_breadcrumb( $args = array() ) {

	global $_genesis_breadcrumb;

	if ( !$_genesis_breadcrumb ) {
		$_genesis_breadcrumb = new Genesis_Breadcrumb;
	}

	$_genesis_breadcrumb->output( $args );

}

add_action('genesis_before_loop', 'genesis_do_breadcrumbs');
/**
 * Display Breadcrumbs above the Loop
 * Concedes priority to popular breadcrumb plugins
 *
 * @since 0.1.6
 */
function genesis_do_breadcrumbs() {

	// Conditional Checks
	if ( ( is_front_page() || is_home() ) && ! genesis_get_option( 'breadcrumb_home' ) ) return;
	if ( is_single() && ! genesis_get_option( 'breadcrumb_single' ) ) return;
	if ( is_page() && ! genesis_get_option( 'breadcrumb_page' ) ) return;
	if ( ( is_archive() || is_search() ) && ! genesis_get_option( 'breadcrumb_archive' ) ) return;
	if ( is_404() && ! genesis_get_option( 'breadcrumb_404' ) ) return;

	if ( function_exists( 'bcn_display' ) ) {
		echo '<div class="breadcrumb">'; bcn_display(); echo '</div>';
	}
	elseif ( function_exists( 'yoast_breadcrumb' ) ) {
		yoast_breadcrumb('<div class="breadcrumb">','</div>');
	}
	elseif ( function_exists( 'breadcrumbs' ) ) {
		breadcrumbs();
	}
	elseif ( function_exists( 'crumbs' ) ) {
		crumbs();
	}
	else {
		genesis_breadcrumb();
	}

}