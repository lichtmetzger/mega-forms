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
 * @author     Ali Khallad <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}


class MegaForms_Radios extends MegaForms_Choice
{

	public $type = 'radios';
	public $inputType = 'radio';

	public function get_field_title()
	{
		return esc_attr__('Radio Buttons', 'megaforms');
	}

	public function get_field_icon()
	{
		return 'mega-icons-dot-circle-o';
	}
}

MF_Fields::register(new MegaForms_Radios());
