<?php

class Struts_Option_Image extends Struts_Option {

	public function input_html() {
		$id = $this->html_id();
		$name = $this->html_name();

		echo '<input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_url( $this->value() ) . '" class="image-input" />';
		echo '<input type="button" id="' . esc_attr( $id . '_button' ) . '" value="' . esc_attr__( 'Upload Image', 'audiotheme' ) . '" data-type="image" data-field-id="' . esc_attr( $id ) . '" class="button struts-image-upload">';
	}

	protected function standard_validation( $value ) {
		return trim( $value );
	}
}