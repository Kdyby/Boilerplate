<?php

namespace Kdyby\Injection;

use Nette;
use NetteDI;



/**
 * @author Filip Procházka
 */
interface IService
{

	function setContainer(Kdyby\Injection\IServiceContainer $container);

}