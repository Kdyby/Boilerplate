<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Doctrine\Audit;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Common\Collections\ArrayCollection;
use Nette;
use Kdyby\Doctrine\Mapping\ClassMetadataFactory;



/**
 * @author Benjamin Eberlei <eberlei@simplethings.de>
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AuditReader extends Nette\Object
{
	/**
	 * @var \Kdyby\Doctrine\Audit\AuditPersister
	 */
    private $persister;



	/**
	 * @param \Kdyby\Doctrine\Audit\AuditPersister $persister
	 */
    public function __construct(AuditPersister $persister)
    {
        $this->persister = $persister;
    }



    /**
     * Find a class at the specific revision.
     *
     * This method does not require the revision to be exact but it also searches for an earlier revision
     * of this entity and always returns the latest revision below or equal the given revision
     *
     * @param string $className
     * @param mixed $id
     * @param int $revision
     * @return object
     */
    public function find($className, $id, $revision)
    {

    }



    /**
     * Return a list of all revisions.
     *
     * @param int $limit
     * @param int $offset
     * @return Revision[]
     */
    public function findRevisionHistory($limit = 20, $offset = 0)
    {

    }



    /**
     * Return a list of ChangedEntity instances created at the given revision.
     *
     * @param int $revision
     * @return ChangedEntity[]
     */
    public function findEntitiesChangedAtRevision($revision)
    {

    }



    /**
     * Return the revision object for a particular revision.
     *
     * @param  int $rev
     * @return Revision
     */
    public function findRevision($rev)
    {

    }



    /**
     * Find all revisions that were made of entity class with given id.
     *
     * @param string $className
     * @param mixed $id
     * @return Revision[]
     */
    public function findRevisions($className, $id)
    {

    }

}
