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
use Nette\Utils\PhpGenerator as Code;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @ORM\Entity()
 * @ORM\Table(name="templates")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="_type", type="string")
 * @ORM\DiscriminatorMap({"base" = "TemplateSource"})
 *
 * @method string getSource()
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
	 * @param string $layoutFile
	 *
	 * @return string
	 */
	public function build(EditableTemplates $templates, array &$db, $layoutFile = NULL)
	{
		$source = $this->source;

		$dp[Nette\Caching\Cache::TAGS][] = 'dbTemplate#' . $this->getId();

		// todo: debugging only?
		$db[Nette\Caching\Cache::FILES][] = self::getReflection()->getFileName();
		$db[Nette\Caching\Cache::FILES][] = EditableTemplates::getReflection()->getFileName();

		if ($this->getExtends()) {
			$file = $templates->getTemplateFile($extended = $this->getExtends(), $layoutFile);

			$db[Nette\Caching\Cache::FILES][] = $file; // todo: why?
			$dp[Nette\Caching\Cache::TAGS][] = 'dbTemplate#' . $extended->getId();

			return '{extends ' . Code\Helpers::dump($file) . '}' .
				"\n" . $source;

		} elseif ($layoutFile !== NULL) {

			return '{extends ' . Code\Helpers::dump($layoutFile) . '}{block #content}' .
				"\n" . $source;

		} else {
			return $source;

		}
	}

}
