<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
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
	protected $name;

	/**
	 * @ORM\Column(type="text", nullable=TRUE)
	 * @var string
	 */
	protected $description;

	/**
	 * @ORM\Column(type="text")
	 * @var string
	 */
	protected $source;

	/**
	 * @ORM\Column(type="boolean")
	 * @var boolean
	 */
	protected $layout = FALSE;

	/**
	 * @ORM\ManyToOne(targetEntity="TemplateSource", cascade={"persist"})
	 * @ORM\JoinColumn(name="extends_id", referencedColumnName="id")
	 * @var \Kdyby\Templates\TemplateSource
	 */
	private $extends;



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
