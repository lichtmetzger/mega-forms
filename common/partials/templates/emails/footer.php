<?php

/**
 * The Template for displaying footer area of megaforms email
 *
 * This template can be overridden by copying it to yourtheme/mega-forms/emails/footer.php.
 *
 * @see https://wpmegaforms.com/docs/template-structure/
 * @package Mega_Forms/Common/Templates
 * @version 1.0.0
 */
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
$footer_text = mfget_option('email_footer_text');
?>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>

<?php if (!empty($footer_text)) { ?>
	<table border="0" cellpadding="10" cellspacing="0" id="mf_email_footer">
		<tbody>
			<tr>
				<td valign="top" id="mf_email_footer_inner">
					<?php echo apply_filters('the_content', mf_merge_tags()->process($footer_text)); ?>
				</td>
			</tr>
		</tbody>
	</table>
<?php } ?>

</td>
</tr>
</table>