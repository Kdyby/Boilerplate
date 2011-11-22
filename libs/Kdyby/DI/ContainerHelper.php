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
use Symfony\Component\Console;



/**
 * @author Patrik Votocek
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class ContainerHelper extends Console\Helper\Helper
{

	/** @var Container */
    protected $container;



    /**
     * @param Container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }



	/**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }



	/**
     * @see Helper
     */
    public function getName()
    {
        return 'diContainer';
    }

}