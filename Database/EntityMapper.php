<?php

namespace Kdyby\Database;




/**
 * Description of UserModel
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
abstract class EntityMapper extends ConnectedObject
{


	public function __construct()
	{
		
	}


	public function findById($id)
	{
		return $this->find('id = %i', $id);
	}

	
	abstract public function save();

	abstract protected function insert();

	abstract protected function update();

	abstract public function find();

}