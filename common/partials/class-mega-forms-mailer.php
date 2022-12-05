<?php

/**
 * Mega Forms Mailer
 *
 * @link       https://wpali.com
 * @since      1.0.0
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials
 */

use Pelago\Emogrifier\CssInliner;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MF_Mailer
{

    /**
     * Notifications to be scheduled.
     *
     * @var array
     */
    private static $deferred = array();

    /**
     * The single instance of the class.
     *
     * @var MF_Mailer
     * @since 1.0.0
     */
    protected static $_instance = null;

    /**
     * Main MF_Mailer Instance.
     *
     * Ensures only one instance of MF_Mailer is loaded or can be loaded.
     *
     * @since 1.0.0
     * @see mf_mail()
     * @return MF_Mailer - Main instance.
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Sends all notifications collected during execution.
     *
     * Do not call manually.
     *
     * @access private
     */
    public static function send_deferred_notifications()
    {
        if (empty(self::$deferred)) {
            return false;
        }

        foreach (self::$deferred as $notification) {
            self::send($notification['email'], $notification['subject'], $notification['message'], $notification['args']);
        }
    }
    /**
     * Sets up an email notification to be sent at the end of the script's execution.
     *
     * Do not call manually.
     *
     * @since    1.0.0
     * @param string $notification
     * @param array  $args
     */
    public static function queue($email, $subject, $message, $args = array())
    {
        $notification = array(
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
            'args' => $args,
        );
        self::$deferred[] = $notification;
    }
    /**
     * Sends an email notification.
     *
     *
     * @param string    $email              The reciever email.
     * @param string    $subject            Email subject.
     * @param string    $content            Email content.
     * @param array     $args               Email arguements.
     * @return bool
     */
    public static function send($emails, $subject, $message, $args = array())
    {

        $to = array();
        // Extract email, or emails into an array
        if (strpos($emails, ',') !== false) {
            $to = explode(",", $emails);
        } else {
            $to[] = $emails;
        }

        // Clean up
        $recipients = array();
        foreach ($to as $email) {
            $email = trim($email);
            if (is_email($email)) {
                $recipients[] = $email;
            }
        }

        // Bail out if no valid recipient was provided
        if (empty($recipients)) {
            return false;
        }

        // Prepare headers
        $headers = isset($args['headers']) && is_array($args['headers']) ? $args['headers'] : array();
        $attachments = isset($args['attachments']) && is_array($args['attachments']) ? $args['attachments'] : array();

        if (!empty($args['from'])) {
            $headers[] = 'From: ' . htmlspecialchars_decode(trim($args['from']));
        } else {
            $from_name = mfget_option('email_from_name', get_bloginfo('name'));
            $from_address =  mfget_option('email_from_address', get_bloginfo('admin_email'));
            $headers[] = htmlspecialchars_decode('From: ' . $from_name . ' <' . $from_address . '>');
        }

        if (!empty($args['cc'])) {
            if (is_array($args['cc'])) {
                foreach ($args['cc'] as $cc) {
                    $headers[] = 'Cc: ' . $cc;
                }
            } else {
                $headers[] = 'CC: ' . $args['cc'];
            }
        }

        if (!empty($args['bcc'])) {
            if (is_array($args['bcc'])) {
                foreach ($args['bcc'] as $bcc) {
                    $headers[] = 'Bcc: ' . $bcc;
                }
            } else {
                $headers[] = 'Bcc: ' . $args['bcc'];
            }
        }

        if (!empty($args['replyto'])) {
            $headers[] = 'Reply-To: ' . $args['replyto'];
        }

        $headers[] = 'Content-Type: text/html';

        // Prepare message content
        $args['message'] = $message;

        $content = self::get_email_content($args);

        /**
         * Allows for short-circuiting the actual sending of email notifications.
         *
         */
        if (!apply_filters('mf_do_send_email', true, $email, $subject, $content, $args)) {
            return false;
        }

        // Send the email
        return wp_mail($recipients, $subject, $content, $headers, $attachments);
    }

    /**
     * Generates the content for an email.
     *
     * @access private
     *
     * @param string $message
     * @return string
     */
    private static function get_email_content($args)
    {

        $is_plain = mfget_option('email_template', 'html') == 'html' ? false : true;

        ob_start();

        /**
         * Output the header for megaforms emails.
         *
         */
        if (!$is_plain) {
            echo self::get_header($args);
        }
        /**
         * Output the body for megaforms emails.
         *
         */
        echo self::get_body($args);


        /**
         * Output the footer for megaforms emails.
         *
         */
        if (!$is_plain) {
            echo self::get_footer($args);
        }

        $content = ob_get_clean();

        if (!$is_plain) {
            $content = self::inject_styles($content);
        }

        return apply_filters('mf_email_content', $content, $is_plain, $args);
    }

    /**
     * Output email header.
     *
     * @param string $email_notification_key  Email notification key for email being sent.
     * @param array   args
     */
    public static function get_header($args)
    {

        $template_name = mfget_template_filename('emails', 'header');
        return mflocate_template_html($template_name, $args);
    }

    /**
     * Output email header.
     *
     * @param string $email_notification_key  Email notification key for email being sent.
     * @param array   args
     */
    public static function get_body($args)
    {
        $template_name = mfget_template_filename('emails', 'body');
        return mflocate_template_html($template_name, $args);
    }

    /**
     * Output email footer.
     *
     * @param string $email_notification_key  Email notification key for email being sent.
     * @param array   args
     */
    public static function get_footer($args)
    {
        $template_name = mfget_template_filename('emails', 'footer');
        return mflocate_template_html($template_name, $args);
    }

    /**
     * Gets the CSS styles to be used in email notifications.
     *
     * @return bool|string
     */
    private static function get_styles()
    {
        $template_name = mfget_template_filename('emails', 'styles');
        return mflocate_template_html($template_name);
    }
    /**
     * Inject inline styles into email content.
     *
     * @param string $content
     * @return string
     */
    private static function inject_styles($content)
    {
        return CssInliner::fromHtml($content)->inlineCss(self::get_styles())->render();
    }
}

# Returns the main instance of MF_Mailer.
function mf_mail()
{
    return MF_Mailer::instance();
}
