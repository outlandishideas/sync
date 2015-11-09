<?php


namespace Outlandish\Sync;

/**
 * Base class for Client and Server
 * @package Outlandish\Sync
 */
abstract class AbstractSync {

	const ACTION_FILELIST = 'filelist';
	const ACTION_FETCH = 'fetch';

	/**
	 * @param $key string
	 * @param $path string
	 */
	public function __construct($key, $path) {
		if (!file_exists($path)) {
			mkdir($path, 0777, true);
		}
		$this->path = realpath($path) . DIRECTORY_SEPARATOR;
		$this->key = $key;
	}

	/**
	 * @param $path string
	 * @return array
	 */
	protected function getFileList($path) {
		$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path,
				\FilesystemIterator::CURRENT_AS_FILEINFO |
				\FilesystemIterator::SKIP_DOTS
		));

		$pathPrefixLength = strlen($path);
		$files = array();
		foreach ($iterator as $fileInfo) {
			$fullPath = str_replace(DIRECTORY_SEPARATOR, '/', substr($fileInfo->getRealPath(), $pathPrefixLength));
			$filePermission = substr(sprintf('%o', fileperms( $fileInfo->getRealPath() )), -4);
			$files[$fullPath] = array('size' => $fileInfo->getSize(), 'timestamp' => $fileInfo->getMTime(), 'fileperm' => $filePermission);
		}

		return $files;
	}
}