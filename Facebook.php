<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Social\Facebook;

use Nette;
use Nette\Diagnostics\Debugger;
use Nette\Utils\Strings;



/**
 * Provides access to the Facebook Platform.  This class provides
 * a majority of the functionality needed, but the class is abstract
 * because it is designed to be sub-classed.  The subclass must
 * implement the four abstract methods listed at the bottom of
 * the file.
 *
 * @property \Kdyby\Extension\Social\Facebook\Configuration $config
 * @property \Kdyby\Extension\Social\Facebook\SessionStorage $session
 *
 * @author Naitik Shah <naitik@facebook.com>
 */
class Facebook extends Nette\Object
{

	/**
	 * Version.
	 */
	const VERSION = '3.2.0';

	/**
	 * @var Configuration
	 */
	private $config;

	/**
	 * @var SessionStorage
	 */
	private $session;

	/**
	 * @var ApiClient
	 */
	private $apiClient;

	/**
	 * @var \Nette\Http\Request
	 */
	private $httpRequest;

	/**
	 * @var \Nette\Http\Response
	 */
	private $httpResponse;

	/**
	 * The data from the signed_request token.
	 * @var string
	 */
	private $signedRequest;

	/**
	 * The ID of the Facebook user, or 0 if the user is logged out.
	 * @var integer
	 */
	private $user;

	/**
	 * The OAuth access token received in exchange for a valid authorization
	 * code.  null means the access token has yet to be determined.
	 * @var string
	 */
	private $accessToken;



	/**
	 * Initialize a Facebook Application.
	 *
	 * @param Configuration $config
	 * @param SessionStorage $session
	 * @param \Nette\Http\Request $httpRequest
	 * @param \Nette\Http\Response $httpResponse
	 * @param ApiClient $client
	 */
	public function __construct(
		Configuration $config, SessionStorage $session, ApiClient $client,
		Nette\Http\Request $httpRequest, Nette\Http\Response $httpResponse)
	{
		$this->config = $config;
		$this->httpResponse = $httpResponse;
		$this->httpRequest = $httpRequest;
		$this->session = $session;
		$this->apiClient = $client;
	}



	/**
	 * @return Configuration
	 */
	public function getConfig()
	{
		return $this->config;
	}



	/**
	 * @return SessionStorage
	 */
	public function getSession()
	{
		return $this->session;
	}



	/**
	 * @param string $id
	 * @return Profile
	 */
	public function getProfile($id = 'me')
	{
		return new Profile($this, $id);
	}



	/**
	 * Make an API call.
	 *
	 * @param string|array $pathOrParams
	 * @param string $method
	 * @param array $params
	 *
	 * @return \Nette\ArrayHash|NULL The decoded response
	 */
	public function api($pathOrParams, $method = NULL, array $params = array())
	{
		if (is_array($pathOrParams)) {
			$response = $this->apiClient->restServer($pathOrParams); // params

		} else {
			$response = $this->apiClient->graph($pathOrParams, $method, $params);
		}

		return $response ? Nette\ArrayHash::from($response) : $response;
	}



	/**
	 * Get the UID of the connected user, or 0 if the Facebook user is not connected.
	 *
	 * @return string the UID if available.
	 */
	public function getUser()
	{
		if ($this->user === NULL) {
			$this->user = $this->getUserFromAvailableData();
		}

		return $this->user;
	}



	/**
	 * @param null $scope
	 * @return Dialog\LoginDialog
	 */
	public function createLoginDialog($scope = NULL)
	{
		$dialog = new Dialog\LoginDialog($this);
		$dialog->setScope($scope);
		return $dialog;
	}



	/**
	 * @return Dialog\LogoutDialog
	 */
	public function createLogoutDialog()
	{
		return new Dialog\LogoutDialog($this);
	}



	/**
	 * @return Dialog\LoginStatusDialog
	 */
	public function createLoginStatusDialog()
	{
		return new Dialog\LoginStatusDialog($this);
	}



	/**
	 * Sets the access token for api calls.  Use this if you get
	 * your access token by other means and just want the SDK
	 * to use it.
	 *
	 * @param string $accessToken an access token.
	 * @return Facebook
	 */
	public function setAccessToken($accessToken)
	{
		$this->accessToken = $accessToken;
		return $this;
	}



	/**
	 * Extend an access token, while removing the short-lived token that might
	 * have been generated via client-side flow. Thanks to http://bit.ly/b0Pt0H
	 * for the workaround.
	 */
	public function setExtendedAccessToken()
	{
		try {
			// need to circumvent json_decode by calling _oauthRequest
			// directly, since response isn't JSON format.
			$response = $this->apiClient->oauth(
				$this->config->createUrl('graph', '/oauth/access_token'),
				array(
					'client_id' => $this->config->appId,
					'client_secret' => $this->config->appSecret,
					'grant_type' => 'fb_exchange_token',
					'fb_exchange_token' => $this->getAccessToken(),
				)
			);

			if (empty($response)) {
				return false;
			}

			parse_str($response, $params);
			if (!isset($params['access_token'])) {
				return false;
			}

			$this->destroySession();
			$this->session->access_token = $params['access_token'];

			return true;

		} catch (FacebookApiException $e) {
			// most likely that user very recently revoked authorization.
			// In any event, we don't have an access token, so say so.
			return false;
		}
	}



