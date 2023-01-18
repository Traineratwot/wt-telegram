<?php

	namespace components\Telegram\commands;


	use components\Telegram\model\AbstractBotChatCommand;
	use TelegramBot\Api\Exception;
	use TelegramBot\Api\InvalidArgumentException;
	use TelegramBot\Api\Types\Message;

	class Ping extends AbstractBotChatCommand
	{
		public $StartCommands
			= [
				'ping',
				'pong',
			];


		public $description = "возвращает pong :)";


		/**
		 * @throws InvalidArgumentException
		 * @throws Exception
		 */
		public function run($id, Message $message = NULL)
		{
			if ($this->command === 'ping') {
				$this->sendMessage($id, 'pong' . implode($this->args), NULL, TRUE);
			} else {
				$this->sendMessage($id, 'ping' . implode($this->args), NULL, TRUE);
			}
		}
	}

	return Ping::class;