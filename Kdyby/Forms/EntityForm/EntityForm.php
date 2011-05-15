<?php

namespace Kdyby\Forms\EntityForm;

use Nette;
use Kdyby;
use Doctrine;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class EntityForm extends BaseForm
{

	/** @var Kdyby\Validation\IValidator */
	private $validator;

	/** @var Kdyby\Doctrine\BaseEntity */
	private $entity;

	/** @var array */
	private $entityData;

	/** @var Doctrine\ORM\EntityManager */
	private $entityManager;

	/** @var EntityFormMapper */
	private $mapper;



	/**
	 * @param Kdyby\Doctrine\BaseEntity $entity
	 */
	public function __construct($entity)
	{
		parent::__construct(NULL, NULL);

		$this->entity = $entity;
		$this->onSubmit = array(callback($this, 'updateEntity'));
		$this->onValidate = array(callback($this, 'isFormCorresponding'));
	}



	/**
	 * @param Kdyby\Doctrine\Mapping\IFormMapper $mapper
	 */
	public function setMapper(Kdyby\Doctrine\Mapping\IFormMapper $mapper)
	{
		$this->mapper = $mapper;
	}



	/**
	 * @return Kdyby\Doctrine\Mapping\IFormMapper
	 */
	public function getMapper()
	{
		if ($this->mapper === NULL) {
			$this->setMapper(new Kdyby\Doctrine\Mapping\EntityFormMapper($this->entityManager));
		}

		return $this->mapper;
	}



	/**
	 * @return Kdyby\Doctrine\BaseEntity
	 */
	public function getEntity()
	{
		return $this->entity;
	}



	/**
	 * @param Kdyby\Validation\IValidator $validator
	 */
	public function setValidator(Kdyby\Validation\IValidator $validator)
	{
		$this->validator = $validator;
		$this->onValidate[] = callback($this, 'validateEntity');
	}



	/**
	 * @return Kdyby\Validation\IValidator
	 */
	public function getValidator()
	{
		return $this->validator;
	}



	/**
	 * @param Kdyby\Forms\EntityForm $form
	 */
	public function validateEntity(self $form)
	{
		$errors = $this->getValidator()->validate($this->entity);

		if ($errors->hasErrors()) {
			$this->addErrorMessages($errors->getErrors());
		}
	}



	/**
	 * @param Kdyby\Forms\EntityForm $form
	 */
	public function updateEntity(self $form)
	{
		return $this->getMapper()->toEntity($form->getValues(), $this->entity);
	}



	/**
	 * @return bool
	 */
	public function isFormCorresponding(self $form)
	{
		$check = function ($entity, $formContainer) use (&$check) {
			foreach ($this as $name => $control) {
				if (!property_exists($entity, $name)) {
					throw FormException::entityPropertyNotExists($entity, $this->getUniqueId(), $name);
				}

				if ($control instanceof Nette\Forms\Container) {
					$check($entity->{$name}, $control);
				}
			}

			return TRUE;
		};

		$check($form->getEntity(), $form);
	}



	/**
	 * Internal: receives submitted HTTP data.
	 * @return array
	 */
	protected function receiveHttpData()
	{
		$presenter = $this->getPresenter();
		if (!$presenter->isSignalReceiver($this, 'submit')) {
			if ($this->entityData === NULL) {
				$this->entityData = $this->getMapper()->toArray($this->entity);
			}

			return $this->entityData;
		}

		return parent::receiveHttpData();
	}



	/**
	 * @param Doctrine\ORM\EntityManager $entityManager
	 */
	public function setEntityManager(Doctrine\ORM\EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}



	/**
	 * @return Doctrine\ORM\EntityManager
	 */
	public function getEntityManager()
	{
		return $this->entityManager;
	}


}