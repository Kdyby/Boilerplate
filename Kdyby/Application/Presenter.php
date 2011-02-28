<?php

namespace Kdyby;

use Nette;
use Kdyby;



class Presenter extends Nette\Application\Presenter
{

	/**
	 * @param string $name
	 * @param array|NULL $options
	 * @return object|\Closure
	 */
	public function getService($name, array $options = array())
	{
		return $this->getApplication()->getContainer()->getService($name, $options);
	}

}