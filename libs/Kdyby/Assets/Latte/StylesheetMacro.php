<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Assets\Latte;

use Assetic;
use Kdyby;
use Kdyby\Assets\FormulaeManager;
use Kdyby\Templates\LatteHelpers;
use Nette;
use Nette\Latte;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class StylesheetMacro extends MacroBase
{

	/** @var array */
	private $prolog = array();



	/**
	 * @param \Nette\Latte\Parser $parser
	 *
	 * @return \Kdyby\Assets\Latte\JavascriptMacro
	 */
	public static function install(Latte\Parser $parser)
	{
		$me = new static($parser);
		$parser->addMacro('stylesheet', $me);
		return $me;
	}



	/**
	 * Finishes template parsing.
	 * @return array(prolog, epilog)
	 */
	public function finalize()
	{
		$prolog = $this->prolog;
		$this->prolog = array();
		return array(implode("\n", $prolog));
	}



	/**
	 * @param \Nette\Latte\MacroNode $node
	 *
	 * @return string
	 */
	public function nodeOpened(Latte\MacroNode $node)
	{
		if ($this->isContext(Latte\Parser::CONTEXT_TAG)) {
			return FALSE;
		}

		$node->isEmpty = TRUE;
		$writer = Latte\PhpWriter::using($node, $this->getParser()->getContext());
		$args = LatteHelpers::readArguments($node->tokenizer, $writer);
		if (isset($args['filter'])) {
			$args['filters'] = $args['filter'];
			unset($args['filter']);
		}

		$this->prolog[] = $this->createFactory($args, FormulaeManager::TYPE_STYLESHEET);

		return "";
	}



	/**
	 * @param \Nette\Latte\MacroNode $node
	 *
	 * @return string
	 */
	public function nodeClosed(Latte\MacroNode $node)
	{
		return "";
	}

}
