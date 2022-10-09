<?php
	namespace components\Telegram\model;
	use Exception;

	abstract class Quiz
	{
		/**
		 * @var array
		 */
		public ?array $chat;
		public        $history;
		/**
		 * @var TelegramController
		 */
		public $scope;

		/**
		 * Quiz constructor.
		 * @param TelegramController $scope
		 * @param                    $id
		 * @param null               $chat
		 * @param null               $history
		 * @param AbstractBotCommand $command
		 * @throws \Exception
		 */
		public function __construct(TelegramController $scope, $id, $chat = NULL, $history = NULL, $command = NULL)
		{

			$this->scope = &$scope;
			$this->id = $id;
			$this->chat = $chat;
			$this->history = $history;
			$this->command = $command;
			$this->name = get_class($this);
			$this->getChat();
			if (!empty($this->chat['quiz']['name']) and $this->chat['quiz']['name'] != $this->name) {
				throw new Exception('some wrong');
			}
			$this->step = $this->chat['quiz']['step'] ?: 0;
			$this->chat['quiz']['name'] = $this->name;
		}

		/**
		 * @param int $step
		 */
		public function step(int $step = 0)
		{
			if ($step) {
				$this->step = $step;
			} else {
				$this->step++;
			}
			$this->chat['quiz']['step'] = $this->step;
			$this->saveChat();
		}

		public function getChat()
		{
			if (empty($this->chat)) {
				$file = WT_CACHE_PATH . 'chats/' . $this->id . '.json';
				if (file_exists($file) and empty($this->chat)) {
					$this->chat = json_decode(file_get_contents($file), 1);
				}
			}
			if (empty($this->history)) {
				$this->history = new History($this->id);
			}
		}

		public function end()
		{
			$this->chat['quiz'] = [];
			$this->scope->removeKeyboard($this->id, 'ok', NULL, FALSE);
			$this->saveChat();
		}

		public function saveChat()
		{

			$file = WT_CACHE_PATH . 'chats/' . $this->id . '.json';
			if (!mkdir($concurrentDirectory = dirname($file), 0777, 1) && !is_dir($concurrentDirectory)) {
				return FALSE;
			}
			file_put_contents($file, json_encode($this->chat, 256));
		}

		abstract public function call($step, $command, $args, $id);
	}
