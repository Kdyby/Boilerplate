<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Forms\BootstrapRenderer\Latte;

use Kdyby;
use Kdyby\Extension\Forms\BootstrapRenderer\BootstrapRenderer;
use Nette;
use Nette\Forms\Form;
use Nette\Latte;
use Nette\Latte\PhpWriter;
use Nette\Latte\MacroNode;



/**
 * Standard macros:
 * <code>
 * {form name} as {$form->render('begin')}
 * {form errors} as {$form->render('errors')}
 * {form body} as {$form->render('body')}
 * {form controls} as {$form->render('controls')}
 * {form actions} as {$form->render('actions')}
 * {/form} as {$form->render('end')}
 * </code>
 *
 * or shortcut
 *
 * <code>
 * {form name /} as {$form->render()}
 * </code>
 *
 * Old macros `input` & `label` are working the same.
 * <code>
 * {input name}
 * {label name /} or {label name}... {/label}
 * </code>
 *
 * Individual rendering:
 * <code>
 * {pair name} as {$form->render($form['name'])}
 * {group name} as {$form->render($form->getGroup('name'))}
 * {container name} as {$form->render($form['name'])}
 * </code>
 *
 * @author Filip Procházka <filip@prochazka.su>
 */
class FormMacros extends Latte\Macros\MacroSet
{

	/**
	 * @param \Nette\Latte\Compiler $compiler
	 * @return \Nette\Latte\Macros\MacroSet|void
	 */
	public static function install(Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('form', array($me, 'macroFormBegin'), array($me, 'macroFormEnd'));
		$me->addMacro('pair', array($me, 'macroPair'));
		$me->addMacro('group', array($me, 'macroGroup'));
		$me->addMacro('container', array($me, 'macroContainer'));
		return $me;
	}



	/**
	 * @param \Nette\Latte\MacroNode $node
	 * @param \Nette\Latte\PhpWriter $writer
	 * @return string
	 */
	public function macroFormBegin(MacroNode $node, PhpWriter $writer)
	{
		if ($node->isEmpty = (substr($node->args, -1) === '/')) {
			$node->setArgs(substr($node->args, 0, -1));

			return $writer->write('$form = $_form = (is_object(%node.word) ? %node.word : $_control->getComponent(%node.word)); $_form->render(NULL, %node.array);');
		}

		$word = $node->tokenizer->fetchWord();
		$node->isEmpty = in_array($word, array('errors', 'body', 'controls', 'buttons'));
		$node->tokenizer->reset();

		return $writer->write('$form = $_form = ' . get_called_class() . '::renderFormPart(%node.word, %node.array, get_defined_vars())');
	}



	/**
	 * @param \Nette\Latte\MacroNode $node
	 * @param \Nette\Latte\PhpWriter $writer
	 */
	public function macroFormEnd(MacroNode $node, PhpWriter $writer)
	{
		return $writer->write('Nette\Latte\Macros\FormMacros::renderFormEnd($_form)');
	}



	/**
	 * @param \Nette\Latte\MacroNode $node
	 * @param \Nette\Latte\PhpWriter $writer
	 */
	public function macroPair(MacroNode $node, PhpWriter $writer)
	{
		if ($this->validateParent($node) === FALSE) {
			return FALSE;
		}

		return $writer->write('$_form->render($_form[%node.word])');
	}



	/**
	 * @param \Nette\Latte\MacroNode $node
	 * @param \Nette\Latte\PhpWriter $writer
	 */
	public function macroGroup(MacroNode $node, PhpWriter $writer)
	{
		if ($this->validateParent($node) === FALSE) {
			return FALSE;
		}

		return $writer->write('$_form->render(is_object(%node.word) ? %node.word : $_form->getGroup(%node.word))');
	}



	/**
	 * @param \Nette\Latte\MacroNode $node
	 * @param \Nette\Latte\PhpWriter $writer
	 */
	public function macroContainer(MacroNode $node, PhpWriter $writer)
	{
		if ($this->validateParent($node) === FALSE) {
			return FALSE;
		}

		return $writer->write('$_form->render($_form[%node.word])');
	}



	/**
	 * @param \Nette\Latte\MacroNode $parent
	 * @throws \Nette\Latte\CompileException
	 * @return bool
	 */
	private function validateParent(MacroNode $parent)
	{
		while ($parent = $parent->parentNode) {
			if ($parent->name === 'form') {
				return TRUE;
			}
		}

		return FALSE;
	}



	/**
	 * @param string $mode
	 * @param array $args
	 * @param array $scope
	 * @throws \Nette\InvalidStateException
	 * @return \Nette\Forms\Form
	 */
	public static function renderFormPart($mode, array $args, array $scope)
	{
		if ($mode instanceof Form) {
			self::renderFormBegin($mode, $args);
			return $mode;

		} elseif (($control = self::scopeVar($scope, 'control')) && ($form = $control->getComponent($mode, FALSE)) instanceof Form) {
			self::renderFormBegin($form, $args);
			return $form;

		} elseif (($form = self::scopeVar($scope, 'form')) instanceof Form) {
			$form->render($mode, $args);

		} else {
			throw new Nette\InvalidStateException('No instanceof Nette\Forms\Form found in local scope.');
		}

		return $form;
	}



	/**
	 * @param Form $form
	 * @param array $args
	 */
	private static function renderFormBegin(Form $form, array $args)
	{
		if ($form->getRenderer() instanceof BootstrapRenderer) {
			$form->render('begin', $args);

		} else {
			Latte\Macros\FormMacros::renderFormBegin($form, $args);
		}
	}



	/**
	 * @param array $scope
	 * @param string $var
	 * @return mixed|NULL
	 */
	private static function scopeVar(array $scope, $var)
	{
		return isset($scope['_' . $var]) ? $scope['_' . $var] : (isset($scope[$var]) ? $scope[$var] : NULL);
	}

}
