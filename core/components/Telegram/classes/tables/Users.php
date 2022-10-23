<?php
	namespace components\Telegram\classes\tables;
	use model\components\ComponentTable;
	use Traineratwot\PDOExtended\tableInfo\PDOENewDbObject;

	/**
	 * Класс для работы с таблицей `users`
	 * вызывается Core::getObject(Users::class)
	 */
	class Users extends ComponentTable
	{
		public $table = 'users';
		public $primaryKey = 'id';

		public function createTable()
		: PDOENewDbObject|false
		{
			$this->core->db->table('users')->alter()->addCol('telegram_chat_id','BIGINT')->run();
			return false;
		}
	}