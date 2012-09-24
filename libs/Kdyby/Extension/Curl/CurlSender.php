<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Extension\Curl;

use Kdyby;
use Nette;
use Nette\Http\UrlScript as Url;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class CurlSender extends RequestOptions
{
	/** @var array */
	public static $userAgents = array(
		'Chrome' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/13.0.782.109 Safari/535.1',
		'FireFox3' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; pl; rv:1.9) Gecko/2008052906 Firefox/3.0',
		'GoogleBot' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
		'IE7' => 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)',
		'Netscape' => 'Mozilla/4.8 [en] (Windows NT 6.0; U)',
		'Opera' => 'Opera/9.25 (Windows NT 6.0; U; en)',
	);

	/** @var array An associative array of headers to send along with requests */
	public $headers = array();

	/** @var boolean|integer */
	public $repeatOnFail = FALSE;

	/** @var array */
	private $proxies = array();

	/** @var \Nette\Callback */
	private $confirmRedirect;

	/** @var string */
	private $downloadDir;

	/** @var Request */
	private $queriedRequest;

	/** @var IRequestLogger */
	private $logger;



	/**
	 * @param int $timeout
	 *
	 * @return CurlSender
	 */
	public function setConnectTimeout($timeout)
	{
		$this->options['connectTimeout'] = $timeout;
		return $this;
	}



	/**
	 * @param string $ua
	 *
	 * @return CurlSender
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
	 * @throws DirectoryNotWritableException
	 */
	public function setDownloadDir($downloadDir)
	{
		if (!is_dir($downloadDir) || !is_writable($downloadDir)) {
			throw new DirectoryNotWritableException("Please make directory $downloadDir writable.");
		}

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
	 * @param callable $confirmRedirect
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
	 * @param Response $response
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
	 * @return CurlSender
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
	 * @param IRequestLogger $logger
	 */
	public function setLogger(IRequestLogger $logger)
	{
		$this->logger = $logger;
	}



	/**
	 * @param Request $request
	 *
	 * @throws \Exception
	 * @return Response
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
	 * @param Request $request
	 * @param int $cycles
	 *
	 * @throws CurlException
	 * @throws BadStatusException
	 * @throws FailedRequestException
	 * @throws DirectoryNotWritableException
	 * @return Response
	 */
	protected function sendRequest(Request $request, $cycles)
	{
		if ($cycles > $this->options['maxRedirs']) {
			throw new CurlException("Redirect loop", $this->queriedRequest);
		}

		// combine setup
		$request->options += $this->options;
		$request->headers += $this->headers;

		// cookies
		if ($request->cookies){
			$request->headers['Cookie'] = $request->getCookies();
		}

		// wrap
		$cUrl = new CurlWrapper($request->getUrl(), $request->method);
		$cUrl->setOptions($request->options);
		$cUrl->setHeaders($request->headers);
		$cUrl->setPost($request->post, $request->files);

		// fallback when safe_mode
		if (!$this->canFollowRedirect()) {
			$cUrl->setOption('followLocation', NULL);
		}

		// method & prepare download
		if ($request->isMethod(Request::DOWNLOAD)) {
			if (!is_dir($this->downloadDir)) {
				throw new DirectoryNotWritableException("Please provide a writable directory for download.");
			}
			FileResponse::prepareDownload($cUrl, $this->downloadDir);

		} else {
			$cUrl->setOption('header', TRUE);
		}

		// logging
		if ($this->logger) {
			$requestId = $this->logger->request($request);
		}

		// sending process
		$repeat = $this->repeatOnFail;
		do {
			$proxies = $this->proxies;
			do {
				if ($cUrl->setProxy(array_shift($proxies))->execute()) {
					break;

				} elseif (!$cUrl->isProxyFail()) {
					break;
				}

			} while (!$cUrl->isOk() && $proxies);
		} while (!$cUrl->response && $repeat-- > 0);

		// request failed
		if (!$cUrl->response) {
			throw new FailedRequestException($cUrl);
		}

		// build & check response
		$response = $this->buildResponse($cUrl);
		if (($statusCode = $response->headers['Status-Code']) >= 400 && $statusCode < 600) {
			throw new BadStatusException($response->headers['Status'], $request, $response);
		}

		// force redirect on Location header
		if ($this->isForcingFollowRedirect($cUrl, $response)) {
			$request = $this->queriedRequest->followRedirect($response);
			$response = $this->sendRequest($request, ++$cycles)
				->setPrevious($response); // override
		}

		// log response
		if ($this->logger && isset($requestId)) {
			$this->logger->response($response, $requestId);
		}

		// return
		return $response;
	}



	/**
	 * @param CurlWrapper $curl
	 *
	 * @return Response
	 */
	protected function buildResponse(CurlWrapper $curl)
	{
		if ($this->queriedRequest->method === Request::DOWNLOAD) {
			$headers = FileResponse::stripHeaders($curl);
			if ($previous = $this->buildRedirectResponse($curl)) {
				$headers = CurlWrapper::parseHeaders($curl->responseHeaders);
			}

			$response = new FileResponse($curl, $headers);
			$response->setPrevious($previous);
			return $response;
		}

		$headers = Response::stripHeaders($curl);
		if ($previous = $this->buildRedirectResponse($curl)) {
			$headers = CurlWrapper::parseHeaders($curl->responseHeaders);
		}

		if ($this->isHtmlResponse($curl, $headers)) {
			$curl->response = HtmlResponse::convertEncoding($curl);
			$response = new HtmlResponse($curl, $headers);
			$response->setPrevious($previous);
			return $response;
		}

		$response = new Response($curl, $headers);
		$response->setPrevious($previous);
		return $response;
	}



	/**
	 * @param CurlWrapper $curl
	 * @param array $headers
	 * @return bool
	 */
	private function isHtmlResponse(CurlWrapper $curl, array $headers)
	{
		return $curl->getMethod() !== Request::HEAD
			&& (strpos($headers['Content-Type'], 'html') !== FALSE
				|| strpos($headers['Content-Type'], 'html') !== FALSE);
	}



	/**
	 * @param CurlWrapper $curl
	 *
	 * @return Response|NULL
	 */
	protected function buildRedirectResponse(CurlWrapper $curl)
	{
		if ($curl->info['redirect_count'] === 0) {
			return NULL;
		}

		$previous = $last = NULL;
		$url = $curl->getUrl();

		$parts = Strings::split($curl->responseHeaders, '~(HTTP/\d\.\d\s\d+\s.*)~m', PREG_SPLIT_NO_EMPTY);
		while ($rawHeaders = array_shift($parts)) {
			if ($http = Strings::match($rawHeaders, CurlWrapper::VERSION_AND_STATUS)) {
				if ($http['code'] < 200) {
					continue;
				}

				$rawHeaders .= array_shift($parts);
			}

			if (!$parts) {
				$curl->responseHeaders = $rawHeaders;
				return $last;
			}

			if ($headers = CurlWrapper::parseHeaders($rawHeaders)) {
				$previous = new Response(new Url($url), $headers);
				if ($last !== NULL) {
					$previous->setPrevious($last);
				}
			}

			$last = $previous;
		}

		return $last;
	}



	/**
	 * @param CurlWrapper $curl
	 * @param Response $response
	 *
	 * @return boolean
	 */
	protected function isForcingFollowRedirect(CurlWrapper $curl, Response $response)
	{
		return isset($response->headers['Location']) && $this->confirmRedirect($response)
			/*&& (!$curl->options['followLocation'] || !$this->canFollowRedirect())*/;
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
