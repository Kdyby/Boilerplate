<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\DI;

use Doctrine;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class SettingsRepository extends Kdyby\Doctrine\ORM\EntityRepository
{

	/**
	 * @param string $name
	 * @param string $section
	 * @return Setting|NULL
	 */
	public function findOneByNameAndSection($name, $section = NULL)
	{
		$qb = $this->createQueryBuilder('s')
			->where('s.name = :name')
			->setParameter('name', $name);

		if ($section === NULL) {
			$qb->andWhere('s.section IS NULL');

		} else {
			$qb->andWhere('s.section = :section')
			->setParameter('section', $section);
		}

		try {
			return $qb->setMaxResults(1)->getQuery()->getSingleResult();

		} catch(Doctrine\ORM\NoResultException $e) {
			return NULL;
		}
	}

}