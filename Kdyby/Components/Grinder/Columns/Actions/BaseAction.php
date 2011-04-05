<?php

namespace Kdyby\Components\Grinder;

use Nette;



/**
 * Action button
 *
 * @author Filip Procházka
 * @author Jan Marek
 * @license MIT
 */
class BaseAction extends Nette\Application\PresenterComponent
{

	/** @var string */
	private $caption;

	/** @var string|callback|null */
	private $confirmationQuestion = NULL;



	/**
	 * Get caption
	 * @return string
	 */
	public function getCaption()
	{
		return $this->caption;
	}



	/**
	 * Set caption
	 * @param string caption
	 * @return BaseButton
	 */
	public function setCaption($caption)
	{
		$this->caption = $caption;
		return $this;
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



	/**
	 * @return Grid
	 */
	public function getGrid()
	{
		return $this->lookup('Kdyby\Components\Grinder\Grid');
	}
	
	

	/**
	 * Is ajax?
	 * @return bool
	 */
	public function isAjax()
	{
		return $this->ajax;
	}



	/**
	 * Set ajax mode
	 * @param bool ajax
	 * @return Button
	 */
	public function setAjax($ajax)
	{
		$this->ajax = (bool) $ajax;
		return $this;
	}



	/**
	 * Get confirmation question
	 * @param mixed row
	 * @return string|callback|null
	 */
	public function getConfirmationQuestion($row)
	{
		if (is_callable($this->confirmationQuestion)) {
			return call_user_func($this->confirmationQuestion, $row);
		} else {
			return $this->confirmationQuestion;
		}
	}



	/**
	 * Set confirmation question
	 * @param string|callback|null confirmation question
	 * @return Button
	 */
	public function setConfirmationQuestion($confirmationQuestion)
	{
		$this->confirmationQuestion = $confirmationQuestion;
		return $this;
	}

	

	/**
	 * Handle click signal
	 * @param string security token
	 * @param mixed primary key
	 */
	public function handleClick($token, $uniqueId = null)
	{
		parent::handleClick($token, $uniqueId);

		if ($this->getPresenter()->isAjax()) {
			$this->getGrid()->invalidateControl();
		} else {
			$this->getGrid()->redirect("this");
		}
	}



	/**
	 * Create button element
	 * @param mixed row
	 * @return Nette\Web\Html
	 */
	protected function createButton($row = null)
	{
		$el = parent::createButton($row);
		$el->class[] = $this->isAjax() ? $this->getGrid()->getAjaxClass() : null;
		$el->data("grinder-question", $this->getConfirmationQuestion($row));
		
		return $el;
	}

}