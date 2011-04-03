<?php

namespace Kdyby\Components\Grinder;

use Nette;



/**
 * Action button
 *
 * @author Jan Marek
 * @license MIT
 */
class LinkAction extends Nette\Application\PresenterComponent
{
	/** @var callback */
	private $handler;

	/** @var string|callback */
	private $link = NULL;



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
	 * @return BaseButton
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
	 * @param string security token
	 * @param mixed primary key
	 */
	public function handleClick($token, $uniqueId = NULL)
	{
		$grid = $this->getGrid();

		if ($token !== $this->getGrid()->getSecurityToken()) {
			throw new Nette\Application\ForbiddenRequestException("Security token does not match. Possible CSRF attack.");
		}

		if ($uniqueId === NULL) {
			call_user_func($this->handler);
		} else {
			call_user_func($this->handler, $grid->getModel()->getItemByUniqueId($uniqueId));
		}

		if ($this->getPresenter()->isAjax()) {
			$this->getGrid()->invalidateControl();
		} else {
			$this->getGrid()->redirect("this");
		}
	}



	/**
	 * Set link URL
	 * @param string|callback link
	 * @return BaseButton
	 */
	public function setLink($link)
	{
		$this->link = $link;
		return $this;
	}



	/**
	 * Get button link
	 * @param mixed row
	 * @return string
	 */
	protected function getLink($row = NULL)
	{
		// custom link
		if (isset($this->link)) {
			if (is_callable($this->link)) {
				return call_user_func($this->link, $row);
			} else {
				return $this->link;
			}
		}

		// link to click signal
		$grid = $this->getGrid();

		return $this->link('click!', array(
			'token' => $grid->getSecurityToken(),
			'uniqueId' => $row === NULL ? NULL : $grid->getModel()->getUniqueId($row),
		));
	}



	/**
	 * Is button visible
	 * @param mixed row
	 * @return bool
	 */
	public function isVisible($row = NULL)
	{
		return is_bool($this->visible) ? $this->visible : call_user_func($this->visible, $row);
	}



	/**
	 * Set visible
	 * @param bool|callback visible
	 * @return BaseButton
	 */
	public function setVisible($visible)
	{
		if (!is_bool($visible) && !is_callable($visible)) {
			throw new \InvalidArgumentException("Argument should be callable or boolean.");
		}

		$this->visible = $visible;
		return $this;
	}



	public function render()
	{
		$el = Nette\Web\Html::el('a');
		$el->link = $this->getLink($row);
		
	}

}