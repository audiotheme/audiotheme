<?php
/*
This is a concept for setting a page as post type archive in
order to give the user control over the archive title, allow for
an introduction/description, and provide a familiar way to edit
the rewrite base via the page slug. It should also provide the
ability to easily add archive pages to a menu without the custom
nav menu meta box currently in place.

The field for designating a page as an archive is currently on
the Reading settings screen and will be moved in the future.

This will replace the rewrite base fields on the Permalinks
settings screen.

The code here is just a rough draft for demonstration purposes.

@todo Remove the AudioTheme Archive meta box from the nav menus screen
@todo Need to update nav menus to reflect when an archive page is being viewed.
*/

Audiotheme_Archive_Pages::load();

class Audiotheme_Archive_Pages {
	public static function load() {
		add_action( 'pre_update_option_audiotheme_page_for_discography', array( __CLASS__, 'filter_options_update' ) );
		
		add_action( 'post_updated', array( __CLASS__, 'update_archive_base' ), 10, 3 );
		
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}
	
	public static function filter_options_update( $value ) {
		$slug = ( empty( $value ) ) ? '' : get_page_uri( $value );
		self::update_discography_rewrite_base( $slug );
		
		return $value;
	}
	
	/*
	When a page that has been designated as an archive page is updated:
	- Make sure it doesn't have a parent
	- Update the rewrite base if the slug has changed
	
	May want to disable the Page Attributes meta box on an archive page.
	Should display a message somewhere on the archive page edit screen about it being used as an archive.
	May also want to designate the page somewhere in the list of pages.
	*/
	public static function update_archive_base( $post_id, $post_after, $post_before ) {
		if ( $post_after->post_name == $post_before->post_name ) {
			return;
		}
		
		if ( get_option( 'audiotheme_page_for_discography' ) == $post_id ) {
			// Fall back to the default base if the page has a parent.
			$slug = ( $post_after->post_parent ) ? '' : $post_after->post_name;
			self::update_discography_rewrite_base( $slug );
		}
	}
	
	public static function update_discography_rewrite_base( $slug ) {
		update_option( 'audiotheme_discography_rewrite_base', $slug );
		flush_rewrite_rules();
	}
	
	
	public static function register_settings() {
		register_setting( 'reading', 'audiotheme_page_for_discography' );
		
		add_settings_section( 'audiotheme-archive-pages', 'AudioTheme Archive Pages', '__return_null', 'reading' );

		add_settings_field(
			'discography-page',
			'Discography Page',
			array( __CLASS__, 'discography_page_field' ),
			'reading',
			'audiotheme-archive-pages'
		);
	}
	
	public static function discography_page_field() {
		?>
		<select name="audiotheme_page_for_discography">
			<option value=""></option>
			<?php
			$pages = get_posts( array(
				'post_type'   => 'page',
				'post_parent' => 0,
				'post_status' => 'publish',
				'orderby'     => 'title',
				'order'       => 'asc',
			) );
			
			if ( $pages ) {
				foreach ( $pages as $page ) {
					printf( '<option value="%d"%s>%s</option>',
						$page->ID,
						selected( $page->ID, get_option( 'audiotheme_page_for_discography' ), false ),
						esc_html( $page->post_title )
					);
				}
			}
			?>
		</select>
		<?php
	}
}


/* Template Tag API */

function the_audiotheme_page_archive_id() {
	if ( is_post_type_archive( 'audiotheme_record' ) ) {
		
	}
}

function get_audiotheme_archive_page_id( $archive ) {
	if ( 'discography' == $archive ) {
		return get_option( 'audiotheme_page_for_discography' );
	}
	
	return;
}

function the_audiotheme_archive_title() {
	if ( is_post_type_archive( 'audiotheme_record' ) ) {
		echo get_audiotheme_archive_title( 'discography' );
	}
}

function get_audiotheme_archive_title( $archive ) {
	$map = array(
		'audiotheme_gig'    => 'gigs',
		'audiotheme_record' => 'discography',
		// etc...
	);
	
	if ( isset( $map[ $archive ] ) ) {
		$archive = $map[ $archive ];
	}
	
	$page_id = get_audiotheme_archive_page_id( $archive );
	if ( $page_id ) {
		return get_the_title( $page_id );
	}
	
	return;
}

function get_audiotheme_archive_description( $archive ) {
	$page_id = get_audiotheme_archive_page_id( $archive );
	if ( $page_id ) {
		$page = get_post( $page_id );
		return apply_filters( 'the_content', $page->post_content );
	}
	
	return;
}
?>