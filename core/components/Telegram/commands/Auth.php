<?php

	namespace components\Telegram\commands;


	use components\Telegram\classes\tables\TelegramUsers;
	use components\Telegram\model\AbstractBotSlashCommand;
	use Exception;
	use model\main\Utilities;
	use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
	use TelegramBot\Api\Types\Message;
	use Traineratwot\Cache\Cache;
	use Traineratwot\config\Config;

	class Auth extends AbstractBotSlashCommand
	{
		public $StartCommands
			= [
				'auth',
			];

		public $description = "Авторизует в сервисе";


		public function run(int $id, Message $message)
		{
			try {
				$salt = Utilities::id(8);
				$u    = $this->core->getObject(TelegramUsers::class, ['chat_id' => $id])->getUser();
				if (!$u->isNew()) {
					Cache::setCache('auth_' . $salt, $u->getID(), 60, 'telegram/auth');
					$keyboard = new InlineKeyboardMarkup(
						[
							[
								[
									'text' => 'Перейти в ' . Config::get('DOMAIN_URL'),
									'url'  => Config::get('DOMAIN_URL') . "/telegram/auth?auth=" . $salt,
								],
							],
						]
					);
					$this->sendMessage($id, 'Перейти', $keyboard);
				} else {
					$this->sendMessage($id, 'Сначала нужно авторизоваться в боте командой:  /login');
				}

			} catch (Exception $e) {
				$this->sendMessage($id, 'error');
			}
		}
	}

	return Auth::class;