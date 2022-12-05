<?php

/**
 * Mega Forms Ajax Class
 *
 * @link       https://wpali.com
 * @since      1.0.0
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class MF_Ajax
{

  protected   $callbackKey = 'callback';
  protected   $requestType;
  protected   $timer = '';
  protected   $callback;
  protected   $validation = array();

  /**
   * Handle ajax request before the execution
   * @return
   */

  public function handler()
  {

    // Keep track of request time
    $this->startTimer();

    // Set request data
    $this->requestType  = $_SERVER['REQUEST_METHOD'];

    // Check if a callback is provided
    if ($this->get_request_data($this->callbackKey) !== null) {
      // Set callback method
      $this->callback = $this->get_request_data($this->callbackKey);
    } else {
      $this->error(__('The callback method is not defined in your request.', 'megaforms'));
    }

    // Define Error Handler
    set_error_handler(array($this, "ajax_error_handler"));

    // Run
    $this->execute();
  }

  /**
   * Run the requested method and handle error/success
   * @return
   */
  public function execute()
  {

    // Check if current request is allowed
    $this->validateSubmission();

    // Run the callback method
    $this->runMethod();

    // If runMethod() does not end the process, end it with a default error.
    $this->error(__('Mega Forms could not process this request. Please make sure the callback method is ending the AJAX process by either calling error() or success()', 'megaforms'));
  }

  /**
   * Search and return appropriate value from the request by key
   * If value not found, return an exception
   * @param string $key The key we want to grab its corresponding value from the request
   * @return
   */
  protected function get_value($key, $requestType = false, $maybe_decode = false)
  {
    $val = $this->get_request_data($key, $requestType);

    if ($val === null) {
      // If no value found, throw an exception
      throw new Exception(__('Your request is missing required parameter ( ' . $key . ' ). To get the expected output, please supply all the data required for the callback method.', 'megaforms'));
    }

    if (!empty($val) && !is_array($val)) {
      if (strtolower($val) == "true") {
        $val = true;
      } elseif (strtolower($val) == "false") {
        $val = false;
      }
    }

    // When `maybe_decode` is set to true, we assume that this is a JSON string that should be converted to an array
    if ($maybe_decode && !is_array($val)) {
      return json_decode($val, true);
    }

    return $val;
  }
  /**
   * Search and return appropriate value from the request by key
   * @param string $key The key we want to grab its corresponding value from the request
   * @return
   */
  protected function maybe_get_value($key, $requestType = false, $maybe_decode = false)
  {
    $val = $this->get_request_data($key, $requestType);

    if (!empty($val) && !is_array($val)) {
      if (strtolower($val) == "true") {
        $val = true;
      } elseif (strtolower($val) == "false") {
        $val = false;
      }
    }

    // When `maybe_decode` is set to true, we assume that this is a JSON string that should be converted to an array
    if ($maybe_decode && !is_array($val)) {
      return json_decode($val, true);
    }

    return $val;
  }
  /**
   * Return a value based on the provided key and request type
   * @return mixed return the value or throw an exception if no value was found
   */
  protected function get_request_data($key, $requestType =  false)
  {

    $request_type = $requestType !== false ? $requestType : $this->requestType;

    switch ($request_type) {
      case 'GET':
      case 'get':
        if (isset($_GET[$key])) {
          return mfget($key);
        }
        break;
      case 'POST':
      case 'post':
        if (isset($_POST[$key])) {
          return mfpost($key);
        }
        break;
      case 'REQUEST':
      case 'request':
        if (isset($_REQUEST[$key])) {
          return $_REQUEST[$key];
        }
        break;
      case 'FILES':
      case 'files':
        if (isset($_FILES[$key])) {
          return $_FILES[$key];
        }
        break;
    }

    return null;
  }

  /**
   * Start the timer
   * @return
   */
  protected function startTimer()
  {
    $this->timer = microtime(true);
  }

  /**
   * End the timer
   * Returns the total time spent executing current AJAX call
   * @return string
   */
  protected function endTimer()
  {
    $end = microtime(true);
    return  sprintf("%01.3f", ($end - $this->timer));
  }

  /**
   * Checks if the current user is allowed to perform this request
   */
  private function validateSubmission()
  {

    $allowed = true;

    if (!empty($this->validation)) {
      foreach ($this->validation as $key => $val) {

        if ($key == 'user_logged_in' && $val === true) {
          if (!is_user_logged_in())
            $allowed = false;
        }

        if ($key == 'current_user_can') {
          if (!current_user_can($val))
            $allowed = false;
        }
      }
    }

    if (!$allowed) {
      return $this->error(__('You don\'t have the permissions to perform this request.', 'megaforms'));
    }

    return true;
  }
  /**
   * Runs the given method
   * @param string $method The name of method we want to call
   */
  private function runMethod($method = false)
  {
    // Add support for manual method processing
    if (!$method) {
      $method = $this->callback;
    }

    // Check if the requested method exist
    if (!method_exists($this, $method)) {
      /* translators: The callback method name. */
      return $this->error(sprintf(__('The callback method ( %s ) does not exist.', 'megaforms'), $method));
    }

    // Run the provided callback method
    try {
      return $this->$method();
    } catch (MF_Ajax_Exception $e) {
      // Catch if MF_Ajax_Exception was thrown
      $data = $e->getData();
      $data['fail'] = true;

      return $this->success($e->getMessage(), $data);
    } catch (Exception $e) {
      // Catch if any exception was thrown
      return $this->error($e->getMessage());
    }
  }
  /**
   * Handle final error response to the AJAX call
   * @param string $message Error message
   * @param array  $response [optional] any extra parameters to be sent to the AJAX success
   */
  public function error($message, $response = array())
  {

    if (is_array($message)) {
      $response = $message;
    } else {
      $response["message"] = $message;
    }

    $response["success"] = false;
    $response["duration"] = $this->endTimer();

    // Restore built-in error handler.
    restore_error_handler();

    // Convert the response to json format and send final result.
    $result = json_encode($response);

    echo $result;
    wp_die();
  }
  /**
   * Handle final success response to the AJAX call
   * @param string $message Success message
   * @param array  $response [optional] any extra parameters to be sent to the AJAX success
   */
  public function success($message = '', $response = array())
  {
    // Allow passing all parameters in the message
    if (is_array($message)) {
      $response = $message;
    } else {
      $response["message"] = $message;
    }

    $response["success"] = true;
    $response["duration"] = $this->endTimer();

    if (!isset($response["fail"])) {
      $response["fail"] = false;
    }
    // Restore built-in error handler.
    restore_error_handler();

    // Convert the response to json format and send final result.
    $result = json_encode($response);
    echo $result;
    wp_die();
  }
  /**
   * Mega forms error handler
   * @param int     $number     The level of error raised.
   * @param string  $message    The error message, as a string.
   * @param string  $filename   The filename the error was raised in.
   * @param int     $line       The line number the error was raised at.
   */
  public function ajax_error_handler($number, $string, $filename, $line)
  {

    $error_is_enabled = (bool) ($number & ini_get('error_reporting'));
    $logging_is_enabled = (bool) ini_get('log_errors');

    if ($error_is_enabled && $logging_is_enabled) {

      switch ($number) {
        case E_WARNING:
        case E_USER_WARNING:
        case E_STRICT:
        case E_NOTICE:
        case E_USER_NOTICE:
          $type = 'Warning';
          $fatal = false;
          break;
        default:
          $type = 'Fatal Error';
          $fatal = true;
          break;
      }

      $trace = array_reverse(debug_backtrace());
      array_pop($trace);

      $items = array();
      $i = 1;
      foreach ($trace as $item) {
        $items[] =  "#" . $i++ . " calling " . $item['function'] . "() from: " . (isset($item['file']) ? $item['file'] : "<unknown file>") . " " . (isset($item['line']) ? $item['line'] : "<unknown line>");
      }

      $message = "";
      $message .= "\n************ Mega Forms AJAX " . $type . " ************";
      $message .= "\n" . $string . " in " . $filename . ' on line: ' . $line;
      $message .= "\n" . join("\n", $items);
      $message .= "\n***********************************************";
      error_log($message, 0);
    }
  }
}
