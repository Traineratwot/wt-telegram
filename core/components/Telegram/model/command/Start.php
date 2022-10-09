<?php

	namespace components\Telegram\model\command;


	use components\Telegram\model\AbstractBotSlashCommand;
	use components\Telegram\model\TelegramController;

	class Start extends AbstractBotSlashCommand
	{
		public array $StartCommands
			= [
				'start',
				'help',
				'?',
			];

		/**
		 * @var TelegramController
		 */
		public TelegramController $scope;

		public function run($command, $args, $id, $isQuiz = FALSE, $message=null)
		{
			foreach ($this->scope->help as $key => $value) {
				if (empty($value)) {
					unset($this->scope->help[$key]);
				}
			}
			rsort($this->scope->help);
			$help = implode("\n", $this->scope->help);
			if($this->scope->core->isAuthenticated){
				$help ="Здравствуйте {$this->scope->core->user->getName()} \n\n". $help;
			}
			$this->scope->sendMessage($id, $help);
		}
	}

	return Start::class;