<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Templates;

use Doctrine;
use Doctrine\ORM\Mapping as ORM;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @ORM\Entity()
 * @ORM\Table(name="templates")
 */
class TemplateSource extends Kdyby\Doctrine\Entities\IdentifiedEntity
{

	/**
	 * @ORM\Column(type="string", nullable=TRUE)
	 * @var string
	 */
	private $name;

	/**
	 * @ORM\Column(type="text", nullable=TRUE)
	 * @var string
	 */
	private $description;

	/**
	 * @ORM\Column(type="text")
	 * @var string
	 */
	private $source;

	/**
	 * @ORM\ManyToOne(targetEntity="TemplateSource", cascade={"persist"})
	 * @ORM\JoinColumn(name="extends_id", referencedColumnName="id")
	 * @var \Kdyby\Templates\TemplateSource
	 */
	private $extends;



	/**
	 * @param string $source
	 */
	public function setSource($source)
	{
		$this->source = $source;
	}



	/**
	 * @return string
	 */
	public function getSource()
	{
		return $this->source;
	}



	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name ?: NULL;
	}



	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}



	/**
	 * @param string $description
	 */
	public function setDescription($description)
	{
		$this->description = $description ?: NULL;
	}



	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}



	/**
	 * @param \Kdyby\Templates\TemplateSource $extends
	 */
	public function setExtends(TemplateSource $extends = NULL)
	{
		$this->extends = $extends;
	}



	/**
	 * @return \Kdyby\Templates\TemplateSource
	 */
	public function getExtends()
	{
		return $this->extends;
	}



	/**
	 * @param \Kdyby\Templates\EditableTemplates $templates
	 * @param array $db
	 *
	 * @return string
	 */
	public function build(EditableTemplates $templates, array &$db)
	{
		if (!$this->getExtends()) {
			return $this->getSource();
		}

		$source = $this->getSource();
		$file = $templates->getTemplateFile($this->getExtends());
		$db[Nette\Caching\Cache::FILES][] = $file;

		return '{extends "' . $file . '"}' . "\n" . $source;
	}

}
