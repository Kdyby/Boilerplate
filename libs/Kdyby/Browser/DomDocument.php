<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Browser;

use Kdyby;
use Nette;
use Nette\Diagnostics\Debugger;
use Nette\Utils\Strings;
use Nette\Utils\Html;
use Symfony\Component\CssSelector\CssSelector;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
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
	 * @param string $query
	 * @param \DOMNode|string $context
	 *
	 * @return \DOMNode[]
	 */
	public function find($query, $context = NULL)
	{
		$xpath = new \DOMXPath($this);
		if (!$context instanceof \DOMNode) {
			$context = $this->find($context);
		}
		return static::nodeListToArray($xpath->query(CssSelector::toXPath($query), $context));
	}



	/**
	 * @param string $query
	 * @param \DOMNode|string $context
	 *
	 * @return \DOMNode
	 */
	public function findOne($query, $context = NULL)
	{
		return current($this->find($query, $context));
	}



	/**
	 * @param string $html
	 * @param string $version
	 * @param string $encoding
	 *
	 * @return \Kdyby\Browser\DomDocument
	 */
	public static function fromMalformedHtml($html, $version = '1.0', $encoding = 'UTF-8')
	{
		$dom = new static($version, $encoding);
		$dom->loadMalformed($html);
		return $dom;
	}



	/**
	 * @param string $html
	 * @return \Kdyby\Browser\DomDocument
	 */
	public function loadMalformed($html)
	{
		$html = static::fixHtml(str_replace("\r", '', $html));

		$this->resolveExternals = FALSE;
		$this->validateOnParse = FALSE;
		$this->preserveWhiteSpace = FALSE;
		$this->strictErrorChecking = FALSE;
		$this->recover = TRUE;

		Debugger::tryError();
		@$this->loadHTML($html); // TODO: purify?
		if (Debugger::catchError($error)) {
			$exception = new DomException($error->getMessage(), NULL, $error);
			$exception->setSource($html);
			if ($m = Nette\Utils\Strings::match($error->getMessage(), '~line\:[^\d]+(?P<line>\d+)~i')) {
				$exception->setDocumentLine((int)$m['line']);
			}
			throw $exception;
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
