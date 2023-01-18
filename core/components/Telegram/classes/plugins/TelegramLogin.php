<?php

	namespace components\Telegram\classes\plugins;

	use components\Telegram\classes\tables\TelegramUsers;
	use components\Telegram\model\TelegramController;
	use model\Events\Plugin;
	use model\main\Core;
	use TelegramBot\Api\Exception;
	use TelegramBot\Api\InvalidArgumentException;

	class TelegramLogin extends Plugin
	{
		/**
		 * @throws Exception
		 * @throws InvalidArgumentException
		 */
		public function process(Core $core)
		{
			if (isset($_COOKIE['telegram_chat_id'])) {
				$chat_id = (int)$_COOKIE['telegram_chat_id'];
				if ($chat_id && $core->isAuthenticated) {
					$user_id = $core->user->getID();
					$t       = $core->getObject(TelegramUsers::class, ['user_id' => $user_id]);
					$t->set('user_id', $user_id);
					$t->set('chat_id', $chat_id);
					$t->save();
					if (!$t->isNew()) {
						(new TelegramController($core))->sendMessage($chat_id, 'вы авторизированны');
					}
					setcookie('telegram_chat_id', NULL, 1, '/');
				}
			}
		}
	}

	return TelegramLogin::class;