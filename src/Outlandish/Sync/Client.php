<?php

namespace Outlandish\Sync;

/**
 * Sync client
 * @package Outlandish\Sync
 */
class Client extends AbstractSync {

	/**
	 * @var resource cURL handle
	 */
	public $curl;

	/**
	 * Initiates the sync by exchanging file lists
	 * @param $url string URL to remote sync server script
	 */
	public function run($url) {

		$this->curl = curl_init($url);

		//send client file list to server
		$localFiles = $this->getFileList($this->path);
		$request = array(
			'action' => self::ACTION_FILELIST,
			'data' => $localFiles
		);
		$response = $this->post($request);

		if (isset($response['error'])) {
			echo $response['error'];
			return;
		}

		//process modified files
		foreach ($response['data'] as $relativePath => $info) {
			//fetch file contents
			$response = $this->post(array(
				'action' => self::ACTION_FETCH,
				'file' => $relativePath
			));

			//save file
			$absolutePath = $this->path . $relativePath;
			if (!file_exists(dirname($absolutePath))) {
				mkdir(dirname($absolutePath), 0777, true);
			}
			file_put_contents($absolutePath, $response);

			//update modified time to match server
			touch($absolutePath, $info['timestamp']);

			//update permissions to match server
			chmod($absolutePath, octdec(intval($info['fileperm'])));
		}
	}

	/**
	 * @param $data array
	 * @return mixed
	 * @throws \RuntimeException
	 */
	protected function post($data) {

		$data['key'] = $this->key;

		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->curl, CURLOPT_HEADER, 1);
		curl_setopt($this->curl, CURLOPT_POST, 1);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

		list($headers, $body) = explode("\r\n\r\n", curl_exec($this->curl), 2);
		$code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
		if ($code != 200) {
			throw new \RuntimeException('HTTP error: '.$code);
		}

		if (stripos($headers, 'Content-type: application/json') !== false) {
			$body = json_decode($body, 1);
		}

		return $body;
	}

}