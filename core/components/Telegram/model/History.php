<?php

	namespace components\Telegram\model;
	class History
	{

		/**
		 * @var array
		 */
		public mixed  $chat;
		public string $file;
		public mixed  $id;

		public function __construct($id)
		{
			$this->id   = $id;
			$this->file = WT_CACHE_PATH . 'chats/' . $this->id . '.json';
			if (empty($this->chat) && file_exists($this->file)) {
				$this->chat = json_decode(file_get_contents($this->file), 1);
			}
		}

		public function saveChat($data)
		{
			$this->chat['history'][time()] = $data;
			$concurrentDirectory           = dirname($this->file);
			if (!is_dir($concurrentDirectory) && !mkdir($concurrentDirectory, 0777, 1) && !is_dir($concurrentDirectory)) {
				return FALSE;
			}
			file_put_contents($this->file, json_encode($this->chat, 256));
		}

		public function getLast($offset = 0)
		{
			if (isset($this->chat['history'])) {
				krsort($this->chat['history']);
				$times = array_keys($this->chat['history']);
				if (isset($times[$offset])) {
					return $this->chat['history'][$times[$offset]];
				}
			}
			return FALSE;
		}

		public function delete()
		{
			unlink($this->file);
		}
	}