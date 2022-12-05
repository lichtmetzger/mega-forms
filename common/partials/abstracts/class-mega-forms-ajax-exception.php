<?php
/**
 * Extending the Exception class to use for Mega Forms AJAX requests
 *
 * @link       https://wpali.com
 * @since      1.0.0
 *
 * @see        MF_Ajax
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials/abstracts
 */

if ( ! defined( 'ABSPATH' ) ) {
   exit; // Exit if accessed directly
}

class MF_Ajax_Exception extends Exception {
  private $_data = array();
  public function __construct( $message, $data = array() ) {
      $this->_data = $data;
      parent::__construct($message);
  }

  public function getData() {
      return $this->_data;
  }
};
