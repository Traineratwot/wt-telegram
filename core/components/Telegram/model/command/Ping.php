<?php

	namespace components\Telegram\model\command;


	use components\Telegram\model\AbstractBotCommand;
	use components\Telegram\model\TelegramController;
	use TelegramBot\Api\Types\Message;

	class Ping extends AbstractBotCommand
	{
		public array $StartCommands
			= [
				'ping',
				'pong',
			];

		/**
		 * @var TelegramController
		 */
		public TelegramController $scope;

		public string $description = "возвращает pong :)";


		public function run($command, $args, $id, $isQuiz = FALSE, Message $message = NULL)
		{
			if ($command === 'ping') {
				$this->scope->sendMessage($id, 'pong' . implode($args), NULL, TRUE);
			} else {
				$this->scope->sendMessage($id, 'ping' . implode($args), NULL, TRUE);
			}
		}
	}

	return Ping::class;