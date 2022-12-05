<?php

/**
 * @link       https://wpali.com
 * @since      1.0.0
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

class MegaForms_Select extends MegaForms_Choice
{

	public $type = 'select';
	public $inputType = 'select';

	public function get_field_title()
	{
		return esc_attr__('Select Dropdown', 'megaforms');
	}

	public function get_field_icon()
	{
		return 'mega-icons-caret-square-o-down';
	}
}

MF_Fields::register(new MegaForms_Select());
