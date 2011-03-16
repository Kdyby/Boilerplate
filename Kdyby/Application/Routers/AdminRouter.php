<?php

namespace Kdyby\Application\Routers;

use Nette;
use Doctrine;



class AdminRouter extends Nette\Application\Route
{

	/**
	 * @param string $mask
	 * @param int $flags
	 */
	public function __construct($mask = NULL, $flags = 0)
	{
		parent::__construct(
				$mask . "/[!<presenter>/][<action>/]",
				$metadata = array(
					'module' => 'Admin',
					'presenter' => 'Dashboard',
					'action' => 'default'
				),
				$flags //| Nette\Application\IRouter::SECURED
			);
	}


	

}