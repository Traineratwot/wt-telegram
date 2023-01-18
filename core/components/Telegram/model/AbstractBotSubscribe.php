<?php
	/**
	 * Created by Kirill Nefediev.
	 * User: Traineratwot
	 * Date: 15.11.2022
	 * Time: 13:26
	 */

	namespace components\Telegram\model;

	use model\main\Core;
	use model\main\CoreObject;
	use PDO;

	abstract class AbstractBotSubscribe extends CoreObject
	{
		public $table      = 'subscribe';
		public $primaryKey = 'id';
		public $id         = 0;
		public $subscribe  = NULL;

		public function __construct(Core $core)
		{
			parent::__construct($core);
			$this->telegram = new TelegramController();
			$this->id       = (int)$this->id;
			if ($this->id) {
				$this->subscribe = $this->core->getObject('subscribe', $this->id);
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