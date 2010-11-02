<?php

namespace Kdyby\Control;

use Nette;
use Nette\String;



class Lookout extends Base
{
	/** @var string */
	private $view;

	/** @var array */
	private $renderParams = array();



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
	 * @param array $params
	 */
	protected function beforeRender($params)
	{

	}


	/**
	 * @param array $params
	 */
	protected function afterRender($params)
	{

	}


	/**
	 * @param string $type
	 * @param mixed $param
	 * @return string
	 */
	final public function render($type = NULL, $param = NULL)
	{
		$this->view = $this->view ?: 'default';
		$this->renderParams = $this->renderParams ?: func_get_args();

		$viewMethod = 'view' . ucfirst($this->view);

		$this->beforeRender($this->renderParams);

		$dir = dirname($this->reflection->fileName);
		$this->template->setFile($dir . '/' . $this->view . '.phtml');

		ob_start();
		call_user_func_array(array($this, $viewMethod), $this->renderParams);
		$output = ob_get_clean();

		if (!$output) {
			$this->template->render();

		} else {
			echo $output;
		}

		$this->afterRender($this->renderParams);

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
			$this->view = String::lower(substr($method, 6));
			$this->renderParams = $args;

			return call_user_func(array($this, 'render'));
		}

		return parent::__call($method, $args);
	}
}