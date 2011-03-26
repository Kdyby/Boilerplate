<?php

namespace Kdyby\Application\Routers;

use Doctrine;
use Doctrine\ORM\EntityManager;
use Kdyby;
use Kdyby\Application\Presentation\Sitemap;
use Kdyby\Application\Presentation\SitemapRepository;
use Kdyby\Application\Presentation\BundleMaskRepository;
use Nette;
use Nette\Application\Route;
use Nette\Application\PresenterRequest;



class SequentialRouter extends Nette\Object implements Nette\Application\IRouter
{

	const PRESENTER_KEY = Route::PRESENTER_KEY;
	const MODULE_KEY = Route::MODULE_KEY;
	const SEQUENCE_KEY = 'sequence';
	const MASK_KEY = 'mask';
	const TLD_KEY = 'tld';

	/** @var SitemapRepository */
	private $sitemaps;

	/** @var Kdyby\Application\Presentation\BundleMaskRepository */
	private $masks;



	/**
	 * @param EntityManager $em
	 * @param string $mask
	 * @param int|NULL $flags
	 */
	public function __construct(EntityManager $em)
	{
		$this->sitemaps = $em->getRepository('Kdyby\Application\Presentation\Sitemap');
		$this->masks = $em->getRepository('Kdyby\Application\Presentation\BundleMask');

// test data
// 
//		$mask = $this->masks->findOneByMask('admin.*');
//
//		$m1 = new Sitemap();
//		$m1->setName('Dashboard');
//		$m1->setDestination(':Admin:Dashboard:');
//		$m1->setSequence('dashboard');
//		$mask->getBundle()->setSitemap($m1);
//
//			$m2 = new Sitemap();
//			$m2->setName('Dsdadsa dsadsa');
//			$m2->setDestination(':Admin:Dashboard:');
//			$m2->setSequence('dsdadsa-dsadsa');
//			$m1->addChild($m2);
//
//				$m4 = new Sitemap();
//				$m4->setName('Dsadsa dšádas');
//				$m4->setDestination(':Admin:Dashboard:');
//				$m4->setSequence('dsadsa-dsadas');
//				$m2->addChild($m4);
//
//					$m5 = new Sitemap();
//					$m5->setName('Dsadsa dsagfd');
//					$m5->setDestination(':Admin:Dashboard:');
//					$m5->setSequence('dsadsa-dsagfd');
//					$m4->addChild($m5);
//
//				$m6 = new Sitemap();
//				$m6->setName('Tmatento');
//				$m6->setDestination(':Admin:Dashboard:');
//				$m6->setSequence('tamtento');
//				$m2->addChild($m6);
//
//				$m7 = new Sitemap();
//				$m7->setName('Tro lo lo');
//				$m7->setDestination(':Admin:Dashboard:');
//				$m7->setSequence('trololo');
//				$m2->addChild($m7);
//
//			$m3 = new Sitemap();
//			$m3->setName('Dsdadsa dsadsa');
//			$m3->setDestination(':Admin:Dashboard:');
//			$m3->setSequence('dsdadsa-dsadsa');
//			$m1->addChild($m3);
//
//		$em->persist($m1);
//		$em->persist($m2);
//		$em->persist($m3);
//		$em->persist($m4);
//		$em->persist($m5);
//		$em->persist($m6);
//		$em->persist($m7);
//		$em->flush();
//
//		die('no more!');
	}



	/**
	 * Maps HTTP request to a PresenterRequest object.
	 * 
	 * @param  Nette\Web\IHttpRequest
	 * @return PresenterRequest|NULL
	 */
	public function match(Nette\Web\IHttpRequest $httpRequest)
	{
		$uri = $httpRequest->getUri();
		$path = $uri->getHost() . $uri->getPath();

		$match = Nette\String::match($path, '~^(?P<' . self::MASK_KEY . '>[^/]+\.(?P<' . self::TLD_KEY . '>[a-z]+))/(?P<' . self::SEQUENCE_KEY . '>(.*?/)+)?~i');
		if ($match === FALSE) {
			return NULL;
		}

		$params = array();
		$params[self::MASK_KEY] = $match[self::MASK_KEY];

		$sequences = rtrim($match[self::SEQUENCE_KEY], '/');
		$params[self::SEQUENCE_KEY] = array_map(callback('rawurldecode'), explode('/', $sequences));

		// mask
		$mask = $this->masks->findOneByMask($params[self::MASK_KEY]);

		// internaly autoloads whole mainmenu, path and returns last in path
		$sitemap = $this->sitemaps->findBySequenceAndBundle($params[self::SEQUENCE_KEY], $mask->getBundle());
	
		return new PresenterRequest(
			$sitemap->destination,
			$httpRequest->getMethod(),
			$params + $sitemap->defaultParams,
			$httpRequest->getPost(),
			$httpRequest->getFiles(),
			array(PresenterRequest::SECURED => $httpRequest->isSecured())
		);
	}



	/**
	 * Constructs absolute URL from PresenterRequest object.
	 * 
	 * @param  PresenterRequest
	 * @param  Nette\Web\Uri referential URI
	 * @return string|NULL
	 */
	public function constructUrl(PresenterRequest $appRequest, Nette\Web\Uri $refUri)
	{
		if (!isset($appRequest->params[self::SEQUENCE_KEY])) {
			return NULL;
		}

		$sequences = implode('/', array_map(callback('rawurlencode'), $appRequest->params[self::SEQUENCE_KEY]));
		$appRequest->setParams(array(self::SEQUENCE_KEY => $sequences) + $appRequest->params);

		return parent::constructUrl($appRequest, $refUri);
	}

}