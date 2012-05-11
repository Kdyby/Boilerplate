<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Doctrine\Audit;

use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Nette;
use Kdyby\Doctrine\Audit\EventListener\CreateSchemaListener;
use Kdyby\Doctrine\Audit\EventListener\LogRevisionsListener;
use Kdyby\Doctrine\Mapping\ClassMetadataFactory;



/**
 * Audit Manager grants access to metadata and configuration
 * and has a factory method for audit queries.
 *
 * @author Benjamin Eberlei <eberlei@simplethings.de>
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AuditManager extends Nette\Object
{
	/**
	 * @var \Kdyby\Doctrine\Audit\AuditConfiguration
	 */
    private $config;

	/**
	 * @var \Kdyby\Doctrine\Mapping\ClassMetadataFactory
	 */
    private $metadataFactory;



	/**
	 * @param \Kdyby\Doctrine\Audit\AuditConfiguration $config
	 * @param \Kdyby\Doctrine\Mapping\ClassMetadataFactory $metadataFactory
	 */
    public function __construct(AuditConfiguration $config, ClassMetadataFactory $metadataFactory)
    {
        $this->config = $config;
        $this->metadataFactory = $metadataFactory;
    }



	/**
	 * @return \Kdyby\Doctrine\Mapping\ClassMetadataFactory
	 */
    public function getMetadataFactory()
    {
        return $this->metadataFactory;
    }



	/**
	 * @return AuditConfiguration
	 */
    public function getConfiguration()
    {
        return $this->config;
    }



	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 * @return AuditReader
	 */
    public function createAuditReader(EntityManager $em)
    {
        return new AuditReader($em, $this->config, $this->metadataFactory);
    }



	/**
	 * @param \Doctrine\Common\EventManager $evm
	 */
    public function registerEvents(EventManager $evm)
    {
        $evm->addEventSubscriber(new CreateSchemaListener($this));
        $evm->addEventSubscriber(new LogRevisionsListener($this));
    }

}
