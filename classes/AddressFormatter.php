<?php
/**
 * Venue address formatter.
 *
 * @package   AudioTheme
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.3.0
 */

/**
 * Venue address formatter class.
 *
 * @package AudioTheme
 * @since   2.3.0
 */
class AudioTheme_AddressFormatter {
	protected $data;

	/**
	 * Create a formatter.
	 *
	 * @since 2.3.0
	 *
	 * @param array|object $data Array of address data or a venue object.
	 */
	public function __construct( $data ) {
		if ( is_object( $data ) ) {
			$data = get_object_vars( $data );
		}

		$this->data = wp_parse_args( $data, array(
			'name'        => '',
			'address'     => '',
			'city'        => '',
			'state'       => '',
			'postal_code' => '',
			'country'     => '',
			'phone'       => '',
			'website'     => '',
		) );
	}

	/**
	 * Retrieve the address as HTML.
	 *
	 * @since 2.3.0
	 *
	 * @param array  $args Optional. Arguments for formatting the address.
	 * @return string
	 */
	public function get_html( $args = array() ) {
		$args = $this->parse_args( $args );

		// Wrap the data in HTML.
		$data = $this->add_vcard_markup( $this->data, $args );
		$data['separator'] = sprintf( '<span class="sep">%s</span>', $args['separator'] );

		// Cache the name to prepend after rendering the template.
		$name = $data['name'];
		$data['name'] = '';

		if ( ! $args['show_country'] ) {
			$data['country'] = '';
		}

		if ( ! $args['show_phone'] ) {
			$data['phone'] = '';
		}

		$output = $this->render( $args['template'], $data );

		// Wrap the address in a container.
		$output = sprintf(
			'<span class="venue-address adr"%2$s%3$s%4$s>%1$s</span>',
			$output,
			$args['microdata'] ? ' itemscope' : '',
			$args['microdata'] ? ' itemtype="http://schema.org/PostalAddress"' : '',
			$args['microdata'] ? ' itemprop="address"' : ''
		);

		// Prepend the venue name.
		if ( $args['show_name'] ) {
			$output = $name . ' ' . $output;
		}

		return $output;
	}

	/**
	 * Retrieve the address as plain text.
	 *
	 * @since 2.3.0
	 *
	 * @param array  $args Optional. Arguments for formatting the address.
	 * @return string
	 */
	public function get_text( $args = array() ) {
		$args = $this->parse_args( $args, array(
			'separator'    => "\n",
			'show_country' => true,
			'show_name'    => true,
			'show_phone'   => true,
		) );

		$data = array_map( 'esc_html', $this->data );
		$data['separator'] = $args['separator'];

		if ( ! $args['show_name'] ) {
			$data['name'] = '';
		}

		if ( ! $args['show_country'] ) {
			$data['country'] = '';
		}

		if ( ! $args['show_phone'] ) {
			$data['phone'] = '';
		}

		$output = $this->render( $args['template'], $data );

		return $output;
	}

	/**
	 * Parse address formatting arguments.
	 *
	 * @since 2.3.0
	 *
	 * @param array $args     Optional. Arguments for formatting the address.
	 * @param array $defaults Optional. Default argument values.
	 * @return array
	 */
	protected function parse_args( $args = array(), $defaults = array() ) {
		$args = wp_parse_args( $args, $defaults );

		$args = wp_parse_args( $args, array(
			'microdata'         => true,
			'show_country'      => true,
			'show_name'         => true,
			'show_name_link'    => true,
			'show_phone'        => true,
			'separator'         => '<br>',
			'template'          => '',
		) );

		// Use the country separator for compatiblity with versions before 2.3.0.
		if ( isset( $args['separator_country'] ) ) {
			$args['separator'] = $args['separator_country'];
		}

		if ( empty( $args['template'] ) ) {
			$args['template'] = self::get_template( $this->data['country'] );
		}

		return $args;
	}

