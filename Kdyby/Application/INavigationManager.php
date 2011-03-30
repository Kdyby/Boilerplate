<?php

namespace Kdyby\Application;

use Kdyby;
use Kdyby\Application\Presentation\Bundle;
use Nette;



interface INavigationManager
{

	/**
	 * @param Presenter $presenter
	 * @param int $maxLevel
	 */
	function createBundleNavigation(Presenter $presenter, $maxLevel = 0);

}