	/**
	 * Determines the access token that should be used for API calls.
	 * The first time this is called, $this->accessToken is set equal
	 * to either a valid user access token, or it's set to the application
	 * access token if a valid user access token wasn't available.  Subsequent
	 * calls return whatever the first call returned.
	 *
	 * @return string The access token
	 */
	public function getAccessToken()
	{
		if ($this->accessToken !== NULL) {
			return $this->accessToken; // we've done this already and cached it.  Just return.
		}

		// first establish access token to be the application
		// access token, in case we navigate to the /oauth/access_token
		// endpoint, where SOME access token is required.
		$this->setAccessToken($this->config->getApplicationAccessToken());
		if ($accessToken = $this->getUserAccessToken()) {
			$this->setAccessToken($accessToken);
		}

		return $this->accessToken;
	}



	/**
	 * Determines and returns the user access token, first using
	 * the signed request if present, and then falling back on
	 * the authorization code if present.  The intent is to
	 * return a valid user access token, or false if one is determined
	 * to not be available.
	 *
	 * @return string A valid user access token, or false if one could not be determined.
	 */
	protected function getUserAccessToken()
	{
		// first, consider a signed request if it's supplied.
		// if there is a signed request, then it alone determines
		// the access token.
		if ($signedRequest = $this->getSignedRequest()) {
			// apps.facebook.com hands the access_token in the signed_request
			if (array_key_exists('oauth_token', $signedRequest)) {
				return $this->session->access_token = $signedRequest['oauth_token'];
			}

			// the JS SDK puts a code in with the redirect_uri of ''
			if (array_key_exists('code', $signedRequest)) {
				$code = $signedRequest['code'];
				if ($accessToken = $this->getAccessTokenFromCode($code, '')) {
					$this->session->code = $code;
					return $this->session->access_token = $accessToken;
				}
			}

			// signed request states there's no access token, so anything
			// stored should be cleared.
			$this->session->clearAll();
			return false;
			// respect the signed request's data, even
			// if there's an authorization code or something else
		}

		if (($code = $this->getCode()) && $code != $this->session->code) {
			if ($accessToken = $this->getAccessTokenFromCode($code)) {
				$this->session->code = $code;
				return $this->session->access_token = $accessToken;
			}

			// code was bogus, so everything based on it should be invalidated.
			$this->session->clearAll();
			return false;
		}

		// as a fallback, just return whatever is in the persistent
		// store, knowing nothing explicit (signed request, authorization
		// code, etc.) was present to shadow it (or we saw a code in $_REQUEST,
		// but it's the same as what's in the persistent store)
		return $this->session->access_token;
	}



	/**
	 * Retrieve the signed request, either from a request parameter or,
	 * if not present, from a cookie.
	 *
	 * @return string the signed request, if available, or null otherwise.
	 */
	public function getSignedRequest()
	{
		if (!$this->signedRequest) {
			if ($signedRequest = $this->getRequest('signed_request')) {
				$this->signedRequest = SignedRequest::decode($signedRequest, $this->config->appSecret);

			} elseif ($signedRequest = $this->httpRequest->getCookie($this->config->getSignedRequestCookieName())) {
				$this->signedRequest = SignedRequest::decode($signedRequest, $this->config->appSecret);
			}
		}

		return $this->signedRequest;
	}



	/**
	 * Determines the connected user by first examining any signed
	 * requests, then considering an authorization code, and then
	 * falling back to any persistent store storing the user.
	 *
	 * @return integer The id of the connected Facebook user, or 0 if no such user exists.
	 */
	protected function getUserFromAvailableData()
	{
		// if a signed request is supplied, then it solely determines
		// who the user is.
		if ($signedRequest = $this->getSignedRequest()) {
			if (array_key_exists('user_id', $signedRequest)) {
				return $this->session->user_id = $signedRequest['user_id'];
			}

			// if the signed request didn't present a user id, then invalidate
			// all entries in any persistent store.
			$this->session->clearAll();
			return 0;
		}

		$user = $this->session->get('user_id', 0);

		// use access_token to fetch user id if we have a user access_token, or if
		// the cached access token has changed.
		if (($accessToken = $this->getAccessToken())
			&& $accessToken !== $this->config->getApplicationAccessToken()
			&& !($user && $this->session->access_token === $accessToken)
		) {
			try {
				$this->session->user_id = $user = $this->api('/me')->id;

			} catch (FacebookApiException $e) {
				$this->session->clearAll();
			}
		}

		return $user;
	}



