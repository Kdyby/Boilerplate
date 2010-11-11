<?php

namespace Kdyby\Database;

use Nette;
use Kdyby;



/**
 * Description of DtMConfigurator
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class DtMConfigurator extends Nette\Object
{

	private $mappers = array(
		'user' => "Kdyby\\Mapper\\dibi\\User",
		'address' => "Kdyby\\Mapper\\dibi\\Address"
	);



	public function createRepository(DtM $DtM, $repository)
	{
		$class = $this->formatRepositoryClass($repository);

		if (!class_exists($class)) {
			throw new \InvalidStateException("Repository '$repository' doesn't exists. Missing class $class");
		}

		return new $class($DtM);
	}



	protected function formatRepositoryClass($repository)
	{
		return "Kdyby\\Repository\\" . $repository;
	}



	public function createMapper($mapper)
	{
		$lMapper = Nette\String::lower($mapper);

		if (isset($this->mappers[$lMapper])){
			if (is_string($this->mappers[$lMapper])) {
				$class = $this->mappers[$lMapper];

			} else {
				$class = call_user_func($this->mappers[$lMapper], $mapper);
			}

		} else {
			$class = $this->formatDefaultMapperClass($mapper);
		}

		if (!class_exists($class)) {
			throw new \InvalidStateException("Mapper '$mapper' doesn't exists. Missing class $class");
		}

		return new $class;
	}



	protected function formatDefaultMapperClass($mapper)
	{
		return "Kdyby\\Mapper\\" . $mapper;
	}



	public function setMapperClass($mapper, $class)
	{
		$lMapper = Nette\String::lower($mapper);
		$this->mappers[$lMapper] = (string)$class;
	}



	public function setMapperFactory($mapper, $factory)
	{
		$lMapper = Nette\String::lower($mapper);
		$this->mappers[$lMapper] = $factory;
	}

}
