<?php

namespace {
	class HomepagePresenter extends \Kdyby\Application\UI\Presenter { }
	abstract class AbstractPresenter extends \Kdyby\Application\UI\Presenter { }
	class FakePresenter { }
}

namespace FrontModule {
	class HomepagePresenter extends \Kdyby\Application\UI\Presenter { }
}

namespace FrontModule\ForumModule {
	class HomepagePresenter extends \Kdyby\Application\UI\Presenter { }
}
