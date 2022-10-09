<?php

	namespace components\Telegram\model;

	use TelegramBot\Api\Types\Document;
	use TelegramBot\Api\Types\message;

	abstract class __AbstractBotCommand
	{
		public const TYPE_COMMAND   = '🔸 ';
		public const TYPE_SLASH     = '🔹 /';
		public const TYPE_FILE      = '📄 ';
		public const TYPE_SUBSCRIBE = '✉ ';
		public array  $StartCommands
								   = [
				'test',
			];
		public string $description = "";
		/**
		 * @var TelegramController
		 */
		public TelegramController $scope;
		public string             $type        = self::TYPE_COMMAND;
		public string             $mainCommand = '';

		public function __construct(TelegramController $scope)
		{
			$this->scope = $scope;
		}

		public function getDescription()
		{
			if (!empty($this->description)) {
				$txt = '<b>' . $this->StartCommands[0] . '</b> — ' . $this->description;
			} else {
				$txt = '<b>' . $this->StartCommands[0] . '</b>';
			}
			return $this->type . $txt;
		}

		function run($command, $args, $id, $isQuiz = FALSE, Message $message = NULL)
		{

		}

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
			$url1  = "https://api.telegram.org/bot" . BOT_TOKEN . "/getFile?file_id=" . $document->getFileId();
			$resp1 = json_decode(file_get_contents($url1), 1);
			if ($resp1['ok'] && isset($resp1['result']['file_path'])) {
				$url2  = "https://api.telegram.org/file/bot" . BOT_TOKEN . "/" . $resp1['result']['file_path'];
				$resp2 = file_get_contents($url2);
				if ($resp2 !== '') {
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
			if (!$this->progress_id && $id) {
				$a                 = $this->scope->sendMessage($id, strtr($this->progress_msg, ['{p}' => 0]));
				$this->progress_id = $a->getMessageId();
			}
			if ($id && $this->progress_id) {
				$this->oldPercent = $this->percent;
				$this->percent    = floor(($now / $total) * 100);
				if (($this->oldPercent !== $this->percent) && $this->percent % $this->progress_case_step === 0) {
					$progress_msg = strtr($this->progress_msg, ['{p}' => $this->percent]);
					$this->scope->editMessageText($id, $this->progress_id, $progress_msg);
				}
				if ($this->percent >= 100) {
					$this->progress_id = NULL;
				}
			}
		}
	}