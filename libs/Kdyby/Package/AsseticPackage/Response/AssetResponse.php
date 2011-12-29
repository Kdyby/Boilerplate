<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Package\AsseticPackage\Response;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @property-read string $file
 * @property-read string $contentType
 */
class AssetResponse extends Nette\Object implements Nette\Application\IResponse
{
	/** @var string */
	private $file;



	/**
	 * @param string $file
	 */
	public function __construct($file)
	{
		if (!is_file($file)) {
			throw new Nette\Application\BadRequestException("File '$file' doesn't exist.");
		}

		$this->file = $file;
	}



	/**
	 * Returns the path to a file.
	 *
	 * @return string
	 */
	final public function getFile()
	{
		return $this->file;
	}



	/**
	 * Returns the MIME content type of a downloaded file.
	 *
	 * @return string
	 */
	final public function getContentType()
	{
		return Nette\Utils\MimeTypeDetector::fromFile($this->file);
	}



	/**
	 * Sends response to output.
	 *
	 * @param \Nette\Http\IRequest $httpRequest
	 * @param \Nette\Http\IResponse $httpResponse
	 *
	 * @return void
	 */
	public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse)
	{
		$httpResponse->setContentType($this->getContentType());
		$httpResponse->setHeader('Content-Length', filesize($this->file));

		$handle = fopen($this->file, 'r');
		while (!feof($handle)) {
			echo fread($handle, 4e6);
		}
		fclose($handle);
	}

}
