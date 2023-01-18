<?php

	namespace components\Telegram;

	use components\Telegram\classes\tables\TelegramHistory;
	use components\Telegram\classes\tables\TelegramSubscribes;
	use components\Telegram\classes\tables\TelegramUsers;
	use components\Telegram\classes\tables\TelegramUserSubscribe;
	use model\components\Manifest;

	class Telegram extends Manifest
	{
		public static function description()
		: string
		{
			return '';
		}

		public function afterInstall()
		{
			//TODO CREATE folders
		}

		public static function getComposerPackage()
		: array
		{
			return [
				'telegram-bot/api',
			];
		}

		public static function getTables()
		: array
		{
			return [
				TelegramHistory::class,
				TelegramSubscribes::class,
				TelegramUserSubscribe::class,
				TelegramUsers::class,
			];
		}
	}