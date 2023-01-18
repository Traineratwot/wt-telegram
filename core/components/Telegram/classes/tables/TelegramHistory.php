<?php

	namespace components\Telegram\classes\tables;

	use model\components\ComponentTable;
	use model\main\Utilities;
	use TelegramBot\Api\Types\Message;
	use TelegramBot\Api\Types\Update;
	use Traineratwot\PDOExtended\PDOE;
	use Traineratwot\PDOExtended\tableInfo\PDOENewDbObject;

	/**
	 * Класс для работы с таблицей `telegram_history`
	 * вызывается Core::getObject(TelegramHistory::class)
	 */
	class TelegramHistory extends ComponentTable
	{
		public $table      = 'telegram_history';
		public $primaryKey = 'id';

		public function save()
		{
			$this->set('msg_time', time());
			return parent::save();
		}

		public function getTime()
		{
			return (int)$this->get('msg_time');
		}

		public function getMessage()
		: Message|null
		{
			try {
				return Message::fromResponse(Utilities::jsonValidate($this->get('raw_data')));
			} catch (\Exception) {
				return $this->getUpdate()?->getMessage();
			}
		}

		public function getUpdate()
		: Update|null
		{
			try {
				return Update::fromResponse(Utilities::jsonValidate($this->get('raw_data')));
			} catch (\Exception) {
				return NULL;
			}
		}

		public static function createTable()
		: PDOENewDbObject|false
		{
			return PDOE::createTable('telegram_history')
					   ->addInt('id')
					   ->setPrimaryKey('id')
					   ->addInt('chat_id')
					   ->addInt('msg_time')
					   ->addString('msg_type')
					   ->addString('action')
					   ->addString('message', 0)
					   ->addString('raw_data', 0)
			;
		}
	}