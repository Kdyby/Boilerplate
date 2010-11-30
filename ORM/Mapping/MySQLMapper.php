<?php

namespace Kdyby\ORM\Mapping;

use Nette;
use Kdyby;
use ORM\Session;
use ORM\IQuery;
use ORM\Query;
use ORM\Mapping\IMapper;
use DibiConnection;



abstract class MySQLMapper extends Nette\Object implements IMapper
{

	/** @var string */
	protected $entityType = "";

	/** @var ORM\Session */
	private $session;

	/** @var DibiConnection */
	private $database;

	/** @var ORM\Mapping\IEntityMap */
	private $entityMap;

	//private $queryCache = array();



	/**
	 * @param ORM\Session $session
	 * @param DibiConnection $database
	 */
	public function __construct(Session $session, DibiConnection $database)
	{
		$this->session = $session;
		$this->database = $database;
	}



	/**
	 * @return ORM\Session
	 */
	public function getSession()
	{
		return $this->session;
	}



	/**
	 * @return DibiConnection
	 */
	public function getDatabase()
	{
		return $this->database;
	}



	/**
	 * @return ORM\Mapping\EntityMap
	 */
	final public function getEntityMap()
	{
		if ($this->entityMap === NULL) {
			$this->entityMap = $this->createEntityMap();
		}
		return $this->entityMap;
	}



	/**
	 * @return ORM\Mapping\IEntityMap
	 */
	protected function createEntityMap()
	{
		return $map = new MySQLAnnotationEntityMap($this->entityType, $this->session);
	}



	/********************* interface IMapper *********************/



	/**
	 * @param  object
	 * @return void
	 */
	public function insert($object)
	{
		$doc = $this->save($object);
		if (!isset($doc['_id'])) unset($doc['_id'], $doc['_rev']); // id is NULL or undefined
		$id = $this->database->save($doc)->id;
		$this->session->getIdentityMap()->add($id, $object);
	}



	/**
	 * @param  object
	 * @return void
	 */
	public function update($object)
	{
		$data = $this->save($object);
		$data['_id'] = $this->session->getIdentityMap()->identify($object);
		$data['_rev'] = $this->revs[ $data['_id'] ];
		$this->database->save($data);
	}



	/**
	 * @param  object
	 * @return void
	 */
	public function delete($object)
	{
	}



	/**
	 * @param  ORM\IQuery
	 * @return array
	 */
	public function query(IQuery $query)
	{
		$cacheKey = md5(serialize($query));
		if (isset($this->queryCache[$cacheKey])) {
			return $this->queryCache[$cacheKey];
		}

		$method = '';
		foreach ($query->getCriteria() as $criteria) {
			$key = $criteria->key;
			$key[0] = $key[0] & "\xDF";
			$method .= $key . 'And';
		}

		$method = isset($key) ? 'findBy' . substr($method, 0, -3) : 'findAll';

		$objects = array();

		if ($method === 'findById') {
			$crit = $query->getCriteria();
			reset($crit);
			$ids = array();
			foreach ((array) current($crit)->value as $id) {
				if ($v = $this->session->getIdentityMap()->get($id)) {
					$objects[] = $v;
				} else {
					$ids[] = $id;
				}
			}
			if (empty($ids)) {
				return $objects;
			}
			$query = new Query('id =', $ids);
		}

		$docs = call_user_func_array(array($this, $method), $query->getCriteria());

		foreach ($docs as $doc) {
			if (isset($doc->_id)) {
				$this->revs[ $doc->_id ] = $doc->_rev;
			}
			$objects[] = $this->load((array) $doc);
		}
		return $this->queryCache[$cacheKey] = $objects;
	}



	/**
	 * @param  ORM\IQuery
	 * @return object|NULL
	 */
	public function queryOne(IQuery $query)
	{
		$objects = $this->query($query);
		reset($objects);
		return current($objects) ?: NULL;
	}



	protected function findById($criteria)
	{
		return $this->database->bulkDocument->keys($criteria->value)->includeDocs(TRUE)
			->query()->fetchDocs();
	}



	/**
	 * @param object $entity
	 * @return array
	 */
	public function save($entity)
	{
		return $this->getEntityMap()->save($entity);
	}



	public function load($data)
	{
		if ($entity = $this->session->getIdentityMap()->get($data['_id'])) {
			return $entity;
		}
		$entity = $this->getEntityMap()->load($data);
		$this->session->addClean($data['_id'], $entity);
		return $entity;
	}
}