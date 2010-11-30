<?php

namespace Kdyby\ORM\Mapping;

use Nette;
use Kdyby;
use ORM;
use ORM\Session;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class MySQLAnnotationEntityMap extends AnnotationEntityMap
{

	/**
	 * @param string $name				property
	 * @param string $key				join key
	 * @param string $childEntityType
	 */
	public function addOneToOne($name, $key, $childEntityType)
	{
		$this->addProperty($name, $key, new OneToOneMapper($childEntityType, $this->session));
	}



	/**
	 * @param string $name				property
	 * @param string $key				join key
	 * @param string $childEntityType
	 */
	public function addOneToMany($name, $key, $childEntityType)
	{
		$this->addProperty($name, $key, new OneToManyMapper($childEntityType, $this->session));
	}



	/**
	 * @param string $name				property
	 * @param string $childEntityType
	 */
	public function addManyToMany($name, /*$key, */$childEntityType)
	{
		$this->addProperty($name, /*$key*/NULL, new ManyToManyMapper($childEntityType, $this->session));
	}

}