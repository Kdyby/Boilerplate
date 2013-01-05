<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\SvgRenderer;

use DOMDocument;
use DOMXPath;
use Kdyby;
use Nette;
use Kdyby\Extension\SvgRenderer\DI\Configuration;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip@prochazka.su>
 * @see http://fukuchi.org/works/qrencode/index.html.en
 */
class SvgImage extends Nette\Object
{

	const XML_HTML_UNKNOWN_TAG = 801;

	/**
	 * @var \DOMDocument
	 */
	private $xml;



	/**
	 * @param string $xml
	 * @throws DomDocumentException
	 */
	public function __construct($xml)
	{
		$this->xml = $this->createDom($xml);
	}



	/**
	 * @return \DOMDocument
	 */
	public function getDocument()
	{
		return $this->xml;
	}



	/**
	 * @return string
	 */
	public function getString()
	{
		return $this->getDocument()->saveXML();
	}



	/**
	 * @param IRenderer $generator
	 * @return string
	 */
	public function render(IRenderer $generator = NULL)
	{
		$generator = $generator ?: new InkscapeRenderer(new Configuration());
		return $generator->render($this);
	}



	/**
	 * @param string $file
	 * @param IRenderer $generator
	 * @return string
	 * @throws IOException
	 */
	public function save($file, IRenderer $generator = NULL)
	{
		if (!@file_put_contents($file, $this->render($generator))) {
			throw new IOException("Cannot write to $file.");
		}

		return $file;
	}



	/**
	 * @param string $xml
	 * @throws DomDocumentException
	 * @return \DOMDocument
	 */
	private function createDom($xml)
	{
		libxml_use_internal_errors(TRUE);
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->resolveExternals = FALSE;
		$dom->validateOnParse = TRUE;
		$dom->preserveWhiteSpace = FALSE;
		$dom->strictErrorChecking = TRUE;
		$dom->recover = TRUE;

		set_error_handler(function ($severity, $message) { });
		@$dom->loadXML(Strings::normalize($xml));
		restore_error_handler();

		$errors = array_filter(libxml_get_errors(), function (\LibXMLError $error) {
			return !in_array((int) $error->code, array(
				SvgImage::XML_HTML_UNKNOWN_TAG,
			), TRUE);
		});
		libxml_clear_errors();

		if ($errors) {
			throw new DomDocumentException($errors);
		}

		$path = new DOMXPath($dom);
		foreach ($path->query('//*') as $node) {
			/** @var \DOMElement $node */
			if ($node->hasAttribute('style')) {
				$node->removeAttribute('style');
			}

//			if (strtolower($node->tagName) === 'image') {
//				try {
//					$imagePath = $imageStorage->find(basename($node->getAttribute('xlink:href')));
//					$node->setAttribute('xlink:href', $imagePath);
//				} catch (FileNotFoundException $e) {
//					$node->parentNode->removeChild($node);
//				}
//			}
		}

		return $dom;
	}

}
