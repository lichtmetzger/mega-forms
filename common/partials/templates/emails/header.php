<?php

/**
 * The Template for displaying header of megaforms email
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
$image = mfget_option('email_header_image');
?>
<table id="mf_email_container" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
	<tbody>
		<tr>
			<td align="center" valign="top">

				<?php if (!empty($image)) { ?>
					<table id="mf_email_header" border="0" cellpadding="0" cellspacing="0">
						<tbody>
							<tr>
								<td id="mf_email_header_inner">
									<img src="<?php echo $image ?>" />
								</td>
							</tr>
						</tbody>
					</table>
				<?php } ?>

				<table id="mf_email_wrapper" border="0" cellpadding="0" cellspacing="0">
					<tbody>
						<tr>
							<td align="left" valign="top">
								<table id="mf_email_body" border="0" cellpadding="0" cellspacing="0">
									<tbody>
										<tr>
											<td id="mf_email_body_inner">