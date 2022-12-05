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

class MegaForms_Checkboxes extends MegaForms_Choice
{

	public $type = 'checkboxes';
	public $inputType = 'checkbox';

	public function get_field_title()
	{
		return esc_attr__('Checkboxes', 'megaforms');
	}

	public function get_field_icon()
	{
		return 'mega-icons-check-square-o';
	}
}

MF_Fields::register(new MegaForms_Checkboxes());
