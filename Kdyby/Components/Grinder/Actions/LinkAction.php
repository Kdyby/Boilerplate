<?php

namespace Kdyby\Components\Grinder\Actions;

use Kdyby;
use Nette;
use Nette\Web\Html;



/**
 * @author Filip Procházka
 */
class LinkAction extends BaseAction
{

	/** @var string|callback */
	private $link;

	/** @var callback */
	private $handler;



	/**
	 * Set link URL
	 * @param string|callback link
	 * @return LinkAction
	 */
	public function setLink($link)
	{
		$this->link = $link;
		return $this;
	}



	/**
	 * Get button link
	 * @param \Iterator $iterator
	 * @return string
	 */
	public function getLink(\Iterator $iterator)
	{
		// custom link
		if ($this->link) {
			if (is_callable($this->link)) {
				return call_user_func($this->link, $iterator, $iterator->current());
			}

			return $this->link;
		}

		return $this->link('click!', array(
			'token' => $this->getGrid()->getSecurityToken(),
			'id' => $iterator->getCurrentUniqueId() ?: NULL,
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
		if (!is_callable($handler)) {
			throw new \InvalidArgumentException("Handler is not callable.");
		}

		$this->handler = $handler;
		return $this;
	}



	/**
	 * Handle click signal
	 * 
	 * @param string $token
	 * @param mixed $id
	 */
	public function handleClick($token, $id = NULL)
	{
		if ($token !== $this->getGrid()->getSecurityToken()) {
			throw new Nette\Application\ForbiddenRequestException("Security token does not match. Possible CSRF attack.");
		}

		// handle
		call_user_func($this->handler, $uniqueId ? $grid->getModel()->getItemByUniqueId($uniqueId) : NULL);

		if ($this->getPresenter()->isAjax()) {
			return $this->getGrid()->invalidateControl();
		}

		$this->getGrid()->redirect("this");
	}



	public function getControl()
	{
		$link = Html::el('a');
		$caption = $this->getCaption();
		$link->{$caption instanceof Html ? 'add' : 'setText'}($caption);

		return $link->setHref($this->getLink());
	}

}