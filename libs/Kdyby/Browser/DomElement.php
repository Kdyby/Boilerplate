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
use Symfony\Component\CssSelector\CssSelector;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class DomElement extends Nette\Object
{

	/** @var \DOMNode|\DomDocument */
	protected $element;



	/**
	 * @param \DOMNode $element
	 */
	public function __construct(\DOMNode $element)
	{
		$this->element = $element;
	}



	/**
	 * @return \DOMNode
	 */
	public function getElement()
	{
		return $this->element;
	}



	/**
	 * @param string $query
	 *
	 * @return \DOMNode[]|NULL
	 */
	public function find($query)
	{
		$xpath = new \DOMXPath($this->element->ownerDocument ?: $this->element);
		return DomDocument::nodeListToArray($xpath->query(CssSelector::toXPath($query), $this->element));
	}



	/**
	 * @param string $query
	 *
	 * @return \DOMNode
	 */
	public function findOne($query)
	{
		return current($this->find($query));
	}



	/**
	 * @param string $text
	 * @param string $element
	 *
	 * @return array|NULL
	 */
	public function findText($text, $element = NULL)
	{
		$xpath = new \DOMXPath($this->element->ownerDocument);
		$element = $element ? CssSelector::toXPath($element) : NULL;
		return DomDocument::nodeListToArray($xpath->query($element . '[contains(., "' . $text . '")]', $this->element));
	}



	/**
	 * @param \Kdyby\Browser\ISnippetProcessor $snippetProcessor
	 *
	 * @return mixed
	 */
	public function processSnippet(ISnippetProcessor $snippetProcessor)
	{
		$node = $this->findOne($snippetProcessor->getSelector());
		return $node ? $snippetProcessor->process($node) : NULL;
	}



	/**
	 * @param \Kdyby\Browser\ISnippetProcessor $snippetProcessor
	 *
	 * @return mixed
	 */
	public function processSnippets(ISnippetProcessor $snippetProcessor)
	{
		$nodes = $this->find($snippetProcessor->getSelector());
		return $nodes ? array_map(array($snippetProcessor, 'process'), $nodes) : NULL;
	}

}
