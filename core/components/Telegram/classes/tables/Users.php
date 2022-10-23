<?php

	namespace components\Telegram\classes\tables;

	use model\components\ComponentTable;
	use model\main\Core;
	use Traineratwot\PDOExtended\tableInfo\PDOENewDbObject;

	/**
	 * Класс для работы с таблицей `users`
	 * вызывается Core::getObject(Users::class)
	 */
	class Users extends ComponentTable
	{
		public $table      = 'users';
		public $primaryKey = 'id';

		public static function createTable()
		: PDOENewDbObject|false
		{
			$core = Core::init();
			if (!$core->db->table('users')->scheme->columnExists('telegram_chat_id')) {
				$core->db->table('users')->alter()->addCol('telegram_chat_id', 'INT')->run();
			}
			return FALSE;
		}
	}