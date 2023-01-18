<?php
	/**
	 * Created by Kirill Nefediev.
	 * User: Traineratwot
	 * Date: 15.11.2022
	 * Time: 13:26
	 */

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