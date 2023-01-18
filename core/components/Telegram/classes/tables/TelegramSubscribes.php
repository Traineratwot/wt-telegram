<?php

	namespace components\Telegram\classes\tables;

	use model\components\ComponentTable;
	use tables\SubscribeUser;
	use Traineratwot\PDOExtended\exceptions\SqlBuildException;
	use Traineratwot\PDOExtended\PDOE;
	use Traineratwot\PDOExtended\tableInfo\PDOENewDbObject;

	/**
	 * Класс для работы с таблицей `telegram_subscribes`
	 * вызывается Core::getObject(TelegramSubscribes::class)
	 */
	class TelegramSubscribes extends ComponentTable
	{
		public $table      = 'telegram_subscribes';
		public $primaryKey = 'id';

		/**
		 * @throws SqlBuildException
		 */
		public function getUsers()
		: array
		{
			$response = [];
			$subs     = $this->core->getIterator(TelegramUserSubscribe::class, ['subscribe_id' => $this->getID()]);
			foreach ($subs as $sub) {
				$response[] = $sub->getUser();
			}
			return $response;
		}

		public static function createTable()
		: PDOENewDbObject|false
		{
			return PDOE::createTable('telegram_subscribes')
					   ->addInt('id')
					   ->setPrimaryKey('id')
					   ->addString('name')
					   ->addString('comment', 0)
					   ->addUniqueKey('name')
			;
		}
	}