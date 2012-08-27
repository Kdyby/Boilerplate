<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Social\Facebook\Api;

use Nette;
use Nette\Diagnostics\Debugger;
use Nette\Http\UrlScript;
use Nette\Utils\Json;
use Kdyby\Extension\Social\Facebook;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class CurlClient extends Nette\Object implements Facebook\ApiClient
{

	/**
	 * Default options for curl.
	 * @var array
	 */
	public $curlOptions = array(
		CURLOPT_CONNECTTIMEOUT => 10,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 20,
		CURLOPT_USERAGENT => 'facebook-php-3.2',
		CURLOPT_HTTPHEADER => array()
	);

	/**
	 * @var \Facebook\Facebook
	 */
	private $fb;

	/**
	 * @var \Facebook\Diagnostics\Panel
	 */
	private $panel;

	/**
	 * @var array
	 */
	private $cache = array();



	/**
	 * @param \Facebook\Facebook $facebook
	 */
	public function injectFacebook(Facebook\Facebook $facebook)
	{
		$this->fb = $facebook;
	}



	/**
	 * @param \Facebook\Diagnostics\Panel $panel
	 */
	public function injectPanel(Facebook\Diagnostics\Panel $panel = NULL)
	{
		$this->panel = $panel;
	}



	/**
	 * Invoke the old restserver.php endpoint.
	 *
	 * @param array $params Method call object
	 * @throws \Facebook\FacebookApiException
	 * @return mixed The decoded response object
	 */
	public function restServer(array $params)
	{
		// generic application level parameters
		$params['api_key'] = $this->fb->config->appId;
		$params['format'] = 'json-strings';

		$result = $this->callOauth($this->fb->config->getApiUrl($params['method']), $params);

		$method = strtolower($params['method']);
		if ($method === 'auth.expiresession' || $method === 'auth.revokeauthorization') {
			$this->fb->destroySession();
		}

		return $result;
	}



	/**
	 * Invoke the Graph API.
	 *
	 * @param string $path The path (required)
	 * @param string $method The http method (default 'GET')
	 * @param array $params The query/post data
	 * @throws \Facebook\FacebookApiException
	 * @return mixed The decoded response object
	 */
	public function graph($path, $method = NULL, array $params = array())
	{
		if (is_array($method) && empty($params)) {
			$params = $method;
			$method = NULL;
		}
		$params['method'] = $method ?: 'GET'; // method override as we always do a POST
		$domainKey = Facebook\Helpers::isVideoPost($path, $method) ? 'graph_video' : 'graph';

		return $this->callOauth($this->fb->config->createUrl($domainKey, $path), $params);
	}



	/**
	 * Make a OAuth Request.
	 *
	 * @param string $url The path (required)
	 * @param array $params The query/post data
	 *
	 * @return string The decoded response object
	 * @throws \Facebook\FacebookApiException
	 */
	public function oauth($url, array $params)
	{
		if (!isset($params['access_token'])) {
			$params['access_token'] = $this->fb->getAccessToken();
		}

		// json_encode all params values that are not strings
		$params = array_map(function ($value) {
			if ($value instanceof UrlScript) {
				return (string)$value;
			}
			return !is_string($value) ? Json::encode($value) : $value;
		}, $params);

		if ($this->panel) $this->panel->begin($url, $params);
		return $this->makeRequest($url, $params);
	}



	/**
	 * @param \Nette\Http\UrlScript $url
	 * @param $params
	 * @throws \Facebook\FacebookApiException
	 * @return array|mixed
	 */
	protected function callOauth(UrlScript $url, $params)
	{
		$result = Json::decode($this->oauth($url, $params), Json::FORCE_ARRAY);

		// results are returned, errors are thrown
		if (is_array($result) && isset($result['error'])) {
			$this->throwAPIException($result);
			// @codeCoverageIgnoreStart
		}
		// @codeCoverageIgnoreEnd

		return $result;
	}



	/**
	 * Makes an HTTP request. This method can be overridden by subclasses if
	 * developers want to do fancier things or use something other than curl to
	 * make the request.
	 *
	 * @param string $url The URL to make the request to
	 * @param array $params The parameters to use for the POST body
	 * @param resource $ch Initialized curl handle
	 *
	 * @throws \Facebook\FacebookApiException
	 * @return string The response text
	 */
	protected function makeRequest($url, array $params, $ch = null)
	{
		if (isset($this->cache[$cacheKey = md5(serialize(array($url, $params)))])) {
			return $this->cache[$cacheKey];
		}

		$ch = $ch ?: curl_init();

		$opts = $this->curlOptions;
		$opts[CURLOPT_POSTFIELDS] = $this->fb->config->fileUploadSupport
			? $params : http_build_query($params, null, '&');
		$opts[CURLOPT_URL] = (string)$url;

		// disable the 'Expect: 100-continue' behaviour. This causes CURL to wait
		// for 2 seconds if the server does not support this header.
		$opts[CURLOPT_HTTPHEADER][] = 'Expect:';

		// execute request
		curl_setopt_array($ch, $opts);
		$result = curl_exec($ch);

		// provide certificate if needed
		if (curl_errno($ch) == CURLE_SSL_CACERT) {
			Debugger::log('Invalid or no certificate authority found, using bundled information', 'facebook');
			curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '/fb_ca_chain_bundle.crt');
			$result = curl_exec($ch);
		}

		// With dual stacked DNS responses, it's possible for a server to
		// have IPv6 enabled but not have IPv6 connectivity.  If this is
		// the case, curl will try IPv4 first and if that fails, then it will
		// fall back to IPv6 and the error EHOSTUNREACH is returned by the
		// operating system.
		if ($result === false && empty($opts[CURLOPT_IPRESOLVE])) {
			$matches = array();
			if (preg_match('/Failed to connect to ([^:].*): Network is unreachable/', curl_error($ch), $matches)) {
				if (strlen(@inet_pton($matches[1])) === 16) {
					Debugger::log('Invalid IPv6 configuration on server, Please disable or get native IPv6 on your server.', 'facebook');
					$this->curlOptions[CURLOPT_IPRESOLVE] = CURL_IPRESOLVE_V4;
					curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
					$result = curl_exec($ch);
				}
			}
		}

		$info = curl_getinfo($ch);
		if ($result === false) {
			$e = new Facebook\FacebookApiException(array(
				'error_code' => curl_errno($ch),
				'error' => array('message' => curl_error($ch), 'type' => 'CurlException')
			));
			if ($this->panel) $this->panel->failure($e, $info);
			curl_close($ch);
			throw $e;
		}

		if (!$result && isset($info['redirect_url'])) {
			$result = Json::encode(array('url' => $info['redirect_url']));
		}

		if ($this->panel) $this->panel->success($result, $info);
		curl_close($ch);
		return $this->cache[$cacheKey] = $result;
	}



	/**
	 * Analyzes the supplied result to see if it was thrown
	 * because the access token is no longer valid.  If that is
	 * the case, then we destroy the session.
	 *
	 * @param $result array A record storing the error message returned by a failed API call.
	 * @throws \Facebook\FacebookApiException
	 */
	protected function throwAPIException($result)
	{
		$e = new Facebook\FacebookApiException($result);
		switch ($e->getType()) {
			case 'OAuthException': // OAuth 2.0 Draft 00 style
			case 'invalid_token': // OAuth 2.0 Draft 10 style
			case 'Exception': // REST server errors are just Exceptions
				if ($this->apiErrorRequiresSessionDestroy($e->getMessage())) {
					$this->fb->destroySession();
				}
				break;
		}

		throw $e;
	}



	/**
	 * @param string $message
	 * @return bool
	 */
	protected function apiErrorRequiresSessionDestroy($message)
	{
		return strpos($message, 'Error validating access token') !== false
			|| strpos($message, 'Invalid OAuth access token') !== false
			|| strpos($message, 'An active access token must be used') !== false;
	}

}
