<?php

	namespace components\Telegram\classes\plugins;

	use components\Telegram\classes\tables\TelegramUsers;
	use components\Telegram\model\TelegramController;
	use model\Events\Event;
	use model\Events\Plugin;

	class AfterAppInit extends Plugin implements \core\model\Events\plugins\AfterAppInit
	{
		public function process($core)
		{
			Event::emit('TelegramLogin',null, $core);

		}
	}

	return AfterAppInit::class;