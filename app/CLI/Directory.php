<?php

namespace App\CLI;

use Roducks\Framework\CLI;

use Lib\Directory as DirectoryHandler;
use Path;

class Directory extends CLI
{

	private function _command()
	{
		$this->error('Run this command:');
		$this->error('[x]');
	}

	public function clean($dir = "")
	{

		if (empty($dir)) {
			$this->_command();
			$this->error(self::command(__CLASS__, __FUNCTION__, '[dir] [flag:--remain-folders]'));
		} else {

			$options = [DirectoryHandler::REMOVE_FILES];

			if ($this->getFlag('--remain-folders')) {
				array_push($options, DirectoryHandler::REMAIN_FOLDERS);
			}

			DirectoryHandler::clean(Path::getData($dir), $options);

			$this->success($dir);

		}

		parent::output();
	}

	public function make($dir = "")
	{

		if (empty($dir)) {
			$this->_command();
			$this->error(self::command(__CLASS__, __FUNCTION__, '[dir]'));
		} else {
			DirectoryHandler::make(Path::getData(), $dir);
		}

		parent::output();
	}

	public function remove($dir = "")
	{
		if (empty($dir)) {
			$this->_command();
			$this->error(self::command(__CLASS__, __FUNCTION__, '[dir]'));
		} else {
			DirectoryHandler::remove(Path::getData($dir));
		}

		parent::output();
	}

	public function move($origin = "", $destination = "")
	{
		if (empty($origin) || empty($destination)) {
			$this->_command();
			$this->error(self::command(__CLASS__, __FUNCTION__, '[dir-origin] [dir-destination]'));
		} else {
			DirectoryHandler::move(Path::getData($dir));
		}

		parent::output();
	}
}