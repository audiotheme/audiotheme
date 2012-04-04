<?php

class Struts_Option_Textarea extends Struts_Option {
	public function input_html() {
		$id = $this->html_id();
		$name = $this->html_name();
		$value = esc_attr( $this->value() );
		
		echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"  cols="130" rows="10">' . esc_attr( $value ) . '</textarea>';
	}

	protected function standard_validation( $value ) {
		return trim( $value );
	}
}