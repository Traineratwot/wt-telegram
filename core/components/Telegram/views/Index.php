<?php

	namespace components\Telegram\views;

	use components\Telegram\model\TelegramController;
	use Exception;
	use model\main\Utilities;
	use model\page\Page;
	use Traineratwot\config\Config;

	class Index extends Page
	{
		public $title = 'Index';


		public function beforeRender()
		{
			try {
				$this->setVar('BOT_NAME', Config::get('NAME', 'telegram'));
				$telegram = new TelegramController($this->core);
				$input = file_get_contents('PHP://input');
				$input = Utilities::jsonValidate($input);
				if (!empty($input)) {
					if (ob_get_length()) {
						ob_end_clean();
					}
					ob_start();
					$telegram->initialize($input);
					die(ob_get_clean());
				}


			} catch (Exception $e) {
			}

		}
	}

	return Index::class;