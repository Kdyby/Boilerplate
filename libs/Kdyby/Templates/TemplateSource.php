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
use Nette\Caching\Cache;
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
	 * @ORM\Column(type="text", nullable=TRUE)
	 * @var string
	 */
	protected $source;

	/**
	 * @ORM\Column(type="boolean")
	 * @var boolean
	 */
	protected $layout = FALSE;

	/**
	 * @ORM\ManyToOne(targetEntity="TemplateSource", cascade={"persist"}, fetch="EAGER")
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
	 * @param array $dp
	 * @param string $layoutFile
	 *
	 * @return string
	 */
	public function build(EditableTemplates $templates, array &$dp, $layoutFile = NULL)
	{
		$source = $this->source;

		$dp[Cache::TAGS] = array('dbTemplate#' . $this->getId());

		if (Nette\Diagnostics\Debugger::$productionMode === FALSE) {
			$dp[Cache::FILES][] = self::getReflection()->getFileName();
			$dp[Cache::FILES][] = EditableTemplates::getReflection()->getFileName();
		}

		if ($extended = $this->getExtends()) {
			$file = $templates->getTemplateFile($extended, $layoutFile);

			$dp[Cache::FILES][] = $file; // todo: why?
			$dp[Cache::TAGS][] = 'dbTemplate#' . $extended->getId();

			return '{extends ' . Code\Helpers::dump($file) . '}' .
				"\n" . $source;

		} elseif ($layoutFile !== NULL) {
			return '{extends ' . Code\Helpers::dump($layoutFile) . '}{block #content}' .
				"\n" . $source;

		} else {
			return $source;

		}
	}



	/**
	 * @return array
	 */
	public function __sleep()
	{
		if (($extends = $this->getExtends()) instanceof Doctrine\ORM\Proxy\Proxy && !$extends->__isInitialized()) {
			/** @var \Doctrine\ORM\Proxy\Proxy $extends */
			$extends->__load();
		}

		return array('id', 'name', 'description', 'source', 'layout', 'extends');
	}

}
