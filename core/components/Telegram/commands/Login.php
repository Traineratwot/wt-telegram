<?php

	namespace components\Telegram\commands;


	use components\Telegram\model\AbstractBotCommand;
	use components\Telegram\model\AbstractBotSlashCommand;
	use Exception;
	use TelegramBot\Api\Types\Message;
	use Traineratwot\config\Config;

	class Login extends AbstractBotSlashCommand
	{
		public $StartCommands
			= [
				'login',
			];

		public $description = "{email} {password} регистрирует/авторизует пользователя";

		/**
		 * @throws Exception
		 */
		public function run(int $id, Message $message)
		{
			$this->sendMessage($id, 'Перейдите по ссылке для авторизации');
			$url = Config::get('DOMAIN_URL') . '/telegram/login?id=' . $id;
			$this->sendMessage($id, '<a href="' . $url . '">Вход</a>');
			return FALSE;
		}
	}

	return Login::class;