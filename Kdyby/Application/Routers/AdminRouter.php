<?php

namespace Kdyby\Application\Routers;

use Doctrine;
use Doctrine\ORM\EntityManager;
use Nette;



class AdminRouter extends Nette\Application\Routers\RouteList
{

	/**
	 * @param EntityManager $em
	 * @param string $mask
	 */
	public function __construct(EntityManager $em, $mask, $flags = 0)
	{
		parent::__construct('Admin');

		// sequential router
		$this[] = new SeaquentialRouter($em, $mask, $flags); //$flags|Nette\Application\IRouter::SECURED

		// fallback router
		$this[] = new Nette\Application\Routers\Route(
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
		if (!Nette\Utils\Strings::match($module, '~^\:?admin(\:.*)?$~i')) {
			return NULL;
		}

		return $module;
	}
	

}