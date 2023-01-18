<?php

	namespace components\Telegram\classes\tables;

	use model\components\ComponentTable;
	use Traineratwot\PDOExtended\PDOE;
	use Traineratwot\PDOExtended\tableInfo\PDOENewDbObject;

	/**
	 * Класс для работы с таблицей `telegram_users`
	 * вызывается Core::getObject(TelegramUsers::class)
	 */
	class TelegramUsers extends ComponentTable
	{
		public $table      = 'telegram_users';
		public $primaryKey = 'id';

		public function getUser()
		{
			return $this->core->getUser((int)$this->get('user_id'));
		}

		public static function createTable()
		: PDOENewDbObject|false
		{
			return PDOE::createTable('telegram_users')
					   ->addInt('id')
					   ->setPrimaryKey('id')
					   ->addInt('chat_id')
					   ->addInt('user_id')
					   ->addUniqueKey(['chat_id', 'user_id'])
			;
		}
	}