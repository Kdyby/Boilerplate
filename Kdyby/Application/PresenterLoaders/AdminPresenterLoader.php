<?php

namespace Kdyby\Application\PresenterLoaders;

use Nette;
use Kdyby;



class AdminPresenterLoader extends BasePresenterLoader implements IPresenterLoader
{

	/**
	 * Formats presenter class name from its name.
	 * @param  string
	 * @return string
	 */
	public function formatPresenterClass($presenter)
	{
		return 'Kdyby\\' . parent::formatPresenterClass($presenter);
	}



	/**
	 * Formats presenter name from class name.
	 * @param  string
	 * @return string
	 */
	public function unformatPresenterClass($class)
	{
		return parent::unformatPresenterClass(strp_replace('Kdyby\\', '', $presenter));
	}



	/**
	 * Formats presenter class file name.
	 * @param string $presenter
	 * @param string $baseDir
	 * @return string
	 */
	public function formatPresenterFile($presenter, $baseDir = NULL)
	{
		return parent::formatPresenterFile($presenter, KDYBY_DIR);
	}

}