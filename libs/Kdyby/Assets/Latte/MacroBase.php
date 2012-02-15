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
use Kdyby\Assets\AssetFactory;
use Kdyby\Templates\LatteHelpers;
use Nette;
use Nette\Utils\PhpGenerator as Code;
use Nette\Latte;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
abstract class MacroBase extends Nette\Object implements Latte\IMacro
{

	/** @var \Kdyby\Assets\AssetFactory */
	private $factory;

	/** @var \Nette\Latte\Compiler; */
	private $compiler;

	/** @var string[] */
	private $assets = array();



	/**
	 * @param \Nette\Latte\Compiler $compiler
	 */
	public function __construct(Latte\Compiler $compiler)
	{
		$this->compiler = $compiler;
	}



	/**
	 * @return \Nette\Latte\Compiler
	 */
	public function getCompiler()
	{
		return $this->compiler;
	}



	/**
	 * @param \Kdyby\Assets\AssetFactory $factory
	 */
	public function setFactory(AssetFactory $factory)
	{
		$this->factory = $factory;
	}



	/**
	 * @param string $context
	 * @return bool
	 */
	protected function isContext($context)
	{
		$current = $this->getCompiler()->getContext();
		return $current[0] === $context;
	}



	/**
	 * Initializes before template parsing.
	 * @return void
	 */
	public function initialize()
	{

	}



	/**
	 * Finishes template parsing.
	 * @return array(prolog, epilog)
	 */
	public function finalize()
	{
		$prolog = array_reverse($this->assets);
		$this->assets = array();
		return array(implode("\n", $prolog));
	}



	/**
	 * @param \Nette\Latte\MacroNode $node
	 * @return array
	 */
	protected static function readArguments(Latte\MacroNode $node)
	{
		$args = LatteHelpers::readArguments(
			new Nette\Latte\MacroTokenizer(rtrim($node->args, '/')),
			Latte\PhpWriter::using($node)
		);

		if (isset($args['filter'])) {
			$args['filters'] = $args['filter'];
			unset($args['filter']);
		}

		return $args;
	}



	/**
	 * @param array $args
	 * @return array
	 */
	protected static function partitionArguments(array $args)
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
	protected function createFactory(array $args, $type)
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
			$this->validateAssetLeaf($leaf);
			$assets .= "\t" . Code\Helpers::formatArgs('unserialize(?)', array(serialize($leaf))) . ",\n";
		}
		$assets = (isset($leaf) ? substr($assets, 0, -2) : $assets) . "\n)";

		if ($asset instanceof Assetic\Asset\AssetInterface && !isset($options['output'])) {
			$options['output'] = $asset->getTargetPath();
		}

		// registration code
		$this->assets[] = Code\Helpers::formatArgs('$template->_fm->register(new Assetic\Asset\AssetCollection(' . $assets . '), ?, ?, ?);', array(
			$type, $filters, $options
		));

		return TRUE;
	}



	/**
	 * @param \Assetic\Asset\AssetInterface $leaf
	 *
	 * @return bool
	 */
	private function validateAssetLeaf(Assetic\Asset\AssetInterface $leaf)
	{
		if (!$leaf instanceof Assetic\Asset\FileAsset) {
			return;
		}

		if (!file_exists($file = $leaf->getSourceRoot() . '/' . $leaf->getSourcePath())) {
			throw new Kdyby\FileNotFoundException('Assetic wasn\'t able to process your input, file "' . $file . '" doesn\'t exists.');
		}
	}

}
