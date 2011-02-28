<?php

namespace Kdyby\Application\PresenterLoaders;

use Nette;



interface IPresenterLoader
{

	/**
	 * Formats presenter class name from its name.
	 * @param  string
	 * @return string
	 */
	function formatPresenterClass($presenter);


	/**
	 * Formats presenter name from class name.
	 * @param  string
	 * @return string
	 */
	function unformatPresenterClass($class);


	/**
	 * Formats presenter class file name.
	 * @param string $presenter
	 * @param string $baseDir
	 * @return string
	 */
	function formatPresenterFile($presenter, $baseDir = NULL);

}