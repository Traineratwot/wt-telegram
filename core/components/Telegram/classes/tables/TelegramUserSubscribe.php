<?php
	namespace components\Telegram\classes\tables;
	use model\components\ComponentTable;
	use tables\Users;
	use Traineratwot\PDOExtended\PDOE;
	use Traineratwot\PDOExtended\tableInfo\PDOENewDbObject;

	/**
	 * Класс для работы с таблицей `telegram_user_subscribe`
	 * вызывается Core::getObject(TelegramUserSubscribe::class)
	 */
	class TelegramUserSubscribe extends ComponentTable
	{
		public $table = 'telegram_user_subscribe';
		public $primaryKey = 'id';

		public function getUser()
		: Users
		{
			return $this->core->getUser($this->get('user_id'));
		}

		public static function createTable()
		: PDOENewDbObject|false
		{
			return PDOE::createTable('telegram_user_subscribe')
					   ->addInt('id')
					   ->setPrimaryKey('id')
					   ->addInt('subscribe_id')
					   ->addInt('user_id')
					   ->addUniqueKey(['subscribe_id', 'user_id'])
			;
		}
	}