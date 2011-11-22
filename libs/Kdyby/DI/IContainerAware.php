<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\DI;

use Kdyby;
use Nette;



/**
 * ContainerAwareInterface should be implemented by classes that depends on a Container.
 *
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
interface IContainerAware
{

    /**
     * Sets the Container.
     *
     * @param IContainer $container A ContainerInterface instance
     */
    function setContainer(IContainer $container = NULL);

}