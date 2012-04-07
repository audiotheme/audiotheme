<?php
/**
 * Helper functions
 *
 * Include this file in a metabox file to have access to helper 
 * functions for creating and saving fields.
 *
 * @since 1.0
 */
 
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

function audiotheme_meta_field( $post, $type = 'text', $field, $label = false, $desc = false){ 
    $value = get_post_meta( $post->ID, $field, true ); ?>
    
    <p>
        <?php if( $label ){ ?><label for="<?php echo $field; ?>"><?php echo $label; ?></label><?php } ?>
        <?php if( $desc ){ ?><span class="description"><?php echo $desc; ?></span><?php } ?>
        
         <?php if( $type == 'url' ) { ?>
            <input type="<?php echo $type; ?>" id="<?php echo $field; ?>" name="<?php echo $field; ?>" value="<?php echo esc_url( $value ); ?>" />
        <?php } elseif( $type == 'text' ) { ?>
            <input type="<?php echo $type; ?>" id="<?php echo $field; ?>" name="<?php echo $field; ?>" value="<?php echo esc_attr( $value ); ?>" />
        <?php } ?>
    </p>
<?php }

?>