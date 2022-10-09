<?php

	namespace components\Telegram\model;

	use Exception;
	use model\main\Core;
	use model\main\CoreObject;
	use PDO;
	use tables\Subscribe;

	abstract class AbstractBotSubscribe extends CoreObject
	{
		public string              $table      = 'subscribe';
		public string              $primaryKey = 'id';
		public int                 $id         = 0;
		public mixed               $subscribe  = NULL;
		private TelegramController $telegram;

		/**
		 * @throws Exception
		 */
		public function __construct(Core $core)
		{
			parent::__construct($core);
			$this->telegram = new TelegramController($core);
			$this->id       = (int)$this->id;
			if ($this->id) {
				$this->subscribe = $this->core->getObject(Subscribe::class, $this->id);
			}
		}

		public function sendMessage($text, $replyMarkup = NULL, $clearKeyboard = FALSE, $parseMode = 'HTML', $disablePreview = FALSE, $replyToMessageId = NULL, $disableNotification = FALSE)
		{
			$userIds = $this->getUserIds();
			$return  = [];
			foreach ($userIds as $id) {
				$user = $this->core->getUser($id);
				if ($user) {
					$chat = $user->get('telegram_chat_id');
					if ($chat) {
						$return[] = $this->telegram->sendMessage($chat, $text, $replyMarkup, $clearKeyboard, $parseMode, $disablePreview, $replyToMessageId, $disableNotification);
					}
				}
			}
			return $return;
		}

		public function getUserIds()
		{
			$id = (int)$this->id;
			if ($id) {
				$q = $this->core->db->query("SELECT `userID` FROM `subscribe_user` WHERE `subscribeID` = $id");
				if ($q) {
					return $q->fetchAll(PDO::FETCH_COLUMN);
				}
			}
			return [];
		}
	}