<?php

	namespace components\Telegram\classes\cli;


	use core\model\cli\commands\Make;
	use model\main\Utilities;
	use Traineratwot\config\Config;
	use Traineratwot\PhpCli\Cmd;
	use Traineratwot\PhpCli\Console;

	class MakeTelegram extends Cmd
	{

		public function __construct()
		{

		}

		public function process()
		{

		}

		/**
		 * @inheritDoc
		 */
		public function help()
		{
			return "Создает телеграм команду";
		}

		public function run()
		{
			$command = $this->getArg('command');
			$path = Make::pathFileUcFirst($command);
			$path = Utilities::pathNormalize(Config::get('COMPONENTS_PATH').'telegram/commands/' . $path.'.php');
			if (!file_exists($path)) {
				Utilities::writeFile($path, $this->makeTelegram($command));
				Console::success('ok: ' . $path);
			} else {
				Console::failure('Already exists, "' . $path . '"');
			}
		}

		public function setup()
		{
			$this->registerParameter('command', 1);
		}

		public function makeTelegram(string $command)
		: string
		{
			$class  = Make::name2class($command);
			$extend = 'AbstractBotChatCommand';
			if (str_starts_with($command, '/')) {
				$command = trim($command, '/');
				$class   = Make::name2class($command);
				$extend  = 'AbstractBotSlashCommand';
			}

			return <<<PHP
<?php
	namespace components\Telegram\commands;
	
	use components\Telegram\model\{$extend};
	use TelegramBot\Api\Types\Message;
	class {$class} extends {$extend}
	{
		public \$StartCommands
			= [
				'{$command}',
			];
	
		public \$description = "описание команды '{$command}'";
	
		public function run(int \$id, Message \$message)
		{
			//TODO make plugin {$class}
		}
	}
	return {$class}::class;
PHP;
		}
	}