<?php
	/**
	 * Created by Kirill Nefediev.
	 * User: Traineratwot
	 * Date: 15.11.2022
	 * Time: 13:26
	 */

	namespace components\Telegram\model;

	abstract class AbstractBotSlashCommand extends AbstractBotCommand
	{
		public $type = self::TYPE_SLASH;
	}