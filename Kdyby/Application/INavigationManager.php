<?php

namespace Kdyby\Application;

use Kdyby;
use Kdyby\Application\Presentation\Bundle;
use Nette;



interface INavigationManager
{

	/**
	 * @param Bundle $bundle
	 * @param string $destination
	 * @param array $args
	 * @return Nette\Application\PresenterRequest
	 */
	function createRequest(Bundle $bundle, $destination, $args);

	/**
	 * @param Presenter $presenter
	 * @param int $maxLevel
	 */
	function createBundleNavigation(Presenter $presenter, $maxLevel = 0);

}