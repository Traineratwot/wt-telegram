<?php
	/**
	 * Created by Kirill Nefediev.
	 * User: Traineratwot
	 * Date: 16.11.2022
	 * Time: 13:18
	 */

	namespace components\Telegram\model;

	use model\main\Core;

	interface HistoryManager extends \Iterator
	{
		public function __construct(Core $core, int $chat_id);

		function write();

	}