<?php

	namespace components\Telegram\classes\ajax;

	use model\Events\Event;
	use model\page\Ajax;
	use model\page\Page;

	class Login extends Ajax
	{
		public function process()
		{
			setcookie('telegram_chat_id', (int)$_GET['id'], time() + 60, '/');
			if ($this->core->isAuthenticated) {
				Event::emit('TelegramLogin', NULL, $this->core);
				return <<<HTML
<script>
alert('Вы авторизованы')
window.close();
</script>
HTML;

			}

			Page::redirect('/user/login');
			return <<<HTML
<script>
window.location.href = '/user/login'
</script>
HTML;
		}
	}

	return Login::class;