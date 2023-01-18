<?php

	namespace components\Telegram\model;


	use components\Telegram\classes\tables\TelegramHistory;
	use Exception;
	use model\main\Core;
	use Traineratwot\PDOExtended\exceptions\SqlBuildException;

	class History implements HistoryManager
	{
		private int  $index = 0;
		private Core $core;
		/**
		 * @var array<TelegramHistory>
		 */
		private array $history;
		/**
		 * @var TelegramHistory
		 */
		private mixed $MessageHistory;

		/**
		 * @throws SqlBuildException
		 * @throws Exception
		 */
		public function __construct(Core $core, int $chat_id)
		{
			$this->core           = $core;
			$this->history        = $this->core->getCollection(TelegramHistory::class, ['chat_id' => $chat_id]);
			$this->MessageHistory = $this->core->getObject(TelegramHistory::class);
			$this->MessageHistory->set('chat_id', $chat_id);
			$this->history = array_filter($this->history, function ($item) {
				/**
				 * @var TelegramHistory $item
				 */
				return $item->get('msg_type') !== 'answer';
			});
			usort($this->history, function ($a, $b) {
				/**
				 * @var TelegramHistory $a
				 * @var TelegramHistory $b
				 */
				return $b->getTime() <=> $a->getTime();
			});
		}

		/**
		 * @inheritDoc
		 */
		public function current()
		: TelegramHistory
		{
			return $this->history[$this->index];
		}

		/**
		 * @inheritDoc
		 */
		public function next()
		: void
		{
			$this->index++;
		}

		/**
		 * @inheritDoc
		 */
		public function key()
		: int
		{
			return $this->index;
		}

		/**
		 * @inheritDoc
		 */
		public function valid()
		: bool
		{
			return isset($this->history[$this->index]);
		}

		/**
		 * @inheritDoc
		 */
		public function rewind()
		: void
		{
			$this->index = 0;
		}

		function write()
		{
			return $this->MessageHistory;
		}

		public function getHistory()
		{
			return $this->history;
		}

		public function getLast($left = 0)
		: TelegramHistory|null
		{
			return $this->history[$left] ?? NULL;
		}

		public function __destruct()
		{
			if ($this->MessageHistory->isNew() || $this->MessageHistory->isDirty()) {
				$this->MessageHistory->save();
			}
		}

	}