	/**
	 * Render the template address.
	 *
	 * @since 2.3.0
	 *
	 * @param string $template Address format template.
	 * @param array  $context  Data to insert into the template.
	 * @return string
	 */
	protected function render( $template, $context ) {
		$replacements = array(
			'{name}'        => $context['name'],
			'{address}'     => $context['address'],
			'{city}'        => $context['city'],
			'{state}'       => $context['state'],
			'{postal_code}' => $context['postal_code'],
			'{country}'     => $context['country'],
			'{phone}'       => $context['phone'],
		);

		$output = str_replace(
			array_keys( $replacements ),
			$replacements,
			$template
		);

		$output = explode( "\n", $output );
		$output = array_filter( array_map( array( $this, 'trim' ), $output ) );
		$output = implode( $context['separator'], $output );

		return $output;
	}

	/**
	 * Add vCard markup to address data.
	 *
	 * @since 2.3.0
	 *
	 * @param array $data Address data.
	 * @param array $args Optional. Arguments for formatting the address.
	 * @return array
	 */
	protected function add_vcard_markup( $data, $args = array() ) {
		if ( $args['show_name'] && ! empty( $data['name'] ) ) {
			$data['name'] = sprintf(
				'<span class="venue-name fn org"%s>%s</span>',
				$args['microdata'] ? ' itemprop="name"' : '',
				esc_html( $data['name'] )
			);
		}

		if ( $args['show_name'] && $args['show_name_link'] && ! empty( $data['name'] ) && ! empty( $data['website'] ) ) {
			$data['name'] = sprintf(
				'<a href="%s" class="url"%s>%s</a>',
				esc_url( $data['website'] ),
				$args['microdata'] ? ' itemprop="url"' : '',
				$data['name']
			);
		}

		if ( ! empty( $data['address'] ) ) {
			$data['address'] = sprintf(
				'<span class="venue-street-address street-address"%s>%s</span>',
				$args['microdata'] ? ' itemprop="streetAddress"' : '',
				esc_html( $data['address'] )
			);
		}

		if ( ! empty( $data['city'] ) ) {
			$data['city'] = sprintf(
				'<span class="venue-locality locality"%s>%s</span>',
				$args['microdata'] ? ' itemprop="addressLocality"' : '',
				esc_html( $data['city'] )
			);
		}

		if ( ! empty( $data['state'] ) ) {
			$data['state'] = sprintf(
				'<span class="venue-region region"%s>%s</span>',
				$args['microdata'] ? ' itemprop="addressRegion"' : '',
				esc_html( $data['state'] )
			);
		}

		if ( ! empty( $data['postal_code'] ) ) {
			$data['postal_code'] = sprintf(
				'<span class="venue-postal-code postal-code"%s>%s</span>',
				$args['microdata'] ? ' itemprop="postalCode"' : '',
				esc_html( $data['postal_code'] )
			);
		}

		if ( $args['show_country'] && ! empty( $data['country'] ) && apply_filters( 'show_audiotheme_venue_country', true ) ) {
			$data['country'] = sprintf(
				'<span class="venue-country country-name country-name-%s"%s>%s</span>',
				sanitize_title( $data['country'] ),
				$args['microdata'] ? ' itemprop="addressCountry"' : '',
				esc_html( $data['country'] )
			);
		}

		if ( ! empty( $data['phone'] ) ) {
			$data['phone'] = sprintf(
				'<span class="venue-phone tel"%s>%s</span>',
				$args['microdata'] ? ' itemprop="telephone"' : '',
				esc_html( $data['phone'] )
			);
		}

		return $data;
	}

	/**
	 * Trim characters from the beginning and end of individual address lines.
	 *
	 * @since 2.3.0
	 *
	 * @param string $value String to trim.
	 * @return string
	 */
	public static function trim( $value ) {
		return trim( $value, ', ' );
	}

