<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Assets\Latte;

use Assetic;
use Kdyby;
use Kdyby\Extension\Assets;
use Kdyby\Templates\LatteHelpers;
use Nette;
use Nette\Utils\PhpGenerator as Code;
use Nette\Latte;
use Nette\Utils\Strings;



/**
 * @todo: merge in JS & CSS macros
 *
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AssetMacros extends Nette\Object implements Latte\IMacro
{

	/**
	 * @var \Kdyby\Extension\Assets\AssetFactory
	 */
	private $factory;

	/**
	 * @var \Kdyby\Extension\Assets\IAssetRepository
	 */
	private $repository;

	/**
	 * @var \Nette\Latte\Compiler;
	 */
	private $compiler;

	/**
	 * @var \Kdyby\Extension\Assets\Repository\AssetFile
	 */
	private $assets = array();



	/**
	 * @param \Nette\Latte\Compiler $compiler
	 */
	public function __construct(Latte\Compiler $compiler)
	{
		$this->compiler = $compiler;
	}



	/**
	 * @param \Nette\Latte\Compiler $compiler
	 *
	 * @return \Kdyby\Extension\Assets\Latte\AssetMacros
	 */
	public static function install(Latte\Compiler $compiler)
	{
		$me = new static($compiler);

		$compiler->addMacro('assets', $me);

		$compiler->addMacro('javascript', $me);
		$compiler->addMacro('js', $me);

		$compiler->addMacro('stylesheet', $me);
		$compiler->addMacro('css', $me);

		return $me;
	}



	/**
	 * New node is found. Returns FALSE to reject.
	 *
	 * @param \Nette\Latte\MacroNode $node
	 *
	 * @return bool
	 */
	public function nodeOpened(Latte\MacroNode $node)
	{
		if ($node->name === 'js' || $node->name === 'javascript') {
			return $this->macroOpen($node, Assets\FormulaeManager::TYPE_JAVASCRIPT);

		} elseif ($node->name === 'css' || $node->name === 'stylesheet') {
			return $this->macroOpen($node, Assets\FormulaeManager::TYPE_STYLESHEET);

		} elseif ($node->name === 'assets') {
			if ($node->data->inline = empty($node->args)) {
				throw new Nette\Latte\CompileException("Macro {assets} cannot be used inline.");

			} elseif ($node->htmlNode) {
				throw new Nette\Latte\CompileException("Macro {assets} cannot be used in HTML tag.");
			}

			try {
				if ($this->createFactory($this->readArguments($node), NULL)) {
					$node->isEmpty = TRUE;
				}

			} catch (Kdyby\FileNotFoundException $e) {
				throw new Nette\Latte\CompileException($e->getMessage());
			}

		} else {
			return FALSE;
		}
	}



	/**
	 * Node is closed.
	 * @return void
	 */
	public function nodeClosed(Latte\MacroNode $node)
	{
		if ($node->name === 'js' || $node->name === 'javascript') {
			$this->macroClosed($node, Assets\FormulaeManager::TYPE_JAVASCRIPT);

		} elseif ($node->name === 'css' || $node->name === 'stylesheet') {
			$this->macroClosed($node, Assets\FormulaeManager::TYPE_STYLESHEET);
		}
	}




	/**
	 * @return \Nette\Latte\Compiler
	 */
	public function getCompiler()
	{
		return $this->compiler;
	}



	/**
	 * @param \Kdyby\Extension\Assets\AssetFactory $factory
	 *
	 * @return \Kdyby\Extension\Assets\Latte\AssetMacros
	 */
	public function setFactory(Assets\AssetFactory $factory)
	{
		$this->factory = $factory;
		return $this;
	}



	/**
	 * @param \Kdyby\Extension\Assets\IAssetRepository $repository
	 *
	 * @return \Kdyby\Extension\Assets\Latte\AssetMacros
	 */
	public function setRepository(Assets\IAssetRepository $repository)
	{
		$this->repository = $repository;
		return $this;
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
	 * @param \Nette\Latte\MacroNode $node
	 * @param string $type
	 *
	 * @return string
	 */
	protected function macroOpen(Latte\MacroNode $node, $type)
	{
		try {
			if (empty($node->args) && $node->htmlNode) { // inline handles head macro
				return FALSE;

			} elseif ($node->htmlNode) {
				$args = $node->htmlNode->attrs + Strings::split($node->args, '~\s*,\s*~');
				if ($this->createFactory($args, $type)) {
					$node->data->emptyTag = TRUE;
					return;
				}
			}

			if ($this->createFactory($this->readArguments($node), $type)) {
				$node->isEmpty = TRUE;
			}

		} catch (Kdyby\FileNotFoundException $e) {
			throw new Nette\Latte\CompileException($e->getMessage());
		}
	}



	/**
	 * @param \Nette\Latte\MacroNode $node
	 * @param string $type
	 *
	 * @return string
	 */
	protected function macroClosed(Latte\MacroNode $node, $type = NULL)
	{
		if (isset($node->data->emptyTag)) {
			$node->content = NULL;
			return;
		}

		$args = Nette\Utils\Html::el(substr($node->content, 1, strpos($node->content, '>') - 1))->attrs;
		if (isset($args['filter'])) {
			$args['filters'] = $args['filter'];
			unset($args['filter']);
		}

		try {
			$this->createFactory(array($node->args) + $args, $type);
			$node->content = NULL;

		} catch (\Exception $e) {
			throw new Nette\Latte\CompileException($e->getMessage());
		}
	}



	/**
	 * Finishes template parsing.
	 * @return array(prolog, epilog)
	 */
	public function finalize()
	{
		if (!$this->assets) {
			return array();
		}

		$lookupMethod = get_called_class() . '::findFormulaeManager';
		$fmLookup = 'if (!isset($template->_fm)) $template->_fm = ' . $lookupMethod . '($control);';

		$prolog = $visited = array();
		foreach (array_reverse($this->assets) as $asset) {
			/** @var \Kdyby\Extension\Assets\Repository\AssetFile $asset */

			$inputs = md5(serialize($asset->input));
			if (in_array($inputs, $visited)) {
				continue;
			}

			// registration code
			$code = '$template->_fm->register(' . $asset->serialized . ', ?, ?, ?, $control);';
			$prolog[] = Code\Helpers::formatArgs($code, array(
				$asset->type, $asset->filters, $asset->options
			));

			// do not include twice
			$visited[] = $inputs;
		}

		$this->assets = array();
		return array(
			$fmLookup . "\n" . implode("\n", $prolog) // prolog
		);
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
	protected function createFactory(array $args, $type)
	{
		if ($this->factory === NULL) {
			throw new Kdyby\InvalidStateException('Please provide instance of Kdyby\Extension\Assets\AssetFactory using ' . get_called_class() . '::setFactory().');
		}

		// divide arguments
		list($inputs, $filters, $options) = $this->partitionArguments($args);
		if (empty($inputs)) {
			throw new Nette\Latte\CompileException("No input file was provided.");
		}

		$packages = array();
		foreach ($inputs as $input) {
			if (Strings::match($input, '~^[-a-z0-9]+/[-a-z0-9]+$~i')) {
				$packages[] = $input;
			}
		}

		// regular inputs
		if ($inputs = array_diff($inputs, $packages)) {
			$this->addAsset(new Assets\Repository\AssetFile($inputs, $type, $options, $filters));
		}

		if ($packages && !$this->repository) {
			throw new Kdyby\InvalidStateException('Please provide instance of Kdyby\Extension\Assets\IAssetRepository using ' . get_called_class() . '::setRepository().');
		}

		// packages
		foreach ($packages as $package) {
			$version = isset($options['version']) ? $options['version'] : NULL;
			$asset = $this->repository->getAsset($package, $version);
			foreach ($asset->resolveFiles($this->repository) as $file) {
				$this->addAsset($file);
			}
		}

		return TRUE;
	}



	/**
	 * @param \Kdyby\Extension\Assets\Repository\AssetFile $asset
	 */
	private function addAsset(Assets\Repository\AssetFile $asset)
	{
		if (isset($asset->options['name']) && isset($asset->options['requiredBy'])) {
			$name = $asset->options['name'];
			foreach ($this->assets as $registered) {
				if (!isset($registered->options['name']) || $name !== $registered->options['name']) {
					continue;
				}

				$requiredBy = array_unique(array_merge(
					$asset->options['requiredBy'],
					isset($registered->options['requiredBy'])
						? $registered->options['requiredBy']
						: array()
				));

				$registered->options['requiredBy'] = $requiredBy;
				$asset->options['requiredBy'] = $requiredBy;
			}
		}

		// add
		$asset->serialized = $asset->serialize($this->factory);
		$this->assets[] = $asset;
	}


	/************************ Helpers ************************/


	/**
	 * @param \Nette\Application\UI\PresenterComponent $control
	 *
	 * @throws \Kdyby\InvalidStateException
	 * @return \Kdyby\Extension\Assets\FormulaeManager
	 */
	public static function findFormulaeManager(Nette\Application\UI\PresenterComponent $control)
	{
		/** @var \Nette\Application\UI\Presenter $presenter */
		$presenter = $control->getPresenter();
		$components = $presenter->getComponents(FALSE, 'Kdyby\Components\Header\HeaderControl');
		if (!$headerControl = iterator_to_array($components)) {
			throw new Kdyby\InvalidStateException(
				'Missing link to FormulaeManager from template. ' .
				'Either provide a $_fm property with instanceof Kdyby\Extension\Assets\FormulaeManager, ' .
				'or register Kdyby\Components\Header\HeaderControl in presenter.' .
				'If you have the component registered and this error keeps returning, try to instantiate it.'
			);
		}

		return reset($headerControl)->getFormulaeManager();
	}

}
