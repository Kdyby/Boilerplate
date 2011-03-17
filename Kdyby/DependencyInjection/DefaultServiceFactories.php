<?php

namespace Kdyby\DependencyInjection;

use Kdyby;
use Nette;
use Nette\Environment;



class DefaultServiceFactories extends Nette\Object
{

	public static $defaultServices = array(
		'Nette\\Application\\Application' => array(
			'factory' => array(__CLASS__, 'createApplication'),
			'arguments' => array('%Application%', '@Nette\\Web\\IHttpRequest'),
			'aliases' => array('application'),
		),
		'Nette\\Application\\IRouter' => array(
			'factory' => array(__CLASS__, 'createRouter'),
			'arguments' => array('@Nette\\Web\\IHttpRequest')
		),
		'Nette\\Web\\HttpContext' => array(
			'class' => 'Nette\Web\HttpContext',
			'aliases' => array('httpContext'),
		),
		'Nette\\Web\\IHttpRequest' => array(
			'factory' => array('Nette\Configurator', 'createHttpRequest'),
			'aliases' => array('httpRequest'),
		),
		'Nette\\Web\\IHttpResponse' => array(
			'class' => 'Nette\Web\HttpResponse',
			'aliases' => array('httpResponse'),
		),
		'Nette\\Caching\\ICacheStorage' => array(
			'factory' => array(__CLASS__, 'createCacheStorage'),
			'arguments' => array('@Nette\\Caching\\ICacheJournal'),
		),
		'Nette\\Caching\\ICacheJournal' => array(
			'factory' => array('Nette\Configurator', 'createCacheJournal'),
		),
		'Nette\\Mail\\IMailer' => array(
			'factory' => array('Nette\Configurator', 'createMailer'),
			'aliases' => array('mailer'),
		),
		'Nette\\Web\\Session' => array(
			'factory' => array(__CLASS__, 'createSession'),
			'arguments' => array('@Nette\\Web\\IHttpRequest'),
			'aliases' => array('sessionStorage'),
		),
		'Nette\\Loaders\\RobotLoader' => array(
			'factory' => array('Nette\Configurator', 'createRobotLoader'),
		),
		'Nette\\Caching\\Cache' => array(
			'class' => 'Nette\\Caching\\Cache',
			'arguments' => array('@Nette\\Caching\\ICacheStorage', 'Nette'),
			'aliases' => array('cache'),
		),
		'Nette\\Security\\IAuthenticator' => array(
			'class' => 'Kdyby\\Security\\Authenticator',
			'arguments' => array('@Doctrine\\ORM\\EntityManager', '%Security%'),
			'aliases' => array('authenticator'),
		),
		'Nette\\Security\\IAuthorizator' => array(
			'class' => 'Kdyby\\Security\Authorizator',
			'arguments' => array('@Doctrine\\ORM\\EntityManager'),
			'aliases' => array('authorizator'),
		),
		'Nette\\Web\\IUser' => array(
			'class' => 'Kdyby\\Security\\User',
			'aliases' => array('user'),
		),
		'Nette\\Application\\IPresenterFactory' => array(
			'class' => 'Kdyby\\Application\\PresenterFactory',
			'arguments' => array('@Kdyby\\Registry\\NamespacePrefixes'),
		),
		'Nette\\Caching\\IMemcacheStorage' => array(
			'class' => 'Nette\\Caching\\MemcachedStorage',
			'arguments' => array('%memcache[host]%', '%memcache[port]%', '%memcache[prefix]%', '@Nette\\Caching\\IMemcacheJournal'),
			'aliases' => array('memcache'),
		),
		'Nette\\Caching\\IMemcacheJournal' => array(
			'factory' => array(__CLASS__, 'createMemcacheJournal'),
		),

		'Doctrine\\ORM\\EntityManager' => array(
			'factory' => array('Kdyby\\Doctrine\\ServiceFactory', 'createEntityManager'),
			'arguments' => array('%Database%', '@Doctrine\\ORM\\Configuration', '@Doctrine\\Common\\EventManager'),
			'aliases' => array('entityManager'),
		),
		'Doctrine\\Common\\Cache\\Cache' => array(
			'class' => 'Kdyby\\Doctrine\\Cache',
			'arguments' => array('@Kdyby\\Doctrine\\Cache'),
		),
		'Doctrine\\ORM\\Configuration' => array(
			'factory' => array('Kdyby\\Doctrine\\ServiceFactory', 'createConfiguration'),
			'arguments' => array('@Doctrine\\Common\\Cache\\Cache', '%EntityDirs%'),
			'methods' => array(
				array('method' => 'setMetadataCacheImpl', 'arguments' => array('@Doctrine\Common\Cache\Cache')),
				array('method' => 'setQueryCacheImpl', 'arguments' => array('@Doctrine\Common\Cache\Cache')),
			),
		),
		'Doctrine\\Common\\EventManager' => array(
			'class' => 'Doctrine\\Common\\EventManager',
		),
//		set through profiler parameter in %Database% parameter
//		'Doctrine\\DBAL\\Logging\\SQLLogger' => array(
//			'factory' => array('Kdyby\\Doctrine\\Panel', 'create'),
//		),

		'Kdyby\\Doctrine\\Cache' => array(
			'class' => 'Nette\\Caching\\Cache',
			'arguments' => array('@Nette\\Caching\\ICacheStorage', 'Doctrine'),
		),
		'Kdyby\\Registry\\NamespacePrefixes' => array(
			'factory' => array(__CLASS__, 'createRegistryNamespacePrefixes'),
			'methods' => array(
				array('method' => 'freeze'),
			),
			'aliases' => array('namespacePrefixes'),
		),
		'Kdyby\\Registry\\TemplateDirs' => array(
			'factory' => array(__CLASS__, 'createRegistryTemplateDirs'),
			'methods' => array(
				array('method' => 'freeze'),
			),
			'aliases' => array('templateDirs'),
		),
	);



