<?php

	namespace components\Telegram\model;

	use components\Telegram\classes\tables\TelegramUsers;
	use Exception;
	use model\main\Core;
	use model\main\User;
	use TelegramBot\Api\InvalidArgumentException;
	use TelegramBot\Api\Types\Document;
	use TelegramBot\Api\Types\Message;

	abstract class AbstractBotCommand
	{
		const TYPE_COMMAND   = '🔸 ';
		const TYPE_SLASH     = '🔹 /';
		const TYPE_FILE      = '📄 ';
		const TYPE_SUBSCRIBE = '✉ ';
		public                    $StartCommands
											   = [
				'test',
			];
		public                    $description = "";
		public TelegramController $scope;
		public                    $type        = self::TYPE_COMMAND;
		public                    $mainCommand = '';
		public                    $command     = '';
		public                    $args        = [];
		public User|null          $user        = NULL;
		public Message            $message;
		public Core               $core;

		public function __construct(TelegramController $scope)
		{
			$this->scope   = $scope;
			$this->core    = $scope->core;
			$this->history = $scope->history;
		}

		/**
		 * @throws \TelegramBot\Api\Exception
		 * @throws InvalidArgumentException
		 * @throws Exception
		 */
		public function sendMessage(
			int          $id,
			string|array $text,
						 $replyMarkup = NULL,
						 $clearKeyboard = FALSE,
						 $parseMode = 'HTML',
						 $disablePreview = FALSE,
						 $replyToMessageId = NULL,
						 $disableNotification = FALSE
		)
		: Message
		{
			return $this->scope->sendMessage($id, $text, $replyMarkup, $clearKeyboard, $parseMode, $disablePreview, $replyToMessageId, $disableNotification);
		}


		public function editMessageText(
			int    $chatId,
			int    $messageId,
			string $text,
				   $parseMode = 'HTML',
				   $disablePreview = FALSE,
				   $replyMarkup = NULL,
				   $inlineMessageId = NULL
		)
		: Message
		{
			return $this->scope->editMessageText($chatId, $messageId, $text, $parseMode, $disablePreview, $replyMarkup, $inlineMessageId);
		}

		public function getDescription()
		{
			if (!empty($this->description)) {
				$txt = '<b>' . $this->StartCommands[0] . '</b> ' . $this->description;
			} else {
				$txt = '<b>' . $this->StartCommands[0] . '</b>';
			}
			return $this->type . $txt;
		}

		/**
		 * @throws Exception
		 */
		public function process(int $id, Message $message, $args = [])
		{
			/** @var TelegramUsers $user */
			$user = $this->core->getObject(TelegramUsers::class, ['chat_id' => $id]);
			if ($user && !$user->isNew()) {
				$this->user = $user->getUser();
				$this->core->auth($this->user);
			}
			$this->message = $message;
			$this->args    = explode(' ', $message->getText());
			if (!empty($this->args)) {
				$this->command = mb_strtolower(array_shift($this->args));
			}
			$this->args += $args;
			$this->run($id, $message);

		}

		abstract protected function run(int $id, Message $message);

		public function getStartCommands()
		{
			return array_unique($this->StartCommands);
		}

		public function getStartCommand()
		{
			return $this->mainCommand ?: $this->StartCommands[0];
		}

		public function download(Document $document, $output = FALSE)
		{
			global $core;
			$url1  = "https://api.telegram.org/bot" . $this->scope->token . "/getFile?file_id=" . $document->getFileId();
			$resp1 = json_decode(file_get_contents($url1), 1);
			if ($resp1['ok'] and isset($resp1['result']['file_path'])) {
				$url2  = "https://api.telegram.org/file/bot" . $this->scope->token . "/" . $resp1['result']['file_path'];
				$resp2 = file_get_contents($url2);
				if ($resp2 != '') {
					if ($output) {
						file_put_contents($output, $resp2);
						if (file_exists($output)) {
							$size = filesize($output);
							$core->db->query(
								<<<SQL
insert into files (`name`,`link`,`size`) values ("$output", "", $size)
SQL
							);
							return $resp2;
						}
					} else {
						return $resp2;
					}
				}
			}
			return FALSE;
		}


		public $progress_id        = 0;
		public $progress_msg       = 'Начинаю работу {p}%';
		public $percent            = 0;
		public $oldPercent         = 0;
		public $progress_case_step = 10;

		public function progress($id = 0, $total = 100, $now = 1)
		{
			$this->progrese_case = 10;
			if (!$this->progress_id and $id) {
				$a                 = $this->scope->sendMessage($id, strtr($this->progress_msg, ['{p}' => 0]));
				$this->progress_id = $a->getMessageId();
			}
			if ($id and $this->progress_id) {
				$this->oldPercent = $this->percent;
				$this->percent    = floor(($now / $total) * 100);
				if (($this->oldPercent != $this->percent) && $this->percent % $this->progress_case_step == 0) {
					$progress_msg = strtr($this->progress_msg, ['{p}' => $this->percent]);
					$this->scope->editMessageText($id, $this->progress_id, $progress_msg);
				}
				if ($this->percent >= 100) {
					$this->progress_id = NULL;
				}
			}
		}
	}

	