<?php

	namespace components\Telegram\classes\ajax;

	use components\Telegram\model\TelegramController;
	use Exception;
	use model\main\Err;
	use model\page\Ajax;

	class Webhook extends Ajax
	{
		public function process()
		{
			try {
				$tl = new TelegramController($this->core);
				if($this->PUT) {
					$tl->initialize($this->PUT);
				}
			} catch (Exception $e) {
				Err::fatal($e->getMessage(), NULL, NULL, $e);
			}
		}
	}

	return Webhook::class;