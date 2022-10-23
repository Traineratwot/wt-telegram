<?php

	namespace components\Telegram\model\command;


	use components\Telegram\model\AbstractBotCommand;
	use components\Telegram\model\TelegramController;
	use Exception;
	use PDO;
	use tables\SubscribeUser;
	use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

	class Subscribes extends AbstractBotCommand
	{
		public array  $StartCommands
								   = [
				'подписки',
				'subscribes',
			];
		public string $description = "возвращает список подписок";
		/**
		 * @var TelegramController
		 */
		public TelegramController $scope;

		/**
		 * @throws Exception
		 */
		public function run($command, $args, $id, $isQuiz = FALSE, $message = NULL)
		{
			$core = &$this->scope->core;
			$user = $core->getUser(['telegram_chat_id' => $id]);
			if ($user->isNew()) {
				$this->scope->sendMessage($id, 'Пользователь не авторизован используйте команду для входа');
				return FALSE;
			}
			$UID = $user->getID();
			$core->auth($user);
			if (empty($args)) {
				$subscribes = $core->db->query("SELECT DISTINCT subscribe.*, subscribe_user.userID FROM  subscribe 
LEFT JOIN subscribe_user ON subscribe.id = subscribe_user.subscribeID AND subscribe_user.userID = '{$UID}'")->fetchAll(PDO::FETCH_ASSOC);
				$keyboard   = [];
				if (!count($subscribes)) {
					$this->scope->sendMessage($id, 'Список подписок пуст');
					return TRUE;
				}
				foreach ($subscribes as $subscribe) {
					if ((int)$subscribe['userID']) {
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
							'text'          => '✔ ' . $subscribe['name'] . ' - ' . $subscribe['comment'],
							'callback_data' => json_encode(
								  [
									  'fn' => 'subscribes', 'args' => ['add', $subscribe['id']],
								  ]
								, 256),
						];
					}
				}
				$keyboard = new InlineKeyboardMarkup($keyboard);
				$this->scope->sendMessage($id, 'Список', $keyboard);
				return TRUE;
			}
			if ($args[0] === 'remove') {
				$SID = (int)$args[1];
				if ($SID) {
					$sub = $core->getObject(SubscribeUser::class, ['userID' => $UID, 'subscribeID' => $SID]);
					if (!$sub->isNew()) {
						$sub->remove();
						$this->scope->sendMessage($id, '✔ вы отписаны');
						return TRUE;
					}
				}
			}
			if ($args[0] === 'add') {
				$SID = (int)$args[1];
				if ($SID) {
					$sub = $core->getObject(SubscribeUser::class, ['userID' => $UID, 'subscribeID' => $SID]);
					if ($sub->isNew()) {
						$sub->set('userID', $UID);
						$sub->set('subscribeID', $SID);
						$sub->save();
						$this->scope->sendMessage($id, '✔ вы подаисаны');
						return TRUE;
					}
				}
			}
			$this->scope->sendMessage($id, '❌ действия не требуются');
			return TRUE;
		}
	}

	return Subscribes::class;