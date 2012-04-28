<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Curl\Diagnostics;

use Kdyby;
use Nette;
use Nette\Utils\PhpGenerator as Code;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FileLogger extends Nette\Object implements Kdyby\Extension\Curl\IRequestLogger
{

	/** @var string */
	private $logDir;

	/** @var \Nette\Callback[] */
	private $formaters = array();



	/**
	 * @param string $logDir
	 */
	public function __construct($logDir = NULL)
	{
		$this->logDir = $logDir ?: Nette\Diagnostics\Debugger::$logDirectory;
	}



	/**
	 * @param callback $callback
	 */
	public function addFormater($callback)
	{
		$this->formaters[] = callback($callback);
	}



	/**
	 * @param \Kdyby\Extension\Curl\Request $request
	 */
	public function request(Kdyby\Extension\Curl\Request $request)
	{
		$id = md5(serialize($request));

		$content = array($request->method . ' ' . $request->getUrl());
		foreach ($request->headers as $name => $value) {
			$content[] = "$name: $value";
		}

		$content = '> ' . implode("\n> ", $content) . "\n";
		Kdyby\Tools\Arrays::flatMapAssoc($request->post + $request->files, function ($val, $keys) use (&$content) {
			$content .= implode("][", $keys) . ": " . Code\Helpers::dump($val) . "\n";
		});

		$this->write($content . "\n", $id);

		return $id;
	}



	/**
	 * @param \Kdyby\Extension\Curl\Response $response
	 * @param string $id
	 */
	public function response(Kdyby\Extension\Curl\Response $response, $id)
	{
		$content = array();
		foreach ($response->getHeaders() as $name => $value) {
			$content[] = "$name: $value";
		}

		$content = '< ' . implode("\n< ", $content);
		$this->write($content . "\n\n", $id);

		$body = $response->getResponse();
		foreach ($this->formaters as $formater) {
			if ($formated = $formater($body, $response)) {
				$body = $formated;
			}
		}
		$this->write($body, $id);
	}



	/**
	 * @param string $content
	 * @param string $id
	 */
	protected function write($content, $id)
	{
		$content = is_string($content) ? $content : Code\Helpers::dump($content);

		$file = $this->logDir . '/curl_' . @date('Y-m-d-H-i-s') . '_' . $id . '.dat';
		foreach (Nette\Utils\Finder::findFiles("curl_*_$id.dat")->in($this->logDir) as $item) {
			/** @var \SplFileInfo $item */
			$file = $item->getRealpath();
		}

		if (!@file_put_contents($file, $content, FILE_APPEND)) {
			Nette\Diagnostics\Debugger::log("Logging to $file failed.");
		}
	}

}
