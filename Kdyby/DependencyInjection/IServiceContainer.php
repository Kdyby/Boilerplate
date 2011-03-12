<?php
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nela-project.org
 */

namespace Kdyby\DependencyInjection;

use Nette;



/**
 * Dependency injection service container interface
 * 
 * @author	Patrik Votoček
 */
interface IServiceContainer extends Nette\IContext
{

	/**
	 * @param string
	 * @param mixed
	 * @return ServiceContainer
	 * @throws \InvalidStateException
	 */
	public function setParameter($key, $value);
	
	/**
	 * @param string
	 * @return mixed
	 */
	public function hasParameter($key);
	
	/**
	 * @param string
	 * @return mixed
	 */
	public function getParameter($key);

}