	/**
	 * Get the authorization code from the query parameters, if it exists,
	 * and otherwise return false to signal no authorization code was
	 * discoverable.
	 *
	 * @return mixed The authorization code, or false if the authorization code could not be determined.
	 */
	protected function getCode()
	{
		$state = $this->getRequest('state');
		if (($code = $this->getRequest('code')) && $state && $this->session->state === $state) {
			$this->session->state = NULL; // CSRF state has done its job, so clear it
			return $code;
		}

		return false;
	}



	/**
	 * Retrieves the UID with the understanding that
	 * $this->accessToken has already been set and is
	 * seemingly legitimate.  It relies on Facebook's Graph API
	 * to retrieve user information and then extract
	 * the user ID.
	 *
	 * @return integer Returns the UID of the Facebook user, or 0
	 *                 if the Facebook user could not be determined.
	 */
	protected function getUserFromAccessToken()
	{
		try {
			return $this->api('/me')->id;

		} catch (FacebookApiException $e) {
			return 0;
		}
	}



	/**
	 * Retrieves an access token for the given authorization code
	 * (previously generated from www.facebook.com on behalf of
	 * a specific user).  The authorization code is sent to graph.facebook.com
	 * and a legitimate access token is generated provided the access token
	 * and the user for which it was generated all match, and the user is
	 * either logged in to Facebook or has granted an offline access permission.
	 *
	 * @param string $code An authorization code.
	 * @param null $redirectUri
	 * @return mixed An access token exchanged for the authorization code, or false if an access token could not be generated.
	 */
	protected function getAccessTokenFromCode($code, $redirectUri = null)
	{
		if (empty($code)) {
			return false;
		}

		$redirectUri = $redirectUri ?: $this->getCurrentUrl();

		try {
			// need to circumvent json_decode by calling _oauthRequest
			// directly, since response isn't JSON format.
			$accessToken = $this->apiClient->oauth(
				$this->config->createUrl('graph', '/oauth/access_token'),
				array(
					'client_id' => $this->config->appId,
					'client_secret' => $this->config->appSecret,
					'redirect_uri' => $redirectUri,
					'code' => $code
				)
			);

			if (empty($accessToken)) {
				return false;
			}

		} catch (FacebookApiException $e) {
			// most likely that user very recently revoked authorization.
			// In any event, we don't have an access token, so say so.
			return false;
		}

		$params = array();
		parse_str($accessToken, $params);
		if (!isset($params['access_token'])) {
			return false;
		}

		return $params['access_token'];
	}



	/**
	 * Get the base domain used for the cookie.
	 *
	 * @return string
	 */
	protected function getBaseDomain()
	{
		// The base domain should be stored in the metadata cookie
		$metadata = $this->getMetadataCookie();
		if (array_key_exists('base_domain', $metadata) && !empty($metadata['base_domain'])) {
			return trim($metadata['base_domain'], '.');
		}

		// fallback to the current hostname
		return $this->getCurrentUrl()->getHost();
	}



	/**
	 * Returns the Current URL, stripping it of known FB parameters that should
	 * not persist.
	 *
	 * @internal
	 * @return \Nette\Http\UrlScript The current URL
	 */
	public function getCurrentUrl()
	{
		$url = clone $this->httpRequest->url;
		if ($this->config->trustForwarded && isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
			$url->setHost($_SERVER['HTTP_X_FORWARDED_HOST']);
		}

		if ($this->config->trustForwarded && isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
			$url->setScheme($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ? 'https' : 'http');
		}

		parse_str($url->getQuery(), $query);
		$query = array_diff_key($query, array_flip($this->config->dropQueryParams));
		$url->setQuery($query);

		return $url;
	}



	/**
	 * Destroy the current session
	 */
	public function destroySession()
	{
		$this->accessToken = null;
		$this->signedRequest = null;
		$this->user = null;
		$this->session->clearAll();

		// Javascript sets a cookie that will be used in getSignedRequest that we need to clear if we can
		$cookieName = $this->config->getSignedRequestCookieName();
		if (array_key_exists($cookieName, $this->httpRequest->cookies)) {
			$this->httpResponse->deleteCookie($cookieName, '/', $this->getBaseDomain());
		}
	}



	/**
	 * Parses the metadata cookie that our Javascript API set
	 *
	 * @return array
	 */
	protected function getMetadataCookie()
	{
		$cookieName = $this->config->getMetadataCookieName();

		// The cookie value can be wrapped in "-characters so remove them
		if (!$cookieValue = trim($this->httpRequest->getCookie($cookieName), '"')) {
			return array();
		}

		parse_str($cookieValue, $metadata);
		array_walk($metadata, function (&$value, &$key) {
			$value = urldecode($value);
			$key = urldecode($key);
		});

		return $metadata;
	}



	/**
	 * @param string $key
	 * @param mixed $default
	 * @return mixed|null
	 */
	protected function getRequest($key, $default = NULL)
	{
		if ($value = $this->httpRequest->getPost($key)) {
			return $value;
		}

		if ($value = $this->httpRequest->getQuery($key)) {
			return $value;
		}

		return $default;
	}

}
