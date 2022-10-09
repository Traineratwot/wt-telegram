<?php

	namespace components\Telegram\model;

	abstract class AbstractBotSlashCommand extends AbstractBotCommand
	{
		public string $type = self::TYPE_SLASH;
	}