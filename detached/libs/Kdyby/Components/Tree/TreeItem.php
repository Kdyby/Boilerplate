<?php

namespace Kdyby\Component;

use Nette;
use Nette\Web\Html;
use Kdyby;



/**
 * @method string startTag
 * @method string getHtml
 * @method string endTag
 * 
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class TreeItem extends Nette\Component
{

	/** @var string|Nette\Web\Html */
	public $title;

	/** @var Nette\Application\Link */
	public $link;

	/** @var Nette\Web\Html */
	public $li;

	/** @var Nette\Web\Html */
	public $a;

	/** @var Nette\Web\Html */
	public $container;

	/** @var Nette\Web\Html */
	private $rendered;

	/** @var array */
	public $options = array();



	/**
	 * @param Nette\Application\PresenterComponent $component
	 * @param string $title
	 * @param string $destination
	 * @param array $linkParams
	 * @param array $itemOptions
	 * @return object
	 */
	public function __construct($title, $linkDestination = NULL, $linkParams = array(), $itemOptions = array())
	{
		$this->title =  $title;
		$this->link = (object)array('destination' => $linkDestination, 'params' => $linkParams);

		//	<li n:class="isset($branch->listclass) ? $branch->listclass">
		//		<a href="{$branch->link}" n:tag-if="isset($branch->link)" n:class="isset($branch->class) ? $branch->class">
		//			<span>{if $branch->title instanceof Html}{!$branch->title}{else}{$branch->title}{/if}</span></a>

		$li = isset($itemOptions['li']) ? $itemOptions['li'] : 'li';
		$this->li = is_string($li) ? Html::el($li) : $li;
		unset($itemOptions['li']);

		$a = isset($itemOptions['a']) ? $itemOptions['a'] : 'a';
		$this->a = is_string($a) ? Html::el($a) : $a;
		$this->a->href = NULL;
		unset($itemOptions['a']);

		$container = isset($itemOptions['container']) ? $itemOptions['container'] : 'span';
		$this->container = is_string($container) ? Html::el($container) : $container;
		unset($itemOptions['container']);

		$this->options = $itemOptions;
	}



	/**
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getOption($name, $default = NULL)
	{
		return isset($this->options[$name]) ? $this->options[$name] : $default;
	}



	/**
	 * @return string
	 */
	public function render()
	{
		if ($this->rendered) {
			return $this->rendered;
		}

		if ($this->title instanceof Html) {
			$this->container->add($this->title);

		} else {
			$this->container->setText($this->title);
		}

		$this->a->href(new Nette\Application\Link($this->getPresenter(), $this->link->destination, $this->link->params));
		$this->a->add($this->container);

		$this->li->add($this->a);

		return $this->rendered = $this->li;
	}



	/**
	 * @return Nette\Application\Presenter
	 */
	public function getPresenter()
	{
		return $this->lookup('Nette\Application\Presenter');
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		return (string)$this->render();
	}



	/**
	 * @param string $name
	 * @return mixed
	 */
	public function &__get($name)
	{
		return $this->render()->$name;
	}



	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		$this->render()->$name = $value;
	}



	/**
	 * @param string $name
	 */
	public function __isset($name)
	{
		return isset($this->render()->$name);
	}



	/**
	 * @param string $name
	 * @param array $args
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		return call_user_func_array(array($this->render(), $name), $args);
	}

}