<?php

namespace Kdyby\Application\Presentation;

use Doctrine;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr\Join;
use Kdyby;
use Kdyby\Application\Presentation\Bundle;
use Kdyby\Application\Presentation\Sitemap;
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
	public function findBySequencesAndBundle(array $sequences, Bundle $bundle)
	{
		if (!$sequences) {
			return $bundle->sitemap;
		}

		// initialize sitemap
		$rootSequence = array_shift($sequences);
		if ($bundle->sitemap->sequence !== $rootSequence) {
			return NULL;
		}

		$tmpSequences = $sequences;
		$secondSequence = array_shift($tmpSequences);
		$qb = $this->createQueryBuilder('l')
			->where('l.parent = :id')
				->andWhere('l.sequence = :sequence')
			->setParameter('id', $bundle->sitemap->id)
			->setParameter('sequence', $secondSequence)
			->setMaxResults(1);

		$alias = 'l';
		foreach ($tmpSequences as $i => $sequence) {
			$aliasName = 'l' . $i;
			$paramName = 'sequence' . $i;

			$qb->leftJoin($alias . '.children', $aliasName, Join::WITH, $aliasName . '.sequence = :' . $paramName)
				->addSelect($alias = $aliasName)
				->setParameter($paramName, $sequence);
		}

		$secondSitemap = $qb->getQuery()->getSingleResult();
//		if (!$secondSitemap) {
//			return NULL;
//		}

		// returns already managed entity
		return $bundle->sitemap->getChildrenBySequences($sequences);
	}



	/**
	 * @param string $destination
	 * @param Bundle $bundle
	 * @return Sitemap
	 */
	public function findOneByDestinationAndBundle($destination, Bundle $bundle)
	{
		$qb = $this->createQueryBuilder('s')
			->where('s.destination = :destination')
				->andWhere('s.nodeRoot = :root')
			->setParameter('destination', $destination)
			->setParameter('root', $bundle->sitemap->id)
			->setMaxResults(1);

		return $qb->getQuery()->getSingleResult();
	}

}