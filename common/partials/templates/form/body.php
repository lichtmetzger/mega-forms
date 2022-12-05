<?php

/**
 * The Template for displaying megaforms body area of the form in view
 *
 * This template can be overridden by copying it to yourtheme/mega-forms/form/body.php.
 *
 * @see https://wpmegaforms.com/docs/template-structure/
 * @package Mega_Forms/Common/Templates
 * @version 1.0.0
 */
if (!defined('ABSPATH')) {
   exit; // Exit if accessed directly
}

# Print all form fields inside their associated wrappers
echo mf_api()->get_fields($form);