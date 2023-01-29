<?php

	namespace components\Telegram\model;

	use components\Telegram\classes\tables\TelegramHistory;
	use components\Telegram\classes\tables\TelegramUsers;
	use Exception;
	use model\main\Core;
	use model\main\CoreObject;
	use model\main\Err;
	use model\main\Utilities;
	use TelegramBot\Api\BotApi;
	use TelegramBot\Api\Client;
	use TelegramBot\Api\InvalidArgumentException;
	use TelegramBot\Api\Types\Message;
	use TelegramBot\Api\Types\ReplyKeyboardRemove;
	use TelegramBot\Api\Types\Update;
	use Traineratwot\config\Config;

	/**
	 * @property BotApi|Client $bot
	 */
	class TelegramController extends CoreObject
	{

		public History $history;
		private TelegramHistory $MessageHistory;
		/**
		 * @var AbstractBotCommand[]|null
		 */
		private array|null $CLASSES;
		/**
		 * @var AbstractBotCallback[]|null
		 */
		private array|null $CALLBACKS;

		/**
		 * @throws \TelegramBot\Api\Exception
		 */
		public function __construct(Core $core)
		{
			parent::__construct($core);
			$this->token = Config::get('TELEGRAM_TOKEN');
			if (!$this->token) {
				Err::fatal("ADD 'Config::set('TELEGRAM_TOKEN',token)' in config.php");
			}
			$this->bot = new Client($this->token);
			if (!file_exists(__DIR__ . "/telegram.lock")) {
				$page_url = Config::get('DOMAIN_URL') . '/telegram/webhook';
				$result = $this->bot->setWebhook($page_url);
				if ($result) {
					file_put_contents(__DIR__ . "/telegram.lock", json_encode(
						  [
							  'time' => time(),
							  'url' => $page_url,
							  'result' => $result,
						  ]
						, 256)); // создаем файл дабы остановить повторные регистрации
				} else {
					Err::fatal("Can not set webhook!");
				}
			}
		}

		/**
		 * @throws Exception
		 * @throws \Throwable
		 */
		public function initialize($input)
		{
			if (isset($input['message']['chat']['id'])) {
				$chatId = (int)$input['message']['chat']['id'];
			}else if($input['callback_query']['message']['chat']['id']){
				$chatId = (int)$input['callback_query']['message']['chat']['id'];
			}
			else{
				file_put_contents(WT_BASE_PATH.'test.json',json_encode($input,256));
				Err::fatal("error telegram message missing ID");
			}
			$this->history = new History($this->core, $chatId);
			$this->MessageHistory = $this->history->write();
			$this->help = [];
			$com = Config::get('TELEGRAM_COMMANDS_PATH') ?: Config::get('COMPONENTS_PATH') . 'Telegram/commands/';
			if (!$com || !file_exists($com)) {
				throw Err::fatal("commands path not found");
			}
			$commands = scandir($com);
			if (!is_array($commands)) {
				throw Err::fatal("commands not found");
			}
			sort($commands);
			foreach ($commands as $c) {
				try {
					if (is_file($com . $c) && ltrim($c, '.') === $c) {
						$cls = $class = include $com . $c;
						if (class_exists($class)) {
							$this->CLASSES[$cls] = new $class($this);
							$this->help[] = (string)$this->CLASSES[$cls]->getDescription();
							if ($this->CLASSES[$cls] instanceof AbstractBotChatCommand) {
								$startCommands = $this->CLASSES[$cls]->getStartCommands();
								foreach ($startCommands as $startCommand) {
									$this->COMMANDS[$startCommand] = $this->CLASSES[$cls];
									$this->CALLBACKS[$startCommand] = $this->CLASSES[$cls];
								}
							} elseif ($this->CLASSES[$cls] instanceof AbstractBotSlashCommand) {
								$startCommands = $this->CLASSES[$cls]->getStartCommands();

								foreach ($startCommands as $startCommand) {
									try {
										// Обязательное. Запуск бота
										$this->bot->command($startCommand, function (Message $Update) use ($cls) {
											$text = $Update->getText();
											$id = $Update->getChat()->getId();
											$this->bot->sendChatAction(
												$id,
												'typing'
											);
											$this->CLASSES[$cls]->process($id, $Update);
											return TRUE;
										});

									} catch (Exception $e) {

									}
									$this->COMMANDS[$startCommand] = $this->CLASSES[$cls];
								}
							} elseif ($this->CLASSES[$cls] instanceof AbstractBotCallback) {
								$startCommands = $this->CLASSES[$cls]->getStartCommands();
								foreach ($startCommands as $startCommand) {
									$this->CALLBACKS[$startCommand] = $this->CLASSES[$cls];
								}
							}
						}
					}
				} catch (Exception $e) {

				}
			}
			if (array_key_exists('callback_query', $input)) {
				//запуск callback`ов
				$this->callback();
			} else {
				//запуск обработки входящих сообщений
				$this->message();
			}
			if ($this->MessageHistory->isNew() || $this->MessageHistory->isDirty()) {
				$this->MessageHistory->save();
			}
		}

		public function callback()
		{
			try {
				$this->bot->callbackQuery(function ($Update) {
					try {
						if (method_exists($Update, 'getMessage')) {
							$message = $Update->getMessage();
							$text = $message->getText() ?: '';
							$id = $message->getChat()->getId() ?: 0;
							$data = $Update->getData() ?: '';
							if ($id) {
								$data = json_decode($data, TRUE);
								if (is_array($data)) {
									$args = $data['args'] ?: $data['words'];
									$this->bot->sendChatAction(
										$id,
										'typing'
									);
									if ($data['fn']) {
										if (array_key_exists($data['fn'], $this->CALLBACKS)) {
											$this->CALLBACKS[$data['fn']]->process($id, $message, $data['args']);
										} else {
											$this->sendMessage($id, 'ошибка: не известная функция: ' . $data['fn']);
										}
									} else {
										$this->sendMessage($id, 'ошибка: empty fn');
									}
								}
							}
						}
					} catch (Exception $e) {
						$this->sendMessage($id, 'ошибка 2: ' . $e->getMessage());
					}
				});
				$this->bot->run();
			} catch (Exception $e) {
			}
		}

		/**
		 * @throws \TelegramBot\Api\Exception
		 * @throws InvalidArgumentException
		 * @throws Exception
		 */
		public function sendMessage(
			int          $id,
			string|array $text,
						 $replyMarkup = NULL,
						 $clearKeyboard = FALSE,
						 $parseMode = 'HTML',
						 $disablePreview = FALSE,
						 $replyToMessageId = NULL,
						 $disableNotification = FALSE
		): Message
		{
			$history = $this->core->getObject(TelegramHistory::class);
			$alt = 'data.json';
			if (is_array($text)) {
				if (array_key_exists('text', $text)) {
					$alt = Utilities::rawText($text['alt']);
					$text = $text['text'];
				} else {
					$text = json_encode($text);
				}
			}
			if (is_null($replyMarkup) and $clearKeyboard) {
				$replyMarkup = new ReplyKeyboardRemove(TRUE, TRUE);
				$parseMode = FALSE;
				$disablePreview = NULL;
				$replyToMessageId = NULL;
			}
			$isLong = FALSE;
			$text = $this->formatMessage($text, $isLong);
			$history->set('msg_type', 'answer');
			$history->set('chat_id', $id);
			$history->set('message', $text);
			if ($isLong) {
				$temp = tmpfile();
				fwrite($temp, $text);
				fseek($temp, 0);
				$temp_data = stream_get_meta_data($temp);
				if ($temp_data['uri']) {
					$document = new \CURLFile($temp_data['uri'], 'application/json', $alt);
					$a = $this->bot->sendDocument($id, $document);
					fclose($temp); // происходит удаление файла
					$history->set('raw_data', $a->toJson());
					$history->save();
					return $a;
				}
				$this->error($id, 'Не удалось отправить данные');
				Err::fatal('Не удалось отправить данные в чат ' . $id);
			} else {
				$a = $this->bot->sendMessage($id, $text, $parseMode, $disablePreview, $replyToMessageId, $replyMarkup, $disableNotification);
				$history->set('raw_data', $a->toJson());
				$history->save();
				return $a;
			}
		}

		public function formatMessage($code, &$isLong = FALSE)
		{
			$response = '';

			if (is_array($code)) {
				$text = json_encode($code, 256 | JSON_PRETTY_PRINT);
				if (strlen($text) < 4096) {
					$response = "<pre><code>" . $text . "</code></pre>";
				} else {
					$response = $text;
					$isLong = TRUE;
				}
			} elseif (is_string($code)) {
				$response = $code;
			} else {
				$response = (string)$code;
			}
			if (strlen($response) < 4096) {
				$isLong = FALSE;
			} else {
				$isLong = TRUE;
			}
			return $response;
		}

		/**
		 * @throws \TelegramBot\Api\Exception
		 * @throws InvalidArgumentException
		 */
		public function error($id, $message)
		{
			$this->bot->sendMessage($id, '*' . $message . '*', 'Markdown');
		}

		public function message()
		{
			try {
				$this->bot->on(function (Update $Update) {
					try {
						$message = $Update->getMessage();
						$text = $message->getText() ?: '';
						$id = $message->getChat()->getId() ?: 0;
						$this->MessageHistory->set('message', $text);
						$this->MessageHistory->set('raw_data', $Update->toJson());
						$this->MessageHistory->set('msg_type', 'message');
						$words = explode(' ', $text);
						$command = mb_strtolower(array_shift($words));
						if (array_key_exists($command, $this->COMMANDS)) {
							//запуск команды
							$this->bot->sendChatAction(
								$id,
								'typing'
							);
							$this->COMMANDS[$command]->process($id, $message);
						}
						$this->MessageHistory->save();
					} catch (InvalidArgumentException|Exception $e) {
						Err::fatal($e->getMessage(), NULL, NULL, $e);
					}
				}, function (Update $Update) {
					return TRUE;
				});
				$this->bot->run();
			} catch (Exception $e) {

			}
		}

		public function editMessageText(
			int    $chatId,
			int    $messageId,
			string $text,
				   $parseMode = 'HTML',
				   $disablePreview = FALSE,
				   $replyMarkup = NULL,
				   $inlineMessageId = NULL
		): Message
		{
			return $this->bot->editMessageText($chatId, $messageId, $text, $parseMode, $disablePreview, $replyMarkup, $inlineMessageId);
		}


	}