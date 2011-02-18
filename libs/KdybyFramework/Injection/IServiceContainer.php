<?php

namespace Kdyby\Injection;

use Nette;



/**
 * @author Filip Procházka
 */
interface IServiceContainer extends Nette\IContext, Nette\IFreezable
{
    function addAlias($service, $alias);
}