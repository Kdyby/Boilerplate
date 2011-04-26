<?php

namespace Kdyby\Application\Routers;

use Doctrine;
use Doctrine\ORM\EntityManager;
use Kdyby;
use Kdyby\Application\Presentation\Sitemap;
use Kdyby\Application\Presentation\SitemapRepository;
use Kdyby\Application\Presentation\BundleMaskRepository;
use Nette;
use Nette\Application\UI\Presenter;
use Nette\Application\Routers\Route;
use Nette\Application\Request;



class SequentialRouter extends Nette\Object implements Nette\Application\IRouter
{

	const PRESENTER_KEY = Route::PRESENTER_KEY;
	const MODULE_KEY = Route::MODULE_KEY;
	const SEQUENCE_KEY = 'sequence';
	const BUNDLE_KEY = 'bundle';
	const MASK_KEY = 'mask';
	const TLD_KEY = 'tld';
	const SITEMAP_KEY = 'sitemap';

	/** @var SitemapRepository */
	private $sitemaps;

	/** @var BundleMaskRepository */
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
	}



	/**
	 * Maps HTTP request to a PresenterRequest object.
	 * 
	 * @param  Nette\Http\IRequest
	 * @return Request|NULL
	 */
	public function match(Nette\Http\IRequest $httpRequest)
	{
		$uri = $httpRequest->getUri();
		$path = $uri->getHost() . $uri->getPath();

		$match = self::matchPath($path);
		if ($match === FALSE || $match['garbage']) {
			return NULL;
		}

		// query params
		$params = $httpRequest->getQuery();

		if (isset($match[self::SEQUENCE_KEY])) {
			$sequences = rtrim($match[self::SEQUENCE_KEY], '/');
			$sequences = $params[self::SEQUENCE_KEY] = array_map(callback('rawurldecode'), explode('/', $sequences));
			$rootSequence = array_shift($params[self::SEQUENCE_KEY]);

		} else {
			$sequences = $params[self::SEQUENCE_KEY] = array();
		}

		$sequences = array_filter(array_map(callback('trim'), $sequences));

		try {
			// mask
			$mask = $this->masks->findOneByMask($match[self::MASK_KEY]);
			$bundle = $mask->getBundle();

		} catch (Doctrine\ORM\NoResultException $e) {
			return NULL;
		}

		try {
			// internaly autoloads whole mainmenu and path. Returns last in path
			$sitemap = $this->sitemaps->findBySequencesAndBundle($sequences, $bundle);

		} catch (Doctrine\ORM\NoResultException $e) {
			$sitemap = $bundle->getSitemap();
		}

		// search from deepest
		$presenterRequest = $deepest = NULL;
		for($deepest = $sitemap; $deepest ;$deepest = $deepest->getParent()) {
			$presenterRequest = $this->matchSitemap($httpRequest, $deepest, $params) ?: NULL;

			if ($presenterRequest) {
				$presenterRequest->params += array(self::SITEMAP_KEY => $deepest->id);
				$presenterRequest->params += array(self::BUNDLE_KEY => $bundle->id);
				break;
			}
		}

		return $presenterRequest;
	}



	/**
	 * @param Nette\Http\IRequest $httpRequest
	 * @param Sitemap $sitemap
	 * @param array $params
	 * @return Request
	 */
	private function matchSitemap(Nette\Http\IRequest $httpRequest, Sitemap $sitemap, array $params)
	{
		foreach ($sitemap->getSequencePathUp() as $sequence) {
			if (reset($params[self::SEQUENCE_KEY]) === $sequence) {
				array_shift($params[self::SEQUENCE_KEY]);
			}
		}

		// translate pieces of sequence into values
		foreach ($sitemap->mapSequence as $key) {
			if (!$params[self::SEQUENCE_KEY]) {
				break;
			}

			if (isset($params[$key])) {
				throw new Nette\MemberAccessException("Cannot overwrite already declared key '$key'");
			}

			$params[$key] = array_shift($params[self::SEQUENCE_KEY]);
		}

		// too many sequences?
		if ($params[self::SEQUENCE_KEY]) {
			return NULL;
		}

		$params = $params + $sitemap->defaultParams;
		unset($params[self::SEQUENCE_KEY]);

		// last resort
		foreach ($sitemap->requiredParams as $param) {
			if (!isset($params[$param])) {
				return NULL;
			}
		}

		foreach ($params as $key => $value) {
			if (is_numeric($key)) {
				unset($params[$key]);
			}
		}

		// destination & action
		$link = explode(':', $sitemap->destination);
		$action = array_pop($link) ?: 'default';
		$destination = implode(':', $link);
		$params = array('action' => $action) + $params;

		// be winner like Charlie Sheen!
		return new Request(
			$destination,
			$httpRequest->getMethod(),
			$params,
			$httpRequest->getPost(),
			$httpRequest->getFiles(),
			array(Request::SECURED => $httpRequest->isSecured())
		);
	}



	/**
	 * @param string $path
	 * @return array
	 */
	public static function matchPath($path)
	{
		return Nette\Utils\Strings::match($path, '~^(?P<' . self::MASK_KEY . '>[^/]+\.(?P<' . self::TLD_KEY . '>[a-z]+))/(?P<' . self::SEQUENCE_KEY . '>(.*?/)+)?(?P<garbage>.*)?$~i');
	}



	/**
	 * Constructs absolute URL from PresenterRequest object.
	 * 
	 * @param  Request
	 * @param  Nette\Http\Url referential URI
	 * @return string|NULL
	 */
	public function constructUrl(Request $appRequest, Nette\Http\Url $refUri)
	{
		$params = $appRequest->getParams();
		$uri = NULL;

		if (isset($params[Presenter::ACTION_KEY]) && $params[Presenter::ACTION_KEY] === 'default') {
			unset($params[Presenter::ACTION_KEY]);
		}

		// mask
		if (isset($params[self::MASK_KEY])) {
//			todo: secured?
//			$uri = ($this->flags & self::SECURED ? 'https:' : 'http:') . $uri;
			$uri .= 'http://';

			$uri .=  $params[self::MASK_KEY];
		}

		$uri .= '/';

		// sequences
		if (isset($params[self::SEQUENCE_KEY])) {
			$uri .= implode('/', array_map(callback('rawurlencode'), $params[self::SEQUENCE_KEY])) . '/';
		}

		// cleanup
		unset($params[self::SEQUENCE_KEY], $params[self::MASK_KEY]);

		$sep = ini_get('arg_separator.input');
		$query = http_build_query($params, '', $sep ? $sep[0] : '&');
		$uri = $uri . ($query ? '?' . $query : NULL);

		return $uri;
	}

}