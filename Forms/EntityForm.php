<?php

namespace Kdyby\Form;

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

	/** @var Doctrine\ORM\EntityManager */
	private $entityManager;

	/** @var EntityFormMapper */
	private $mapper;



	/**
	 * @param Kdyby\Doctrine\BaseEntity $entity
	 * @param Nette\IComponentContainer $parent
	 * @param string $name
	 */
	public function __construct($entity, Nette\IComponentContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);

		$this->entity = $entity;
		$this->onSubmit = array(callback($this, 'updateEntity'));
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
	public function setValidator(\Kdyby\Validation\IValidator $validator)
	{
		$this->validator = $validator;
		$this->onValidate = array(callback($this, 'validateEntity'));
	}



	/**
	 * @return Kdyby\Validation\IValidator
	 */
	public function getValidator()
	{
		return $this->validator;
	}



	/**
	 * @param Kdyby\Form\EntityForm $form
	 */
	public function validateEntity(self $form)
	{
		$errors = $this->getValidator()->validate($this->entity);

		if ($errors->hasErrors()) {
			$this->addErrorMessages($errors->getErrors());
		}
	}



	/**
	 * @param Kdyby\Form\EntityForm $form
	 */
	public function updateEntity(self $form)
	{
		return $this->getMapper()->toEntity($form->getValues(), $this->entity);
	}



	/**
	 * @return bool
	 */
	public function isFormCorresponding()
	{
		$check = function ($entity, $formContainer) use (&$check) {
			foreach ($this as $name => $control) {
				if (method_exists($entity, $name)) {
					return FALSE;
				}

				if ($control instanceof Nette\Forms\FormContainer) {
					if (!$check($control)) {
						return FALSE;
					}
				}
			}

			return TRUE;
		};

		return $check($this->entity, $this);
	}



	/**
	 * Internal: receives submitted HTTP data.
	 * @return array
	 */
	protected function receiveHttpData()
	{
		$presenter = $this->getPresenter();
		if (!$presenter->isSignalReceiver($this, 'submit')) {
			return $this->getMapper()->toArray($this->entity);
		}

		return parent::receiveHttpData();
	}



	/**
	 * @param Doctrine\ORM\EntityManager $entityManager
	 */
	public function bindModel(Doctrine\ORM\EntityManager $entityManager)
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