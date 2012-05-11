<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Doctrine\Audit;

use Nette;



/**
 * @author Benjamin Eberlei <eberlei@simplethings.de>
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class ChangedEntity extends Nette\Object
{
	/**
	 * @var string
	 */
    private $className;

	/**
	 * @var array
	 */
    private $id;

	/**
	 * @var string
	 */
    private $revType;

	/**
	 * @var object
	 */
    private $entity;



	/**
	 * @param string $className
	 * @param array $id
	 * @param string $revType
	 * @param object $entity
	 */
    public function __construct($className, array $id, $revType, $entity)
    {
        $this->className = $className;
        $this->id = $id;
        $this->revType = $revType;
        $this->entity = $entity;
    }



    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }



    /**
     *
     * @return array
     */
    public function getId()
    {
        return $this->id;
    }



    /**
     * @return string
     */
    public function getRevisionType()
    {
        return $this->revType;
    }



    /**
     * @return object
     */
    public function getEntity()
    {
        return $this->entity;
    }

}
