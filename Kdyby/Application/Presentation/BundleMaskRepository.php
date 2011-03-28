<?php

namespace Kdyby\Application\Presentation;

use Doctrine\ORM\EntityRepository;
use Kdyby;
use Nette;



class BundleMaskRepository extends EntityRepository
{

	/**
	 * @param string $mask
	 * @return BundleMask
	 */
	public function findOneByMask($mask)
	{
		$qb = $this->createQueryBuilder('m')
			->select('m', 'b', 's')
			->innerJoin('m.bundle', 'b')
			->innerJoin('b.sitemap', 's')
			->orderBy('m.clarity', 'DESC')
			->setMaxResults(1);

		// exact match
		$qb->where('m.mask = :fullmask')
			->setParameter('fullmask', $mask);

		$parts = explode('.', $mask);
		while ($i = count($parts)) {
			$paramName = 'mask' . $i;
			$qb->orWhere('m.mask = :' . $paramName)
				->setParameter($paramName, implode('.', $parts) . '.*');

			array_pop($parts);
		}

		return $qb->getQuery()->getSingleResult();
	}
	
}