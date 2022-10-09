<?php

	namespace model\cli\types;

	use Exception;
	use model\main\Utilities;
	use Traineratwot\config\Config;
	use Traineratwot\PhpCli\types\TString;

	class FilePath extends TString
	{
		/**
		 * @throws Exception
		 */
		public function validate($value)
		{
			$value_ = Utilities::findPath($value);
			return file_exists($value_) ?: 'Invalid path "' . $value . '" ';
		}
	}