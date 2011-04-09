<?php

namespace Kdyby\Components\Grinder\Actions;

use Kdyby;
use Nette;
use Nette\Application\Link;
use Nette\Web\Html;



/**
 * @author Filip Procházka
 */
class LinkAction extends BaseAction
{

	/** @var boolean */
	public $handlerPassEntity = FALSE;

	/** @var callback */
	private $handler;

	/** @var string|callback */
	private $link;

	/** @var array */
	private $mask = array();



	/**
	 * @param Link $link
	 * @param array $mask
	 * @return LinkAction
	 */
	public function setLink($link, array $mask = array())
	{
		if (!is_callable($link) && !$link instanceof Link) {
			throw new \InvalidArgumentException("Link must be callable or instance of Nette\\Application\\Link");
		}

		$this->mask = $mask;
		$this->link = $link;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getLink()
	{
		$record = $this->getGrid()->getCurrentRecord();

		// custom link
		if ($this->link) {
			$args = array();
			foreach ($this->mask as $argName => $paramName) {
				if (method_exists($record, $method = 'get' . ucfirst($paramName))) {
					$args[$argName] = $record->$method();

				} elseif (isset($record->$paramName)) {
					$args[$argName] = $record->$paramName;

				} else {
					throw new \InvalidStateException("Record " . (is_object($record) ? "of entity " . get_class($record) . ' ' : NULL) . "has no parameter named '" . $paramName . "'.");
				}
			}

			if (is_callable($this->link)) {
				return call_user_func($this->link, $args);
			}

			$link = clone $this->link;
			foreach ($args as $argName => $value) {
				$link->setParam($argName, $value);
			}
			return (string)$link;
		}

		return $this->getGrid()->link('action!', array(
			'action' => $this->name,
			'token' => $this->getGrid()->getSecurityToken(),
			'id' => $this->getGrid()->getModel()->getUniqueId($record) ?: NULL,
		));
	}



	/**
	 * Get handler
	 * @return callback
	 */
	public function getHandler()
	{
		return $this->handler;
	}



	/**
	 * Set handler
	 * @param callback handler
	 * @return LinkAction
	 */
	public function setHandler($handler)
	{
		$handler = callback($handler);
		if (!is_callable($handler)) {
			throw new \InvalidArgumentException("Handler is not callable.");
		}

		$this->handler = $handler;
		return $this;
	}



	/**
	 * @param int $id
	 */
	public function handleClick($id = NULL)
	{
		if (!is_callable($this->getHandler())) {
			throw new \InvalidStateException("Handler for action '" . $this->name . "' is not callable.");
		}

		$id = $id ?: NULL;
		if ($this->handlerPassEntity === TRUE) {
			if (!$id) {
				throw new \InvalidStateException("Missing argument 'id' in action '" . $this->name . "'.");
			}

			$id = $this->getGrid()->getModel()->getItemByUniqueId($id);
		}

		call_user_func($this->getHandler(), $this, $id);
	}



	/**
	 * @return Nette\Web\Html
	 */
	public function getControl()
	{
		$link = Html::el('a');
		$caption = $this->getCaption();
		$link->{$caption instanceof Html ? 'add' : 'setText'}($caption);

		return $link->setHref($this->getLink());
	}

}