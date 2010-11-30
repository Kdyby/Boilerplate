<?php

namespace Kdyby\ORM\Mapping;

use ORM\Mapping\IPropertyMapper;
use ORM\Session;
use ORM\Query;



class ManyToManyMapper extends Nette\Object implements IPropertyMapper
{
	private $entityType;

	private $session;



	public function __construct($entityType, Session $session)
	{
		$this->entityType = $entityType;
		$this->session = $session;
	}



	public function save($value, $entity)
	{
		$data = array();
		foreach ($value as $entity) {
			$data[] = $this->entityMap->save($entity);
		}
		return $data;
	}



	public function load($key, $data)
	{
		$list = array();
		foreach ($data[$key] as $v) {
			$list[] = $this->entityMap->load((array) $v);
		}
		return $list;
	}
}