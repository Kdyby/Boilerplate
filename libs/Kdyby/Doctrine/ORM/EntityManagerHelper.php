<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\ORM;

use Kdyby;
use Nette;



/**
 * @author Patrik Votoček
 * @author Filip Procházka
 */
class EntityManagerHelper extends Kdyby\DI\ContainerHelper
{

	/**
     * @return Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->container->doctrine->getEntityManager();
    }



    /**
     * @see Helper
     */
    public function getName()
    {
        return 'entityManager';
    }

}
