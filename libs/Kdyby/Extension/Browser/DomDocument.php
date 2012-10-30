<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Extension\Browser;

use Kdyby;
use Nette;
use Nette\Diagnostics\Debugger;
use Nette\Utils\Strings;
use Nette\Utils\Html;
use Symfony\Component\CssSelector\CssSelector;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class DomDocument extends \DOMDocument
{

	/**
	 * @param string $version
	 * @param string $encoding
	 */
	public function __construct($version = '1.0', $encoding = 'UTF-8')
	{
		parent::__construct($version, $encoding);
	}



	/**
	 * @param string $selector
	 * @param \DOMNode|string $context
	 *
	 * @return \DOMNode[]|\DOMElement[]
	 */
	public function find($selector, $context = NULL)
	{
		if (strpos($selector, ',') !== FALSE) {
			$result = array();
			foreach (Strings::split($selector, '~\s*,\s*~') as $part) {
				$result = array_merge($result, (array)$this->find($part, $context));
			}

			return $result;
		}

		if ($context !== NULL && !$context instanceof \DOMNode) {
			$context = $this->find($context);
		}
		$xpath = new \DOMXPath($this);
		return static::nodeListToArray($xpath->query(CssSelector::toXPath($selector), $context));
	}



	/**
	 * @param string $selector
	 * @param \DOMNode|string $context
	 *
	 * @return \DOMNode|\DOMElement
	 */
	public function findOne($selector, $context = NULL)
	{
		return ($result = $this->find($selector, $context)) ? current($result) : NULL;
	}



	/**
	 * @param \Kdyby\Extension\Browser\IDocumentProcessor $processor
	 *
	 * @return mixed
	 */
	public function process(IDocumentProcessor $processor)
	{
		return $processor->process($this);
	}



	/**
	 * @param string $selector
	 * @param \Kdyby\Extension\Browser\ISnippetProcessor $processor
	 *
	 * @return mixed
	 */
	public function processSnippets($selector, ISnippetProcessor $processor)
	{
		$result = array();
		foreach ($this->find($selector) as $node) {
			$result[] = $processor->process($node);
		}

		return $result;
	}



	/**
	 * @param string $html
	 * @param string $version
	 * @param string $encoding
	 *
	 * @return \Kdyby\Extension\Browser\DomDocument
	 */
	public static function fromMalformedHtml($html, $version = '1.0', $encoding = 'UTF-8')
	{
		if ($html[0] === '/' && file_exists($html)) {
			$html = file_get_contents($html);
		}

		$dom = new static($version, $encoding);
		$dom->loadMalformed($html);
		return $dom;
	}



	/**
	 * @param string $html
	 * @return \Kdyby\Extension\Browser\DomDocument
	 */
	public function loadMalformed($html)
	{
		$html = static::fixHtml(str_replace("\r", '', $html));

		$this->resolveExternals = FALSE;
		$this->validateOnParse = FALSE;
		$this->preserveWhiteSpace = FALSE;
		$this->strictErrorChecking = FALSE;
		$this->recover = TRUE;

		set_error_handler(function ($severity, $message) {
			restore_error_handler();
			throw new DomException($message);
		});

		try {
			@$this->loadHTML($html); // TODO: purify?
			restore_error_handler();

		} catch (DomException $e) {
			$e->setSource($html);
			if ($m = Nette\Utils\Strings::match($e->getMessage(), '~line\:[^\d]+(?P<line>\d+)~i')) {
				$e->setDocumentLine((int)$m['line']);
			}
			throw $e;
		}

		return $this;
	}



	/**
	 * @param string $html
	 * @return string
	 */
	public static function fixHtml($html)
	{
		// & html entities FUUUU
		$html = Strings::replace($html, '~\&([^\s]{0,10})~i', function ($m) {
			return empty($m[1]) ? '&amp;' : (strpos($m[0], ';') === FALSE ? '' : $m[0]);
		});

		// xhtml FUUUU
		$html = Strings::replace($html, array(
			'~<!DOCTYPE[^>]+>~im' => '<!DOCTYPE html>',
			'~<html[^>]+>~im' => '<html>',
		));

		// multiplied attributes FUUUU
		$html = Strings::replace($html, '~</?(([^\s>](?<!\!)[^\s>]*)[^>]*?)?(?:\s+?/)?>~im', function ($m) {
			if (count($m) < 3) return $m[0];
			return str_replace($m[1], $m[2] . Html::el($m[1])->attributes(), $m[0]);
		});

		return $html;
	}



	/**
	 * @param \DOMNodeList $nodeList
	 *
	 * @return \DOMNode[]|NULL
	 */
	public static function nodeListToArray($nodeList)
	{
		if ($nodeList === FALSE) {
			return NULL;
		}

		$nodes = array();
		foreach ($nodeList as $node) {
			$nodes[] = $node;
		}
		return $nodes ?: NULL;
	}

}
