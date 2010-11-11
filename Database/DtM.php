<?php

namespace Kdyby\Database;

use Nette;
use Kdyby;



/**
 * Database Manager - DtM is simple and easy to use
 * interface for defined-by-programmator entities, repositories and mappers
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class DtM extends Nette\Object
{

	/** @var \Kdyby\Database\DtMConfigurator */
	private $DtMConfigurator;

	/** @var array */
	private $Repositories = array();

	/** @var array */
	private $Mappers = array();



	/**
	 * @param \Kdyby\Database\DtMConfigurator $DtMConfigurator
	 */
	public function __construct(DtMConfigurator $DtMConfigurator)
	{
		$this->DtMConfigurator = $DtMConfigurator;
	}



	/**
	 * @return \Kdyby\Database\DtMConfigurator
	 */
	public function getConfigurator()
	{
		return $this->DtMConfigurator;
	}



	/**
	 * @param string $repository
	 * @return \Kdyby\Database\IRepository
	 */
	public function getRepository($repository)
	{
		if (!isset($this->Repositories[$repository])) {
			$this->Repositories[$repository] = $this->DtMConfigurator->createRepository($this, $repository);
		}

		return $this->Repositories[$repository];
	}



	/**
	 * @param string $mapper
	 * @return \Kdyby\Database\IEntityMapper
	 */
	public function getMapper($mapper)
	{
		if (!isset($this->Mappers[$repository])) {
			$this->Mappers[$repository] = $this->DtMConfigurator->createMapper($mapper);
		}

		return $this->Mappers[$repository];
	}

}