<?php

	namespace components\Telegram\model\overwrite;


	use TelegramBot\Api\Events\EventCollection;

	class Client extends \TelegramBot\Api\Client
	{
		public function __construct($token, $trackerToken = null)
		{
			parent::__construct($token, $trackerToken = null);
			$this->api = new BotApi($token);
			$this->events = new EventCollection($trackerToken);
		}
	}