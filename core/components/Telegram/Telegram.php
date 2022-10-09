<?php

	namespace components\Telegram;

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
				'telegram-bot/api'
			];
		}
	}