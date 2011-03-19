<?php

namespace Gridito;

use Nette\Web\Html;

/**
 * Button base
 *
 * @author Jan Marek
 * @license MIT
 */
abstract class BaseButton extends \Nette\Application\PresenterComponent
{
	/** @var string */
	private $label;

	/** @var callback */
	private $handler;

	/** @var string */
	private $icon = null;

	/** @var bool|callback */
	private $visible = true;

	/** @var string|callback */
	private $link = null;
	
	/** @var bool */
	private $showText = true;
	


	/**
	 * Get label
	 * @return string
	 */
	public function getLabel()
	{
		return $this->label;
	}



	/**
	 * Set label
	 * @param string label
	 * @return BaseButton
	 */
	public function setLabel($label)
	{
		$this->label = $label;
		return $this;
	}



	/**
	 * Get jQuery UI icon
	 * @return string
	 */
	public function getIcon()
	{
		return $this->icon;
	}



	/**
	 * Set jQuery UI icon
	 * @param string icon
	 * @return BaseButton
	 */
	public function setIcon($icon)
	{
		$this->icon = $icon;
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
	protected function getLink($row = null)
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
			'uniqueId' => $row === null ? null : $grid->getModel()->getUniqueId($row),
		));
	}



	/**
	 * Is button visible
	 * @param mixed row
	 * @return bool
	 */
	public function isVisible($row = null)
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
	 * Show button text
	 * @return bool
	 */
	public function getShowText()
	{
		return $this->showText;
	}



	/**
	 * @param bool show text
	 * @return BaseButton 
	 */
	public function setShowText($showText)
	{
		$this->showText = $showText;
		return $this;
	}


	
	/**
	 * @return Grid
	 */
	public function getGrid()
	{
		return $this->getParent()->getParent();
	}



	/**
	 * Handle click signal
	 * @param string security token
	 * @param mixed primary key
	 */
	public function handleClick($token, $uniqueId = null)
	{
		$grid = $this->getGrid();

		if ($token !== $this->getGrid()->getSecurityToken()) {
			throw new \Nette\Application\ForbiddenRequestException("Security token does not match. Possible CSRF attack.");
		}

		if ($uniqueId === null) {
			call_user_func($this->handler);
		} else {
			call_user_func($this->handler, $grid->getModel()->getItemByUniqueId($uniqueId));
		}
	}



	/**
	 * Create button element
	 * @param mixed row
	 * @return Nette\Web\Html
	 */
	protected function createButton($row = null)
	{
		return Html::el("a")
			->href($this->getLink($row))
			->data("gridito-icon", $this->icon)
			->class(array("gridito-button", $this->showText ? null : "gridito-hide-text"))
			->setText($this->label);
	}

	

	/**
	 * Render button
	 * @param mixed row
	 */
	public function render($row = null)
	{
		if ($this->isVisible($row)) {
			echo $this->createButton($row);
		}
	}

}