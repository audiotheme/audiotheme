<div id="taxonomy_<?php echo esc_attr( $taxonomy ); ?>" class="audiotheme-taxonomy-meta-box" data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>">
	<div class="audiotheme-taxonomy-term-list">
		<ul>
			<?php
			if ( ! empty( $terms ) )  {
				foreach ( $terms as $id => $name ) {
					printf(
						'<li><label><input type="checkbox" name="audiotheme_post_terms[%s][]" value="%d"%s> %s</label></li>',
						esc_attr( $taxonomy ),
						absint( $id ),
						checked( in_array( $id, $selected_ids ), true, false ),
						esc_html( $name )
					);
				}
			}
			?>
		</ul>
		<input type="hidden" name="audiotheme_post_terms[<?php echo esc_attr( $taxonomy ); ?>][]" value="0">
	</div>

	<?php /*<div class="audiotheme-add-term-group hide-if-no-js">
		<label for="add-<?php echo esc_attr( $taxonomy ); ?>" class="screen-reader-text"><?php echo esc_html( $taxonomy_object->labels->add_new_item ); ?></label>
		<span class="audiotheme-input-group">
			<input type="text" id="add-<?php echo esc_attr( $taxonomy ); ?>" class="audiotheme-add-term-field audiotheme-input-group-field">
			<span class="audiotheme-input-group-button">
				<input type="button" value="<?php echo esc_attr( $button_text ); ?>" class="button button-secondary audiotheme-button-load">
			</span>
		</span>
		<input type="hidden" class="audiotheme-add-term-nonce" value="<?php echo wp_create_nonce( 'add-term_' . $taxonomy ); ?>">
		<span class="audiotheme-add-term-response"></span>
	</div>*/ ?>
</div>
