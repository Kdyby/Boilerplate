<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Doctrine\Forms;

use Kdyby;
use Kdyby\Doctrine\Type;
use Kdyby\Doctrine\Mapping\ClassMetadata;
use Nette;
use Nette\Forms\Container;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class ContainerBuilder extends Nette\Object
{

	/**
	 * @var \Nette\Forms\Container|\Kdyby\Application\UI\Form
	 */
	private $container;

	/**
	 * @var \Kdyby\Doctrine\Mapping\ClassMetadata
	 */
	private $class;



	/**
	 * @param \Nette\Forms\Container $container
	 * @param \Kdyby\Doctrine\Mapping\ClassMetadata $class
	 */
	public function __construct(Container $container, ClassMetadata $class)
	{
		$this->container = $container;
		$this->class = $class;
	}



	/**
	 * Adds all fields to container
	 */
	public function addAllFields()
	{
		foreach ($this->class->getFieldNames() as $field) {
			if ($this->class->isIdentifier($field)) {
				continue;

			} elseif (in_array($this->class->getTypeOfField($field), array('array', 'object', 'blog'))) {
				continue;
			}

			$this->addFields($field);
		}
	}



	/**
	 * @param string|array $fields
	 */
	public function addFields($fields)
	{
		foreach (is_array($fields) ? $fields : func_get_args() as $field) {
			$control = $this->buildField($field);
			$this->buildValidations($control);
		}
	}



	/**
	 * @param $field
	 *
	 * @throws \Kdyby\NotSupportedException
	 * @throws \Kdyby\InvalidArgumentException
	 * @return \Nette\Forms\Controls\BaseControl
	 */
	protected function buildField($field)
	{
		if (!$this->class->hasField($field)) {
			if (!$this->class->hasAssociation($field)) {
				throw new Kdyby\InvalidArgumentException("Given name '$field' is not entity field.");

			} else {
				throw new Kdyby\NotSupportedException("Association container for '$field' cannot be auto-generated.");
			}
		}

		switch ($this->class->getTypeOfField($field)) {
			case Type::BIGINT:
			case Type::DECIMAL:
			case Type::INTEGER:
			case Type::SMALLINT:
			case Type::STRING:
			case Type::FLOAT:
				return $this->container->addText($field, $field);
				break;

			case Type::DATE:
				return $this->container->addDate($field, $field);
				break;

			case Type::TIME:
				return $this->container->addTime($field, $field);
				break;

			case Type::DATETIME:
			case Type::DATETIMETZ:
				return $this->container->addDatetime($field, $field);
				break;

			case Type::TEXT:
			case Type::BLOB:
				return $this->container->addTextArea($field, $field);
				break;

			case Type::BOOLEAN:
				return $this->container->addCheckbox($field, $field);
				break;

			default:
				throw new Kdyby\NotSupportedException("Form type for '$field' cannot be resolved.");
		}
	}



	/**
	 * @param \Nette\Forms\Controls\BaseControl $control
	 */
	public function buildValidations(Nette\Forms\Controls\BaseControl $control)
	{
		$mapping = $this->class->getFieldMapping($field = $control->getName());
		switch ($this->class->getTypeOfField($field)) {
			case Type::BIGINT:
			case Type::INTEGER:
			case Type::SMALLINT:
			case Type::DECIMAL:
				$control->addCondition(Form::FILLED)
					->addRule(Form::NUMERIC);
				break;

			case Type::STRING:
				$control->addCondition(Form::FILLED)
					->addRule(Form::MAX_LENGTH, NULL, $mapping['length'] ?: 255);
				break;

			case Type::TEXT:
				$control->addCondition(Form::FILLED)
					->addRule(Form::MAX_LENGTH, NULL, $mapping['length'] ?: 255);
				break;

			case Type::FLOAT:
				$control->addCondition(Form::FILLED)
					->addRule(Form::FLOAT);
				break;
		}

		if (!$mapping['nullable']) {
			$control->setRequired();
		}
	}

}
