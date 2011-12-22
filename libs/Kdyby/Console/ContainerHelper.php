<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Console;

use Kdyby;
use Nette;
use Symfony;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class ContainerHelper extends Symfony\Component\Console\Helper\Helper
{

	/** @var \Nette\DI\Container */
    protected $container;



	/**
	 * @param \Nette\DI\Container $container
	 */
    public function __construct(Nette\DI\Container $container)
    {
        $this->container = $container;
    }



	/**
     * @return \Nette\DI\Container
     */
    public function getContainer()
    {
        return $this->container;
    }



	/**
     * @see \Symfony\Component\Console\Helper\Helper::getName()
     */
    public function getName()
    {
        return 'diContainer';
    }

}
