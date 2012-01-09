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
use Kdyby\Assets\AssetFactory;
use Kdyby\Templates\LatteHelpers;
use Nette;
use Nette\Utils\PhpGenerator as Code;
use Nette\Latte;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AsseticMacroSet extends Latte\Macros\MacroSet
{

	/** @var \Kdyby\Assets\AssetFactory */
	private $factory;

	/** @var array */
	private $prolog = array();



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
		$prolog = $this->prolog;
		$this->prolog = array();
		return array(implode("\n", $prolog));
	}



	/**
	 * @param \Nette\Latte\MacroNode $node
	 * @param \Nette\Latte\PhpWriter $writer
	 *
	 * @return bool
	 */
	public function javascriptMacro(Latte\MacroNode $node, Latte\PhpWriter $writer)
	{
		$args = LatteHelpers::readArguments($node->tokenizer, $writer);
		if (isset($args['filter'])) {
			$args['filters'] = $args['filter'];
			unset($args['filter']);
		}

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
		$args = LatteHelpers::readArguments($node->tokenizer, $writer);
		if (isset($args['filter'])) {
			$args['filters'] = $args['filter'];
			unset($args['filter']);
		}

		$this->prolog[] = $this->createFactory($args, FormulaeManager::TYPE_STYLESHEET);

		return "";
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
		if ($this->factory === NULL) {
			throw new Kdyby\InvalidStateException('Please provide instance of Kdyby\Assets\AssetFactory using ' . get_called_class() . '::setFactory().');
		}

		// divide arguments
		list($inputs, $filters, $options) = $this->partitionArguments($args);
		if (empty($inputs)) {
			throw new Nette\Latte\ParseException("No input file was provided.");
		}

		// array for AssetCollection
		$assets = "array(\n";
		foreach ($asset = $this->factory->createAsset($inputs, array(), $options) as $leaf) {
			$assets .= "\t" . Code\Helpers::formatArgs('unserialize(?)', array(serialize($leaf))) . ",\n";
		}
		$assets = (isset($leaf) ? substr($assets, 0, -2) : $assets) . "\n)";

		if ($asset instanceof Assetic\Asset\AssetInterface) {
			if (!isset($options['output'])) {
				$options['output'] = $asset->getTargetPath();
			}

		} else {
			throw new Kdyby\InvalidStateException('Assetic wasn\'t able to create asset from your input "' . implode('", "', $inputs) . '".');
		}

		// registration code
		return Code\Helpers::formatArgs('$template->_fm->register(new Assetic\Asset\AssetCollection(' . $assets . '), ?, ?, ?);', array(
			$type, $filters, $options
		));
	}

}
