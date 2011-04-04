<?php

namespace Kdyby\DependencyInjection;

use Doctrine;
use Kdyby;
use Nette;
use Nette\Environment;



class DefaultServiceFactories extends Nette\Object
{

	public static $defaultServices = array(
		'Nette\\Application\\Application' => array(
			'factory' => array(__CLASS__, 'createApplication'),
			'arguments' => array('%Application[application.class]%'),
			'aliases' => array('application'),
		),
		'Nette\\Application\\IPresenterFactory' => array(
			'class' => 'Kdyby\\Application\\PresenterFactory',
			'arguments' => array('@Kdyby\\Registry\\NamespacePrefixes'),
		),
		'Nette\\Application\\IRouter' => array(
			'factory' => array(__CLASS__, 'createRouter'),
			'arguments' => array('@Doctrine\\ORM\\EntityManager'),
		),
		'Nette\\Caching\\ICacheStorage' => array(
			'factory' => array(__CLASS__, 'createCacheStorage'),
			'arguments' => array('@Nette\\Caching\\ICacheJournal'),
			'aliases' => array('cache'),
		),
		'Nette\\Caching\\ICacheJournal' => array(
			'factory' => array('Nette\Configurator', 'createCacheJournal'),
		),
		'Nette\\Caching\\IMemcacheStorage' => array(
			'class' => 'Nette\\Caching\\MemcachedStorage',
			'arguments' => array('%memcache[host]%', '%memcache[port]%', '%memcache[prefix]%'),
			'aliases' => array('memcache'),
		),
		'Nette\\ITranslator' => array(
			'factory' => 'Kdyby\\Translator\\Gettext::getTranslator',
			'arguments' => array('%translator[translationsDir]%', '%translator[language]%'),
			'aliases' => array('translator')
		),
		'Nette\\Loaders\\RobotLoader' => array(
			'factory' => array('Nette\Configurator', 'createRobotLoader'),
			'arguments' => array(
				array('directory' => array('%appDir%')),
			),
		),
		'Nette\\Mail\\IMailer' => array(
			'factory' => array('Nette\Configurator', 'createMailer'),
			'aliases' => array('mailer'),
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
		'Nette\\Web\\Session' => array(
			'factory' => array(__CLASS__, 'createSession'),
			'arguments' => array('@Nette\\Web\\IHttpRequest'),
			'aliases' => array('sessionStorage'),
		),
		'Nette\\Web\\IUser' => array(
			'class' => 'Kdyby\\Security\\User',
			'aliases' => array('user'),
		),

		'Doctrine\\ORM\\EntityManager' => array(
			'factory' => array('Kdyby\\Doctrine\\ServiceFactory', 'createEntityManager'),
			'arguments' => array('%database%', '@Doctrine\\ORM\\Configuration', '@Doctrine\\Common\\EventManager'),
			'aliases' => array('entityManager'),
		),
		'Doctrine\\Common\\Cache\\Cache' => array(
			'class' => 'Kdyby\\Doctrine\\Cache',
			'arguments' => array('@Kdyby\\Doctrine\\Cache'),
		),
		'Doctrine\\ORM\\Configuration' => array(
			'factory' => array('Kdyby\\Doctrine\\ServiceFactory', 'createConfiguration'),
			'arguments' => array('%EntityDirs%'),
			'methods' => array(
				array('method' => 'setMetadataCacheImpl', 'arguments' => array('@Doctrine\Common\Cache\Cache')),
				array('method' => 'setQueryCacheImpl', 'arguments' => array('@Doctrine\Common\Cache\Cache')),
			),
		),
		'Doctrine\\Common\\EventManager' => array(
			'class' => 'Doctrine\\Common\\EventManager',
			'methods' => array(
				array('method' => 'addEventSubscriber', 'arguments' => array('@Gedmo\\Tree\\TreeListener')),
			),
		),

		'Gedmo\\Tree\\TreeListener' => array(
			'class' => 'Gedmo\\Tree\\TreeListener',
		),
//		set through profiler parameter in %database% parameter
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
		'Kdyby\\Templates\\ITemplateFactory' => array(
			'class' => 'Kdyby\\Templates\\TemplateFactory',
			'arguments' => array('@Nette\\ITranslator', '@Nette\\Web\\IUser', '%baseUri%'),
//				('Nette\\Templates\\FileTemplate', '@Nette\\ITranslator'),
			'aliases' => array('templateFactory'),
		),
		'Kdyby\\Application\\INavigationManager' => array(
			'class' => 'Kdyby\\Components\\Navigation\\NavigationManager',
			'arguments' => array('@Doctrine\\ORM\\EntityManager'),
			'aliases' => array('navigationManager'),
		),
		'Kdyby\\Application\\IRequestManager' => array( // todo: realy interface?
			'class' => 'Kdyby\\Application\\RequestManager',
			'arguments' => array('@Doctrine\\ORM\\EntityManager', '@Nette\\Caching\\ICacheStorage'),
			'aliases' => array('requestManager'),
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
	 * @param string $class
	 * @return Nette\Application\Application
	 */
	public static function createApplication($class)
	{
		$ref = Kdyby\Reflection\ServiceReflection::from($class);
		$params = $ref->getConstructorParamClasses();
		$serviceContainer = clone self::getServiceContainer();

		$application = $params ? $ref->newInstanceArgs($serviceContainer->expandParams($params)) : new $class;
		$application->setServiceContainer($serviceContainer);
		$application->catchExceptions = Environment::isProduction();

		if (!$application instanceof Nette\Application\Application) {
			throw new \InvalidArgumentException("Application class " . $class .  " must be descendant of Nette\Application\Application.");
		}

		return $application;
	}



	/**
	 * @param Doctrine\ORM\EntityManager $em
	 * @return Nette\Application\MultiRouter
	 */
	public static function createRouter(Doctrine\ORM\EntityManager $em)
	{
		$router = new Nette\Application\MultiRouter;
		$router[] = new Kdyby\Application\Routers\SequentialRouter($em);

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
	 * @param array $directories
	 * @return Nette\Loaders\RobotLoader
	 */
	public static function createRobotLoader(array $directories)
	{
		return Nette\Configurator::createRobotLoader(array(
			'directory' => $directories
		));
	}

}