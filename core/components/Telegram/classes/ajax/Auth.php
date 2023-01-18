<?php

	namespace components\Telegram\classes\ajax;

	use model\page\Ajax;
	use model\page\Page;
	use Traineratwot\Cache\Cache;

	class Auth extends Ajax
	{
		public function process()
		{
			$salt    = substr($_GET['auth'], 0, 8);
			$user_id = (int)Cache::getCache('auth_' . $salt, 'telegram/auth');
			$user    = $this->core->getUser($user_id);
			if (!$user->isNew()) {
				$this->core->auth($user);
			}
			Page::redirect('/user/profile');
		}
	}

	return Auth::class;
