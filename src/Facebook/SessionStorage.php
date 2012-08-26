<?php

namespace Facebook;

use Nette;
use Nette\Diagnostics\Debugger;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @property string $state
 * @property string $code
 * @property string $access_token
 * @property string $user_id
 * @property-read string $user_id
 */
class SessionStorage extends Nette\Object
{

	const FBSS_COOKIE_NAME = 'fbss';

	// We can set this to a high number because the main session
	// expiration will trump this.
	const FBSS_COOKIE_EXPIRE = 31556926; // 1 year

	/**
	 * @var array
	 */
	public $keysWhitelist = array('state', 'code', 'access_token', 'user_id');

	/**
	 * @var \Nette\Http\SessionSection
	 */
	private $session;



	/**
	 * @todo: shared session
	 *
	 * @param \Nette\Http\Session $session
	 * @param Configuration $config
	 */
	public function __construct(Nette\Http\Session $session, Configuration $config)
	{
		$this->session = $session->getSection('Facebook/' . $config->getApplicationAccessToken());
	}

//	// Stores the shared session ID if one is set.
//	protected $sharedSessionID;
//
//	/**
//	 * Identical to the parent constructor, except that
//	 * we start a PHP session to store the user ID and
//	 * access token if during the course of execution
//	 * we discover them.
//	 *
//	 * @param Array $config the application configuration. Additionally
//	 * accepts "sharedSession" as a boolean to turn on a secondary
//	 * cookie for environments with a shared session (that is, your app
//	 * shares the domain with other apps).
//	 * @see BaseFacebook::__construct in facebook.php
//	 */
//	public function __construct($config)
//	{
//		if (!session_id()) {
//			session_start();
//		}
//		parent::__construct($config);
//		if (!empty($config['sharedSession'])) {
//			$this->initSharedSession();
//		}
//	}
//
//
//
//	protected function initSharedSession()
//	{
//		$cookie_name = $this->getSharedSessionCookieName();
//		if (isset($_COOKIE[$cookie_name])) {
//			$data = $this->parseSignedRequest($_COOKIE[$cookie_name]);
//			if ($data && !empty($data['domain']) &&
//				self::isAllowedDomain($this->getHttpHost(), $data['domain'])
//			) {
//				// good case
//				$this->sharedSessionID = $data['id'];
//				return;
//			}
//			// ignoring potentially unreachable data
//		}
//		// evil/corrupt/missing case
//		$base_domain = $this->getBaseDomain();
//		$this->sharedSessionID = md5(uniqid(mt_rand(), true));
//		$cookie_value = $this->makeSignedRequest(
//			array(
//				'domain' => $base_domain,
//				'id' => $this->sharedSessionID,
//			)
//		);
//		$_COOKIE[$cookie_name] = $cookie_value;
//		if (!headers_sent()) {
//			$expire = time() + self::FBSS_COOKIE_EXPIRE;
//			setcookie($cookie_name, $cookie_value, $expire, '/', '.' . $base_domain);
//		} else {
//			// @codeCoverageIgnoreStart
//			self::errorLog(
//				'Shared session ID cookie could not be set! You must ensure you ' .
//					'create the Facebook instance before headers have been sent. This ' .
//					'will cause authentication issues after the first request.'
//			);
//			// @codeCoverageIgnoreEnd
//		}
//	}

	/**
	 * Lays down a CSRF state token for this process.
	 *
	 * @return void
	 */
	public function establishCSRFTokenState()
	{
		if ($this->state === NULL) {
			$this->state = md5(uniqid(mt_rand(), TRUE));
			$this->set('state', $this->state);
		}
	}



	/**
	 * Stores the given ($key, $value) pair, so that future calls to
	 * getPersistentData($key) return $value. This call may be in another request.
	 *
	 * Provides the implementations of the inherited abstract
	 * methods.  The implementation uses PHP sessions to maintain
	 * a store for authorization codes, user ids, CSRF states, and
	 * access tokens.
	 */
	public function set($key, $value)
	{
		if (!in_array($key, $this->keysWhitelist)) {
			Debugger::log('Unsupported key passed to setPersistentData.', 'facebook');
			return;
		}

		$this->session->$key = $value;
	}



	/**
	 * Get the data for $key, persisted by BaseFacebook::setPersistentData()
	 *
	 * @param string $key The key of the data to retrieve
	 * @param mixed $default The default value to return if $key is not found
	 *
	 * @return mixed
	 */
	public function get($key, $default = false)
	{
		if (!in_array($key, $this->keysWhitelist)) {
			Debugger::log('Unsupported key passed to get persistent data.', 'facebook');
			return $default;
		}

		return isset($this->session->$key) ? $this->session->$key : $default;
	}



	/**
	 * Clear the data with $key from the persistent storage
	 *
	 * @param string $key
	 * @return void
	 */
	public function clear($key)
	{
		if (!in_array($key, $this->keysWhitelist)) {
			Debugger::log('Unsupported key passed to clearPersistentData.', 'facebook');
			return;
		}

		unset($this->session->$key);
	}



	/**
	 * Clear all data from the persistent storage
	 *
	 * @return void
	 */
	public function clearAll()
	{
		foreach ($this->keysWhitelist as $key) {
			$this->clear($key);
		}
//		if ($this->sharedSessionID) {
//			$this->deleteSharedSessionCookie();
//		}
	}



	/**
	 * @param string $name
	 * @return mixed
	 */
	public function &__get($name)
	{
		return $this->get($name);
	}



	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		$this->set($name, $value);
	}



	/**
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name)
	{
		return isset($this->session->$name);
	}



	/**
	 * @param string $name
	 */
	public function __unset($name)
	{
		$this->clear($name);
	}

//	protected function deleteSharedSessionCookie()
//	{
//		$cookie_name = $this->getSharedSessionCookieName();
//		unset($_COOKIE[$cookie_name]);
//		$base_domain = $this->getBaseDomain();
//		setcookie($cookie_name, '', 1, '/', '.' . $base_domain);
//	}



//	protected function getSharedSessionCookieName()
//	{
//		return self::FBSS_COOKIE_NAME . '_' . $this->getAppId();
//	}



//	protected function constructSessionVariableName($key)
//	{
//		$parts = array('fb', $this->getAppId(), $key);
//		if ($this->sharedSessionID) {
//			array_unshift($parts, $this->sharedSessionID);
//		}
//		return implode('_', $parts);
//	}

}
