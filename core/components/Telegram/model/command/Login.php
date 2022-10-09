<?php

	namespace components\Telegram\model\command;


	use components\Telegram\model\AbstractBotCommand;
	use components\Telegram\model\TelegramController;
	use Exception;
	use model\main\Core;
	use TelegramBot\Api\Types\Message;

	class Login extends AbstractBotCommand
	{
		public array $StartCommands
			= [
				'вход',
				'login',
				'я',
				'пароль',
			];

		public string $description = "{email} {password} регистрирует/авторизует пользователя";

		/**
		 * @var TelegramController
		 */
		public TelegramController $scope;


		/**
		 * @throws Exception
		 */
		public function run($command, $args, $id, $isQuiz = FALSE, Message $message = NULL)
		{
			$core = Core::init();

			if (empty($args[0])) {
				$this->scope->sendMessage($id, 'введите почту');
				return FALSE;
			}
			$u = $core->getUser(['email' => $args[0]]);
			if (empty($args[1])) {
				$this->scope->sendMessage($id, 'вы не ввели пароль');
				return FALSE;
			}
			$password = $args[1];
			if ($u->verifyPassword($password)) {
				$u->set('telegram_chat_id', $id);
				$u->save();
				$this->scope->sendMessage($id, 'ok');
			} else {
				$this->scope->sendMessage($id, 'НЕ правильный пароль');
			}
		}
	}

	return Login::class;