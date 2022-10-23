<?php

	namespace components\Telegram\classes\tables;

	use Exception;
	use model\components\ComponentTable;
	use model\main\Core;
	use Traineratwot\PDOExtended\tableInfo\PDOENewDbObject;

	/**
	 * Класс для работы с таблицей `subscribe_user`
	 * вызывается Core::getObject(SubscribeUser::class)
	 */
	class SubscribeUser extends ComponentTable
	{
		public $table      = 'subscribe_user';
		public $primaryKey = 'id';

		public function getUser()
		: \tables\Users
		{
			return $this->core->getUser($this->get('userID'));
		}

		/**
		 * @throws Exception
		 */
		public function getSubscribe()
		: Subscribe
		{
			return $this->core->getObject(Subscribe::class, $this->get('subscribeID'));
		}

		public static function createTable()
		: PDOENewDbObject|false
		{
			$core = Core::init();
			return $core->db->newTable('subscribe_user')
								  ->addInt('id', unsigned: TRUE, canBeBull: FALSE)
								  ->addInt('userID', unsigned: TRUE)
								  ->addInt('subscribeID', unsigned: TRUE)
								  ->setPrimaryKey('id')
			;
		}
	}