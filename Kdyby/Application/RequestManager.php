<?php

namespace Kdyby\Application;

use Doctrine;
use Nette;
use Nette\Application\PresenterRequest;
use Nette\Caching\Cache;
use Kdyby;
use Kdyby\Application\Presentation\Bundle;
use Kdyby\Application\Presentation\Sitemap;



class RequestManager extends Nette\Object
{

	/** @var Doctrine\ORM\EntityManager */
	private $em;

	/** @var Kdyby\Application\Presentation\SitemapRepository */
	private $sitemapRepository;

	/** @var Doctrine\ORM\EntityRepository */
	private $maskRepository;

	/** @var Nette\Caching\Cache */
	private $uriCache;



	public function __construct(Doctrine\ORM\EntityManager $em, Nette\Caching\ICacheStorage $storage)
	{
		$this->em = $em;
		$this->sitemapRepository = $em->getRepository('Kdyby\Application\Presentation\Sitemap');
		$this->maskRepository = $em->getRepository('Kdyby\Application\Presentation\BundleMask');
		$this->uriCache = new Cache($storage, 'Kdyby.Application.Requests');
	}



	/**
	 * smarter application::storeRequest
	 *
	 * @param PresenterRequest $request
	 * @return <type>
	 */
	public function storeRequest(PresenterRequest $request)
	{
		throw new \NotImplementedException;

		return $key;
	}



	/**
	 * smarter application::restoreRequest
	 *
	 * @param <type> $key
	 */
	public function restoreRequest($key)
	{
		throw new \NotImplementedException;
	}



	/**
	 * @param Bundle $bundle
	 * @param object $request
	 * @return Nette\Application\PresenterRequest
	 */
	public function prepareRequest(Bundle $bundle, $request)
	{
		$search = ltrim($request->destination, ':');
		if (substr($search, -7) === 'default') {
			$search = substr($search, 0, -7);
		}

		$targetSitemap = $this->sitemapRepository->findOneByDestinationAndBundle($search, $bundle);
		$sequences = $targetSitemap->getSequencePathUp();
		$params = $request->args;

		foreach ($targetSitemap->mapSequence as $param) {
			if (!isset($params[$param])) {
				throw new \MemberAccessException("Parameter " . $param . " is missing.");
			}

			$sequences[] = $params[$param];
			unset($params[$param]);
		}

		$mask = $this->maskRepository->createQueryBuilder('m')
			->where('m.bundle = :bundle')
			->orderBy('m.clarity', 'DESC')
			->setParameter('bundle', $bundle->id)
			->setMaxResults(1)
			->getQuery()->getSingleResult()->mask;

		$params[Routers\SequentialRouter::MASK_KEY] = $mask;
		$params[Routers\SequentialRouter::SEQUENCE_KEY] = $sequences;

		return (object)array(
			'destination' => ':' . ltrim($request->destination, ':'),
			'args' => $params
		);
	}



	/**
	 * @param Sitemap $sitemap
	 * @param array $request
	 * @param string $uri
	 */
	public function storeRequestUri(Sitemap $sitemap, $request, $uri)
	{
		$uris = $this->uriCache[$sitemap->id];
		$uris[serialize($request)] = $uri;

		$this->uriCache->save($sitemap->id, $uris, array(
			Cache::TAGS => array('links', 'sitemap#' . $sitemap->id)
		));
	}



	/**
	 * @param Sitemap $sitemap
	 * @param array $request
	 */
	public function restoreRequestUri(Sitemap $sitemap, $request)
	{
		$key = serialize($request);

		if (!isset($this->uriCache[$sitemap->id][$key])) {
			throw new \MemberAccessException("Given request for sitemap " . implode('/', $sitemap->getSequencePathUp()) . " was not found.");
		}

		return $this->uriCache[$sitemap->id][$key];
	}

}