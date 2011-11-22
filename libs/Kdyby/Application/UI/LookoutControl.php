<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Application\UI;

use Kdyby;
use Nette;
use Nette\Utils\Strings;



/**
 * Class uses methods view<view>([param1 [, param2]]) as alias
 * for Latte's {control component:<view>, [param1 [, param2]]}
 *
 * Also tryies to find latte file, named as the <view> and use it as template,
 * if nothing is in method output
 *
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
abstract class LookoutControl extends Control
{

	/** @var string */
	private $view;

	/** @var array */
	private $renderParams = array();

	/** @var array */
	private static $methods = array();



	/**
	 * @return array
	 */
	public function getRenderParams()
	{
		return $this->renderParams;
	}



	/**
	 * @return string
	 */
	public function getView()
	{
		return $this->view;
	}



	/**
	 * Get's called before rendering
	 */
	protected function beforeRender() { }



	/**
	 * @param string $type
	 * @param mixed $param
	 * @return string
	 */
	final public function render($type = NULL, $param = NULL)
	{
		$class = get_class($this);
		if (!isset(self::$methods[$class])) {
			self::$methods[$class] = get_class_methods($this);
		}

		$this->view = $this->view ?: 'default';
		$this->renderParams = $this->renderParams ?: func_get_args();

		$viewMethod = 'view' . ucfirst($this->view);

		// allways call
		call_user_func_array(array($this, 'beforeRender'), $this->renderParams);

		$dir = dirname($this->reflection->fileName);
		$view = lcfirst($this->view);
		$templates = array(
				$dir . '/' . $view . '.latte',
				$dir . '/' . $view . '.phtml'
			);
		foreach ($templates as $file){
			if (file_exists($file)) {
				$this->template->setFile($file);
				break;
			}
		}

		ob_start();
		call_user_func_array(array($this, $viewMethod), $this->renderParams);
		$output = ob_get_clean();

		if (!$output && file_exists($file)) { // raw output from function
			$output = (string)$this->template;
		}

		echo $output;

		// allways call
		call_user_func_array(array($this, 'afterRender'), $this->renderParams);

		$this->view = NULL;
		$this->renderParams = array();
	}



	/**
	 * Get's called before rendering
	 */
	protected function afterRender() { }



	/**
	 * Calls self::render($view, $args) instead of nonexisting render<view>($args) methods
	 *
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 */
	public function __call($method, $args)
	{
		if (Strings::startsWith($method, 'render')) {
			$this->view = substr($method, 6);
			$this->renderParams = $args;

			return call_user_func(array($this, 'render'));
		}

		return parent::__call($method, $args);
	}

}