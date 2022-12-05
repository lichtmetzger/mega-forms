<?php

/**
 * Mega Forms Session Class
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

if (is_admin()  && !defined('DOING_AJAX')) {
	return; // Exit if current request is came from admin screen, but is not an ajax request
}
if (defined('DOING_CRON')) {
	return; // Exit if current request is came from a cron job
}

class MF_Session
{

	/**
	 * The single instance of the class.
	 *
	 * @var MF_Session
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Cookie name used for the session.
	 *
	 * @var string cookie name
	 */
	protected $_cookie;

	/**
	 * Session ID.
	 *
	 * @var string The current session unique ID
	 */
	protected $_session_id;
	/**
	 * Session Data.
	 *
	 * @var array $_data Data array.
	 */
	protected $_data = array();

	/**
	 * Dirty when the session needs saving.
	 *
	 * @var bool $_dirty When something changes
	 */
	protected $_dirty = false;
	/**
	 * The duration of the session.
	 *
	 * @var int number of hours this sessions should be saved for.
	 */
	protected $_session_duration;
	/**
	 * Stores session expiry.
	 *
	 * @var string session due to expire timestamp
	 */
	protected $_session_expiring;

	/**
	 * Stores session due to expire timestamp.
	 *
	 * @var string session expiration timestamp
	 */
	protected $_session_expiration;

	/**
	 * True when the cookie exists.
	 *
	 * @var bool Based on whether a cookie exists.
	 */
	protected $_has_cookie = false;

	/**
	 * Table name for session data.
	 *
	 * @var string Custom session table name
	 */
	protected $_table;

	/**
	 * The key for session cookie 
	 * We are using the `wordpress_` prefix to ensure to cookie is ignored
	 * by some caching plugins, and hosting providers (eg; WPEngine).
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $session_cookie_key = 'wordpress_mf';
	/**
	 * The key for session cache
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $session_cache_key = 'mf_session_cache';

	/**
	 * Main MF_Session Instance.
	 *
	 * Ensures only one instance of MF_Session is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @see mf_session()
	 * @return MF_Session - Main instance.
	 */
	public static function instance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Init hooks and session data.
	 *
	 */
	public function start(int $duration = 24)
	{

		$cookiehash = (defined('COOKIEHASH')) ? COOKIEHASH : md5(home_url());

		if (defined('MF_SESSION_NAME')) {
			$this->session_cookie_key = MF_SESSION_NAME;
		}


		$this->_session_duration  = $duration;
		$this->_cookie            = $this->session_cookie_key . '_' . $cookiehash;
		$this->_table             = $GLOBALS['wpdb']->prefix . 'mf_sessions';

		add_action('init', array($this, 'init_mf_session'), 0);
	}

	/**
	 * Init Mega Forms session hooks and data.
	 *
	 */
	public function init_mf_session()
	{

		$this->init_session_cookie();

		// Set session cookie when the form is loaded only
		add_action('init', array($this, 'set_session_cookie'), 1);
		// Save the data stored in $this->_data in the end of each page load
		add_action('shutdown', array($this, 'save_data'), 20);
		// Destory session after the user is logged out
		add_action('wp_logout', array($this, 'destroy_session'));
	}


	/**
	 * Setup cookie and session ID.
	 *
	 * @since 3.6.0
	 */
	public function init_session_cookie()
	{

		$cookie = $this->get_session_cookie();

		if ($cookie) {
			$this->_session_id         = $cookie[0];
			$this->_session_expiration = $cookie[1];
			$this->_session_expiring   = $cookie[2];
			$this->_has_cookie         = true;
			$this->_data               = $this->get_session_data();

			// If the user logs in, update session.
			if (is_user_logged_in() && strval(get_current_user_id()) !== $this->_session_id) {
				$guest_session_id = $this->_session_id;
				$this->_session_id = strval(get_current_user_id());
				$this->_dirty = true;
				$this->save_data($guest_session_id);
				$this->set_session_cookie();
			}

			// Update session if its close to expiring.
			if (time() > $this->_session_expiring) {
				$this->set_session_expiration();
				$this->update_session_timestamp($this->_session_id, $this->_session_expiration);
			}
		} else {
			$this->set_session_expiration();
			$this->_session_id = $this->generate_session_id();
			$this->_data = $this->get_session_data();
		}
	}
	/**
	 * Get a session variable.
	 *
	 * @param string $key Key to get.
	 * @param mixed  $default used if the session variable isn't set.
	 * @return array|string value of session variable
	 */
	public function get($key, $default = null)
	{
		$key = sanitize_key($key);
		return isset($this->_data[$key]) ? maybe_unserialize($this->_data[$key]) : $default;
	}

	/**
	 * Set a session variable.
	 *
	 * @param string $key Key to set.
	 * @param mixed  $value Value to set.
	 */
	public function set($key, $value)
	{
		if ($value !== $this->get($key)) {
			$this->_data[sanitize_key($key)] = maybe_serialize($value);
			$this->_dirty = true;
		}
	}
	/**
	 * Unset a session variable.
	 *
	 * @param string $key Key to set.
	 * @param mixed  $value Value to set.
	 */
	public function unset($key)
	{
		if (isset($this->_data[$key])) {
			unset($this->_data[$key]);
			$this->_dirty = true;
		}
	}
	/**
	 * Set cookie.
	 *
	 * @param void
	 */
	public function setcookie($name, $value, $expire = 0, $secure = false, $httponly = false)
	{
		if (!headers_sent()) {
			setcookie($name, $value, $expire, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, $secure, $httponly);
		} elseif (defined('WP_DEBUG') && WP_DEBUG) {
			headers_sent($file, $line);
			trigger_error("{$name} cookie cannot be set - headers already sent by {$file} on line {$line}", E_USER_NOTICE); // @codingStandardsIgnoreLine
		}
	}
	/**
	 * Sets the session cookie on-demand.
	 *
	 * Warning: Cookies will only be set if this is called before the headers are sent.
	 *
	 * @param bool $set Should the session cookie be set.
	 */
	public function set_session_cookie()
	{
		$this->_has_cookie = true;
		$to_hash = $this->_session_id . '|' . $this->_session_expiration;
		$cookie_hash = hash_hmac('md5', $to_hash, wp_hash($to_hash));
		$cookie_value = $this->_session_id . '||' . $this->_session_expiration . '||' . $this->_session_expiring . '||' . $cookie_hash;

		if (!isset($_COOKIE[$this->_cookie]) || $_COOKIE[$this->_cookie] !== $cookie_value) {
			$this->setcookie($this->_cookie, $cookie_value, $this->_session_expiration, $this->use_secure_cookie(), true);
		}
	}

	/**
	 * Should the session cookie be secure?
	 *
	 * @return bool
	 */
	protected function use_secure_cookie()
	{
		return is_ssl();
	}

	/**
	 * Return true if the current user has an active session, i.e. a cookie to retrieve values.
	 *
	 * @return bool
	 */
	public function has_session()
	{
		return isset($_COOKIE[$this->_cookie]) || $this->_has_cookie || is_user_logged_in();
	}

	/**
	 * Set session expiration.
	 */
	public function set_session_expiration()
	{
		$this->_session_expiring   = time() + intval(apply_filters('mf_session_expiring', 60 * 60 * $this->_session_duration)); // 12 Hours.
		$this->_session_expiration = time() + intval(apply_filters('mf_session_expiration', 60 * 60 * ($this->_session_duration + 1))); // 13 Hours.
	}

	/**
	 * Generate a unique user ID for guests, or return user ID if logged in.
	 *
	 * Uses Portable PHP password hashing framework to generate a unique cryptographically strong ID.
	 *
	 * @return string
	 */
	public function generate_session_id()
	{
		$session_id = '';

		if (is_user_logged_in()) {
			$session_id = strval(get_current_user_id());
		}

		if (empty($session_id)) {
			require_once ABSPATH . 'wp-includes/class-phpass.php';
			$hasher      = new PasswordHash(8, false);
			$session_id = md5($hasher->get_random_bytes(32));
		}

		return $session_id;
	}

	/**
	 * Get the session cookie, if set. Otherwise return false.
	 *
	 * Session cookies without a user ID are invalid.
	 *
	 * @return bool|array
	 */
	public function get_session_cookie()
	{
		$cookie_value = isset($_COOKIE[$this->_cookie]) ? wp_unslash($_COOKIE[$this->_cookie]) : false;

		if (empty($cookie_value) || !is_string($cookie_value)) {
			return false;
		}

		list($session_id, $session_expiration, $session_expiring, $cookie_hash) = explode('||', $cookie_value);

		if (empty($session_id)) {
			return false;
		}

		// Validate hash.
		$to_hash = $session_id . '|' . $session_expiration;
		$hash    = hash_hmac('md5', $to_hash, wp_hash($to_hash));

		if (empty($cookie_hash) || !hash_equals($hash, $cookie_hash)) {
			return false;
		}

		return array($session_id, $session_expiration, $session_expiring, $cookie_hash);
	}

	/**
	 * Get session data.
	 *
	 * @return array
	 */
	public function get_session_data()
	{
		return $this->has_session() ? (array) $this->get_session($this->_session_id, array()) : array();
	}

	/**
	 * Gets a cache prefix. This is used in session names so the entire cache can be invalidated with 1 function call.
	 *
	 * @return string
	 */
	private function get_cache_prefix()
	{
		return 'mf_cache_' . $this->session_cache_key . '_';
	}

	/**
	 * Save data and delete guest session.
	 *
	 * @param int $old_session_key session ID before user logs in.
	 */
	public function save_data($old_session_key = 0)
	{

		// Dirty if something changed - prevents saving nothing new.
		if ($this->_dirty && $this->has_session()) {
			global $wpdb;

			$wpdb->query(
				$wpdb->prepare(
					"INSERT INTO $this->_table (`session_key`, `session_value`, `session_expiry`) VALUES (%s, %s, %d)
 					ON DUPLICATE KEY UPDATE `session_value` = VALUES(`session_value`), `session_expiry` = VALUES(`session_expiry`)",
					$this->_session_id,
					maybe_serialize($this->_data),
					$this->_session_expiration
				)
			);

			wp_cache_set($this->get_cache_prefix() . $this->_session_id, $this->_data, $this->session_cache_key, $this->_session_expiration - time());

			if (get_current_user_id() != $old_session_key && !is_object(get_user_by('id', $old_session_key))) {
				$this->delete_session($old_session_key);
			}

			$this->_dirty = false;
		}
	}

	/**
	 * Destroy all session data.
	 */
	public function destroy_session()
	{
		$this->delete_session($this->_session_id);
		$this->forget_session();
	}

	/**
	 * Forget all session data without destroying it.
	 */
	public function forget_session()
	{
		$this->setcookie($this->_cookie, '', time() - YEAR_IN_SECONDS, $this->use_secure_cookie(), true);

		$this->_data        = array();
		$this->_dirty       = false;
		$this->_session_id = $this->generate_session_id();
	}
	/**
	 * Cleanup session data from the database and clear caches.
	 */
	public function cleanup_sessions()
	{
		global $wpdb;

		$wpdb->query($wpdb->prepare("DELETE FROM $this->_table WHERE session_expiry < %d", time())); // @codingStandardsIgnoreLine.

	}

	/**
	 * Returns the session.
	 *
	 * @param string $user_id Custo ID.
	 * @param mixed  $default Default session value.
	 * @return string|array
	 */
	public function get_session($user_id, $default = false)
	{
		global $wpdb;

		// Try to get it from the cache, it will return false if not present or if object cache not in use.
		$value = wp_cache_get($this->get_cache_prefix() . $user_id, $this->session_cache_key);

		if (false === $value) {
			$value = $wpdb->get_var($wpdb->prepare("SELECT session_value FROM $this->_table WHERE session_key = %s", $user_id));

			if (is_null($value)) {
				$value = $default;
			}

			$cache_duration = $this->_session_expiration - time();
			if (0 < $cache_duration) {
				wp_cache_add($this->get_cache_prefix() . $user_id, $value, $this->session_cache_key, $cache_duration);
			}
		}

		return maybe_unserialize($value);
	}

	/**
	 * Delete the session from the cache and database.
	 *
	 * @param int $user_id Session ID.
	 */
	public function delete_session($user_id)
	{
		global $wpdb;

		wp_cache_delete($this->get_cache_prefix() . $user_id, $this->session_cache_key);

		$wpdb->delete(
			$this->_table,
			array(
				'session_key' => $user_id,
			)
		);
	}

	/**
	 * Update the session expiry timestamp.
	 *
	 * @param string $user_id Session ID.
	 * @param int    $timestamp Timestamp to expire the cookie.
	 */
	public function update_session_timestamp($session_id, $timestamp)
	{
		global $wpdb;

		$wpdb->update(
			$this->_table,
			array(
				'session_expiry' => $timestamp,
			),
			array(
				'session_key' => $session_id,
			),
			array(
				'%d',
			)
		);
	}
}

# Create a helper function that calls an instance of MF_Session so to that the same instance can be called anywhere.
function mf_session()
{
	return MF_Session::instance();
}
// Start a session
mf_session()->start();
