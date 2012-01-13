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
use Nette;
use Nette\Latte;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class JavascriptMacro extends MacroBase
{

	/**
	 * @param \Nette\Latte\Compiler $compiler
	 *
	 * @return \Kdyby\Assets\Latte\JavascriptMacro
	 */
	public static function install(Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$compiler->addMacro('javascript', $me);
		$compiler->addMacro('js', $me);
		return $me;
	}



	/**
	 * @param \Nette\Latte\MacroNode $node
	 *
	 * @return string
	 */
	public function nodeOpened(Latte\MacroNode $node)
	{
		if ($node->data->inline = empty($node->args)) {
			return '<?php ob_start(); ?>';
		}

		if ($this->createFactory($this->readArguments($node), FormulaeManager::TYPE_JAVASCRIPT)) {
			$node->isEmpty = TRUE;
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

		$args = Nette\Utils\Html::el(substr($node->content, 1, strpos($node->content, '>') - 1))->attrs;
		if (isset($args['filter'])) {
			$args['filters'] = $args['filter'];
			unset($args['filter']);
		}

		$this->createFactory(array($node->args) + $args, FormulaeManager::TYPE_JAVASCRIPT);

		return NULL;
	}

}