	/**
	 * @throws InvalidStateException
	 */
	final public function __construct()
	{
		throw new \InvalidStateException("Cannot instantiate static class " . __CLASS__ . ".");
	}



	/**
	 * @return IServiceContainer
	 */
	public static function getServiceContainer()
	{
		return Environment::getContext();
	}



	/**
	 * @param array $parameters
	 * @return Kdyby\Application\Application
	 */
	public static function createApplication(array $parameters, Nette\Web\IHttpRequest $httpRequest)
	{
		$class = $parameters['application.class'];

		$ref = Kdyby\Reflection\ServiceReflection::from($class);
		$params = $ref->getConstructorParamClasses();
		$serviceContainer = clone self::getServiceContainer();

		$application = $params ? $ref->newInstanceArgs($serviceContainer->expandParams($params)) : new $class;
		$application->setServiceContainer($serviceContainer);
		$application->catchExceptions = Environment::isProduction();

		return $application;
	}



	/**
	 * @param Nette\Web\HttpRequest $httpRequest
	 * @return Nette\Application\MultiRouter
	 */
	public static function createRouter(Nette\Web\HttpRequest $httpRequest)
	{
		$domainMap = (object)Nette\String::match($httpRequest->uri->host, Kdyby\Web\HttpHelpers::DOMAIN_PATTERN);

		$router = new Nette\Application\MultiRouter;
		$router[] = new Kdyby\Application\Routers\AdminRouter('//admin.' . $domainMap->domain);

		return $router;
	}



	/**
	 * @author Patrik Votoček
	 *
	 * @return FreezableArray
	 */
	public static function createRegistryNamespacePrefixes()
	{
		$register = new Kdyby\Tools\FreezableArray();
		$register['app'] = 'App\\';
		$register['framework'] = 'Kdyby\\';

		return $register;
	}



	/**
	 * @author Patrik Votoček
	 *
	 * @return FreezableArray
	 */
	public static function createRegistryTemplateDirs()
	{
		$register = new Kdyby\Tools\FreezableArray();
		$register['app'] = APP_DIR;
		$register['framework'] = KDYBY_DIR;

		return $register;
	}



	/**
	 * @param Nette\Web\Session $session
	 */
	public static function createSession(Nette\Web\HttpRequest $httpRequest)
	{
		$session = new Nette\Web\Session;

		// setup session
		if (!$session->isStarted()) {
			if (!Nette\Environment::isConsole()){
				$domainMap = (object)Nette\String::match($httpRequest->uri->host, Kdyby\Web\HttpHelpers::DOMAIN_PATTERN);
				$session->setCookieParams('/', '.' . $domainMap->domain);
			}

			$session->setExpiration(Nette\Tools::YEAR);
			if (!$session->exists()) {
				$session->start();
			}
		}

		return $session;
	}



	/**
	 * @return Nette\Caching\FileJournal
	 */
	public static function createCacheStorage(Nette\Caching\ICacheJournal $cacheJournal)
	{
		$dir = Kdyby\Tools\FileSystem::prepareWritableDir('%varDir%/cache');
		return new Kdyby\Caching\FileStorage($dir, $cacheJournal);
	}



	/**
	 * @return Nette\Caching\ICacheJournal
	 */
	public static function createMemcacheJournal()
	{
		/*if (Nette\Caching\SqliteJournal::isAvailable()) {
			return new Nette\Caching\SqliteJournal(Environment::getVariable('tempDir') . '/cachejournal.db');
		} else*/ {
			$dir = Kdyby\Tools\FileSystem::prepareWritableDir('%tempDir%/memcache');
			return new Nette\Caching\FileJournal($dir);
		}
	}

}