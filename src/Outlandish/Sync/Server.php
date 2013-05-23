<?php

namespace Outlandish\Sync;

/**
 * Sync server
 * @package Outlandish\Sync
 */
class Server extends AbstractSync {

	/**
	 * Process JSON request from POST body
	 */
	public function run() {
		//read post data
		$rawRequest = file_get_contents('php://input');

		$response = $this->processRequest($rawRequest);

		if (is_string($response)) {
			$response = array('error' => $response);
		}

		header('Content-type: application/json');
		echo json_encode($response);
	}

	/**
	 * @param $rawRequest string
	 * @return array|string
	 */
	protected function processRequest($rawRequest) {
		if (empty($rawRequest)) {
			return 'No input';
		}

		$request = json_decode($rawRequest, true);

		if (!$request) {
			return 'Invalid JSON';
		} elseif (empty($request['key']) || $request['key'] != $this->key) {
			return 'Missing or invalid key';
		} elseif (empty($request['action'])) {
			return 'Missing action';
		}

		switch ($request['action']) {
			case self::ACTION_FILELIST:

				$localFiles = $this->getFileList($this->path);
				$remoteFiles = $request['data'];

				//compare local and remote file list to get updated files
				$updatedFiles = array();
				foreach ($localFiles as $filePath => $info) {
					if (empty($remoteFiles[$filePath]) || $remoteFiles[$filePath] != $info) {
						$updatedFiles[$filePath] = $info;
					}
				}

				return array('data' => $updatedFiles);

			case self::ACTION_FETCH:

				if (strpos($request['file'], '..') !== false) {
					return 'Security violation';
				} elseif (!file_exists($this->path.$request['file'])) {
					return 'File not found';
				}

				//output file with generic binary mime type
				header('Content-type: application/octet-stream');
				$fp = fopen($this->path . $request['file'], 'rb');
				fpassthru($fp);

				exit;

			default :
				return 'Unhandled action';
		}

	}

}