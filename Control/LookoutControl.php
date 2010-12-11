<?php

/**
 * This file is part of the Framework - Content Managing System (F-CMS) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package F-CMS Kdyby-Common
 */


namespace Kdyby\Control;

use Nette;
use Nette\String;



class LookoutControl extends BaseControl
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

		if (in_array('beforeRender', self::$methods[$class])) {
			call_user_func_array(array($this, 'beforeRender'), $this->renderParams);
		}

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

		if (in_array('afterRender', self::$methods[$class])) {
			call_user_func_array(array($this, 'afterRender'), $this->renderParams);
		}

		$this->view = NULL;
		$this->renderParams = array();
	}



	/**
	 * Calls self::render($view, $args) instead of nonexisting render<view>($args) methods
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 */
	public function __call($method, $args)
	{
		if (String::startsWith($method, 'render')) {
			$this->view = substr($method, 6);
			$this->renderParams = $args;

			return call_user_func(array($this, 'render'));
		}

		return parent::__call($method, $args);
	}

}