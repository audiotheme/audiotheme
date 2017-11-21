<?php
/**
 * General hooks provider.
 *
 * @package   AudioTheme
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */

/**
 * General hooks provider class.
 *
 * @package AudioTheme
 * @since   2.0.0
 */
class AudioTheme_Provider_GeneralHooks extends AudioTheme_AbstractProvider {
	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'init',                         array( $this, 'add_post_gallery_support' ) );
		add_filter( 'wp_image_editors',             array( $this, 'register_image_editors' ) );
		add_filter( 'audiotheme_archive_title',     array( $this, 'taxonomy_archives_titles' ) );
		add_filter( 'wp_nav_menu_objects',          array( $this, 'nav_menu_classes' ), 10, 3 );
		add_filter( 'wp_prepare_attachment_for_js', array( $this, 'prepare_audio_attachment_for_js' ), 10, 3 );
		add_filter( 'kses_allowed_protocols',       array( $this, 'register_urn_protocol' ) );
		add_filter( 'attachment_link',              array( $this, 'filter_attachment_links' ), 10, 2 );

		// Deprecated.
		add_action( 'init',                   'audiotheme_less_setup' );
		add_filter( 'embed_oembed_html',      'audiotheme_oembed_html', 10, 4 );
		add_filter( 'embed_handler_html',     'audiotheme_oembed_html', 10, 4 );
		add_filter( 'video_embed_html',       'audiotheme_oembed_html', 10 ); // Jetpack compat.
		add_filter( 'dynamic_sidebar_params', 'audiotheme_widget_count_class' );
		add_filter( 'get_pages',              'audiotheme_page_list' );
		add_filter( 'page_css_class',         'audiotheme_page_list_classes', 10, 2 );
		add_filter( 'wp_nav_menu_objects',    'audiotheme_nav_menu_classes', 10, 3 );
		add_filter( 'nav_menu_css_class',     'audiotheme_nav_menu_name_class', 10, 2 );
	}

	/**
	 * Add support for custom post gallery output.
	 *
	 * This feature was deprecated in 2.0.0 and will be removed in a future
	 * release.
	 *
	 * @since 2.0.0
	 */
	public function add_post_gallery_support() {
		if ( current_theme_supports( 'audiotheme-post-gallery' ) ) {
			// High priority so plugins filtering ouput don't get stomped. Jetpack, etc.
			add_filter( 'post_gallery', 'audiotheme_post_gallery', 5000, 2 );
		}
	}

	/**
	 * Register custom image editors.
	 *
	 * @since 2.0.0
	 *
	 * @param array $editors Array of image editors.
	 * @return array
	 */
	public function register_image_editors( $editors ) {
		array_unshift( $editors, 'AudioTheme_Image_Editor_GD' );
		array_unshift( $editors, 'AudioTheme_Image_Editor_Imagick' );

		return $editors;
	}

	/**
	 * Filter record type archive titles.
	 *
	 * @since 2.0.0
	 *
	 * @param string $title Archive title.
	 * @return string
	 */
	public function taxonomy_archives_titles( $title ) {
		if ( is_tax() ) {
			$title = get_queried_object()->name;
		}

		return $title;
	}

	/**
	 * Add helpful nav menu item classes.
	 *
	 * @since 2.0.0
	 *
	 * @param array $items List of menu items.
	 * @param array $args Menu display args.
	 * @return array
	 */
	public function nav_menu_classes( $items, $args ) {
		global $wp;

		if ( is_404() || is_search() ) {
			return $items;
		}

		$current_url  = trailingslashit( home_url( add_query_arg( array(), $wp->request ) ) );
		$blog_page_id = get_option( 'page_for_posts' );
		$is_blog_post = is_singular( 'post' );

		$is_audiotheme_post_type = is_singular( array( 'audiotheme_gig', 'audiotheme_record', 'audiotheme_track', 'audiotheme_video' ) );
		$post_type_archive_id    = get_audiotheme_post_type_archive( get_post_type() );
		$post_type_archive_link  = get_post_type_archive_link( get_post_type() );

		$current_menu_parents = array();

		foreach ( $items as $key => $item ) {
			if (
				'audiotheme_archive' === $item->object &&
				$post_type_archive_id == $item->object_id &&
				trailingslashit( $item->url ) === $current_url
			) {
				$items[ $key ]->classes[] = 'current-menu-item';
				$current_menu_parents[] = $item->menu_item_parent;
			}

			if ( $is_blog_post && $blog_page_id === $item->object_id ) {
				$items[ $key ]->classes[] = 'current-menu-parent';
				$current_menu_parents[] = $item->menu_item_parent;
			}

			// Add 'current-menu-parent' class to CPT archive links when viewing a singular template.
			if ( $is_audiotheme_post_type && $post_type_archive_link === $item->url ) {
				$items[ $key ]->classes[] = 'current-menu-parent';
			}
		}

		// Add 'current-menu-parent' classes.
		$current_menu_parents = array_filter( $current_menu_parents );

		if ( ! empty( $current_menu_parents ) ) {
			foreach ( $items as $key => $item ) {
				if ( in_array( $item->ID, $current_menu_parents ) ) {
					$items[ $key ]->classes[] = 'current-menu-parent';
				}
			}
		}

		return $items;
	}

	/**
	 * Add audio metadata to attachment response objects.
	 *
	 * @since 2.0.0
	 *
	 * @param array   $response Attachment data to send as JSON.
	 * @param WP_Post $attachment Attachment object.
	 * @param array   $meta Attachment meta.
	 * @return array
	 */
	public function prepare_audio_attachment_for_js( $response, $attachment, $meta ) {
		if ( 'audio' !== $response['type'] ) {
			return $response;
		}

		$response['audiotheme'] = $meta;

		return $response;
	}

	/**
	 * Register urn: as an allowed protocol.
	 *
	 * The GUID gets passed through `esc_url_raw`, so we need to allow urn.
	 *
	 * @since 2.0.0
	 *
	 * @link https://github.com/rmccue/realguids
	 *
	 * @param  array $protocols Allowed protocols.
	 * @return array
	 */
	public function register_urn_protocol( $protocols ) {
		$protocols[] = 'urn';
		return $protocols;
	}

	/**
	 * Filter links for attachments uploaded to gigs, records, and tracks.
	 *
	 * Ensures /attachment/ is in the path when permalinks are enabled to let
	 * WordPress handle displaying the attachment page.
	 *
	 * @since 2.2.1
	 *
	 * @see get_attachment_link()
	 *
	 * @param string  $link          Attachment link.
	 * @param integer $attachment_id Attachment ID.
	 * @return string
	 */
	public function filter_attachment_links( $link, $attachment_id ) {
		global $wp_rewrite;

		$attachment = get_post( $attachment_id );
		$leavename  = false !== strpos( $link, '%postname%' );
		$parent     = $attachment->post_parent > 0 && $attachment->post_parent !== $attachment->ID ? get_post( $attachment->post_parent ) : false;

		if (
			! $wp_rewrite->using_permalinks()
			|| ! $parent
			|| ! in_array( $parent->post_type, array( 'audiotheme_gig', 'audiotheme_record', 'audiotheme_track' ) )
		) {
			return $link;
		}

		$parent_link = get_permalink( $parent );

		if ( false === strpos( $parent_link, '?' ) ) {
			$link = user_trailingslashit( trailingslashit( $parent_link ) . 'attachment/%postname%' );
		}

		if ( ! $leavename ) {
			$link = str_replace( '%postname%', $attachment->post_name, $link );
		}

		return $link;
	}
}
