<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Curl;

use Kdyby;
use Nette;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class HtmlResponse extends Response
{

	/**#@+ regexp's for parsing */
	const CONTENT_TYPE = '~^(?P<type>[^;]+);[\t ]*charset=(?P<charset>.+)$~i';
	/**#@- */

	/** @var \Kdyby\Extension\Browser\DomDocument */
	private $document;



	/**
	 * @return \Kdyby\Extension\Browser\DomDocument
	 */
	public function getDocument()
	{
		if ($this->document === NULL) {
			$this->document = Kdyby\Extension\Browser\DomDocument::fromMalformedHtml($this->getResponse());
		}

		return $this->document;
	}



	/**
	 * @param \Kdyby\Extension\Curl\CurlWrapper $curl
	 * @return string
	 */
	public static function convertEncoding(CurlWrapper $curl)
	{
		if (Strings::checkEncoding($response = $curl->response)) {
			return Strings::normalize($response);
		}

		if ($charset = static::charsetFromContentType($curl->info['content_type'])) {
			$response = @iconv($charset, 'UTF-8', $response);

		} else {
			if ($contentType = Strings::match($response, '~<(?P<el>meta[^>]+Content-Type[^>]+)>~i')) {
				foreach (Nette\Utils\Html::el($contentType['el'])->attrs as $attr => $value) {
					if (strtolower($attr) !== 'content') {
						continue;
					}

					if ($charset = static::charsetFromContentType($value)) {
						$response = @iconv($charset, 'UTF-8', $response);
						$response = static::fixContentTypeMeta($response);
						break;
					}
				}

			}
		}

		return Strings::normalize($response);
	}



	/**
	 * @param string $contentType
	 * @return string
	 */
	public static function charsetFromContentType($contentType)
	{
		if ($m = Strings::match($contentType, static::CONTENT_TYPE)) {
			return $m['charset'];
		}
		return NULL;
	}



	/**
	 * Hack for DOMDocument
	 *
	 * @param string $document
	 * @param string $charset
	 *
	 * @return string
	 */
	public static function fixContentTypeMeta($document, $charset = 'utf-8')
	{
		return Strings::replace($document,
			'~<meta[^>]+Content-Type[^>]+>~i',
			'<meta http-equiv="Content-Type" content="text/html; charset=' . $charset . '" />'
		);
	}

}
