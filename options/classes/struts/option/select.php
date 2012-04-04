<?php

class Struts_Option_Select extends Struts_Option {
	public function input_html() {
		$id = $this->html_id();
		$name = $this->html_name();

		$output = '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';

		foreach ( $this->valid_values() as $value => $text ) {
			$output .= '<option value="' . esc_attr( $value ) . '" ' . selected( $this->value(), $value, false ) . '>' . esc_html( $text ) . '</option>';
		}

		$output .= "</select>";

		echo $output;
	}

	protected function standard_validation( $value ) {
		$valid_values = $this->valid_values();
		if ( array_key_exists( $value, $valid_values ) ) {
			return $value;
		}

		return $this->default_value();
	}
}