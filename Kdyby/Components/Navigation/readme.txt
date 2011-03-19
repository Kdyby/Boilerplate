Navigation
==========

Control pro Nette Framework usnadňující tvorbu menu a drobečkové navigace

Autor: Jan Marek
Licence: MIT

Použití
-------

Továrnička v presenteru:

	protected function createComponentNavigation($name) {
		$nav = new Navigation($this, $name);
		$nav->setupHomepage("Úvod", $this->link("Homepage:"));
		$sec = $nav->add("Sekce", $this->link("Category:", array("id" => 1)));
		$article = $sec->add("Článek", $this->link("Article:", array("id" => 1)));
		$nav->setCurrent($article);
	}


Menu v šabloně:

	{widget navigation}


Drobečková navigace v šabloně:

	{widget navigation:breadcrumbs}