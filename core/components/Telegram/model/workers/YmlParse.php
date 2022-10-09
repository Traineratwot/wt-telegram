<?php

	namespace components\Telegram\model\workers;

	use components\Telegram\model\AbstractWorker;
	use model\main\Core;

	class YmlParse extends AbstractWorker
	{
		public string $yml     = '';
		public array  $unlinks = [];
		public string $out     = '';
		public array  $allKeys;
		public array  $offers;

		public function __construct(Core $core, $telegram, $work)
		{
			error_reporting(E_ERROR);
			parent::__construct($core, $telegram, $work);
		}

		public function process()
		{
			$path      = $this->work['file'];
			$this->yml = trim(file_get_contents($path));
			if ($this->validate($path)) {
				unset($this->yml);
				if (!file_exists($path . '.json')) {
					$py = WT_CORE_PATH . 'python' . DIRECTORY_SEPARATOR . 'xml_to_json.py';
					exec("python '$py' '$path'", $output);
				}
				sleep(10);
				$path .= '.json';
				if (file_exists($path)) {
					$id        = md5(serialize($this->work));
					$this->out = WT_ASSETS_PATH . 'output/' . $id . '.csv';
					$url       = str_replace(WT_ASSETS_PATH, "https://telegram.massive.ru/assets/", $this->out);
					$this->preParse($path);
					$i = $this->parse();
					if (file_exists($this->out)) {
						$this->writeFile($this->out, $url);
						$this->telegram->sendMessage(
							$this->work['chat'],
							<<<TXT
Готово.\n
Ссылка: $url \n
Ссылка будет доступна 24 часа \n
Количество Строк: {$i}  
TXT
						);
					} else {
						$this->telegram->sendMessage($this->work['chat'], 'Ошибка: при парсинге');
						return FALSE;
					}
				} else {
					$this->telegram->sendMessage($this->work['chat'], 'Ошибка: yml не распознан, попробуйте изменить файл так что бы он начинался с "<yml_catalog"');
					return FALSE;
				}
			}
		}

		public function preParse($path)
		{
			$data          = json_decode(file_get_contents($path), 1);
			$data          = $data['yml_catalog']['shop'];
			$this->offers  = [];
			$this->allKeys = [];

			foreach ($data['offers']['offer'] as $key => $offer) {
				$key = $this->r($key);
				foreach ($offer as $k => $v) {
					$k = $this->r($k);
					if (!is_array($v)) {
						$this->offers[$key][$k] = preg_replace('@\s+@', ' ', htmlspecialchars_decode($v));
					} else {
						switch ($k) {
							case 'prices':
								$this->offers[$key][$this->r('price_' . $v['price']['@price_type'])] = $v['price']['#text'];
								break;
							case 'sizes':
								foreach ($v['size'] as $size) {
									foreach ($size as $u => $h) {
										if ($u !== '@unit') {
											$this->offers[$key][$this->r('size_' . $size['@unit'] . '_' . $u)] = $h;
										}
									}
								}
								break;
							case 'params':
							case 'param':
								foreach ($v as $param) {
									$this->offers[$key][$this->r($param['@name'])] = $param['#text'];
								}
								break;
							default:
								$this->offers[$key][$k] = implode(',', $v);
								break;
						}
					}
				}

				$arrK = array_keys($this->offers[$key]);
				foreach ($arrK as $o) {
					$this->allKeys[$o] = 1;
				}
			}
			$this->allKeys = array_keys($this->allKeys);
		}

		public function parse()
		{
			$i       = 0;
			$keysAll = array_flip($this->allKeys);
			$f       = fopen($this->out, 'wb+');
			fwrite($f, chr(239) . chr(187) . chr(191));
			fputcsv($f, $this->allKeys, ';');
			$total = count($this->offers);
			foreach ($this->offers as $data) {
				$row = array_fill(0, count($this->allKeys), '');
				foreach ($data as $key => $v) {
					if (isset($keysAll[$key])) {
						$row[$keysAll[$key]] = $v;
					}
				}
				fputcsv($f, $row, ';');
				$i++;
				$this->progress($total, $i);
			}
			fclose($f);
			return $i;
		}

		public function validate($path)
		{
			$offset = stripos($this->yml, '<yml_catalog');

			if ($offset !== FALSE) {
				if ($offset > 0) {
					$this->yml = substr($this->yml, $offset);
					file_put_contents($path, $this->yml);
				}
				if (strpos($this->yml, 'shop') !== FALSE) {
					if (strpos($this->yml, 'offers') !== FALSE) {
						return TRUE;
					}
				}
			}
			$this->telegram->sendMessage($this->work['chat'], 'Ошибка: не правильный формат данных');
			return FALSE;
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

		public function r($a)
		{
			return str_replace('@', '', $a);
		}
	}