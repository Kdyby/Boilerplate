<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Assets\Latte;

use Kdyby;
use Nette;
use Nette\Utils\PhpGenerator as Code;
use Nette\Latte;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AsseticMacroSet extends Latte\Macros\MacroSet
{

	/** @var \Kdyby\Assets\FormulaeManager */
	private $manager;

	/** @var \Kdyby\Assets\AssetFactory */
	private $factory;

	/** @var array */
	private $prolog = array();



	/**
	 * @param \Kdyby\Assets\FormulaeManager $manager
	 */
	public function setManager(FormulaeManager $manager)
	{
		$this->manager = $manager;
	}



	/**
	 * @param \Kdyby\Assets\AssetFactory $factory
	 */
	public function setFactory(AssetFactory $factory)
	{
		$this->factory = $factory;
	}



	/**
	 * @param \Nette\Latte\Parser $parser
	 * @return \Kdyby\Package\AsseticPackage\Latte\AsseticMacroSet
	 */
	public static function install(Latte\Parser $parser)
	{
		$me = new static($parser);
		$me->addMacro('javascript', array($me, 'javascriptMacro'));
		$me->addMacro('stylesheet', array($me, 'stylesheetMacro'));
		return $me;
	}



	/**
	 * Finishes template parsing.
	 * @return array(prolog, epilog)
	 */
	public function finalize()
	{
		return array(implode("\n", $this->prolog));
	}



	/**
	 * @param \Nette\Latte\MacroNode $node
	 * @param \Nette\Latte\PhpWriter $writer
	 *
	 * @return bool
	 */
	public function javascriptMacro(Latte\MacroNode $node, Latte\PhpWriter $writer)
	{
		$args = $this->readArguments($node->tokenizer, $writer);
		$this->prolog[] = $this->createFactory($args, FormulaeManager::TYPE_JAVASCRIPT);

		return "";
	}



	/**
	 * @param \Nette\Latte\MacroNode $node
	 * @param \Nette\Latte\PhpWriter $writer
	 *
	 * @return bool
	 */
	public function stylesheetMacro(Latte\MacroNode $node, Latte\PhpWriter $writer)
	{
		$args = $this->readArguments($node->tokenizer, $writer);
		$this->prolog[] = $this->createFactory($args, FormulaeManager::TYPE_STYLESHEET);

		return "";
	}



	/**
	 * @param \Nette\Latte\MacroTokenizer $tokenizer
	 * @param \Nette\Latte\PhpWriter $writer
	 * @return array
	 */
	private static function readArguments(Latte\MacroTokenizer $tokenizer, Latte\PhpWriter $writer)
	{
		$args = array();
		$tokenizer = $writer->preprocess($tokenizer);

		$key = $value = NULL;
		while ($token = $tokenizer->fetchToken()) {
			if ($tokenizer->isCurrent($tokenizer::T_STRING) || $tokenizer->isCurrent($tokenizer::T_SYMBOL)) {
				$value = trim($token['value'], '\'"');

				if ($tokenizer->fetchUntil($tokenizer::T_CHAR)) {
					$key = $value;
					continue;
				}

				if ($key === NULL) {
					$args[] = $value;
					$value = NULL;

				} else {
					if ($key === 'filter') {
						$key = 'filters';
					}

					if (isset($args[$key])) {
						throw new Nette\Latte\ParseException("Ambiguous definition of '$key'.");
					}

					$args[$key] = $value;
					$key = $value = NULL;
				}
			}
		}

		return $args;
	}



	/**
	 * @param array $args
	 * @return array
	 */
	private static function partitionArguments(array $args)
	{
		$assets = $options = array();
		foreach ($args as $key => $arg) {
			if (is_int($key)) {
				$assets[] = $arg;
			} else {
				$options[$key] = $arg;
			}
		}

		$filters = isset($options['filters']) ? explode(',', $options['filters']) : array();
		unset($options['filters']);

		return array($assets, $filters, $options);
	}



	/**
	 * @param array $args
	 * @param string $type
	 *
	 * @return string
	 */
	private function createFactory(array $args, $type)
	{
		if ($this->manager === NULL) {
			throw new Kdyby\InvalidStateException('Please provide instance of Kdyby\Assets\FormulaeManager using ' . get_called_class() . '::setManager().');
		}
		if ($this->factory === NULL) {
			throw new Kdyby\InvalidStateException('Please provide instance of Kdyby\Assets\AssetFactory using ' . get_called_class() . '::setFactory().');
		}

		// divide arguments
		list($inputs, $filters, $options) = $this->partitionArguments($args);

		// array for AssetCollection
		$assets = "array(\n";
		foreach ($this->factory->createAsset($inputs, array(), $options) as $leaf) {
			$assets .= "\t" . Code\Helpers::formatArgs('unserialize(?)', serialize($leaf)) . ",\n";
		}
		$assets = substr($assets, 0, -2) . "\n)";

		// registration code
		return Code\Helpers::formatArgs('$template->_fm->register(new \Assetic\Asset\AssetCollection(' . $assets . '), ?, ?, ?)', array(
			$type, $filters, $options
		));
	}

}
