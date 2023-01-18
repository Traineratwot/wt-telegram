<?php

	namespace components\Telegram\commands;


	use components\Telegram\model\AbstractBotSlashCommand;
	use TelegramBot\Api\Exception;
	use TelegramBot\Api\InvalidArgumentException;
	use TelegramBot\Api\Types\Message;

	class Start extends AbstractBotSlashCommand
	{
		public $StartCommands
			= [
				'start',
				'help',
				'?',
			];

		/**
		 * @throws Exception
		 * @throws InvalidArgumentException
		 */
		function run(int $id,Message $message)
		{
			foreach ($this->scope->help as $key => $value) {
				if (empty($value)) {
					unset($this->scope->help[$key]);
				}
			}
			rsort($this->scope->help);
			$help = implode("\n", $this->scope->help);
			$this->scope->sendMessage($id, $help);
		}
	}

	return Start::class;