<?php
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 */

namespace KdybyTests\DependencyInjection;

use Kdyby;
use Nette;



require_once __DIR__ . "/../../bootstrap.php";

class ServiceFactoryMock extends Kdyby\DependencyInjection\ServiceFactory
{
	public function getServiceContainer()
	{
		return $this->serviceContainer;
	}
	
	public function getClass()
	{
		return $this->class;
	}
	
	public function getFactory()
	{
		return $this->factory;
	}
	
	public function getArguments()
	{
		return $this->arguments;
	}
	
	public function getMethods()
	{
		return $this->methods;
	}
	
	public function createInstanceMock()
	{
		return $this->createInstance();
	}
}
