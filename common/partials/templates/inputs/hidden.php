<?php
/**
 * The Template for displaying input type: hidden
 *
 * This template can be overridden by copying it to yourtheme/mega-forms/input/hidden.php.
 *
 * @see https://wpmegaforms.com/docs/template-structure/
 * @package Mega_Forms/Common/Templates
 * @version 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
   exit; // Exit if accessed directly
}

echo get_mf_input( 'hidden', $attributes );
