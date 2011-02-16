<?php

namespace Kdyby\Application;

use Nette;
use NetteDI;



/**
 * @author Filip Procházka
 */
abstract class Presenter extends Nette\Application\Presenter implements Kdyby\Injection\IService
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