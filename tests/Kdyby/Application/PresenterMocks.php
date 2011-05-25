<?php

namespace App\Front {
	class HomepagePresenter extends \Kdyby\Application\UI\Presenter { }
	abstract class AbstractPresenter extends \Kdyby\Application\UI\Presenter { }
	class FakePresenter { }
}

namespace App\Front\Forum {
	class HomepagePresenter extends \Kdyby\Application\UI\Presenter { }
}

namespace Foo\Back {
	class HomepagePresenter extends \Kdyby\Application\UI\Presenter { }
}

namespace Foo\Back\Forum {
	class HomepagePresenter extends \Kdyby\Application\UI\Presenter { }
}

namespace Kdyby\Module\Admin {
	class DashboardPresenter extends \Kdyby\Application\UI\Presenter { }
	class ListPresenter extends \Kdyby\Application\UI\Presenter { }
	class MyAnotherPresenter { }
}

namespace Kdyby\Module\Admin\Articles {
	class ListPresenter extends \Kdyby\Application\UI\Presenter { }
}
