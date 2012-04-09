<?php
/**
 * Include metaboxes
 *
 * @since 1.0
 */
include_once( 'records.php' );
include_once( 'track.php' );
include_once( 'video.php' );

/**
 * Enqueue CSS and JS
 *
 * @since 1.0
 */
function audiotheme_metabox_assets( $hook ) {
    $screen = get_current_screen();
    $screen = $screen->id;
    
    if( $screen != 'audiotheme_record' && $screen != 'audiotheme_track' )
        return;
        
    wp_register_style( 'audiotheme_metaboxes_css', AUDIOTHEME_URI . '/metaboxes/css/metaboxes.css', false, '1.0.0' );
    wp_enqueue_style( 'audiotheme_metaboxes_css' );
}
add_action( 'admin_enqueue_scripts', 'audiotheme_metabox_assets' );

/**
 * Update post meta shortcut
 *
 * @since 1.0
 */
function audiotheme_update_post_meta( $post_id, $fields_array = null, $type = 'text' ){
    if( is_array( $fields_array ) ):
        foreach( $fields_array as $field ){
             if ( isset( $_POST[$field] ) ):
             
                if( $type == 'url' ){
                    update_post_meta( $post_id, $field, esc_url( $_POST[$field], array( 'http', 'https' ) ) );
                } else{
                    update_post_meta( $post_id, $field, strip_tags( $_POST[$field] ) ); 
                }
             
            endif;
        }
    endif;
}

/**
 * Meta fields shortcut
 *
 * @since 1.0
 */
function audiotheme_meta_field( $post, $type = 'text', $field, $label = false, $desc = false ) { 
    $value = get_post_meta( $post->ID, $field, true ); ?>
    
    <p class="audiotheme-field">
        <?php if( $label ) { ?><label for="<?php echo $field; ?>"><?php echo $label; ?></label><?php } ?>
        
         <?php if( $type == 'url' ) { ?>
            <input type="<?php echo $type; ?>" id="<?php echo $field; ?>" name="<?php echo $field; ?>" value="<?php echo esc_url( $value ); ?>" />
        <?php } elseif( $type == 'text' ) { ?>
            <input type="<?php echo $type; ?>" id="<?php echo $field; ?>" name="<?php echo $field; ?>" value="<?php echo esc_attr( $value ); ?>" />
        <?php } ?>
        
        <?php if( $desc ) { ?><span class="description"><?php echo $desc; ?></span><?php } ?>
    </p>
<?php }



?>