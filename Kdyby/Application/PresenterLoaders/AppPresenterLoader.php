<?php

namespace Kdyby\Application\PresenterLoaders;

use Nette;
use Kdyby;



class AppPresenterLoader extends BasePresenterLoader implements IPresenterLoader
{

	/**
	 * Formats presenter class file name.
	 * @param string $presenter
	 * @param string $baseDir
	 * @return string
	 */
	public function formatPresenterFile($presenter, $baseDir = NULL)
	{
		return parent::formatPresenterFile($presenter, APP_DIR);
	}

}