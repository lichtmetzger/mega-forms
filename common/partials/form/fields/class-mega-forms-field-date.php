<?php

/**
 * @link       https://wpali.com
 * @since      1.0.0
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 */

/**
 * Date field type class
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/fields
 * @author     Ali Khallad <ali@wpali.com>
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class MegaForms_Date extends MF_Field
{

  public $type = 'date';
  public $hasJSDependency = true;
  public $hasCSSDependency = true;
  public $editorSettings = array(
    'general' => array(
      'date_format' => array(
        'priority' => 50,
      ),
      'date_year_range' => array(
        'priority' => 60,
      ),
      'date_mode' => array(
        'priority' => 70,
        'size' => 'half-left'
      ),
      'date_calendar_icon' => array(
        'priority' => 80,
        'size' => 'half-right'
      )
    ),
  );
  public function get_field_title()
  {
    return esc_attr__('Date', 'megaforms');
  }

  public function get_field_icon()
  {
    return 'mega-icons-calendar';
  }

  public function get_field_js_dependencies()
  {
    return array(
      'jquery-mf-datepicker' => array(
        'src'   => MEGAFORMS_COMMON_URL . 'assets/js/deps/datepicker.min.js',
        'deps'  => array('jquery'),
        'ver'   => '2.2.3',
      ),
    );
  }

  public function get_field_css_dependencies()
  {
    return array(
      'mf-datepicker' => array(
        'src' => MEGAFORMS_COMMON_URL . 'assets/css/deps/datepicker.min.css',
        'deps' => array(),
        'ver' => '2.2.3',
        'in_footer' => true,
      ),
    );
  }

  public function get_field_display($value = null)
  {

    # Define arguements array and pass required arguements
    $args = $this->build_field_display_args();
    $args['value'] = $value;
    $date_format = $this->get_setting_value('date_format');
    $date_year_range = $this->get_setting_value('date_year_range');
    $date_mode = $this->get_setting_value('date_mode');
    $date_calendar_icon = $this->get_setting_value('date_calendar_icon', 'show');

    $args['attributes']['data-language'] = 'en';

    if (!empty($date_format)) {
      $args['attributes']['data-date-format'] = $date_format;
    }
    if (!empty($date_year_range)) {
      if (isset($date_year_range['from']) && isset($date_year_range['to'])) {
        $args['attributes']['data-date-from'] = $date_year_range['from'];
        $args['attributes']['data-date-to'] = $date_year_range['to'];
      }
    }

    if (!empty($date_mode) && $date_mode == 'range') {
      $args['attributes']['data-range'] = 'true';
      $args['attributes']['data-multiple-dates-separator'] = ' - ';
    }
    if (!empty($date_calendar_icon)) {
      $args['wrapper_class'] = !empty($args['wrapper_class']) ? $args['wrapper_class'] . ' mf_date_icon' : 'mf_date_icon';
    }

    # Add 'datepicker-here' to the input classes to make sure the date picker is initialized on load
    $args['class'] = !empty($args['class']) ? $args['class'] . ' datepicker-here' : 'datepicker-here';

    # retrieve and return the input markup
    $input = mfinput('text', $args, $this->is_editor);

    return $input;
  }

  /**********************************************************************
   ********************** Fields Options Markup *************************
   **********************************************************************/
  /**
   * Returns the markup for date format.
   *
   * @return string
   */
  protected function date_format()
  {
    # Date format arguements
    $date_format_label   = __('Date Format', 'megaforms');
    $date_format_key     = 'date_format';
    $date_format_desc    = __('Select the format you\'d like to use for the date field.', 'megaforms');

    $typeArgs                 = array();
    $typeArgs['id']           = $this->get_field_key('options', $date_format_key);
    $typeArgs['label']        = $date_format_label;
    $typeArgs['value']        = $this->get_setting_value($date_format_key);
    $typeArgs['after_label']  = $this->get_description_tip_markup($date_format_label, $date_format_desc);
    $typeArgs['options']      = $this->get_date_formats();

    $input = mfinput('select', $typeArgs, true);

    return $input;
  }

  /**
   * Returns the markup for date mode.
   *
   * @return string
   */
  protected function date_mode()
  {

    $label       = __('Date Mode', 'megaforms');
    $field_key  = 'date_mode';
    $desc       = __('Choose whether you want to allow users to select a single date or a date range.', 'megaforms');

    $args['id']           = $this->get_field_key('options', $field_key);
    $args['label']        = $label;
    $args['after_label']  = $this->get_description_tip_markup($label, $desc);
    $args['value']        = $this->get_setting_value($field_key);
    $args['options']      = array(
      'single' => __('Single', 'megaforms'),
      'range' => __('Range', 'megaforms'),
    );

    $args['default'] = 'single';

    $input = mfinput('radio', $args, true);

    return $input;
  }
  /**
   * Returns the markup for date mode.
   *
   * @return string
   */
  protected function date_year_range()
  {

    $label       = __('Year Range', 'megaforms');
    $field_key  = 'date_year_range';
    $desc       = __('Limit the year range appearing in the date picker.', 'megaforms');

    $args['id']           = $this->get_field_key('options', $field_key);
    $args['label']         = $label;
    $args['after_label']   = $this->get_description_tip_markup($label, $desc);

    $value = $this->get_setting_value($field_key);
    $from  = isset($value['from']) ? $value['from'] : '';
    $to    = isset($value['to']) ? $value['to'] : '';

    $yearRange = '';
    $yearRange .= '<table class="mf-year-range-options"><tbody>';

    $yearRange .= '<tr>';


    $yearRange .= '<td>';
    $yearRange .= get_mf_input('number', array('name' => $args['id'] . '[from]', 'value' => $from, 'placeholder' => date("Y")));
    $yearRange .= '</td>';

    $yearRange .= '<td>';
    $yearRange .= get_mf_input('number', array('name' => $args['id'] . '[to]', 'value' => $to, 'placeholder' => date("Y") + 9));
    $yearRange .= '</td>';

    $yearRange .= '</tr>';

    $yearRange .= '</tbody></table>';

    $args['content'] = $yearRange;
    $input = mfinput('custom', $args, true);
    return $input;
  }
  /**
   * Returns the markup for date mode.
   *
   * @return string
   */
  protected function date_calendar_icon()
  {

    $label       = __('Calendar Icon', 'megaforms');
    $field_key  = 'date_calendar_icon';
    $desc       = __('Choose whether the calendar icon should appear on the date field or not.', 'megaforms');

    $args['id']           = $this->get_field_key('options', $field_key);
    $args['label']        = $label;
    $args['after_label']  = $this->get_description_tip_markup($label, $desc);
    $args['value']        = $this->get_setting_value($field_key);
    $args['options']      = array(
      'show' => __('Show', 'megaforms'),
      'hide' => __('Hide', 'megaforms'),
    );
    $args['attributes'] = array();
    $args['attributes']['show'] = array();
    $args['attributes']['show']['data-preview'] = $this->get_js_helper_rules('.mf_input_date', 'add_class');
    $args['attributes']['show']['data-add-class'] = 'mf_date_icon';
    $args['attributes']['hide'] = array();
    $args['attributes']['hide']['data-preview'] = $this->get_js_helper_rules('.mf_input_date', 'remove_class');
    $args['attributes']['hide']['data-remove-class'] = 'mf_date_icon';

    $args['default'] = 'show';

    $input = mfinput('radio', $args, true);

    return $input;
  }

  /**********************************************************************
   ************************* Helpers ******************************
   **********************************************************************/
  public function get_date_formats($php_formats = false)
  {

    // Make sure there is a PHP format for each JS format ( Used for field submission validation )
    $formats = array(
      'dd/mm/yyyy' => $php_formats ? 'd/m/Y' : __('22/03/1999', 'megaforms'),
      'dd-mm-yyyy' => $php_formats ? 'd-m-Y' : __('22-03-1999', 'megaforms'),
      'dd.mm.yyyy' => $php_formats ? 'd.m.Y' : __('22.03.1999', 'megaforms'),
      'yyyy/mm/dd' => $php_formats ? 'Y/m/d' : __('1999/03/22', 'megaforms'),
      'yyyy-mm-dd' => $php_formats ? 'Y-m-d' : __('1999-03-22', 'megaforms'),
      'yyyy.mm.dd' => $php_formats ? 'Y.m.d' : __('1999.03.22', 'megaforms'),
      'mm/dd/yyyy' => $php_formats ? 'm/d/Y' : __('03/22/1999', 'megaforms'),
      'M dd, yyyy' => $php_formats ? 'M d, Y' : __('Mar 22, 1999', 'megaforms'),
      'MM dd, yyyy' => $php_formats ? 'F d, Y' : __('March 22, 1999', 'megaforms'),
      'MM dd yyyy' => $php_formats ? 'F d Y' : __('March 22 1999', 'megaforms'),
      'dd M yyyy' => $php_formats ? 'd M Y' : __('22 Mar 1999', 'megaforms'),
      'dd M, yyyy' => $php_formats ? 'd M, Y' : __('22 Mar, 1999', 'megaforms'),
    );

    return apply_filters('mf_date_formats', $formats, $php_formats);
  }

  public function is_valid_date($date, $format, $is_range = false)
  {

    $php_formats = $this->get_date_formats(true);
    $php_format = mfget($format, $php_formats);

    if ($is_range) {
      $date_parts = explode(" - ", $date);
      $is_valid = true;
      foreach ($date_parts as $date_part) {
        $d = DateTime::createFromFormat($php_format, $date);
        $is_valid = $d && $d->format($php_format) == $date;
        if (!$is_valid) {
          return false;
        }
      }
    } else {
      $d = DateTime::createFromFormat($php_format, $date);
      return $d && $d->format($php_format) == $date;
    }
  }

  /**********************************************************************
   ********************* Validation && Sanitazation *********************
   **********************************************************************/

  public function validate($value, $context = '')
  {

    $date_format = $this->get_setting_value('date_format');
    $date_mode = $this->get_setting_value('date_mode');

    $is_range = 'range' == $date_mode ? true : false;
    $is_valid_date = $this->is_valid_date($value, $date_format, $is_range);

    if (!$is_valid_date) {
      $notice = $is_range ? __('Please enter a valid date range.', 'megaforms') : __('Please enter a valid date.', 'megaforms');
      $notice_code = $is_range  ? 'invalid_date_range' : 'invalid_date';
      return array(
        'notice' => $notice,
        'notice_code' => $notice_code,
      );
    }

    return true;
  }
  public function sanitize_settings()
  {

    $sanitized = parent::sanitize_settings();

    $sanitized['date_format'] = wp_strip_all_tags($this->get_setting_value('date_format'));

    $date_range = $this->get_setting_value('date_year_range');
    $sanitized['date_year_range'] = array(
      'from' => !empty($date_range['from']) ? (int) $date_range['from'] : '',
      'to' => !empty($date_range['to']) ? (int) $date_range['to'] : '',
    );

    $sanitized['date_mode'] = sanitize_text_field($this->get_setting_value('date_mode'));
    $sanitized['date_calendar_icon'] = sanitize_text_field($this->get_setting_value('date_calendar_icon'));

    return $sanitized;
  }
}

MF_Fields::register(new MegaForms_Date());
