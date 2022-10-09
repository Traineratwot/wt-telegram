<?php

	namespace components\Telegram\model\workers;


	use components\Telegram\model\AbstractWorker;

	class UrlCheck extends AbstractWorker
	{

		public $work;

		public function process()
		{
			$path     = $this->work['file'];
			$f        = fopen($path, 'r');
			$i        = 0;
			$e        = 0;
			$csv = [];
			$response = [];
			while ($row = fgetcsv($f, '40960', ';')) {
				$csv[] = $row;
			}
			$c = count($csv);
			foreach ($csv as $row) {
				$url = $row[0];
				$code       = $this->check($url);
				$response[] = [
					$url,
					$code,
				];
				if ($code > 300) {
					$e++;
				}
				$i++;
				$this->progress($c,$i);
				echo $this->percent.'%'.PHP_EOL;
			}
			fclose($f);
			$id        = md5(serialize($this->work));
			$this->out = WT_ASSETS_PATH . 'output/' . $id . '.csv';
			$r         = fopen($this->out, 'w');
			foreach ($response as $row) {
				fputcsv($r, $row, ';');
			}
			fclose($r);
			$url = str_replace(WT_ASSETS_PATH, "https://telegram.massive.ru/assets/", $this->out);

			if (file_exists($this->out)) {
				$this->writeFile($this->out, $url);
				$this->telegram->sendMessage(
					$this->work['chat'],
					<<<TXT
Готово.\n
Ссылка: $url \n
Ссылка будет доступна 24 часа \n
Количество ссылок/ошибок: {$i}/{$e}  
TXT
				);
			} else {
				$this->telegram->sendMessage($this->work['chat'], 'Ошибка: при парсинге');
				return FALSE;
			}
		}

		public function check($url)
		{
			$curl = curl_init();
			curl_setopt_array($curl, [
				CURLOPT_URL            => $url,
				CURLOPT_RETURNTRANSFER => TRUE,
				CURLOPT_ENCODING       => '',
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 10,
				CURLOPT_FOLLOWLOCATION => TRUE,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => 'GET',
			]);
			curl_exec($curl);
			$info = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			curl_close($curl);
			return (int)$info;
		}

		public function writeFile($name, $url)
		{
			$size = filesize($name);
			$this->core->db->query(
				<<<SQL
insert into files (`name`,`link`,`size`) values ("$name","$url", $size)
SQL
			);
		}
	}