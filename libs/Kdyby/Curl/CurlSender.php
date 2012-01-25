<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Curl;

use Kdyby;
use Nette;
use Nette\Http\UrlScript as Url;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class CurlSender extends RequestOptions implements ICurlSender
{
	/** @var array */
	public static $userAgents = array(
		'FireFox3' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; pl; rv:1.9) Gecko/2008052906 Firefox/3.0',
		'GoogleBot' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
		'IE7' => 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)',
		'Netscape' => 'Mozilla/4.8 [en] (Windows NT 6.0; U)',
		'Opera' => 'Opera/9.25 (Windows NT 6.0; U; en)',
	);

	/** @var array An associative array of headers to send along with requests */
	public $headers = array();

	/** @var array */
	private $proxies = array();

	/** @var \Nette\Callback */
	private $confirmRedirect;

	/** @var string */
	private $downloadDir;

	/** @var \Kdyby\Curl\Request */
	private $queriedRequest;



	/**
	 * @param int $timeout
	 *
	 * @return \Kdyby\Curl\CurlSender
	 */
	public function setConnectTimeout($timeout)
	{
		$this->options['connectTimeout'] = $timeout;
		return $this;
	}



	/**
	 * @param string $ua
	 *
	 * @return \Kdyby\Curl\CurlSender
	 */
	public function setUserAgent($ua)
	{
		if (isset(static::$userAgents[$ua])) {
			$ua = static::$userAgents[$ua];
		}
		return parent::setUserAgent($ua);
	}



	/**
	 * @param string $downloadDir
	 */
	public function setDownloadDir($downloadDir)
	{
		if (!is_writable($downloadDir)) {
			throw Kdyby\DirectoryNotWritableException::fromDir($downloadDir);
		}

		Kdyby\Tools\Filesystem::mkDir($downloadDir);
		$this->downloadDir = $downloadDir;
	}



	/**
	 * @return string
	 */
	public function getDownloadDir()
	{
		return $this->downloadDir;
	}



	/**
	 * @param callback $confirmRedirect
	 */
	public function setConfirmRedirect($confirmRedirect)
	{
		$this->confirmRedirect = callback($confirmRedirect);
	}



	/**
	 * @return \Nette\Callback
	 */
	public function getConfirmRedirect()
	{
		return $this->confirmRedirect;
	}



	/**
	 * Asks for confirmation whether to manually follow redirect
	 * @param \Kdyby\Curl\Response $response
	 *
	 * @return boolean
	 */
	protected function confirmRedirect(Response $response)
	{
		if ($this->confirmRedirect !== NULL) {
			return (bool)$this->confirmRedirect->invoke($response);
		}

		return TRUE;
	}



	/**
	 * @param string $ip
	 * @param int $port
	 * @param string $username
	 * @param string $password
	 * @param int $timeout
	 *
	 * @return \Kdyby\Curl\CurlSender
	 */
	public function addProxy($ip, $port = 3128, $username = NULL, $password = NULL, $timeout = 15)
	{
		$this->proxies[] = array(
			'ip' => $ip,
			'port' => $port,
			'user' => $username,
			'pass' => $password,
			'timeout' => $timeout
		);

		return $this;
	}



	/**
	 * @return array
	 */
	public function getProxies()
	{
		return $this->proxies;
	}



	/**
	 * @param \Kdyby\Curl\Request $request
	 *
	 * @return \Kdyby\Curl\Response
	 */
	public function send(Request $request)
	{
		$this->queriedRequest = $request;

		try {
			return $this->sendRequest($request, 1);

		} catch (\Exception $e) {
			$this->queriedRequest = NULL;
			throw $e;
		}
	}



	/**
	 * @param \Kdyby\Curl\Request $request
	 * @param int $cycles
	 *
	 * @return \Kdyby\Curl\Response
	 */
	protected function sendRequest(Request $request, $cycles)
	{
		if ($cycles > $this->options['maxRedirs']) {
			throw new CurlException("Redirect loop", $this->queriedRequest);
		}

		$cUrl = new CurlWrapper($request->url, $request->method);
		$cUrl->setOptions($request->options + $this->options);
		$cUrl->setHeaders($request->headers + $this->headers);
		$cUrl->setPost($request->post, $request->files);

		if (!$this->canFollowRedirect()) {
			$cUrl->setOption('followLocation', NULL);
		}

		if ($request->isMethod(Request::DOWNLOAD)) {
			if (!is_dir($this->downloadDir)) {
				throw new Kdyby\InvalidStateException("Please provide a writable directory for download.");
			}
			FileResponse::prepareDownload($cUrl, $this->downloadDir);

		} else {
			$cUrl->setOption('header', TRUE);
		}

		$proxies = $this->proxies;
		do {
			if ($cUrl->setProxy(array_shift($proxies))->execute()) {
				break;

			} elseif (!$cUrl->isProxyFail()) {
				break;
			}

		} while (!$cUrl->isOk() && $proxies);

		if (!$cUrl->response) {
			throw new FailedRequestException($cUrl);
		}

		$response = $this->buildResponse($cUrl);
		if (($statusCode = $response->headers['Status-Code']) >= 400 && $statusCode < 600) {
			throw new BadStatusException("Status $statusCode: $cUrl->error", $request, $response);
		}

		if ($this->isForcingFollowRedirect($cUrl, $response)) {
			$request = $this->queriedRequest->followRedirect($response);
			$response = $this->sendRequest($request, ++$cycles);
		}

		return $response;
	}



	/**
	 * @param \Kdyby\Curl\CurlWrapper $curl
	 *
	 * @return \Kdyby\Curl\Response
	 */
	protected function buildResponse(CurlWrapper $curl)
	{
		if ($this->queriedRequest->method === Request::DOWNLOAD) {
			$headers = FileResponse::stripHeaders($curl);
			return new FileResponse($headers, $curl->file);
		}

		$headers = Response::stripHeaders($curl);
		if (strpos($headers['Content-Type'], 'html') !== FALSE || strpos($headers['Content-Type'], 'html') !== FALSE) {
			return new HtmlResponse($headers, $curl->response);
		}

		return new Response($headers, $curl->response);
	}



	/**
	 * @param \Kdyby\Curl\CurlWrapper $curl
	 * @param \Kdyby\Curl\Response $response
	 *
	 * @return boolean
	 */
	protected function isForcingFollowRedirect(CurlWrapper $curl, Response $response)
	{
		return isset($response->headers['Location'])
			&& $this->confirmRedirect($response)
			&& ($curl->options['followLocation'] || !$this->canFollowRedirect());
	}



	/**
	 * @return boolean
	 */
	public function canFollowRedirect()
	{
		return !$this->isInSafeMode() && ini_get('open_basedir') == "";
	}



	/**
	 * @return boolean
	 */
	public static function isInSafeMode()
	{
		$status = strtolower(ini_get('safe_mode'));
		return $status === 'on' || $status === 'true' || $status === 'yes' || $status % 256;
	}

}
