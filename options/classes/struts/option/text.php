<?php

class Struts_Option_Text extends Struts_Option {
	public function input_html() {
		$id = $this->html_id();
		$name = $this->html_name();
		$value = esc_attr( $this->value() );

		echo '<input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" />';
	}

	protected function standard_validation( $value ) {
		return trim( $value );
	}
}