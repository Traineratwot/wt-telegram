<?php

	namespace components\Telegram\model;

	use components\Telegram\model\overwrite\Client;
	use CURLFile;
	use model\main\Core;
	use model\main\CoreObject;
	use model\main\Utilities;
	use TelegramBot\Api\BotApi;
	use TelegramBot\Api\Exception;
	use TelegramBot\Api\InvalidArgumentException;
	use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
	use TelegramBot\Api\Types\Message;
	use TelegramBot\Api\Types\ReplyKeyboardRemove;
	use TelegramBot\Api\Types\Update;
	use Traineratwot\Cache\Cache;
	use Traineratwot\Cache\CacheException;
	use Traineratwot\config\Config;


	class TelegramController extends CoreObject
	{
		public const PARSE_MD   = 'Markdown';
		public const PARSE_MDV2 = 'MarkdownV2';
		public const PARSE_HTML = 'HTML';

//		#public $token = "1756282824:AAFNvD_VoMb2a8kk3Zj4K1gBzMq70U_y0Cs"; #fight
		/**
		 * @var string
		 */
		public string $token; #test
		/**
		 * @var Client|overwrite\BotApi
		 */
		public Client|BotApi $bot;
		/**
		 * @var History
		 */
		public History $history;
		/**
		 * @var string[]
		 */
		public array $NO
			= [
				'нет',
				'ytn',
				'jnvtyf',
				'отмена',
				'забудь',
				'stop',
				'стоп',
			];
		/**
		 * @var array
		 */
		public array $YES
			= [

			];
		/**
		 * @var Quiz
		 */
		public Quiz $quiz;
		/**
		 * @var array
		 */
		public array $help = [];
		/**
		 * @var array<AbstractBotCommand>
		 */
		public array $CLASSES = [];
		/**
		 * @var array<AbstractBotCommand>
		 */
		public array $COMMANDS = [];
		/**
		 * @var array<AbstractBotCallback>
		 */
		public array $CALLBACKS = [];
		public array $chat      = [];

		/**
		 * TelegramController constructor.
		 * @throws CacheException
		 */
		public function __construct(Core $core)
		{
			parent::__construct($core);
			$this->token     = Config::get('TOKEN', 'telegram');
			$this->bot       = new Client($this->token);
			$this->chat      = [];
			$this->COMMANDS  = [];
			$this->CLASSES   = [];
			$this->CALLBACKS = [];

			// если Телеграм-бот не зарегистрирован - регистрируем
			try {
				if (!Cache::getCache('lock', 'telegram', FALSE)) {
					$page_url = WT_DOMAIN_URL . '/telegram';
					$result   = $this->bot->setWebhook($page_url);
					if ($result) {
						Cache::setCache('lock', [
							'time'   => time(),
							'url'    => $page_url,
							'result' => $result,
						],              0, 'telegram');

					}
				}
			} catch (Exception $e) {
			}
		}

		public function initialize($input)
		{
			$ChatID = (int)$input['message']['chat']['id'];
			$u      = $this->core->getUser(['telegram_chat_id' => $ChatID]);
			if (!$u->isNew()) {
				$this->core->auth($u);
			}
			$this->help = [];
			$com        = __DIR__ . '/command/';
			$commands   = scandir($com);
			$commands   = array_map(function ($command) use ($com) {
				if (is_file($com . $command)) {
					return $com . $command;
				}
				return NULL;
			}, $commands);

			$com2 = WT_CLASSES_PATH . 'telegram/command/';
			if (file_exists($com2)) {
				$commands2 = scandir($com2);
				$commands2 = array_map(function ($command) use ($com2) {
					if (is_file($com2 . $command)) {
						return $com2 . $command;
					}
					return NULL;
				}, $commands2);
				$commands  = array_merge($commands2, $commands);
			}
			$commands = array_unique($commands);
			sort($commands);
			// Инициализация всех команд
			foreach ($commands as $command) {
				try {
					if ($command && is_file($command)) {
						$cls = include $command;
						if (class_exists($cls)) {
							$this->CLASSES[$cls] = new $cls($this);
							$this->help[]        = (string)$this->CLASSES[$cls]->getDescription();
							if ($this->CLASSES[$cls] instanceof AbstractBotSlashCommand) {
								$startCommands = $this->CLASSES[$cls]->getStartCommands();
								foreach ($startCommands as $startCommand) {
									try {
										// обязательное. Запуск бота
										$this->bot->command($startCommand, function (Message $Update) use ($cls) {
											if (method_exists($Update, 'getText')) {
												$text    = $Update->getText();
												$id      = $Update->getChat()->getId();
												$words   = explode(' ', $text);
												$command = mb_strtolower(array_shift($words));
												$this->CLASSES[$cls]->run($command, $words, $id, FALSE, $Update);
												return TRUE;
											}
											return FALSE;
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
							} elseif ($this->CLASSES[$cls] instanceof AbstractBotCommand) {
								$startCommands = $this->CLASSES[$cls]->getStartCommands();
								foreach ($startCommands as $startCommand) {
									$this->COMMANDS[$startCommand]  = $this->CLASSES[$cls];
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
		}

		/**
		 * Start callback function
		 */
		public function callback()
		{
			try {
				$self = &$this;
				$this->bot->callbackQuery(function ($Update) use ($self) {
					try {
						if (method_exists($Update, 'getMessage')) {
							$message = $Update->getMessage();
							$text    = $message->getText() ?: '';
							$id      = $message->getChat()->getId() ?: 0;
							$data    = $Update->getData() ?: '';
							if ($id) {
								$data = json_decode($data, TRUE);
								if (is_array($data)) {
									$args = $data['args'] ?: $data['words'];
									$self->bot->sendChatAction(
										$id,
										'typing'
									);
									if ($data['fn']) {
										if (array_key_exists($data['fn'], $this->CALLBACKS)) {
											$this->CALLBACKS[$data['fn']]->run($message, $args, $id);
										} else {
											$self->sendMessage($id, 'ошибка: не известная функция: ' . $data['fn']);
										}
									} else {
										$self->sendMessage($id, 'ошибка: empty fn');
									}
								}
							}
						}
					} catch (Exception $e) {
						$self->sendMessage($id, 'ошибка 2: ' . $e->getMessage());
					}
				}, function ($message) {
					return TRUE; // когда тут true - команда проходит
				});
				$this->bot->run();
			} catch (Exception $e) {
			}
		}

		/**
		 * @param                                               $id
		 * @param                                               $text
		 * @param ReplyKeyboardRemove|InlineKeyboardMarkup|null $replyMarkup
		 * @param false                                         $clearKeyboard
		 * @param string                                        $parseMode
		 * @param false                                         $disablePreview
		 * @param null                                          $replyToMessageId
		 * @param false                                         $disableNotification
		 * @return Message|bool
		 */
		public function sendMessage($id, $text, ReplyKeyboardRemove|InlineKeyboardMarkup $replyMarkup = NULL, bool $clearKeyboard = FALSE, string $parseMode = self::PARSE_HTML, bool $disablePreview = FALSE,
			$replyToMessageId
			= NULL, bool $disableNotification = FALSE)
		: Message|bool
		{
			$alt = 'data.json';
			if (is_array($text)) {
				if (array_key_exists('text', $text)) {
					$alt  = self::rawText($text['alt']);
					$text = $text['text'];
				} else {
					$text = json_encode($text);
				}
			}
			if (is_null($replyMarkup) && $clearKeyboard) {
				$replyMarkup      = new ReplyKeyboardRemove(TRUE, TRUE);
				$parseMode        = FALSE;
				$disablePreview   = NULL;
				$replyToMessageId = NULL;
			}
			$isLong = FALSE;
			$text   = $this->formatMessage($text, $isLong);
			if ($isLong) {
				$temp = tmpfile();
				fwrite($temp, $text);
				fseek($temp, 0);
				$temp_data = stream_get_meta_data($temp);
				if ($temp_data['uri']) {
					$document = new CURLFile($temp_data['uri'], 'application/json', $alt);
					try {
						$a = $this->bot->sendDocument($id, $document);
					} catch (InvalidArgumentException|Exception $e) {
					}
					fclose($temp); // происходит удаление файла
					return $a;
				}

				$this->error($id, 'Не удалось отправить данные');
			} else {
				try {
					return $this->bot->sendMessage($id, $text, $parseMode, $disablePreview, $replyToMessageId, $replyMarkup, $disableNotification);
				} catch (InvalidArgumentException|Exception $e) {
				}
			}
			return FALSE;
		}

		/**
		 * @throws Exception
		 * @throws InvalidArgumentException
		 */
		public function editMessageText($chatId, $messageId, $text, $parseMode = self::PARSE_HTML, $disablePreview = FALSE, $replyMarkup = NULL, $inlineMessageId = NULL)
		: Message
		{
			return $this->bot->sendMessage($chatId, $messageId, $text, $parseMode, $disablePreview, $replyMarkup, $inlineMessageId);
		}

		/**
		 * @param string $a
		 * @return string
		 */
		public static function rawText(string $a = '')
		{
			return Utilities::rawText($a);
		}

		/**
		 * @param       $code
		 * @param false $isLong
		 * @return string
		 */
		public function formatMessage($code, bool &$isLong = FALSE)
		{
			$response = '';

			if (is_array($code)) {
				$text = json_encode($code, 256 | JSON_PRETTY_PRINT);
				if (strlen($text) < 4096) {
					$response = "<pre><code>" . $text . "</code></pre>";
				} else {
					$response = $text;
					$isLong   = TRUE;
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
		 * @param $id
		 * @param $message
		 * @throws Exception
		 * @throws InvalidArgumentException
		 */
		public function error($id, $message)
		{
			$this->bot->sendMessage($id, '*' . $message . '*', 'Markdown');
		}

		/**
		 * Start callback message
		 */
		public function message()
		{
			try {
				$self = &$this;
				$this->bot->on(function (Update $Update) use ($self) {
					try {
						if (method_exists($Update, 'getMessage')) {
							$message  = $Update->getMessage();
							$text     = $message->getText() ?: '';
							$id       = $message->getChat()->getId() ?: 0;
							$document = $message->getDocument();
							$caption  = $message->getCaption();

							if ($id) {
								//парсинг сообщения
								$self->getChat($id);
								$words   = explode(' ', $text);
								$command = mb_strtolower(array_shift($words));
								if (!empty($caption) && $document !== NULL) {
									$command = $caption;
								}
								//сохранение в историю
								$self->history->saveChat(
									[
										'message' => $message,
										'text'    => $text,
										'id'      => $id,
										'words'   => $words,
									]
								);
								if (mb_strtolower($command) === 'restart' || mb_strtolower($command) === 'reset') {
									//удаление истории если
									$this->history->delete();
									$self->removeKeyboard($id, 'Сессия перезапущена');
									return TRUE;
								}
								//проверка на quiz(связанные сообщения)
								if (!empty($self->chat['quiz'])) {
									try {
										$class      = $self->chat['quiz']['name'];
										$self->quiz = new $class($self, $id, $this->chat, $this->history);
										if ($self->quiz instanceof Quiz) {
											//вызов следующего шага
											$self->bot->sendChatAction(
												$id,
												'typing'
											);
											$self->quiz->call($self->chat['quiz']['step'], $command, $words, $id);
										} else {
											throw new Exception('error code 3');
										}
									} catch (Exception $e) {
										$self->sendMessage($id, $e->getMessage());
									}
								} else {
									if (array_key_exists($command, $this->COMMANDS)) {
										//запуск команды
										$self->bot->sendChatAction(
											$id,
											'typing'
										);
										return $this->COMMANDS[$command]->run($command, $words, $id, FALSE, $message);
									}
//									$self->sendMessage($id, 'Даже не знаю что и сказать :)');
								}
							} else {
								throw new Exception('error code 2');
							}
						}
					} catch (Exception $e) {
						$self->sendMessage($id, 'Ошибка события : ' . $e->getMessage());
					}
					return TRUE;
				}, function ($message) {
					return TRUE; // когда тут true - команда проходит
				});
				$this->bot->run();
			} catch (Exception $e) {

			}
		}

		/**
		 * @param $id
		 */
		public function getChat($id)
		{
			$file = WT_CACHE_PATH . 'chats/' . $id . '.json';
			if (file_exists($file)) {
				$this->chat = json_decode(file_get_contents($file), 1);
			}
			$this->history = new History($id);
		}

		/**
		 * @param $id
		 * @param $txt
		 */
		public function removeKeyboard($id, $txt)
		{
			$this->sendMessage($id, $txt, NULL, TRUE);
		}

		/**
		 * @param string $txt
		 * @return bool
		 */
		public function likeNo(string $txt = '')
		{
			$txt = mb_strtolower(trim($txt));
			if (in_array($txt, $this->NO, TRUE)) {
				return TRUE;
			}
			return FALSE;
		}

		/**
		 * @param $v
		 * @return array|string
		 */
		public function objectInfo(&$v)
		{
			if (is_object($v)) {
				$obj            = [];
				$obj['name']    = get_class($v);
				$obj['methods'] = get_class_methods($v);
				$class_vars     = array_keys(get_class_vars($obj['name']) ?: []);
				$object_vars    = array_keys(get_object_vars($v) ?: []);
				$obj['vars']    = array_unique(array_merge($class_vars, $object_vars));
				return $obj;
			}
			return gettype($v);
		}
	}