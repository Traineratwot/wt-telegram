<?php

	namespace components\Telegram\commands;


	use components\Telegram\classes\tables\TelegramUserSubscribe;
	use components\Telegram\model\AbstractBotChatCommand;
	use Exception;
	use PDO;
	use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
	use TelegramBot\Api\Types\Message;

	class Subscribes extends AbstractBotChatCommand
	{
		public $StartCommands
							= [
				'подписки',
				'subscribes',
			];
		public $description = "возвращает список подписок";

		/**
		 * @throws Exception
		 */
		public function run(int $id, Message $message)
		{

			if (!$this->user) {
				$this->sendMessage($id, 'Пользователь не авторизован используйте команду для входа');
				return FALSE;
			}
			$UID = $this->user->getID();
			if (empty($this->args)) {
				$subscribes = $this->core->db->query(<<<SQL
SELECT DISTINCT telegram_subscribes.*, telegram_user_subscribe.user_id FROM  telegram_subscribes
LEFT JOIN telegram_user_subscribe ON telegram_subscribes.id = telegram_user_subscribe.subscribe_id AND telegram_user_subscribe.user_id = '{$UID}'
SQL
				)->fetchAll(PDO::FETCH_ASSOC);

				$keyboard = [];
				if(empty($subscribes)){
					$this->sendMessage($id, 'нет доступных подписок');
				}else {
					foreach ($subscribes as $subscribe) {
						if ((int)$subscribe['user_id']) {
							$keyboard[][0] = [
								'text'          => '❌ ' . $subscribe['name'] . ' - ' . $subscribe['comment'],
								'callback_data' => json_encode(
									  [
										  'fn' => 'subscribes', 'args' => ['remove', $subscribe['id']],
									  ]
									, 256),
							];
						} else {
							$keyboard[][0] = [
								'text'          => '✅ ' . $subscribe['name'] . ' - ' . $subscribe['comment'],
								'callback_data' => json_encode(
									  [
										  'fn' => 'subscribes', 'args' => ['add', $subscribe['id']],
									  ]
									, 256),
							];
						}
					}
					$keyboard = new InlineKeyboardMarkup($keyboard);
					$this->sendMessage($id, 'Список', $keyboard);
				}
				return TRUE;
			}
			if ($this->args[0] === 'remove') {
				$SID = (int)$this->args[1];
				if ($SID) {
					$sub = $this->core->getObject(TelegramUserSubscribe::class, ['user_id' => $UID, 'subscribe_id' => $SID]);
					if (!$sub->isNew()) {
						$sub->remove();
						$this->sendMessage($id, '✔ вы отписаны');
						return TRUE;
					}
				}
			}
			if ($this->args[0] === 'add') {
				$SID = (int)$this->args[1];
				if ($SID) {
					$sub = $this->core->getObject(TelegramUserSubscribe::class, ['user_id' => $UID, 'subscribe_id' => $SID]);
					if ($sub->isNew()) {
						$sub->set('user_id', $UID);
						$sub->set('subscribe_id', $SID);
						$sub->save();
						$this->sendMessage($id, '✔ вы подписаны');
						return TRUE;
					}
				}
			}
			$this->sendMessage($id, '❌ действия не требуются');
			return TRUE;
		}
	}

	return Subscribes::class;