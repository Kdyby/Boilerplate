<?php

namespace Kdyby\Application\Presentation;

use Doctrine;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr\Join;
use Kdyby;
use Nette;



class SitemapRepository extends Kdyby\Doctrine\Repositories\NestedTreeRepository
{

	const SITEMAP_ENTITY_NAME = 'Kdyby\Application\Presentation\Sitemap';



	/**
	 * @param Doctrine\ORM\EntityManager $em
	 * @param ClassMetadata $class
	 */
	public function __construct($em, ClassMetadata $class)
	{
		if ($class->rootEntityName !== self::SITEMAP_ENTITY_NAME && $class->getReflectionClass()->isSubclassOf(self::SITEMAP_ENTITY_NAME)) {
			throw new \InvalidArgumentException("Entity " . $class->rootEntityName . " must be subclass of " . self::SITEMAP_ENTITY_NAME);
		}

		parent::__construct($em, $class);
	}



	/**
	 * @param array $sequences
	 * @param Bundle $bundle
	 * @return Sitemap
	 */
	public function findBySequenceAndBundle(array $sequences, Bundle $bundle)
	{
		// initialize sitemap
		$rootSequence = array_shift($sequences);
		if ($bundle->sitemap->sequence !== $rootSequence) {
			return NULL;
		}

		$secondSequence = array_shift($sequences);
		$qb = $this->createQueryBuilder('l')
			->where('l.parent = :id')
				->andWhere('l.sequence = :sequence')
			->setParameter('id', $bundle->sitemap->id)
			->setParameter('sequence', $secondSequence)
			->setMaxResults(1);

		$alias = 'l';
		foreach ($sequences as $i => $sequence) {
			$aliasName = 'l' . $i;
			$paramName = 'sequence' . $i;

			$qb->leftJoin($alias . '.children', $aliasName, Join::WITH, $aliasName . '.sequence = :' . $paramName)
				->addSelect($alias = $aliasName)
				->setParameter($paramName, $sequence);
		}

		$secondSitemap = $qb->getQuery()->getSingleResult();
		if (!$secondSitemap) {
			return NULL;
		}

		// returns already managed entity
		return $bundle->sitemap;
	}

}