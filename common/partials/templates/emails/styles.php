<?php

/**
 * The Template for megaforms email styles
 *
 * This template can be overridden by copying it to yourtheme/mega-forms/emails/header.php.
 *
 * @see https://wpmegaforms.com/docs/template-structure/
 * @package Mega_Forms/Common/Templates
 * @version 1.0.0
 */
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

$styles = array();
$styles['primary_bg_color'] = mfget_option('email_primary_bg_color', '#f3f3f5');
$styles['secondary_bg_color'] = mfget_option('email_secondary_bg_color', '#ffffff');
$styles['primary_text_color'] = mfget_option('email_primary_text_color', '#000000');
$styles['secondary_text_color'] = mfget_option('email_secondary_text_color', '#cccccc');
$styles['body_font_size'] = '13px';
$styles['footer_font_size'] = '12px';

?>
#mf_email_container {
	background-color: <?php echo esc_attr($styles['primary_bg_color']); ?>;
	margin: 0;
	padding: 0;
	-webkit-text-size-adjust: none !important;
	width: 100%;
	font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;
}

#mf_email_header, #mf_email_footer {
	color: <?php echo esc_attr($styles['secondary_text_color']); ?>;
	padding:30px;
	text-align:left;
	max-width: 680px;
	width: 100%;
}
#mf_email_header a, #mf_email_footer a {
	color: <?php echo esc_attr($styles['secondary_text_color']); ?>;
	font-weight: normal;
	text-decoration: underline;
}
#mf_email_footer, #mf_email_footer a {
	font-size: <?php echo esc_attr($styles['footer_font_size']); ?>;
}
#mf_email_header img {
	border: none;
	display: inline-block;
	outline: none;
	text-decoration: none;
	text-transform: capitalize;
	vertical-align: middle;
    width: auto;
    max-height: 40px;
    background: transparent;
}
#mf_email_wrapper {
	border-radius: 5px;
	background-color: <?php echo esc_attr($styles['secondary_bg_color']); ?>;
	padding:30px;
	max-width: 680px;
	width: 100%;
	font-size: <?php echo esc_attr($styles['body_font_size']); ?>;
}
#mf_email_body_inner {
	color: <?php echo esc_attr($styles['primary_text_color']); ?>;
}
#mf_email_body_inner a {
	color: <?php echo esc_attr($styles['primary_text_color']); ?>;
	font-weight: normal;
	text-decoration: underline;
}
