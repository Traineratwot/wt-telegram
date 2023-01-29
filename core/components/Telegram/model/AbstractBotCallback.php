<?php
	/**
	 * Created by Kirill Nefediev.
	 * User: Traineratwot
	 * Date: 15.11.2022
	 * Time: 13:26
	 */

	namespace components\Telegram\model;

	use components\Telegram\classes\tables\TelegramUsers;
	use TelegramBot\Api\Types\message;

	abstract class AbstractBotCallback extends AbstractBotCommand
	{

		public function process(int $id, Message $message, $args = [])
		{
			/** @var TelegramUsers $user */
			$user = $this->core->getObject(TelegramUsers::class, ['chat_id' => $id]);
			if ($user && !$user->isNew()) {
				$this->user = $user->getUser();
				$this->core->auth($this->user);
			}
			$this->message = $message;
			$this->args = $args;
			$this->run($id, $message,$args);

		}

		public function getDescription()
		{
			return NULL;
		}

		abstract public function run(int $id, Message $message);

		public function getStartCommands()
		{
			return $this->StartCommands;
		}
	}