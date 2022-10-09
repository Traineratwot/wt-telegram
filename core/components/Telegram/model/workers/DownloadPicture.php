<?php

	namespace components\Telegram\model\workers;


	use components\Telegram\model\AbstractWorker;
	use model\main\Core;
	use RecursiveDirectoryIterator;
	use RecursiveIteratorIterator;
	use ZipArchive;

	class DownloadPicture extends AbstractWorker
	{
		public array $ftps = [];

		public function __construct(Core $core, $telegram, $work)
		{
			error_reporting(E_ERROR);
			parent::__construct($core, $telegram, $work);
		}

		public function process()
		{
			$dir = WT_ASSETS_PATH . 'output';
			if (file_exists($dir)) {
				$it    = new RecursiveDirectoryIterator($dir);
				$files = new RecursiveIteratorIterator(
					$it,
					RecursiveIteratorIterator::CHILD_FIRST
				);
				foreach ($files as $file) {
					if ($file->getFilename() === '.' || $file->getFilename() === '..') {
						continue;
					}
					if ($file->isDir()) {
						rmdir($file->getRealPath());
					} else {
						unlink($file->getRealPath());
					}
				}
			}
			if (!file_exists($dir)) {
				if (!mkdir($concurrentDirectory = WT_ASSETS_PATH . 'output') && !is_dir($concurrentDirectory)) {
					throw new \RuntimeException(sprintf('Directory "%s" was not created' . '. Процесс остановлен ', $concurrentDirectory), 3);
				}
			}
			if (!file_exists($this->work['file'])) {
				throw new \Exception('Не удалось загрузить файл', 4);
			}
			// рассмотрим файл как массив
			$file_arr = file($this->work['file']);
			// подсчитываем количество строк в массиве
			$total = count($file_arr);
			$f = fopen($this->work['file'], 'r');
			if (fgets($f, 4) !== "\xef\xbb\xbf") {
				rewind($f);
			}
			$i      = 0;
			$b      = 0;
			$l      = 0;
			$errors = [];
			while ($row = fgetcsv($f, 4096, ';')) {
				$l++;
				try {
					$art = $row[0];
					$url = $row[1];
					$ext = $row[2];
					if (empty($url)) {
						throw new \Exception('пустой url' . $row[0], 5);
						continue;
					}
					if (mb_strtolower(trim($url)) == 'url') {
						continue;
					}
					if (empty($ext)) {
						$a   = explode('.', $url);
						$ext = array_pop($a);
					}
					if (empty($art)) {
						$name = basename($url);
					} else {
						$name = $art . '.' . $ext;
					}
					$name = strtr($name, [
						'/'  => '~',
						'\\' => '~',
						'*'  => '~',
						'"'  => '~',
						'?'  => '~',
						':'  => '~',
						'|'  => '~',
						'<'  => '~',
						'>'  => '~',
					]);
					echo $name . PHP_EOL;
					$name = WT_ASSETS_PATH . 'output' . DIRECTORY_SEPARATOR . $name;
					$i++;
					$this->progress($total, $i);
					if (!file_exists($name)) {
						if (strpos($url, 'ftp://') !== FALSE) {
							$this->ftpDownload($url, $name, $b);
						} else {
							$this->httpDownload($url, $name, $b);
						}
					} else {
						throw new \Exception('Дубликат ' . $row[0], 6);
					}
					if (file_exists($name)) {
						if(!filesize($name)){
							unlink($name);
						}
					}

				} catch (\Exception $e) {
					$code     = $e->getCode() ?: 0;
					$errors[] = '[' . $code . ']' . $e->getMessage() . '. Строка: ' . $l;
					echo '[' . $code . ']' . $e->getMessage() . '. Строка: ' . $l;
				}
			}
			$id     = md5(serialize($this->work));
			$output = WT_ASSETS_PATH . 'zip/' . $id . '_output.zip';
			$this->zip(WT_ASSETS_PATH . 'output/', $output);
			$url = "https://telegram.massive.ru/assets/zip/" . $id . '_output.zip';
			if (file_exists($output)) {
				$this->writeFile($output, $url);
				try {
					$this->telegram->sendMessage(
						$this->work['chat'],
						'Готово.' . PHP_EOL . '
 Ссылка: ' . $url . PHP_EOL . '
 Ссылка будет доступна 24 часа' . PHP_EOL . '
 Обработано ' . $i . ' Строк' . PHP_EOL . '
 Скачено ' . $b . ' файлов' . PHP_EOL
					);
					if (count($errors)) {
						$this->telegram->sendMessage(
							$this->work['chat'],
							"Ошибки\n" . json_encode($errors, 256)
						);
					}
				} catch (\Exception $e) {
					echo $e->getMessage();
				}
			} else {
				try {
					$this->telegram->sendMessage(
						$this->work['chat'],
						'Ошибка. Не удалось создать архив'
					);
					if (count($errors)) {
						$this->telegram->sendMessage(
							$this->work['chat'],
							"Ошибки\n" . json_encode($errors, 256)
						);
					}
				} catch (\Exception $e) {
					echo $e->getMessage();
				}
			}
			if (!empty($this->ftps)) {
				foreach ($this->ftps as $ftp) {
					ftp_close($ftp);
				}
			}
			unlink($this->work['file']);
			if (file_exists($dir)) {
				$it    = new RecursiveDirectoryIterator($dir);
				$files = new RecursiveIteratorIterator(
					$it,
					RecursiveIteratorIterator::CHILD_FIRST
				);
				foreach ($files as $file) {
					if ($file->getFilename() === '.' || $file->getFilename() === '..') {
						continue;
					}
					if ($file->isDir()) {
						rmdir($file->getRealPath());
					} else {
						unlink($file->getRealPath());
					}
				}
			}

		}

		public function httpDownload($url, $name, &$b)
		{
			$curl = curl_init();
			curl_setopt_array($curl, [
				CURLOPT_URL            => $url,
				CURLOPT_RETURNTRANSFER => TRUE,
				CURLOPT_ENCODING       => '',
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 120,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_FOLLOWLOCATION => TRUE,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => 'GET',
				CURLOPT_HTTPHEADER     => [
					'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0',
					'Cache-Control: no-cache',
				],
			]);
			$response     = curl_exec($curl);
			$responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			curl_close($curl);
			if (!$responseCode or (int)$responseCode >= 400) {
				throw new \Exception('Битая ссылка ' . $url, 7);
			}
			if ($response) {
				$b++;
				file_put_contents($name, $response);
			}
			if (!file_exists($name)) {
				throw new \Exception('Битая ссылка ' . $url, 8);
			}
		}

		public function ftpDownload($url_string, $name, &$b = 0)
		{
			$url = parse_url($url_string);
			preg_match("/@" . $url['host'] . "(.+)/", $url_string, $m);
			$url['path'] = $m[1];
			if (isset($this->ftps[$url['host'] . $url['login'] . $url['pass']])) {
				$conn_id = $this->ftps[$url['host'] . $url['login'] . $url['pass']];
			} else {
				$conn_id      = ftp_connect($url['host']);
				$login_result = ftp_login($conn_id, $url['user'], $url['pass']);
				if ($login_result) {
					ftp_pasv($conn_id, TRUE);
					$this->ftps[$url['host'] . $url['login'] . $url['pass']] = $conn_id;
				}
			}
			$path = explode('/', dirname($url['path']));
			//переходим в необходимый каталог
			ftp_chdir($conn_id, '/');
			foreach ($path as $folder) {
				if ($folder) {
					$list = ftp_nlist($conn_id, ".");
					if (in_array($folder, $list)) {
						ftp_chdir($conn_id, $folder);
					}
				}
			}
			$list     = ftp_nlist($conn_id, ".");
			$fileName = basename($url['path']);
			if (in_array($fileName, $list)) {
				$handle   = fopen($name, 'w');
				if (ftp_fget($conn_id, $handle, $fileName, FTP_BINARY, 0)) {
				}
				fclose($handle);
				if (!file_exists($name) or filesize($name) == 0) {
					throw new \Exception('Битая ftp ссылка ' . $url_string, 1);
				} else {
					$b++;
				}
			} else {
				throw new \Exception('Битая ftp ссылка ' . $url_string, 2);
			}
		}

		public function zip($source, $destination)
		{
			if (!extension_loaded('zip') || !file_exists($source)) {
				return FALSE;
			}

			$zip = new ZipArchive();
			if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
				return FALSE;
			}

			$source = str_replace('\\', DIRECTORY_SEPARATOR, realpath($source));
			$source = str_replace('/', DIRECTORY_SEPARATOR, $source);

			if (is_dir($source) === TRUE) {
				$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source),
													   RecursiveIteratorIterator::SELF_FIRST);

				foreach ($files as $file) {
					$file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
					$file = str_replace('/', DIRECTORY_SEPARATOR, $file);

					if ($file == '.' || $file == '..' || empty($file) || $file == DIRECTORY_SEPARATOR) {
						continue;
					}
					// Ignore "." and ".." folders
					if (in_array(substr($file, strrpos($file, DIRECTORY_SEPARATOR) + 1), ['.', '..'])) {
						continue;
					}

					$file = realpath($file);
					$file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
					$file = str_replace('/', DIRECTORY_SEPARATOR, $file);

					if (is_dir($file) === TRUE) {
						$d = str_replace($source . DIRECTORY_SEPARATOR, '', $file);
						if (empty($d)) {
							continue;
						}
						$zip->addEmptyDir($d);
					} elseif (is_file($file) === TRUE) {
						$zip->addFromString(str_replace($source . DIRECTORY_SEPARATOR, '', $file),
											file_get_contents($file));
					} else {
						// do nothing
					}
				}
			} elseif (is_file($source) === TRUE) {
				$zip->addFromString(basename($source), file_get_contents($source));
			}

			return $zip->close();
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