<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Doctrine\Mapping;

use Doctrine;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @method \Kdyby\Doctrine\Mapping\ClassMetadata getMetadataFor($className)
 * @method \Kdyby\Doctrine\Mapping\ClassMetadata[] getAllMetadata()
 */
class ClassMetadataFactory extends Doctrine\ORM\Mapping\ClassMetadataFactory
{

	/**
	 * Enforce Nette\Reflection
	 */
	public function __construct()
	{
		$this->setReflectionService(new RuntimeReflectionService);
	}



	/**
	 * @param string|object $entity
	 * @return bool
	 */
	public function isAudited($entity)
	{
		$class = $this->getMetadataFor(is_object($entity) ? get_class($entity) : $entity);
		return $class->isAudited();
	}



	/**
	 * @return \Kdyby\Doctrine\Mapping\ClassMetadata[]
	 */
	public function getAllAudited()
	{
		return array_filter($this->getAllMetadata(), function (ClassMetadata $class) {
			return $class->isAudited();
		});
	}



	/**
     * Creates a new ClassMetadata instance for the given class name.
     *
     * @param string $className
     * @return ClassMetadata
     */
    protected function newClassMetadataInstance($className)
    {
        return new ClassMetadata($className);
    }

}
