<?php

	namespace components\Telegram\classes\tables;

	use model\components\ComponentTable;
	use model\main\Core;
	use Traineratwot\PDOExtended\exceptions\SqlBuildException;
	use Traineratwot\PDOExtended\tableInfo\PDOENewDbObject;

	/**
	 * Класс для работы с таблицей `subscribe`
	 * вызывается Core::getObject(Subscribe::class)
	 */
	class Subscribe extends ComponentTable
	{
		public $table      = 'subscribe';
		public $primaryKey = 'id';

		/**
		 * @return array<Users>
		 * @throws SqlBuildException
		 */
		public function getUsers()
		: array
		{
			$response = [];
			$subs     = $this->core->getCollection(SubscribeUser::class, ['subscribeID' => $this->getID()]);
			foreach ($subs as $sub) {
				$response[] = $sub->getUser();
			}
			return $response;
		}

		public static function createTable()
		: PDOENewDbObject|false
		{
			$core = Core::init();
			return $core->db->newTable('subscribe')
							->setComment('table for telegram subscribes')
							->addInt('id', unsigned: TRUE, canBeBull: FALSE)
							->addString('name', 50)
							->addString('description', 0)
							->setPrimaryKey('id')
			;
		}
	}