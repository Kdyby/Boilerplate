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
use Nette\Templating\Template;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class JavascriptTag extends MacroBase
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
		$parser->addMacro('javascript', $me);
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
		return array(implode("\n", $prolog) . "\n" . '$_g->kdyby->assets = array();');
	}



	/**
	 * @param \Nette\Latte\MacroNode $node
	 *
	 * @return string
	 */
	public function nodeOpened(Latte\MacroNode $node)
	{
		if (!$this->isContext(Latte\Parser::CONTEXT_TAG)) {
			return FALSE;
		}

		dump($node, empty($node->args));
		$node->data->inline = empty($node->args);
		if ($node->data->inline) {
			return '<?php ob_start(); ?>';
		}

		return NULL;
	}



	/**
	 * @param \Nette\Latte\MacroNode $node
	 *
	 * @return string
	 */
	public function nodeClosed(Latte\MacroNode $node)
	{
		if ($node->data->inline) {
			return '<?php $_g->kdyby->assets["js"][] = ob_get_clean();' .
				'if (empty($_g->kdyby->captureAssets)) echo array_pop($_g->kdyby->assets["js"]); ?>';
		}

		return NULL;
	}

}
