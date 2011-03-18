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
//				$mask . "/[<module>/][!<presenter>/][<action>/]",
				$mask . "/[!<presenter>/][<action>/]",
				$metadata = array(
//					'module' => array(
//						Route::FILTER_IN => callback($this, 'filterRequestsToAdminModule'),
//						Route::FILTER_OUT => function ($module) { return $module; }, // prevent sanitization by urlencode
//					),
					'module' => 'Admin',
					'presenter' => 'Dashboard',
					'action' => 'default'
				),
				$flags //| Nette\Application\IRouter::SECURED
			);
	}



	/**
	 * Checks if request directs to Admin Module
	 * to prevent module in own key variable
	 *
	 * @param string $module
	 * @return string|NULL
	 */
	public function filterRequestsToAdminModule($module)
	{
		if (!Nette\String::match($module, '~^\:?admin(\:.*)?$~i')) {
			return NULL;
		}

		return $module;
	}
	

}