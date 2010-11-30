<?php

namespace Kdyby\Application;

use Doctrine\ORM\EntityManager;
use PandaWeb\Entities\BaseEntity;


/**
 * @property-read \Doctrine\ORM\EntityManager $entityManager
 * @property-read \Doctrine\ORM\EntityRepository $page
 * @property-read \PandaWeb\Repositories\UserRepository $user
 * ...
 *
 * @method void clear() clear()
 * @method void flush() flush()
 * @method void remove() remove(BaseEntity $entity)
 * @method void refresh() refresh(BaseEntity $entity)
 * @method void beginTransaction() beginTransaction()
 * @method void commit() commit()
 * @method void rollback() rollback()
 *
 * @author Jan Smitka
 */
class DatabaseManager
{
        /** @var EntityManager */
        protected $entityManager;


        public function __construct()
        {
                $this->entityManager = \Nette\Environment::getService('Doctrine\ORM\EntityManager');
        }

        public function __get($name)
        {
                if ($name == 'entityManager') {
                        return $this->entityManager;
                } else {
                        return $this->entityManager->getRepository('PandaWeb\\Entities\\' . ucfirst($name));
                }
        }

        public function __call($name, $arguments)
        {
                return \call_user_func_array(array($this->entityManager, $name), $arguments);
        }


        public function persist($entity)
        {
                $this->entityManager->persist($entity);
        }

        public function lock($entity, $version)
        {
                $this->entityManager->lock($entity, \Doctrine\DBAL\LockMode::OPTIMISTIC, $version);
        }
}