<?php

	namespace model\cli\commands\make;

	use core\model\cli\commands\Make;
	use model\main\Utilities;
	use Traineratwot\config\Config;
	use Traineratwot\PhpCli\Cmd;
	use Traineratwot\PhpCli\Console;
	use Traineratwot\PhpCli\types\TString;

	class MakeCron extends Cmd
	{

		/**
		 * @inheritDoc
		 */
		public function help()
		{
			return "⏱ Создает шаблон крон задачи";
		}

		public function run()
		{
			$cron = $this->getArg('cron');
			if (substr($cron, -4, strlen($cron)) === '.php') {
				$cron = substr($cron, 0, -4);
			}
			$cron = Make::pathFileUcFirst($cron);
			$p = Utilities::pathNormalize(Config::get('CRON_PATH') . 'controllers/' . $cron . '.php');
			if (!file_exists($p)) {
				Utilities::writeFile($p, Make::makeCron($cron));
				Console::success('ok: ' . $p);
			} else {
				Console::failure('Already exists');
			}
		}

		public function setup()
		{
			$this->registerParameter('cron', 1, TString::class, 'Введите alias задачи "/**/*.php"');
		}
	}