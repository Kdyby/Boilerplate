<?php

namespace Kdyby\Database;




/**
 * Description of ConnectedObject
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class ConnectedObject extends \Nette\Object
{

	/** @var \Kdyby\Database\EntityRepository */
	private $db;



	/**
	 * @return \DibiConnection
	 */
	public function getConnection()
	{
		return \dibi::getConnection();
	}



	/**
	 * @return \Kdyby\Database\EntityBin
	 */
	public function getDb()
	{
		if ($this->db === NULL) {
			$this->db = \Nette\Environment::getService("EntityRepository");
		}

		return $this->db;
	}



	/**
	 * @return \Kdyby\Database\EntityBin
	 */
	public function getEntityRepository()
	{
		return $this->getDb();
	}

}