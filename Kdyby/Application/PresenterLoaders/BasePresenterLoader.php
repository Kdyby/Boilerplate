<?php

namespace Kdyby\Application\PresenterLoaders;

use Nette;
use Kdyby;



abstract class BasePresenterLoader extends Nette\Object
{

	/** @var array */
	private $errors = array();



	/**
	 * @param InvalidPresenterException $exception
	 */
	function addError(\Exception $exception)
	{
		$this->errors[] = $exception;
	}



	/**
	 * return array of InvalidPresenterException
	 */
	function getErrors()
	{
		return $this->errors;
	}



	/**
	 * Formats presenter class name from its name.
	 * @param  string
	 * @return string
	 */
	public function formatPresenterClass($presenter)
	{
		/*5.2*return strtr($presenter, ':', '_') . 'Presenter';*/
		return str_replace(':', 'Module\\', $presenter) . 'Presenter';
	}



	/**
	 * Formats presenter name from class name.
	 * @param  string
	 * @return string
	 */
	public function unformatPresenterClass($class)
	{
		/*5.2*return strtr(substr($class, 0, -9), '_', ':');*/
		return str_replace('Module\\', ':', substr($class, 0, -9));
	}



	/**
	 * Formats presenter class file name.
	 * @param string $presenter
	 * @param string $baseDir
	 * @return string
	 */
	public function formatPresenterFile($presenter, $baseDir = NULL)
	{
		$path = '/' . str_replace(':', 'Module/', $presenter);
		return $baseDir . substr_replace($path, '/presenters', strrpos($path, '/'), 0) . 'Presenter.php';
	}

}