<?php

	namespace components\Telegram;

	use components\Telegram\classes\tables\Subscribe;
	use components\Telegram\classes\tables\SubscribeUser;
	use components\Telegram\classes\tables\Users;
	use model\components\Manifest;

	class Telegram extends Manifest
	{
		public static function description()
		: string
		{
			return '';
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
				Users::class,
				Subscribe::class,
				SubscribeUser::class,
			];
		}
	}