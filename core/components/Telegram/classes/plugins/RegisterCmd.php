<?php

	namespace components\Telegram\classes\plugins;

	use components\Telegram\classes\cli\MakeTelegram;
	use model\Events\Plugin;
	use Traineratwot\PhpCli\CLI;

	class RegisterCmd extends Plugin implements \core\model\Events\plugins\RegisterCmd
	{
		public function process(CLI $cli)
		{
			$cli->registerCmd('makeTelegram', new MakeTelegram());
		}
	}

	return RegisterCmd::class;