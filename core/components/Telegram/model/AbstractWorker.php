<?php

	namespace components\Telegram\model;


	use model\main\Core;

	abstract class AbstractWorker
	{

		public $telegram;
		public $work;
		public $core;
		public $percent            = 0;
		public $oldPercent         = 0;
		public $progress_case      = 0;
		public $progress_case_step = 10;

		public $progress_msg = 'Начинаю работу ...';

		public function __construct(Core $core, TelegramController $telegram, $work)
		{
			$this->core     = $core;
			$this->telegram = $telegram;
			$this->work     = $work;
		}

		public function sendMessage($text, $replyMarkup = NULL, $clearKeyboard = FALSE, $parseMode = 'HTML', $disablePreview = FALSE, $replyToMessageId = NULL, $disableNotification = FALSE)
		{
			if ($this->work['chat']) {
				return $this->telegram->sendMessage($this->work['chat'], $text, $replyMarkup = NULL, $clearKeyboard = FALSE, $parseMode = 'HTML', $disablePreview = FALSE, $replyToMessageId = NULL, $disableNotification = FALSE);
			}
		}

		/**
		 * @param mixed $work
		 */
		public function progress($total = 100, $now = 1)
		{
			$this->progrese_case = 10;
			if ($this->work['chat'] and $this->progress_id) {
				$this->oldPercent = $this->percent;
				$this->percent    = floor(($now/$total) * 100);
				if ($this->oldPercent != $this->percent) {
					if ($this->percent >= $this->progress_case) {
						if ($this->percent < ($this->progress_case + $this->progress_case_step)) {
							$progress_msg = $this->progress_msg . ' ' . $this->percent . '%';
							$this->telegram->bot->editMessageText($this->work['chat'], $this->progress_id, $progress_msg);
						}
						$this->progress_case += $this->progress_case_step;
					}
				}
			}
		}

		final public function run()
		{
			$a                 = $this->sendMessage($this->progress_msg);
			$this->progress_id = $a->getMessageId();
			$this->process();
		}

		abstract function process();
	}