<?php

/**
 * The Template for displaying body area of megaforms email
 *
 * This template can be overridden by copying it to yourtheme/mega-forms/emails/body.php.
 *
 * @see https://wpmegaforms.com/docs/template-structure/
 * @package Mega_Forms/Common/Templates
 * @version 1.0.0
 */
if (!defined('ABSPATH')) {
   exit; // Exit if accessed directly
}
echo apply_filters('the_content', $message);
