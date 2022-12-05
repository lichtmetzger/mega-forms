<?php

/**
 * @link       https://wpali.com
 * @since      1.0.3
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 */

/**
 * Text field type class
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 * @author     ALI KHALLAD <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class MegaForms_Address extends MF_Field
{

	public $type = 'address';
	public $isCompoundField = true;
	public $editorSettings = array(
		'general' => array(
			'address_inputs'
		),
		'display' => array(
			'field_sub_label_position'
		),
	);
	public $editorExceptions = array(
		'field_placeholder',
		'field_default',
	);

	public function get_field_title()
	{
		return esc_attr__('Address', 'megaforms');
	}

	public function get_field_icon()
	{
		return 'mega-icons-map-marker';
	}

	public function get_field_display($value = null)
	{

		# Define arguements array and pass required arguements
		$args = $this->build_field_display_args();
		$args['value'] = $value !== null ? $value : array();
		$notices = mfget('compound_notices', $args, false);
		# Build field markup
		$addressSettings   = $this->get_setting_value('address_inputs');
		$addressComponents = $this->get_address_components($addressSettings, $args['value']);
		$sub_labels_pos    = $this->get_setting_value('field_sub_label_position');

		$addressHTML = '';
		$startDirection = 'left';
		foreach ($addressComponents as $aKey => $aVal) {

			$enabled = $aVal['enable'];

			# Only load field if enabled on the front end, and always load it in the backend.
			if ($enabled || $this->is_editor) {

				$subArgs = array();
				$subArgs['id'] = sprintf('%s[%s]', $args['id'], $aKey);
				$subArgs['tag'] = 'span';
				$subArgs['desc'] = !empty($aVal['desc']) ? $aVal['desc'] : $aVal['label'];
				$subArgs['desc_position'] = $sub_labels_pos;
				$subArgs['value'] = $aVal['value'] !== null ? $aVal['value'] : $aVal['default'];
				$subArgs['placeholder'] = $aVal['placeholder'];
				$subArgs['notice'] = isset($notices[$aKey]) && !isset($args['notice']) ? $notices[$aKey] : false;
				$subArgs['notice_css_class'] = isset($notices[$aKey]) ? true : false;
				// Define wrapper CSS classes
				$class = '';
				$class .= 'mf_sub_' . $aKey;

				if ('city' == $aKey || 'zip_code' == $aKey || 'state' == $aKey || 'country' == $aKey) {
					$class .= ' mf_half ' . $startDirection;
					if ($enabled) {
						$startDirection = $startDirection == 'left' ? 'right' : 'left';
					}
				} else {
					$class .= ' mf_full';
				}
				// Hide the field on the preview screen if not enabled
				if (!$enabled) {
					$class .= ' mf_hidden';
				}
				$subArgs['wrapper_class'] = $class;

				// Define input content
				$inputHTML = '';
				if ('country' == $aKey) {

					$cPlaceholder = !empty($subArgs['placeholder']) ? $subArgs['placeholder'] : __('-- Select Country --', 'megaforms');
					$inputHTML .= get_mf_select(array('name' => $subArgs['id'], 'value' => $subArgs['value'], 'placeholder' => $cPlaceholder), $this->get_countries());
				} else {
					$inputHTML .= get_mf_input('text', array('name' => $subArgs['id'], 'value' => $subArgs['value'], 'placeholder' => $subArgs['placeholder'],));
				}


				$subArgs['content'] = $inputHTML;

				// Create a break after the last sub input in each row to avoid display issues in case these inputs does not have same height.
				if (strpos($subArgs['wrapper_class'], 'right') !== false && !$this->is_editor) {
					$subArgs['after_field'] = '<span class="mf_clearfix"></span>';
				}

				$addressHTML .= mfinput('subcustom', $subArgs, $this->is_editor);
			}
		}

		# retrieve and return the input markup
		$args['content'] = $addressHTML;
		$input = mfinput('custom', $args, $this->is_editor);
		return $input;
	}

	/**********************************************************************
	 ********************** Fields Options Markup *************************
	 **********************************************************************/

	/**
	 * Returns the markup for max-length option.
	 *
	 * @return string
	 */
	protected function address_inputs()
	{

		$label 		= __('Address Inputs', 'megaforms');
		$field_key = 'address_inputs';
		$desc 		= __('Select the fields you want to display and customize them as needed by entering the appropriate values in their respective inputs.', 'megaforms');
		$value    = $this->get_setting_value($field_key);

		$args['id'] = $this->get_field_key('options', $field_key);
		$args['label'] = $label;
		$args['after_label'] = $this->get_description_tip_markup($label, $desc);

		$addressComponents = $this->get_address_components($value);

		$address_inputs = '';
		$address_inputs .= '<table class="mf-field-inputs"><tbody>';

		$address_inputs .= '<tr>';

		$address_inputs .= '<td class="mf-input-header-enable">' . __('Enable', 'megaforms') . '</td>';

		$navigate_input_settings = $this->get_js_helper_rules('none', 'navigate_input_settings', true);
		$address_inputs .= '<td class="mf-input-header-customize">';
		$address_inputs .= '<button class="previous" tabindex="-1" ' . mf_esc_attr('data-edit', $navigate_input_settings) . '><i class="mega-icons-arrow-back"></i></button>';
		$address_inputs .= '<span data-type="desc" class="first desc active">' . __('Sub Labels', 'megaforms') . '</span>';
		$address_inputs .= '<span data-type="default" class="default">' . __('Default Values', 'megaforms') . '</span>';
		$address_inputs .= '<span data-type="placeholder" class="last placeholder">' . __('Placeholders', 'megaforms') . '</span>';
		$address_inputs .= '<button class="next" tabindex="-1" ' . mf_esc_attr('data-edit', $navigate_input_settings) . '><i class="mega-icons-arrow-forward"></i></button>';
		$address_inputs .= '</td>';

		$address_inputs .= '</tr>';

		$countryPlaceholder = __('-- Select Country --', 'megaforms');
		foreach ($addressComponents as $aKey => $aVal) {

			$name = sprintf('%s[%s]', $args['id'], $aKey);
			$toggle_fields      = $this->get_js_helper_rules('.mf_sub_' . $aKey, 'toggle_field_inputs');
			$update_sublabel    = $this->get_js_helper_rules('.mf_sub_' . $aKey, 'update_sub_label', true);
			$update_value       = $this->get_js_helper_rules('.mf_sub_' . $aKey . ' :input', 'update_value', true);
			$update_placeholder = $this->get_js_helper_rules('.mf_sub_' . $aKey . ' :input', 'update_placeholder', true);

			$address_inputs .= sprintf('<tr data-key="%s">', $aKey);
			$address_inputs .= '<td>';
			$address_inputs .= mfinput('switch', array(
				'id'            => $name . '[enable]',
				'label'         => $aVal['label'],
				'value'         => $aVal['enable'],
				'size'          => 'small',
				'labelRight'    => true,
				'onchange_preview'    => $toggle_fields,
				'wrapper_class' => 'mf-field-inputs-switch',
			), true);
			$address_inputs .= '</td>';

			$address_inputs .= '<td>';

			# Sub Label Field
			$address_inputs .= get_mf_input('text', array('name' => sprintf('%s[desc]', $name), 'value' => $aVal['desc'], 'class' => 'desc active', 'placeholder' => $aVal['label'], 'data-default' => $aVal['label'], 'data-preview' => $update_sublabel));

			# Default Value Field
			if ('country' == $aKey) {
				$address_inputs .= get_mf_select(array('name' => sprintf('%s[default]', $name), 'value' => $aVal['default'], 'placeholder' => '', 'class' => 'default', 'data-preview' => $update_value), $this->get_countries());
			} else {
				$address_inputs .= get_mf_input('text', array('name' => sprintf('%s[default]', $name), 'value' => $aVal['default'], 'class' => 'default', 'data-preview' => $update_value));
			}
			# Placeholder Field
			if ('country' == $aKey) {
				$address_inputs .= get_mf_input('text', array('name' => sprintf('%s[placeholder]', $name), 'value' => $aVal['placeholder'], 'class' => 'placeholder', 'data-default' => $countryPlaceholder, 'data-preview' => $update_placeholder));
			} else {
				$address_inputs .= get_mf_input('text', array('name' => sprintf('%s[placeholder]', $name), 'value' => $aVal['placeholder'], 'class' => 'placeholder', 'data-preview' => $update_placeholder));
			}

			$address_inputs .= '</td>';

			$address_inputs .= '</tr>';
		}

		$address_inputs .= '</tbody></table>';

		$args['content'] = $address_inputs;
		$input = mfinput('custom', $args, true);
		return $input;
	}

	/**********************************************************************
	 ************************* Helpers ******************************
	 **********************************************************************/
	public function get_us_states()
	{
		$states = array(
			'AL' => 'Alabama',
			'AK' => 'Alaska',
			'AR' => 'Arkansas',
			'AZ' => 'Arizona',
			'CA' => 'California',
			'CO' => 'Colorado',
			'CT' => 'Connecticut',
			'DE' => 'Delaware',
			'DC' => 'District of Columbia',
			'FL' => 'Florida',
			'GA' => 'Georgia',
			'HI' => 'Hawaii',
			'ID' => 'Idaho',
			'IL' => 'Illinois',
			'IN' => 'Indiana',
			'IA' => 'Iowa',
			'KS' => 'Kansas',
			'KY' => 'Kentucky',
			'LA' => 'Louisiana',
			'ME' => 'Maine',
			'MD' => 'Maryland',
			'MA' => 'Massachusetts',
			'MI' => 'Michigan',
			'MN' => 'Minnesota',
			'MS' => 'Mississippi',
			'MO' => 'Missouri',
			'MT' => 'Montana',
			'NE' => 'Nebraska',
			'NV' => 'Nevada',
			'NH' => 'New Hampshire',
			'NJ' => 'New Jersey',
			'NM' => 'New Mexico',
			'NY' => 'New York',
			'NC' => 'North Carolina',
			'ND' => 'North Dakota',
			'OH' => 'Ohio',
			'OK' => 'Oklahoma',
			'OR' => 'Oregon',
			'PA' => 'Pennsylvania',
			'RI' => 'Rhode Island',
			'SC' => 'South Carolina',
			'SD' => 'South Dakota',
			'TN' => 'Tennessee',
			'TX' => 'Texas',
			'UT' => 'Utah',
			'VT' => 'Vermont',
			'VA' => 'Virginia',
			'WA' => 'Washington',
			'WV' => 'West Virginia',
			'WI' => 'Wisconsin',
			'WY' => 'Wyoming',
		);

		return apply_filters('mf_us_states', $states);
	}

	public function get_countries()
	{
		$countries = array(
			__('Afghanistan', 'megaforms'),
			__('Aland Islands', 'megaforms'),
			__('Albania', 'megaforms'),
			__('Algeria', 'megaforms'),
			__('American Samoa', 'megaforms'),
			__('Andorra', 'megaforms'),
			__('Angola', 'megaforms'),
			__('Anguilla', 'megaforms'),
			__('Antarctica', 'megaforms'),
			__('Antigua and Barbuda', 'megaforms'),
			__('Argentina', 'megaforms'),
			__('Armenia', 'megaforms'),
			__('Aruba', 'megaforms'),
			__('Australia', 'megaforms'),
			__('Austria', 'megaforms'),
			__('Azerbaijan', 'megaforms'),
			__('Bahamas', 'megaforms'),
			__('Bahrain', 'megaforms'),
			__('Bangladesh', 'megaforms'),
			__('Barbados', 'megaforms'),
			__('Belarus', 'megaforms'),
			__('Belgium', 'megaforms'),
			__('Belize', 'megaforms'),
			__('Benin', 'megaforms'),
			__('Bermuda', 'megaforms'),
			__('Bhutan', 'megaforms'),
			__('Bolivia', 'megaforms'),
			__('Bonaire, Sint Eustatius and Saba', 'megaforms'),
			__('Bosnia and Herzegovina', 'megaforms'),
			__('Botswana', 'megaforms'),
			__('Bouvet Island', 'megaforms'),
			__('Brazil', 'megaforms'),
			__('British Indian Ocean Territory', 'megaforms'),
			__('Brunei', 'megaforms'),
			__('Bulgaria', 'megaforms'),
			__('Burkina Faso', 'megaforms'),
			__('Burundi', 'megaforms'),
			__('Cambodia', 'megaforms'),
			__('Cameroon', 'megaforms'),
			__('Canada', 'megaforms'),
			__('Cape Verde', 'megaforms'),
			__('Cayman Islands', 'megaforms'),
			__('Central African Republic', 'megaforms'),
			__('Chad', 'megaforms'),
			__('Chile', 'megaforms'),
			__('China', 'megaforms'),
			__('Christmas Island', 'megaforms'),
			__('Cocos (Keeling) Islands', 'megaforms'),
			__('Colombia', 'megaforms'),
			__('Comoros', 'megaforms'),
			__('Congo', 'megaforms'),
			__('Cook Islands', 'megaforms'),
			__('Costa Rica', 'megaforms'),
			__('C&ocirc;te d\'Ivoire', 'megaforms'),
			__('Croatia', 'megaforms'),
			__('Cuba', 'megaforms'),
			__('Curacao', 'megaforms'),
			__('Cyprus', 'megaforms'),
			__('Czech Republic', 'megaforms'),
			__('Denmark', 'megaforms'),
			__('Djibouti', 'megaforms'),
			__('Dominica', 'megaforms'),
			__('Dominican Republic', 'megaforms'),
			__('East Timor', 'megaforms'),
			__('Ecuador', 'megaforms'),
			__('Egypt', 'megaforms'),
			__('El Salvador', 'megaforms'),
			__('Equatorial Guinea', 'megaforms'),
			__('Eritrea', 'megaforms'),
			__('Estonia', 'megaforms'),
			__('Ethiopia', 'megaforms'),
			__('Falkland Islands (Malvinas)', 'megaforms'),
			__('Faroe Islands', 'megaforms'),
			__('Fiji', 'megaforms'),
			__('Finland', 'megaforms'),
			__('France', 'megaforms'),
			__('French Guiana', 'megaforms'),
			__('French Polynesia', 'megaforms'),
			__('French Southern Territories', 'megaforms'),
			__('Gabon', 'megaforms'),
			__('Gambia', 'megaforms'),
			__('Georgia', 'megaforms'),
			__('Germany', 'megaforms'),
			__('Ghana', 'megaforms'),
			__('Gibraltar', 'megaforms'),
			__('Greece', 'megaforms'),
			__('Greenland', 'megaforms'),
			__('Grenada', 'megaforms'),
			__('Guadeloupe', 'megaforms'),
			__('Guam', 'megaforms'),
			__('Guatemala', 'megaforms'),
			__('Guernsey', 'megaforms'),
			__('Guinea', 'megaforms'),
			__('Guinea-Bissau', 'megaforms'),
			__('Guyana', 'megaforms'),
			__('Haiti', 'megaforms'),
			__('Heard Island and McDonald Islands', 'megaforms'),
			__('Holy See', 'megaforms'),
			__('Honduras', 'megaforms'),
			__('Hong Kong', 'megaforms'),
			__('Hungary', 'megaforms'),
			__('Iceland', 'megaforms'),
			__('India', 'megaforms'),
			__('Indonesia', 'megaforms'),
			__('Iran', 'megaforms'),
			__('Iraq', 'megaforms'),
			__('Ireland', 'megaforms'),
			__('Israel', 'megaforms'),
			__('Isle of Man', 'megaforms'),
			__('Italy', 'megaforms'),
			__('Jamaica', 'megaforms'),
			__('Japan', 'megaforms'),
			__('Jersey', 'megaforms'),
			__('Jordan', 'megaforms'),
			__('Kazakhstan', 'megaforms'),
			__('Kenya', 'megaforms'),
			__('Kiribati', 'megaforms'),
			__('North Korea', 'megaforms'),
			__('South Korea', 'megaforms'),
			__('Kosovo', 'megaforms'),
			__('Kuwait', 'megaforms'),
			__('Kyrgyzstan', 'megaforms'),
			__('Laos', 'megaforms'),
			__('Latvia', 'megaforms'),
			__('Lebanon', 'megaforms'),
			__('Lesotho', 'megaforms'),
			__('Liberia', 'megaforms'),
			__('Libya', 'megaforms'),
			__('Liechtenstein', 'megaforms'),
			__('Lithuania', 'megaforms'),
			__('Luxembourg', 'megaforms'),
			__('Macao', 'megaforms'),
			__('Macedonia', 'megaforms'),
			__('Madagascar', 'megaforms'),
			__('Malawi', 'megaforms'),
			__('Malaysia', 'megaforms'),
			__('Maldives', 'megaforms'),
			__('Mali', 'megaforms'),
			__('Malta', 'megaforms'),
			__('Marshall Islands', 'megaforms'),
			__('Martinique', 'megaforms'),
			__('Mauritania', 'megaforms'),
			__('Mauritius', 'megaforms'),
			__('Mayotte', 'megaforms'),
			__('Mexico', 'megaforms'),
			__('Micronesia', 'megaforms'),
			__('Moldova', 'megaforms'),
			__('Monaco', 'megaforms'),
			__('Mongolia', 'megaforms'),
			__('Montenegro', 'megaforms'),
			__('Montserrat', 'megaforms'),
			__('Morocco', 'megaforms'),
			__('Mozambique', 'megaforms'),
			__('Myanmar', 'megaforms'),
			__('Namibia', 'megaforms'),
			__('Nauru', 'megaforms'),
			__('Nepal', 'megaforms'),
			__('Netherlands', 'megaforms'),
			__('New Caledonia', 'megaforms'),
			__('New Zealand', 'megaforms'),
			__('Nicaragua', 'megaforms'),
			__('Niger', 'megaforms'),
			__('Nigeria', 'megaforms'),
			__('Niue', 'megaforms'),
			__('Norfolk Island', 'megaforms'),
			__('Northern Mariana Islands', 'megaforms'),
			__('Norway', 'megaforms'),
			__('Oman', 'megaforms'),
			__('Pakistan', 'megaforms'),
			__('Palau', 'megaforms'),
			__('Palestine', 'megaforms'),
			__('Panama', 'megaforms'),
			__('Papua New Guinea', 'megaforms'),
			__('Paraguay', 'megaforms'),
			__('Peru', 'megaforms'),
			__('Philippines', 'megaforms'),
			__('Pitcairn', 'megaforms'),
			__('Poland', 'megaforms'),
			__('Portugal', 'megaforms'),
			__('Puerto Rico', 'megaforms'),
			__('Qatar', 'megaforms'),
			__('Reunion', 'megaforms'),
			__('Romania', 'megaforms'),
			__('Russia', 'megaforms'),
			__('Rwanda', 'megaforms'),
			__('Saint Barthelemy', 'megaforms'),
			__('Saint Helena, Ascension and Tristan da Cunha', 'megaforms'),
			__('Saint Kitts and Nevis', 'megaforms'),
			__('Saint Lucia', 'megaforms'),
			__('Saint Martin (French part)', 'megaforms'),
			__('Saint Pierre and Miquelon', 'megaforms'),
			__('Saint Vincent and the Grenadines', 'megaforms'),
			__('Samoa', 'megaforms'),
			__('San Marino', 'megaforms'),
			__('Sao Tome and Principe', 'megaforms'),
			__('Saudi Arabia', 'megaforms'),
			__('Senegal', 'megaforms'),
			__('Serbia', 'megaforms'),
			__('Seychelles', 'megaforms'),
			__('Sierra Leone', 'megaforms'),
			__('Singapore', 'megaforms'),
			__('Sint Maarten (Dutch part)', 'megaforms'),
			__('Slovakia', 'megaforms'),
			__('Slovenia', 'megaforms'),
			__('Solomon Islands', 'megaforms'),
			__('Somalia', 'megaforms'),
			__('South Africa', 'megaforms'),
			__('South Georgia and the South Sandwich Islands', 'megaforms'),
			__('South Sudan', 'megaforms'),
			__('Spain', 'megaforms'),
			__('Sri Lanka', 'megaforms'),
			__('Sudan', 'megaforms'),
			__('Suriname', 'megaforms'),
			__('Svalbard and Jan Mayen', 'megaforms'),
			__('Swaziland', 'megaforms'),
			__('Sweden', 'megaforms'),
			__('Switzerland', 'megaforms'),
			__('Syria', 'megaforms'),
			__('Taiwan', 'megaforms'),
			__('Tajikistan', 'megaforms'),
			__('Tanzania', 'megaforms'),
			__('Thailand', 'megaforms'),
			__('Timor-Leste', 'megaforms'),
			__('Togo', 'megaforms'),
			__('Tokelau', 'megaforms'),
			__('Tonga', 'megaforms'),
			__('Trinidad and Tobago', 'megaforms'),
			__('Tunisia', 'megaforms'),
			__('Turkey', 'megaforms'),
			__('Turkmenistan', 'megaforms'),
			__('Turks and Caicos Islands', 'megaforms'),
			__('Tuvalu', 'megaforms'),
			__('Uganda', 'megaforms'),
			__('Ukraine', 'megaforms'),
			__('United Arab Emirates', 'megaforms'),
			__('United Kingdom', 'megaforms'),
			__('United States', 'megaforms'),
			__('United States Minor Outlying Islands', 'megaforms'),
			__('Uruguay', 'megaforms'),
			__('Uzbekistan', 'megaforms'),
			__('Vanuatu', 'megaforms'),
			__('Vatican City', 'megaforms'),
			__('Venezuela', 'megaforms'),
			__('Vietnam', 'megaforms'),
			__('Virgin Islands, British', 'megaforms'),
			__('Virgin Islands, U.S.', 'megaforms'),
			__('Wallis and Futuna', 'megaforms'),
			__('Western Sahara', 'megaforms'),
			__('Yemen', 'megaforms'),
			__('Zambia', 'megaforms'),
			__('Zimbabwe', 'megaforms'),
		);

		# Force the array to start at index 1 instead of 0
		array_unshift($countries, "");
		unset($countries[0]);

		return apply_filters('mf_countries', $countries);
	}

	public function get_country_codes()
	{
		$codes = array(
			__('AFGHANISTAN', 'megaforms')                       => 'AF',
			__('ALBANIA', 'megaforms')                           => 'AL',
			__('ALGERIA', 'megaforms')                           => 'DZ',
			__('AMERICAN SAMOA', 'megaforms')                    => 'AS',
			__('ANDORRA', 'megaforms')                           => 'AD',
			__('ANGOLA', 'megaforms')                            => 'AO',
			__('ANTIGUA AND BARBUDA', 'megaforms')               => 'AG',
			__('ARGENTINA', 'megaforms')                         => 'AR',
			__('ARMENIA', 'megaforms')                           => 'AM',
			__('AUSTRALIA', 'megaforms')                         => 'AU',
			__('AUSTRIA', 'megaforms')                           => 'AT',
			__('AZERBAIJAN', 'megaforms')                        => 'AZ',
			__('BAHAMAS', 'megaforms')                           => 'BS',
			__('BAHRAIN', 'megaforms')                           => 'BH',
			__('BANGLADESH', 'megaforms')                        => 'BD',
			__('BARBADOS', 'megaforms')                          => 'BB',
			__('BELARUS', 'megaforms')                           => 'BY',
			__('BELGIUM', 'megaforms')                           => 'BE',
			__('BELIZE', 'megaforms')                            => 'BZ',
			__('BENIN', 'megaforms')                             => 'BJ',
			__('BERMUDA', 'megaforms')                           => 'BM',
			__('BHUTAN', 'megaforms')                            => 'BT',
			__('BOLIVIA', 'megaforms')                           => 'BO',
			__('BOSNIA AND HERZEGOVINA', 'megaforms')            => 'BA',
			__('BOTSWANA', 'megaforms')                          => 'BW',
			__('BRAZIL', 'megaforms')                            => 'BR',
			__('BRUNEI', 'megaforms')                            => 'BN',
			__('BULGARIA', 'megaforms')                          => 'BG',
			__('BURKINA FASO', 'megaforms')                      => 'BF',
			__('BURUNDI', 'megaforms')                           => 'BI',
			__('CAMBODIA', 'megaforms')                          => 'KH',
			__('CAMEROON', 'megaforms')                          => 'CM',
			__('CANADA', 'megaforms')                            => 'CA',
			__('CAPE VERDE', 'megaforms')                        => 'CV',
			__('CAYMAN ISLANDS', 'megaforms')                    => 'KY',
			__('CENTRAL AFRICAN REPUBLIC', 'megaforms')          => 'CF',
			__('CHAD', 'megaforms')                              => 'TD',
			__('CHILE', 'megaforms')                             => 'CL',
			__('CHINA', 'megaforms')                             => 'CN',
			__('COLOMBIA', 'megaforms')                          => 'CO',
			__('COMOROS', 'megaforms')                           => 'KM',
			__('CONGO, DEMOCRATIC REPUBLIC OF THE', 'megaforms') => 'CD',
			__('CONGO, REPUBLIC OF THE', 'megaforms')            => 'CG',
			__('COSTA RICA', 'megaforms')                        => 'CR',
			__("CÔTE D'IVOIRE", 'megaforms')                     => 'CI',
			__('CROATIA', 'megaforms')                           => 'HR',
			__('CUBA', 'megaforms')                              => 'CU',
			__('CURAÇAO', 'megaforms')                           => 'CW',
			__('CYPRUS', 'megaforms')                            => 'CY',
			__('CZECH REPUBLIC', 'megaforms')                    => 'CZ',
			__('DENMARK', 'megaforms')                           => 'DK',
			__('DJIBOUTI', 'megaforms')                          => 'DJ',
			__('DOMINICA', 'megaforms')                          => 'DM',
			__('DOMINICAN REPUBLIC', 'megaforms')                => 'DO',
			__('EAST TIMOR', 'megaforms')                        => 'TL',
			__('ECUADOR', 'megaforms')                           => 'EC',
			__('EGYPT', 'megaforms')                             => 'EG',
			__('EL SALVADOR', 'megaforms')                       => 'SV',
			__('EQUATORIAL GUINEA', 'megaforms')                 => 'GQ',
			__('ERITREA', 'megaforms')                           => 'ER',
			__('ESTONIA', 'megaforms')                           => 'EE',
			__('ETHIOPIA', 'megaforms')                          => 'ET',
			__('FAROE ISLANDS', 'megaforms')                     => 'FO',
			__('FIJI', 'megaforms')                              => 'FJ',
			__('FINLAND', 'megaforms')                           => 'FI',
			__('FRANCE', 'megaforms')                            => 'FR',
			__('FRENCH POLYNESIA', 'megaforms')                  => 'PF',
			__('GABON', 'megaforms')                             => 'GA',
			__('GAMBIA', 'megaforms')                            => 'GM',
			__('GEORGIA', 'megaforms')                           => 'GE',
			__('GERMANY', 'megaforms')                           => 'DE',
			__('GHANA', 'megaforms')                             => 'GH',
			__('GREECE', 'megaforms')                            => 'GR',
			__('GREENLAND', 'megaforms')                         => 'GL',
			__('GRENADA', 'megaforms')                           => 'GD',
			__('GUAM', 'megaforms')                              => 'GU',
			__('GUATEMALA', 'megaforms')                         => 'GT',
			__('GUINEA', 'megaforms')                            => 'GN',
			__('GUINEA-BISSAU', 'megaforms')                     => 'GW',
			__('GUYANA', 'megaforms')                            => 'GY',
			__('HAITI', 'megaforms')                             => 'HT',
			__('HONDURAS', 'megaforms')                          => 'HN',
			__('HONG KONG', 'megaforms')                         => 'HK',
			__('HUNGARY', 'megaforms')                           => 'HU',
			__('ICELAND', 'megaforms')                           => 'IS',
			__('INDIA', 'megaforms')                             => 'IN',
			__('INDONESIA', 'megaforms')                         => 'ID',
			__('IRAN', 'megaforms')                              => 'IR',
			__('IRAQ', 'megaforms')                              => 'IQ',
			__('IRELAND', 'megaforms')                           => 'IE',
			__('ISRAEL', 'megaforms')                            => 'IL',
			__('ITALY', 'megaforms')                             => 'IT',
			__('JAMAICA', 'megaforms')                           => 'JM',
			__('JAPAN', 'megaforms')                             => 'JP',
			__('JORDAN', 'megaforms')                            => 'JO',
			__('KAZAKHSTAN', 'megaforms')                        => 'KZ',
			__('KENYA', 'megaforms')                             => 'KE',
			__('KIRIBATI', 'megaforms')                          => 'KI',
			__('NORTH KOREA', 'megaforms')                       => 'KP',
			__('SOUTH KOREA', 'megaforms')                       => 'KR',
			__('KOSOVO', 'megaforms')                            => 'KV',
			__('KUWAIT', 'megaforms')                            => 'KW',
			__('KYRGYZSTAN', 'megaforms')                        => 'KG',
			__('LAOS', 'megaforms')                              => 'LA',
			__('LATVIA', 'megaforms')                            => 'LV',
			__('LEBANON', 'megaforms')                           => 'LB',
			__('LESOTHO', 'megaforms')                           => 'LS',
			__('LIBERIA', 'megaforms')                           => 'LR',
			__('LIBYA', 'megaforms')                             => 'LY',
			__('LIECHTENSTEIN', 'megaforms')                     => 'LI',
			__('LITHUANIA', 'megaforms')                         => 'LT',
			__('LUXEMBOURG', 'megaforms')                        => 'LU',
			__('MACEDONIA', 'megaforms')                         => 'MK',
			__('MADAGASCAR', 'megaforms')                        => 'MG',
			__('MALAWI', 'megaforms')                            => 'MW',
			__('MALAYSIA', 'megaforms')                          => 'MY',
			__('MALDIVES', 'megaforms')                          => 'MV',
			__('MALI', 'megaforms')                              => 'ML',
			__('MALTA', 'megaforms')                             => 'MT',
			__('MARSHALL ISLANDS', 'megaforms')                  => 'MH',
			__('MAURITANIA', 'megaforms')                        => 'MR',
			__('MAURITIUS', 'megaforms')                         => 'MU',
			__('MEXICO', 'megaforms')                            => 'MX',
			__('MICRONESIA', 'megaforms')                        => 'FM',
			__('MOLDOVA', 'megaforms')                           => 'MD',
			__('MONACO', 'megaforms')                            => 'MC',
			__('MONGOLIA', 'megaforms')                          => 'MN',
			__('MONTENEGRO', 'megaforms')                        => 'ME',
			__('MOROCCO', 'megaforms')                           => 'MA',
			__('MOZAMBIQUE', 'megaforms')                        => 'MZ',
			__('MYANMAR', 'megaforms')                           => 'MM',
			__('NAMIBIA', 'megaforms')                           => 'NA',
			__('NAURU', 'megaforms')                             => 'NR',
			__('NEPAL', 'megaforms')                             => 'NP',
			__('NETHERLANDS', 'megaforms')                       => 'NL',
			__('NEW ZEALAND', 'megaforms')                       => 'NZ',
			__('NICARAGUA', 'megaforms')                         => 'NI',
			__('NIGER', 'megaforms')                             => 'NE',
			__('NIGERIA', 'megaforms')                           => 'NG',
			__('NORTHERN MARIANA ISLANDS', 'megaforms')          => 'MP',
			__('NORWAY', 'megaforms')                            => 'NO',
			__('OMAN', 'megaforms')                              => 'OM',
			__('PAKISTAN', 'megaforms')                          => 'PK',
			__('PALAU', 'megaforms')                             => 'PW',
			__('PALESTINE, STATE OF', 'megaforms')               => 'PS',
			__('PANAMA', 'megaforms')                            => 'PA',
			__('PAPUA NEW GUINEA', 'megaforms')                  => 'PG',
			__('PARAGUAY', 'megaforms')                          => 'PY',
			__('PERU', 'megaforms')                              => 'PE',
			__('PHILIPPINES', 'megaforms')                       => 'PH',
			__('POLAND', 'megaforms')                            => 'PL',
			__('PORTUGAL', 'megaforms')                          => 'PT',
			__('PUERTO RICO', 'megaforms')                       => 'PR',
			__('QATAR', 'megaforms')                             => 'QA',
			__('ROMANIA', 'megaforms')                           => 'RO',
			__('RUSSIA', 'megaforms')                            => 'RU',
			__('RWANDA', 'megaforms')                            => 'RW',
			__('SAINT KITTS AND NEVIS', 'megaforms')             => 'KN',
			__('SAINT LUCIA', 'megaforms')                       => 'LC',
			__('SAINT MARTIN', 'megaforms')					  => 'MF',
			__('SAINT VINCENT AND THE GRENADINES', 'megaforms')  => 'VC',
			__('SAMOA', 'megaforms')                             => 'WS',
			__('SAN MARINO', 'megaforms')                        => 'SM',
			__('SAO TOME AND PRINCIPE', 'megaforms')             => 'ST',
			__('SAUDI ARABIA', 'megaforms')                      => 'SA',
			__('SENEGAL', 'megaforms')                           => 'SN',
			__('SERBIA', 'megaforms')                            => 'RS',
			__('SEYCHELLES', 'megaforms')                        => 'SC',
			__('SIERRA LEONE', 'megaforms')                      => 'SL',
			__('SINGAPORE', 'megaforms')                         => 'SG',
			__('SINT MAARTEN', 'megaforms')                      => 'SX',
			__('SLOVAKIA', 'megaforms')                          => 'SK',
			__('SLOVENIA', 'megaforms')                          => 'SI',
			__('SOLOMON ISLANDS', 'megaforms')                   => 'SB',
			__('SOMALIA', 'megaforms')                           => 'SO',
			__('SOUTH AFRICA', 'megaforms')                      => 'ZA',
			__('SPAIN', 'megaforms')                             => 'ES',
			__('SRI LANKA', 'megaforms')                         => 'LK',
			__('SUDAN', 'megaforms')                             => 'SD',
			__('SUDAN, SOUTH', 'megaforms')                      => 'SS',
			__('SURINAME', 'megaforms')                          => 'SR',
			__('SWAZILAND', 'megaforms')                         => 'SZ',
			__('SWEDEN', 'megaforms')                            => 'SE',
			__('SWITZERLAND', 'megaforms')                       => 'CH',
			__('SYRIA', 'megaforms')                             => 'SY',
			__('TAIWAN', 'megaforms')                            => 'TW',
			__('TAJIKISTAN', 'megaforms')                        => 'TJ',
			__('TANZANIA', 'megaforms')                          => 'TZ',
			__('THAILAND', 'megaforms')                          => 'TH',
			__('TOGO', 'megaforms')                              => 'TG',
			__('TONGA', 'megaforms')                             => 'TO',
			__('TRINIDAD AND TOBAGO', 'megaforms')               => 'TT',
			__('TUNISIA', 'megaforms')                           => 'TN',
			__('TURKEY', 'megaforms')                            => 'TR',
			__('TURKMENISTAN', 'megaforms')                      => 'TM',
			__('TUVALU', 'megaforms')                            => 'TV',
			__('UGANDA', 'megaforms')                            => 'UG',
			__('UKRAINE', 'megaforms')                           => 'UA',
			__('UNITED ARAB EMIRATES', 'megaforms')              => 'AE',
			__('UNITED KINGDOM', 'megaforms')                    => 'GB',
			__('UNITED STATES', 'megaforms')                     => 'US',
			__('URUGUAY', 'megaforms')                           => 'UY',
			__('UZBEKISTAN', 'megaforms')                        => 'UZ',
			__('VANUATU', 'megaforms')                           => 'VU',
			__('VATICAN CITY', 'megaforms')                      => 'VA',
			__('VENEZUELA', 'megaforms')                         => 'VE',
			__('VIRGIN ISLANDS, BRITISH', 'megaforms')           => 'VG',
			__('VIRGIN ISLANDS, U.S.', 'megaforms')              => 'VI',
			__('VIETNAM', 'megaforms')                           => 'VN',
			__('YEMEN', 'megaforms')                             => 'YE',
			__('ZAMBIA', 'megaforms')                            => 'ZM',
			__('ZIMBABWE', 'megaforms')                          => 'ZW',
		);

		return $codes;
	}
	public function get_country($index)
	{
		$countries = $this->get_countries();
		return mfget($index, $countries);
	}
	public function get_country_code($country_name)
	{
		$codes = $this->get_country_codes();
		return mfget(mb_strtoupper($country_name), $codes);
	}
	public function is_valid_zip_code($country, $value)
	{

		$country_regex = apply_filters('mf_zip_code_validation_regex', array(
			"US" => "/\d{5}([\-]?\d{4})?/i",
			"UK" => "/(GIR|[A-Z]\d[A-Z\d]??|[A-Z]{2}\d[A-Z\d]??)[ ]??(\d[A-Z]{2})/i",
			"DE" => "/\b((?:0[1-46-9]\d{3})|(?:[1-357-9]\d{4})|(?:[4][0-24-9]\d{3})|(?:[6][013-9]\d{3}))\b/i",
			"CA" => "/([ABCEGHJKLMNPRSTVXY]\d[ABCEGHJKLMNPRSTVWXYZ])\ {0,1}(\d[ABCEGHJKLMNPRSTVWXYZ]\d)/i",
			"FR" => "/(F-)?((2[A|B])|[0-9]{2})[ ]?[0-9]{3}/i",
			"IT" => "/(V-|I-)?[0-9]{5}/i",
			"AU" => "/(0[289][0-9]{2})|([1345689][0-9]{3})|(2[0-8][0-9]{2})|(290[0-9])|(291[0-4])|(7[0-4][0-9]{2})|(7[8-9][0-9]{2})/i",
			"NL" => "/[1-9][0-9]{3}\s?([a-zA-Z]{2})?/i",
			"ES" => "/([1-9]{2}|[0-9][1-9]|[1-9][0-9])[0-9]{3}/i",
			"DK" => "/([D|d][K|k]( |-))?[1-9]{1}[0-9]{3}/i",
			"SE" => "/(s-|S-){0,1}[0-9]{3}\s?[0-9]{2}/i",
			"BE" => "/[1-9]{1}[0-9]{3}/i",
			'default' => "/.+/i" // any chars
		));

		$country_code = $this->get_country_code($country);
		if (!empty($country_code) && isset($country_regex[$country_code])) {
			return preg_match($country_regex[$country_code], $value);
		}

		return preg_match($country_regex['default'], $value);
	}

	public function get_address_components($settingValues, $displayValues = array())
	{

		$components = array(
			'address_line_1'  => array(
				'label'         => __('Address', 'megaforms'),
				'enable'        => isset($settingValues['address_line_1']['enable']) && $settingValues['address_line_1']['enable'] ? true : false,
				'default'       => isset($settingValues['address_line_1']['default']) && !empty($settingValues['address_line_1']['default'])  ? $settingValues['address_line_1']['default'] : '',
				'desc'          => isset($settingValues['address_line_1']['desc']) && !empty($settingValues['address_line_1']['desc'])  ? $settingValues['address_line_1']['desc'] : '',
				'placeholder'   => isset($settingValues['address_line_1']['placeholder']) && !empty($settingValues['address_line_1']['placeholder'])  ? $settingValues['address_line_1']['placeholder'] : '',
				'value'         => isset($displayValues['address_line_1']) && !empty($displayValues['address_line_1'])  ? $displayValues['address_line_1'] : null,
				'is_required'   => true,
			),
			'address_line_2' => array(
				'label'         => __('Address Line 2', 'megaforms'),
				'enable'        => isset($settingValues['address_line_2']['enable']) && $settingValues['address_line_2']['enable']  ? true : false,
				'default'       => isset($settingValues['address_line_2']['default']) && !empty($settingValues['address_line_2']['desc'])  ? $settingValues['address_line_2']['default'] : '',
				'desc'          => isset($settingValues['address_line_2']['desc']) && !empty($settingValues['address_line_2']['desc'])  ? $settingValues['address_line_2']['desc'] : '',
				'placeholder'   => isset($settingValues['address_line_2']['placeholder']) && !empty($settingValues['address_line_2']['placeholder'])  ? $settingValues['address_line_2']['placeholder'] : '',
				'value'         => isset($displayValues['address_line_2']) && !empty($displayValues['address_line_2'])  ? $displayValues['address_line_2'] : null,
				'is_required'   => false,
			),
			'city' => array(
				'label'         => __('City', 'megaforms'),
				'enable'        => isset($settingValues['city']['enable']) && $settingValues['city']['enable']  ? true : false,
				'default'       => isset($settingValues['city']['default']) && !empty($settingValues['city']['default'])  ? $settingValues['city']['default'] : '',
				'desc'          => isset($settingValues['city']['desc']) && !empty($settingValues['city']['desc'])  ? $settingValues['city']['desc'] : '',
				'placeholder'   => isset($settingValues['city']['placeholder']) && !empty($settingValues['city']['placeholder'])  ? $settingValues['city']['placeholder'] : '',
				'value'         => isset($displayValues['city']) && !empty($displayValues['city'])  ? $displayValues['city'] : null,
				'is_required'   => true,
			),
			'state' => array(
				'label'         => __('State / Province / Region', 'megaforms'),
				'enable'        => isset($settingValues['state']['enable']) && $settingValues['state']['enable']  ? true : false,
				'default'       => isset($settingValues['state']['default']) && !empty($settingValues['state']['default'])  ? $settingValues['state']['default'] : '',
				'desc'          => isset($settingValues['state']['desc']) && !empty($settingValues['state']['desc'])  ? $settingValues['state']['desc'] : '',
				'placeholder'   => isset($settingValues['state']['placeholder']) && !empty($settingValues['state']['placeholder'])  ? $settingValues['state']['placeholder'] : '',
				'value'         => isset($displayValues['state']) && !empty($displayValues['state'])  ? $displayValues['state'] : null,
				'is_required'   => false,
			),
			'zip_code' => array(
				'label'         => __('ZIP / Postal Code', 'megaforms'),
				'enable'        => isset($settingValues['zip_code']['enable']) && $settingValues['zip_code']['enable']  ? true : false,
				'default'       => isset($settingValues['zip_code']['default']) && !empty($settingValues['zip_code']['default'])  ? $settingValues['zip_code']['default'] : '',
				'desc'          => isset($settingValues['zip_code']['desc']) && !empty($settingValues['zip_code']['desc'])  ? $settingValues['zip_code']['desc'] : '',
				'placeholder'   => isset($settingValues['zip_code']['placeholder']) && !empty($settingValues['zip_code']['placeholder'])  ? $settingValues['zip_code']['placeholder'] : '',
				'value'         => isset($displayValues['zip_code']) && !empty($displayValues['zip_code'])  ? $displayValues['zip_code'] : null,
				'is_required'   => true,
			),
			'country' => array(
				'label'         => __('Country', 'megaforms'),
				'enable'        => isset($settingValues['country']['enable']) && $settingValues['country']['enable'] ? true : false,
				'default'       => isset($settingValues['country']['default']) && !empty($settingValues['country']['default'])  ? $settingValues['country']['default'] : '',
				'desc'          => isset($settingValues['country']['desc']) && !empty($settingValues['country']['desc']) ? $settingValues['country']['desc'] : '',
				'placeholder'   => isset($settingValues['country']['placeholder']) && !empty($settingValues['country']['placeholder'])  ? $settingValues['country']['placeholder'] : __('-- Select Country --', 'megaforms'),
				'value'         => isset($displayValues['country']) && !empty($displayValues['country'])  ? $displayValues['country'] : null,
				'is_required'   => true,
			),
		);

		// Make sure all sub fields are enabled by default if not already saved to database ( when adding new field to the form )
		if (empty($settingValues)) {
			foreach ($components as $key => $val) {
				$components[$key]['enable'] = true;
			}
		}

		return apply_filters('mf_address_components', $components);
	}

	public function get_formatted_value_short($value)
	{

		if (is_array($value)) {
			if (isset($value['country'])) {
				$value['country'] = $this->get_country($value['country']);
			}
			$value = array_filter($value);
			return esc_html(implode(', ', $value));
		}


		return esc_html($value);
	}

	public function get_formatted_value_long($value)
	{

		if (is_array($value)) {
			$output = '';
			$output .= '<ul class="mf_formatted_' . $this->type . '_value">';
			foreach ($value as $key => $val) {
				if ($key == 'country') {
					$output .= !empty($val) ? '<li>' . esc_html($this->get_country($val)) . '</li>' : '';
				} else {
					$output .= !empty($val) ? '<li>' . esc_html($val) . '</li>' : '';
				}
			}
			$output .= '</ul>';
			return $output;
		}

		return esc_html($value);
	}
	/**********************************************************************
	 ********************* Validation && Sanitazation *********************
	 **********************************************************************/

	protected function compound_required_check($value)
	{

		$field_settings = $this->get_setting_value('address_inputs');
		$address_components = $this->get_address_components($field_settings, $value);

		return $this->compound_required_check_helper($address_components, $value);
	}

	public function validate($value, $context = '')
	{

		// Only validate zip code ( the rest can accept any text value )
		if (!empty($value['country']) && !empty($value['zip_code'])) {

			if (!$this->is_valid_zip_code($value['country'], $value['zip_code'])) {
				$notice = __('Please enter a valid zip code.');
				return array(
					'notice' => $notice,
					'compound_notices' => array(
						'zip_code' => $notice
					),
				);
			}
		}

		return true;
	}
	public function sanitize_settings()
	{

		$sanitized = parent::sanitize_settings();

		$address_inputs = $this->get_setting_value('address_inputs', array());

		foreach ($address_inputs as &$component) {
			foreach ($component as $key => &$val) {

				if ($key == 'enable') {
					$val = mfget_bool_value($val);
				}

				if ($key == 'default' || $key == 'desc' || $key == 'placeholder') {
					$val = wp_strip_all_tags($val);
				}
			}
		}

		$addressComponents = $this->get_address_components($address_inputs);

		$sanitized['address_inputs'] = $addressComponents;

		return $sanitized;
	}
}

MF_Fields::register(new MegaForms_Address());
