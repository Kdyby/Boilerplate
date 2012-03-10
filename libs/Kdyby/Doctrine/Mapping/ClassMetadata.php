<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\Mapping;

use Doctrine;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Kdyby;
use Kdyby\Doctrine\Mapping\EntityMetadataMapper;
use Nette;
use Nette\Reflection\ClassType;
use Nette\Reflection\Property;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class ClassMetadata extends Doctrine\ORM\Mapping\ClassMetadata
{

	/** @var string */
	public $customRepositoryClassName = 'Kdyby\Doctrine\Dao';



	/**
     * Initializes a new ClassMetadata instance that will hold the object-relational mapping
     * metadata of the class with the given name.
     *
     * @param string $entityName The name of the entity class the new instance is used for.
     */
    public function __construct($entityName)
    {
        $this->reflClass = new ClassType($entityName);
        $this->namespace = $this->reflClass->getNamespaceName();
        $this->table['name'] = $this->reflClass->getShortName();
        ClassMetadataInfo::__construct($this->reflClass->getName()); // do not use $entityName, possible case-problems
    }



    /**
     * Gets the ReflectionClass instance of the mapped class.
     *
     * @return ClassType
     */
    public function getReflectionClass()
    {
        if (!$this->reflClass) {
            $this->reflClass = new ClassType($this->name);
        }

        return $this->reflClass;
    }



    /**
     * Restores some state that can not be serialized/unserialized.
     *
     * @return void
     */
    public function __wakeup()
    {
        // Restore ReflectionClass and properties
        $this->reflClass = new ClassType($this->name);

        foreach ($this->fieldMappings as $field => $mapping) {
            if (isset($mapping['declared'])) {
                $reflField = new Property($mapping['declared'], $field);
            } else {
                $reflField = $this->reflClass->getProperty($field);
            }
            $reflField->setAccessible(true);
            $this->reflFields[$field] = $reflField;
        }

        foreach ($this->associationMappings as $field => $mapping) {
            if (isset($mapping['declared'])) {
                $reflField = new Property($mapping['declared'], $field);
            } else {
                $reflField = $this->reflClass->getProperty($field);
            }

            $reflField->setAccessible(true);
            $this->reflFields[$field] = $reflField;
        }
    }

}
