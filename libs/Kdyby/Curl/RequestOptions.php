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



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @property-write int $timeout
 * @property-write string $referer
 * @property-write string $userAgent
 * @property-write bool $followRedirects
 * @property-write int $maximumRedirects
 * @property-write bool $returnTransfer
 */
abstract class RequestOptions extends Nette\Object
{

	/** @var array */
	public $options = array(
		'timeout' => 15,
		'followLocation' => FALSE, // curl is not passing cookies around
		'maxRedirs' => 10,
		'returnTransfer' => TRUE,
	);



	/**
	 * @param int $timeout
	 *
	 * @return \Kdyby\Curl\RequestOptions
	 */
	public function setTimeout($timeout)
	{
		$this->options['timeout'] = $timeout;
		return $this;
	}



	/**
	 * @param string $referer
	 *
	 * @return \Kdyby\Curl\RequestOptions
	 */
	public function setReferer($referer)
	{
		$this->options['referer'] = (string)$referer;
		return $this;
	}



	/**
	 * @param string $ua
	 *
	 * @return \Kdyby\Curl\RequestOptions
	 */
	public function setUserAgent($ua)
	{
		$this->options['userAgent'] = $ua;
		return $this;
	}



	/**
	 * @param boolean $yes
	 *
	 * @return \Kdyby\Curl\RequestOptions
	 */
	public function setFollowRedirects($yes = TRUE)
	{
		$this->options['followLocation'] = (bool)$yes;
		return $this;
	}



	/**
	 * @param int $count
	 *
	 * @return \Kdyby\Curl\RequestOptions
	 */
	public function setMaximumRedirects($count)
	{
		$this->options['maxRedirs'] = $count;
		return $this;
	}



	/**
	 * @param boolean $yes
	 *
	 * @return \Kdyby\Curl\RequestOptions
	 */
	public function setReturnTransfer($yes = TRUE)
	{
		$this->options['returnTransfer'] = (bool)$yes;
		return $this;
	}



	/**
	 * Sets if all certificates are trusted by default
	 *
	 * @param boolean $yes
	 *
	 * @return \Kdyby\Curl\RequestOptions
	 */
	public function setCertificationVerify($yes = TRUE)
	{
		$this->options['ssl_verifyPeer'] = (bool)$yes;
		return $this;
	}



	/**
	 * Adds path to trusted certificate and unsets directory with certificates if set
	 * WARNING: Overwrites the last given certificate
	 *
	 * CURLOPT_SSL_VERIFYHOST:
	 *	0: Don’t check the common name (CN) attribute
	 *	1: Check that the common name attribute at least exists
	 *	2: Check that the common name exists and that it matches the host name of the server
	 *
	 * @param string $cert
	 * @param int $verifyHost
	 *
	 * @throws \Kdyby\InvalidArgumentException
	 * @return \Kdyby\Curl\RequestOptions
	 */
	public function setTrustedCertificate($cert, $verifyHost = self::VERIFYHOST_MATCH)
	{
		if (!in_array($verifyHost, range(0, 2))) {
			throw new Kdyby\InvalidArgumentException("Verify host must be 0, 1 or 2");
		}

		if (!file_exists($cert)) {
			throw new Kdyby\FileNotFoundException('Certificate "' . $cert . ' is not readable.');
		}

		unset($this->options['caPath']);
		$this->setCertificationVerify();
		$this->options['ssl_verifyHost'] = $verifyHost; // 2=secure
		$this->options['caInfo'] = $cert;

		return $this;
	}



	/**
	 * Adds path to directory which contains trusted certificate and unsets single certificate if set
	 * WARNING: Overwrites the last one
	 *
	 * CURLOPT_SSL_VERIFYHOST:
	 *	0: Don’t check the common name (CN) attribute
	 *	1: Check that the common name attribute at least exists
	 *	2: Check that the common name exists and that it matches the host name of the server
	 *
	 * @param string $dir
	 * @param int $verifyHost
	 *
	 * @throws \Kdyby\InvalidArgumentException
	 * @return \Kdyby\Curl\RequestOptions
	 */
	public function setTrustedCertificatesDirectory($dir, $verifyHost = 2)
	{
		if (!in_array($verifyHost, range(0, 2))) {
			throw new Kdyby\InvalidArgumentException("Verify host must be 0, 1 or 2");
		}

		if (!is_dir($dir)) {
			throw new Kdyby\FileNotFoundException('Certificate directory "' . $dir . ' is not readable.');
		}

		unset($this->options['caInfo']);
		$this->setCertificationVerify();
		$this->options['ssl_verifyHost'] = $verifyHost; // 2=secure
		$this->options['caPath'] = $dir;

		return $this;
	}

}
