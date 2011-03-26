<?php

namespace Kdyby\Application;

use Kdyby;
use Nette;



interface INavigationManager
{

	function createBundleNavigation(Kdyby\Application\Presenter $presenter, $maxLevel = 0);

}