	/**
	 * Retrieve the address template for a country.
	 *
	 * @since 2.3.0
	 *
	 * @param string $country Country name or code.
	 * @return string
	 */
	protected static function get_template( $country ) {
		/* translators: Default address template. Use placeholders and \n for line separators. */
		$template = esc_html__( "{name}\n{address}\n{city}, {state} {postal_code}\n{country}\n{phone}", 'audiotheme' );

		$templates    = self::get_templates();
		$country_code = self::determine_country_code( $country );

		if ( isset( $templates[ $country_code ] ) ) {
			$template = $templates[ $country_code ];
		}

		return apply_filters( 'audiotheme_address_format_template', $template, $country_code, $country );
	}

	/**
	 * Retrieve address templates.
	 *
	 * @since 2.3.0
	 *
	 * @return array
	 */
	protected static function get_templates() {
		return array(
			'AT' => "{name}\n{address}\n{postal_code} {city}\n{country}\n{phone}",
			'AU' => "{name}\n{address}\n{city} {state} {postal_code}\n{country}\n{phone}",
			'CA' => "{name}\n{address}\n{city} {state} {postal_code}\n{country}\n{phone}",
			'DE' => "{name}\n{address}\n{postal_code} {city}\n{country}\n{phone}",
			'CH' => "{name}\n{address}\n{postal_code} {city}\n{country}\n{phone}",
			'ES' => "{name}\n{address}\n{postal_code} {city}\n{country}\n{phone}",
			'GB' => "{name}\n{address}\n{city}, {state}\n{postal_code}\n{country}\n{phone}",
			'IT' => "{name}\n{address}\n{postal_code} {city}\n{country}\n{phone}",
			'FR' => "{name}\n{address}\n{postal_code} {city}\n{country}\n{phone}",
			'NL' => "{name}\n{address}\n{postal_code} {city}\n{country}\n{phone}",
			"US" => "{name}\n{address}\n{city}, {state} {postal_code}\n{country}\n{phone}",
		);
	}

	/**
	 * Try to determine the country code given a country name.
	 *
	 * @since 2.3.0
	 *
	 * @param string $country Country name.
	 * @return string
	 */
	protected static function determine_country_code( $country ) {
		if ( empty( $country ) ) {
			return '';
		}

		if ( self::is_country_code( $country ) ) {
			return strtoupper( $country );
		}

		$country = strtolower( $country );
		foreach ( self::get_countries() as $code => $name ) {
			if ( strtolower( $name ) == $country ) {
				return $code;
			}
		}

		$variants = self::get_country_variants();
		if ( isset( $variants[ $country ] ) ) {
			return $variants[ $country ];
		}

		// @todo If the country code is still empty, try to match the locale to
		//       a country to use the default template?

		return '';
	}

	/**
	 * Whether a string is a country code.
	 *
	 * @since 2.3.0
	 *
	 * @param string $country Country code.
	 * @return boolean
	 */
	protected static function is_country_code( $country ) {
		$contries = self::get_countries();
		$country = strtoupper( $country );
		return 2 === strlen( $country ) && isset( $countries[ $country ] );
	}

	/**
	 * Retrieve country name variations to improve country name to code mapping.
	 *
	 * @since 2.3.0
	 *
	 * @todo https://en.wikipedia.org/wiki/List_of_alternative_country_names
	 *
	 * @todo Check an API and cache the results?
	 *       SELECT * FROM $wpdb->postmeta WHERE meta_key = '_audiotheme_country' GROUP BY meta_value;
	 *
	 * @return array
	 */
	protected static function get_country_variants() {
		return array(
			'germany'     => 'DE',
			'deutschland' => 'DE',
		);
	}

