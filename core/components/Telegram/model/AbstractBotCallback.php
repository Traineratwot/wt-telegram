<?php

	namespace components\Telegram\model;

	use TelegramBot\Api\Types\message;

	abstract class AbstractBotCallback
	{

		public $fn = 'test';
		/**
		 * @var TelegramController
		 */
		public $scope = "";

		public function __construct(TelegramController $scope)
		{
			$this->scope = $scope;
		}

		public function getDescription()
		{
			return NULL;
		}

		abstract function run(Message $message, $args, $id);

		public function getStartCommands()
		{
			return [$this->fn];
		}
	}