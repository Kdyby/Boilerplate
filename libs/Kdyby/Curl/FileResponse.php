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
class FileResponse extends Response
{

	/** @var string */
	private $file;

	/** @var string */
	private $type;



	/**
	 * @param \Kdyby\Curl\CurlWrapper $curl
	 * @param array $headers
	 */
	public function __construct(CurlWrapper $curl, array $headers)
	{
		parent::__construct($curl, $headers);
		$this->file = $curl->file;
	}



	/**
	 * Returns the MIME content type of a file.
	 * @return string
	 */
	public function getContentType()
	{
		if ($this->type === NULL) {
			$this->type = Nette\Utils\MimeTypeDetector::fromFile($this->file);
		}
		return $this->type;
	}



	/**
	 * Returns the size of a file.
	 * @return int
	 */
	public function getSize()
	{
		return filesize($this->file);
	}



	/**
	 * Returns the path to a file.
	 * @return string
	 */
	public function getTemporaryFile()
	{
		return $this->file;
	}



	/**
	 * Returns the path to a file.
	 * @return string
	 */
	public function __toString()
	{
		return $this->file;
	}



	/**
	 * Move file to new location.
	 * @param string $dest
	 *
	 * @return \Kdyby\Curl\FileResponse
	 */
	public function move($dest)
	{
		Kdyby\Tools\Filesystem::mkDir(dirname($dest));
		if (!@rename($this->file, $dest)) {
			throw new Kdyby\IOException("Unable to move file '$this->file' to '$dest'.");
		}
		chmod($dest, 0666);
		$this->file = $dest;
		return $this;
	}



	/**
	 * Is uploaded file GIF, PNG or JPEG?
	 * @return boolean
	 */
	public function isImage()
	{
		return in_array($this->getContentType(), array('image/gif', 'image/png', 'image/jpeg'), TRUE);
	}



	/**
	 * Returns the image.
	 * @return \Nette\Image
	 */
	public function toImage()
	{
		return Nette\Image::fromFile($this->file);
	}



	/**
	 * Returns the dimensions of an image as array.
	 * @return array
	 */
	public function getImageSize()
	{
		return @getimagesize($this->file); // @ - files smaller than 12 bytes causes read error
	}



	/**
	 * Get file contents.
	 * @return string
	 */
	public function getContents()
	{
		return file_get_contents($this->file);
	}



	/**
	 * @param \Kdyby\Curl\CurlWrapper $curl
	 *
	 * @return array
	 */
	public static function stripHeaders(CurlWrapper $curl)
	{
		$headersFile = $curl->file . '.headers';
		@fclose($curl->options['file']); // internationally @
		@fclose($curl->options['writeHeader']); // internationally @

		if (($headersHandle = @fopen($headersFile, "rb")) === FALSE) { // internationally @
			throw new Kdyby\IOException("File '$headersFile' not readable.");
		}

		$curl->responseHeaders = fread($headersHandle, filesize($headersFile));
		if (!$headers = CurlWrapper::parseHeaders($curl->responseHeaders)) {
			throw new CurlException("Failed parsing of response headers");
		}
		if (!@fclose($headersHandle) || !@unlink($headersFile)) {
			throw new Kdyby\IOException("File '$headersFile' can't be deleted.");
		}

		return $headers;
	}



	/**
	 * @param \Kdyby\Curl\CurlWrapper $curl
	 * @param string $dir
	 *
	 * @return \Kdyby\Curl\CurlWrapper
	 */
	public static function prepareDownload(CurlWrapper $curl, $dir)
	{
		$fileName = urlencode((string)$curl->getUrl()) . '.' . Strings::random();
		if (($fileHandle = @fopen($curl->file = $dir . '/' . $fileName, 'wb')) === FALSE) {
			throw Kdyby\FileNotWritableException::fromFile($curl->file);
		}
		if (($headersHandle = @fopen($curl->file . '.headers', 'wb')) === FALSE) {
			throw Kdyby\FileNotWritableException::fromFile($curl->file);
		}
		return $curl->setOptions(array(
			'file' => $fileHandle,
			'writeHeader' => $headersHandle,
			'binaryTransfer' => TRUE
		));
	}

}