	/**
	 * Retrieve a list of countries.
	 *
	 * The key is the ISO 3166-1 alpha-2 code.
	 *
	 * @link https://en.wikipedia.org/wiki/ISO_3166-1
	 *
	 * @since 2.3.0
	 *
	 * @return array
	 */
	protected static function get_countries() {
		return array(
			'AF' => esc_html__( 'Afghanistan', 'audiotheme' ),
			'AX' => esc_html__( '&#197;land Islands', 'audiotheme' ),
			'AL' => esc_html__( 'Albania', 'audiotheme' ),
			'DZ' => esc_html__( 'Algeria', 'audiotheme' ),
			'AS' => esc_html__( 'American Samoa', 'audiotheme' ),
			'AD' => esc_html__( 'Andorra', 'audiotheme' ),
			'AO' => esc_html__( 'Angola', 'audiotheme' ),
			'AI' => esc_html__( 'Anguilla', 'audiotheme' ),
			'AQ' => esc_html__( 'Antarctica', 'audiotheme' ),
			'AG' => esc_html__( 'Antigua and Barbuda', 'audiotheme' ),
			'AR' => esc_html__( 'Argentina', 'audiotheme' ),
			'AM' => esc_html__( 'Armenia', 'audiotheme' ),
			'AW' => esc_html__( 'Aruba', 'audiotheme' ),
			'AU' => esc_html__( 'Australia', 'audiotheme' ),
			'AT' => esc_html__( 'Austria', 'audiotheme' ),
			'AZ' => esc_html__( 'Azerbaijan', 'audiotheme' ),
			'BS' => esc_html__( 'Bahamas', 'audiotheme' ),
			'BH' => esc_html__( 'Bahrain', 'audiotheme' ),
			'BD' => esc_html__( 'Bangladesh', 'audiotheme' ),
			'BB' => esc_html__( 'Barbados', 'audiotheme' ),
			'BY' => esc_html__( 'Belarus', 'audiotheme' ),
			'BE' => esc_html__( 'Belgium', 'audiotheme' ),
			'PW' => esc_html__( 'Belau', 'audiotheme' ),
			'BZ' => esc_html__( 'Belize', 'audiotheme' ),
			'BJ' => esc_html__( 'Benin', 'audiotheme' ),
			'BM' => esc_html__( 'Bermuda', 'audiotheme' ),
			'BT' => esc_html__( 'Bhutan', 'audiotheme' ),
			'BO' => esc_html__( 'Bolivia', 'audiotheme' ),
			'BQ' => esc_html__( 'Bonaire, Saint Eustatius and Saba', 'audiotheme' ),
			'BA' => esc_html__( 'Bosnia and Herzegovina', 'audiotheme' ),
			'BW' => esc_html__( 'Botswana', 'audiotheme' ),
			'BV' => esc_html__( 'Bouvet Island', 'audiotheme' ),
			'BR' => esc_html__( 'Brazil', 'audiotheme' ),
			'IO' => esc_html__( 'British Indian Ocean Territory', 'audiotheme' ),
			'VG' => esc_html__( 'British Virgin Islands', 'audiotheme' ),
			'BN' => esc_html__( 'Brunei', 'audiotheme' ),
			'BG' => esc_html__( 'Bulgaria', 'audiotheme' ),
			'BF' => esc_html__( 'Burkina Faso', 'audiotheme' ),
			'BI' => esc_html__( 'Burundi', 'audiotheme' ),
			'KH' => esc_html__( 'Cambodia', 'audiotheme' ),
			'CM' => esc_html__( 'Cameroon', 'audiotheme' ),
			'CA' => esc_html__( 'Canada', 'audiotheme' ),
			'CV' => esc_html__( 'Cape Verde', 'audiotheme' ),
			'KY' => esc_html__( 'Cayman Islands', 'audiotheme' ),
			'CF' => esc_html__( 'Central African Republic', 'audiotheme' ),
			'TD' => esc_html__( 'Chad', 'audiotheme' ),
			'CL' => esc_html__( 'Chile', 'audiotheme' ),
			'CN' => esc_html__( 'China', 'audiotheme' ),
			'CX' => esc_html__( 'Christmas Island', 'audiotheme' ),
			'CC' => esc_html__( 'Cocos (Keeling) Islands', 'audiotheme' ),
			'CO' => esc_html__( 'Colombia', 'audiotheme' ),
			'KM' => esc_html__( 'Comoros', 'audiotheme' ),
			'CG' => esc_html__( 'Congo (Brazzaville)', 'audiotheme' ),
			'CD' => esc_html__( 'Congo (Kinshasa)', 'audiotheme' ),
			'CK' => esc_html__( 'Cook Islands', 'audiotheme' ),
			'CR' => esc_html__( 'Costa Rica', 'audiotheme' ),
			'HR' => esc_html__( 'Croatia', 'audiotheme' ),
			'CU' => esc_html__( 'Cuba', 'audiotheme' ),
			'CW' => esc_html__( 'Cura&ccedil;ao', 'audiotheme' ),
			'CY' => esc_html__( 'Cyprus', 'audiotheme' ),
			'CZ' => esc_html__( 'Czech Republic', 'audiotheme' ),
			'DK' => esc_html__( 'Denmark', 'audiotheme' ),
			'DJ' => esc_html__( 'Djibouti', 'audiotheme' ),
			'DM' => esc_html__( 'Dominica', 'audiotheme' ),
			'DO' => esc_html__( 'Dominican Republic', 'audiotheme' ),
			'EC' => esc_html__( 'Ecuador', 'audiotheme' ),
			'EG' => esc_html__( 'Egypt', 'audiotheme' ),
			'SV' => esc_html__( 'El Salvador', 'audiotheme' ),
			'GQ' => esc_html__( 'Equatorial Guinea', 'audiotheme' ),
			'ER' => esc_html__( 'Eritrea', 'audiotheme' ),
			'EE' => esc_html__( 'Estonia', 'audiotheme' ),
			'ET' => esc_html__( 'Ethiopia', 'audiotheme' ),
			'FK' => esc_html__( 'Falkland Islands', 'audiotheme' ),
			'FO' => esc_html__( 'Faroe Islands', 'audiotheme' ),
			'FJ' => esc_html__( 'Fiji', 'audiotheme' ),
			'FI' => esc_html__( 'Finland', 'audiotheme' ),
			'FR' => esc_html__( 'France', 'audiotheme' ),
			'GF' => esc_html__( 'French Guiana', 'audiotheme' ),
			'PF' => esc_html__( 'French Polynesia', 'audiotheme' ),
			'TF' => esc_html__( 'French Southern Territories', 'audiotheme' ),
			'GA' => esc_html__( 'Gabon', 'audiotheme' ),
			'GM' => esc_html__( 'Gambia', 'audiotheme' ),
			'GE' => esc_html__( 'Georgia', 'audiotheme' ),
			'DE' => esc_html__( 'Germany', 'audiotheme' ),
			'GH' => esc_html__( 'Ghana', 'audiotheme' ),
			'GI' => esc_html__( 'Gibraltar', 'audiotheme' ),
			'GR' => esc_html__( 'Greece', 'audiotheme' ),
			'GL' => esc_html__( 'Greenland', 'audiotheme' ),
			'GD' => esc_html__( 'Grenada', 'audiotheme' ),
			'GP' => esc_html__( 'Guadeloupe', 'audiotheme' ),
			'GU' => esc_html__( 'Guam', 'audiotheme' ),
			'GT' => esc_html__( 'Guatemala', 'audiotheme' ),
			'GG' => esc_html__( 'Guernsey', 'audiotheme' ),
			'GN' => esc_html__( 'Guinea', 'audiotheme' ),
			'GW' => esc_html__( 'Guinea-Bissau', 'audiotheme' ),
			'GY' => esc_html__( 'Guyana', 'audiotheme' ),
			'HT' => esc_html__( 'Haiti', 'audiotheme' ),
			'HM' => esc_html__( 'Heard Island and McDonald Islands', 'audiotheme' ),
			'HN' => esc_html__( 'Honduras', 'audiotheme' ),
			'HK' => esc_html__( 'Hong Kong', 'audiotheme' ),
			'HU' => esc_html__( 'Hungary', 'audiotheme' ),
			'IS' => esc_html__( 'Iceland', 'audiotheme' ),
			'IN' => esc_html__( 'India', 'audiotheme' ),
			'ID' => esc_html__( 'Indonesia', 'audiotheme' ),
			'IR' => esc_html__( 'Iran', 'audiotheme' ),
			'IQ' => esc_html__( 'Iraq', 'audiotheme' ),
			'IE' => esc_html__( 'Ireland', 'audiotheme' ),
			'IM' => esc_html__( 'Isle of Man', 'audiotheme' ),
			'IL' => esc_html__( 'Israel', 'audiotheme' ),
			'IT' => esc_html__( 'Italy', 'audiotheme' ),
			'CI' => esc_html__( 'Ivory Coast', 'audiotheme' ),
			'JM' => esc_html__( 'Jamaica', 'audiotheme' ),
			'JP' => esc_html__( 'Japan', 'audiotheme' ),
			'JE' => esc_html__( 'Jersey', 'audiotheme' ),
			'JO' => esc_html__( 'Jordan', 'audiotheme' ),
			'KZ' => esc_html__( 'Kazakhstan', 'audiotheme' ),
			'KE' => esc_html__( 'Kenya', 'audiotheme' ),
			'KI' => esc_html__( 'Kiribati', 'audiotheme' ),
			'KW' => esc_html__( 'Kuwait', 'audiotheme' ),
			'KG' => esc_html__( 'Kyrgyzstan', 'audiotheme' ),
			'LA' => esc_html__( 'Laos', 'audiotheme' ),
			'LV' => esc_html__( 'Latvia', 'audiotheme' ),
			'LB' => esc_html__( 'Lebanon', 'audiotheme' ),
			'LS' => esc_html__( 'Lesotho', 'audiotheme' ),
			'LR' => esc_html__( 'Liberia', 'audiotheme' ),
			'LY' => esc_html__( 'Libya', 'audiotheme' ),
			'LI' => esc_html__( 'Liechtenstein', 'audiotheme' ),
			'LT' => esc_html__( 'Lithuania', 'audiotheme' ),
			'LU' => esc_html__( 'Luxembourg', 'audiotheme' ),
			'MO' => esc_html__( 'Macao S.A.R., China', 'audiotheme' ),
			'MK' => esc_html__( 'Macedonia', 'audiotheme' ),
			'MG' => esc_html__( 'Madagascar', 'audiotheme' ),
			'MW' => esc_html__( 'Malawi', 'audiotheme' ),
			'MY' => esc_html__( 'Malaysia', 'audiotheme' ),
			'MV' => esc_html__( 'Maldives', 'audiotheme' ),
			'ML' => esc_html__( 'Mali', 'audiotheme' ),
			'MT' => esc_html__( 'Malta', 'audiotheme' ),
			'MH' => esc_html__( 'Marshall Islands', 'audiotheme' ),
			'MQ' => esc_html__( 'Martinique', 'audiotheme' ),
			'MR' => esc_html__( 'Mauritania', 'audiotheme' ),
			'MU' => esc_html__( 'Mauritius', 'audiotheme' ),
			'YT' => esc_html__( 'Mayotte', 'audiotheme' ),
			'MX' => esc_html__( 'Mexico', 'audiotheme' ),
			'FM' => esc_html__( 'Micronesia', 'audiotheme' ),
			'MD' => esc_html__( 'Moldova', 'audiotheme' ),
			'MC' => esc_html__( 'Monaco', 'audiotheme' ),
			'MN' => esc_html__( 'Mongolia', 'audiotheme' ),
			'ME' => esc_html__( 'Montenegro', 'audiotheme' ),
			'MS' => esc_html__( 'Montserrat', 'audiotheme' ),
			'MA' => esc_html__( 'Morocco', 'audiotheme' ),
			'MZ' => esc_html__( 'Mozambique', 'audiotheme' ),
			'MM' => esc_html__( 'Myanmar', 'audiotheme' ),
			'NA' => esc_html__( 'Namibia', 'audiotheme' ),
			'NR' => esc_html__( 'Nauru', 'audiotheme' ),
			'NP' => esc_html__( 'Nepal', 'audiotheme' ),
			'NL' => esc_html__( 'Netherlands', 'audiotheme' ),
			'NC' => esc_html__( 'New Caledonia', 'audiotheme' ),
			'NZ' => esc_html__( 'New Zealand', 'audiotheme' ),
			'NI' => esc_html__( 'Nicaragua', 'audiotheme' ),
			'NE' => esc_html__( 'Niger', 'audiotheme' ),
			'NG' => esc_html__( 'Nigeria', 'audiotheme' ),
			'NU' => esc_html__( 'Niue', 'audiotheme' ),
			'NF' => esc_html__( 'Norfolk Island', 'audiotheme' ),
			'MP' => esc_html__( 'Northern Mariana Islands', 'audiotheme' ),
			'KP' => esc_html__( 'North Korea', 'audiotheme' ),
			'NO' => esc_html__( 'Norway', 'audiotheme' ),
			'OM' => esc_html__( 'Oman', 'audiotheme' ),
			'PK' => esc_html__( 'Pakistan', 'audiotheme' ),
			'PS' => esc_html__( 'Palestinian Territory', 'audiotheme' ),
			'PA' => esc_html__( 'Panama', 'audiotheme' ),
			'PG' => esc_html__( 'Papua New Guinea', 'audiotheme' ),
			'PY' => esc_html__( 'Paraguay', 'audiotheme' ),
			'PE' => esc_html__( 'Peru', 'audiotheme' ),
			'PH' => esc_html__( 'Philippines', 'audiotheme' ),
			'PN' => esc_html__( 'Pitcairn', 'audiotheme' ),
			'PL' => esc_html__( 'Poland', 'audiotheme' ),
			'PT' => esc_html__( 'Portugal', 'audiotheme' ),
			'PR' => esc_html__( 'Puerto Rico', 'audiotheme' ),
			'QA' => esc_html__( 'Qatar', 'audiotheme' ),
			'RE' => esc_html__( 'Reunion', 'audiotheme' ),
			'RO' => esc_html__( 'Romania', 'audiotheme' ),
			'RU' => esc_html__( 'Russia', 'audiotheme' ),
			'RW' => esc_html__( 'Rwanda', 'audiotheme' ),
			'BL' => esc_html__( 'Saint Barth&eacute;lemy', 'audiotheme' ),
			'SH' => esc_html__( 'Saint Helena', 'audiotheme' ),
			'KN' => esc_html__( 'Saint Kitts and Nevis', 'audiotheme' ),
			'LC' => esc_html__( 'Saint Lucia', 'audiotheme' ),
			'MF' => esc_html__( 'Saint Martin (French part)', 'audiotheme' ),
			'SX' => esc_html__( 'Saint Martin (Dutch part)', 'audiotheme' ),
			'PM' => esc_html__( 'Saint Pierre and Miquelon', 'audiotheme' ),
			'VC' => esc_html__( 'Saint Vincent and the Grenadines', 'audiotheme' ),
			'SM' => esc_html__( 'San Marino', 'audiotheme' ),
			'ST' => esc_html__( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'audiotheme' ),
			'SA' => esc_html__( 'Saudi Arabia', 'audiotheme' ),
			'SN' => esc_html__( 'Senegal', 'audiotheme' ),
			'RS' => esc_html__( 'Serbia', 'audiotheme' ),
			'SC' => esc_html__( 'Seychelles', 'audiotheme' ),
			'SL' => esc_html__( 'Sierra Leone', 'audiotheme' ),
			'SG' => esc_html__( 'Singapore', 'audiotheme' ),
			'SK' => esc_html__( 'Slovakia', 'audiotheme' ),
			'SI' => esc_html__( 'Slovenia', 'audiotheme' ),
			'SB' => esc_html__( 'Solomon Islands', 'audiotheme' ),
			'SO' => esc_html__( 'Somalia', 'audiotheme' ),
			'ZA' => esc_html__( 'South Africa', 'audiotheme' ),
			'GS' => esc_html__( 'South Georgia/Sandwich Islands', 'audiotheme' ),
			'KR' => esc_html__( 'South Korea', 'audiotheme' ),
			'SS' => esc_html__( 'South Sudan', 'audiotheme' ),
			'ES' => esc_html__( 'Spain', 'audiotheme' ),
			'LK' => esc_html__( 'Sri Lanka', 'audiotheme' ),
			'SD' => esc_html__( 'Sudan', 'audiotheme' ),
			'SR' => esc_html__( 'Suriname', 'audiotheme' ),
			'SJ' => esc_html__( 'Svalbard and Jan Mayen', 'audiotheme' ),
			'SZ' => esc_html__( 'Swaziland', 'audiotheme' ),
			'SE' => esc_html__( 'Sweden', 'audiotheme' ),
			'CH' => esc_html__( 'Switzerland', 'audiotheme' ),
			'SY' => esc_html__( 'Syria', 'audiotheme' ),
			'TW' => esc_html__( 'Taiwan', 'audiotheme' ),
			'TJ' => esc_html__( 'Tajikistan', 'audiotheme' ),
			'TZ' => esc_html__( 'Tanzania', 'audiotheme' ),
			'TH' => esc_html__( 'Thailand', 'audiotheme' ),
			'TL' => esc_html__( 'Timor-Leste', 'audiotheme' ),
			'TG' => esc_html__( 'Togo', 'audiotheme' ),
			'TK' => esc_html__( 'Tokelau', 'audiotheme' ),
			'TO' => esc_html__( 'Tonga', 'audiotheme' ),
			'TT' => esc_html__( 'Trinidad and Tobago', 'audiotheme' ),
			'TN' => esc_html__( 'Tunisia', 'audiotheme' ),
			'TR' => esc_html__( 'Turkey', 'audiotheme' ),
			'TM' => esc_html__( 'Turkmenistan', 'audiotheme' ),
			'TC' => esc_html__( 'Turks and Caicos Islands', 'audiotheme' ),
			'TV' => esc_html__( 'Tuvalu', 'audiotheme' ),
			'UG' => esc_html__( 'Uganda', 'audiotheme' ),
			'UA' => esc_html__( 'Ukraine', 'audiotheme' ),
			'AE' => esc_html__( 'United Arab Emirates', 'audiotheme' ),
			'GB' => esc_html__( 'United Kingdom', 'audiotheme' ),
			'US' => esc_html__( 'United States', 'audiotheme' ),
			'UM' => esc_html__( 'United States Minor Outlying Islands', 'audiotheme' ),
			'VI' => esc_html__( 'United States Virgin Islands', 'audiotheme' ),
			'UY' => esc_html__( 'Uruguay', 'audiotheme' ),
			'UZ' => esc_html__( 'Uzbekistan', 'audiotheme' ),
			'VU' => esc_html__( 'Vanuatu', 'audiotheme' ),
			'VA' => esc_html__( 'Vatican', 'audiotheme' ),
			'VE' => esc_html__( 'Venezuela', 'audiotheme' ),
			'VN' => esc_html__( 'Vietnam', 'audiotheme' ),
			'WF' => esc_html__( 'Wallis and Futuna', 'audiotheme' ),
			'EH' => esc_html__( 'Western Sahara', 'audiotheme' ),
			'WS' => esc_html__( 'Samoa', 'audiotheme' ),
			'YE' => esc_html__( 'Yemen', 'audiotheme' ),
			'ZM' => esc_html__( 'Zambia', 'audiotheme' ),
			'ZW' => esc_html__( 'Zimbabwe', 'audiotheme' ),
		);
	}
}
