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
		if ($bundle->sitemap->sequence !== reset($sequences)) {
			return NULL;
		}

		$rootSequence = array_shift($sequences);
		$secondSequence = array_shift($sequences);

		// first query...
		$section = $bundle->sitemap->getChildren()->filter(function (Sitemap $sitemap) use ($secondSequence) {
			return $sitemap->sequence === $secondSequence;
		})->first();

		if (!$section) {
			return NULL;
		} dump('section', $section);

		$qb = $this->createQueryBuilder('l')
			->where('l.parent = :id')
			->setParameter('id', $section->id)
			->setMaxResults(1);

		$alias = 'l';
		foreach ($sequences as $i => $sequence) {
			$aliasName = 'l' . $i;
			$paramName = 'sequence' . $i;

			$qb->leftJoin($alias . '.children', $aliasName, Join::WITH, $aliasName . '.sequence = :' . $paramName)
				->addSelect($alias = $aliasName)
				->setParameter($paramName, $sequence);
		}

		echo $qb->getQuery()->getDQL(), "<hr>";
		echo $qb->getQuery()->getSQL(), "<hr>";
		dump($qb->getQuery()->getParameters());

		foreach ($leafs = $qb->getQuery()->getResult() as $leaf) {
			dump($leaf);
		} dump('count', count($leafs)); echo "<hr>";


		// returns already managed entity
		return $this->find($id);
	}

}