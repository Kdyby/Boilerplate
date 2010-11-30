<?php

namespace Kdyby\ORM;

use dibi;
use Nette;
use Nette\Environment;
use Kdyby;
use ORM;
use ORM\Session;
use ORM\IConfigurator;
use ORM\Mapping\IMapper;
use ORM\Workspace;



class Configurator extends ORM\Configurator
{

	/** @var DibiConnection */
	private $connection;



	/**
	 * @param string $entityType
	 * @param ORM\Session $session
	 * @return ORM\Mapping\IMapper
	 */
	public function createMapper($entityType, Session $session)
	{
		if (!isset($this->mappers[$entityType])) {
			throw new \InvalidStateException("Mapper for entity type '$entityType' was not defined");
		}

		$mapper = $this->mappers[$entityType];

		if (is_string($mapper)) {
			return new $mapper($session, $this->connection);

		} elseif ($mapper instanceof IMapper) {
			return $mapper;

		} elseif (is_callable($mapper)) {
			return call_user_func($mapper, $session, $this->connection);

		} else {
			$class = get_class($this);
			throw new \InvalidStateException("Value gived as $class::addMapper($entityType, ?) is not a class, factory or IMapper");
		}
	}



	/**
	 * @return Kdyby\ORM\EntityIdentityMap
	 */
	public function createIdentityMap()
	{
		return new EntityIdentityMap;
	}



	/**
	 * @param array $options
	 * @return ORM\Session
	 */
	public static function createWorkspace($options)
	{
		$configurator = new self;
		$creditians = Environment::getConfig('database');
		$configurator->connection = dibi::connect($creditians);

		foreach ($options['mappers'] as $entityType => $mapper) {
			$configurator->addMapper($entityType, $mapper);
		}

		foreach ($options['repositories'] as $entityType => $repository) {
			$configurator->addRepository($entityType, $repository);
		}

		$session = new ORM\Workspace($configurator);

		return $session;
	}
}