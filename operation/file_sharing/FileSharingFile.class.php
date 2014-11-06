<?php
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2010 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.


class FileSharingFile {

	protected $id;
	protected $description;
	protected $uploader;

	protected $name;
	protected $fullpath;
	protected $dirname;
	protected $basename;
	protected $filename;
	protected $extension;

	protected $publicKey;
	protected $downloads;

	protected $size;
	protected $created;
	protected $modified;

	protected $exists = false;
	protected $readable = false;

	protected static $fileSharingDir = "attachment/file_sharing";
	protected static $dbTable = "tattachment";
	protected static $dbTableTrack = "tattachment_track";

	/*
	 * Params can be an int with the file id or an array with the file values
	 */
	function __construct ($params = false) {
		global $config;

		self::$fileSharingDir = str_replace($config['homedir']."/", '', self::$fileSharingDir);
		self::$fileSharingDir = $config['homedir']."/".self::$fileSharingDir;

		if (!empty($params)) {
			if (is_array($params)) {
				$this->loadWithArray($params);
			}
			else if (is_numeric($params)) {
				$this->loadWithID($params);
			}
		}
	}

	public function loadWithID ($id) {
		$result = false;

		if (!empty($id) && is_numeric($id)) {
			$fileRow = get_db_row(self::$dbTable, 'id_attachment', $id);

			if (!empty($fileRow)) {
				$this->id = $id;
				$this->description = (string) safe_output($fileRow['description']);
				$this->uploader = (string) safe_output($fileRow['id_usuario']);
				$this->created = !empty($fileRow['timestamp']) ? strtotime($fileRow['timestamp']) : false;
				$this->name = (string) safe_output($fileRow['filename']);
				$this->publicKey = (string) safe_output($fileRow['public_key']);

				// File info
				if (!empty($fileRow) && !empty($this->uploader)) {
					$filename = (string) safe_output($fileRow['filename']);
					if (!empty($filename)) {
						$this->loadFileInfo(self::$fileSharingDir . "/" . $this->uploader . "/" . $this->id . "_" . $filename);
					}

					$result = true;
				}
			}
		}

		return $result;
	}

	public function loadWithArray ($params) {
		$result = false;

		if (!empty($params)) {
			if (isset($params['id']))
				$this->id = $params['id'];
			if (isset($params['description']))
				$this->description = $params['description'];
			if (isset($params['uploader']))
				$this->uploader = $params['uploader'];

			if (isset($params['publicKey']))
				$this->publicKey = $params['publicKey'];
			// Don't load the track info!
			// if (isset($params['downloads']))
			// 	$this->downloads = $params['downloads'];

			if (isset($params['fullpath']))
				$this->fullpath = $params['fullpath'];
			if (isset($params['dirname']))
				$this->dirname = $params['dirname'];
			if (isset($params['basename']))
				$this->basename = $params['basename'];
			if (isset($params['filename']))
				$this->filename = $params['filename'];
			if (isset($params['name']))
				$this->name = $params['name'];
			if (isset($params['extension']))
				$this->extension = $params['extension'];

			if (isset($params['size']))
				$this->size = $params['size'];
			if (isset($params['created']))
				$this->created = $params['created'];
			if (isset($params['modified']))
				$this->modified = $params['modified'];

			if (isset($params['exists']))
				$this->exists = $params['exists'];
			if (isset($params['readable']))
				$this->readable = $params['readable'];

			$result = true;
		}

		return $result;
	}

	public function loadFileInfo ($fullpath) {
		$this->fullpath = $fullpath;

		$path_parts = pathinfo($this->fullpath);

		$this->dirname = $path_parts['dirname'];
		$this->basename = $path_parts['basename'];
		$this->extension = $path_parts['extension'];

		if (!empty($path_parts['filename'])) {
			$this->filename = $path_parts['filename'];

			if (empty($this->name))
				$this->name = $this->filename;
		}
		else {
			$this->filename = str_replace($path_parts['extension'], '', $path_parts['basename']);

			if (empty($this->name))
				$this->name = $this->filename;
		}

		$this->exists = file_exists($this->fullpath);
		$this->readable = is_readable($this->fullpath);

		if ($this->exists && $this->readable) {
			$this->size = filesize($this->fullpath);
			$this->modified = filemtime($this->fullpath);
		}
	}

	public function delete () {
		$this->trackingDeletion();
		$result = $this->deleteFromDisk() && $this->deleteFromDB();

		return $result;
	}

	public function deleteFromDB () {
		$result = false;

		if (isset($this->id)) {
			$where = array('id_attachment' => $this->id);
			$result = process_sql_delete(self::$dbTable, $where);

			if ($result)
				unset($this->id);
		}

		return $result;
	}

	public function deleteFromDisk () {
		$result = false;

		if (isset($this->fullpath)) {
			if (file_exists($this->fullpath)) {
				if ($result = @unlink($this->fullpath)) {
					$this->exists = false;
					$this->readable = false;
				}
			}
			else {
				$this->exists = false;
				$this->readable = false;
				$result = true;
			}
		}

		return $result;
	}

	protected function tracking ($action) {
		global $config;

		$result = false;

		if (isset($this->id) && !empty($this->id)) {
			$userID = isset($config['id_user']) && !empty($config['id_user']) ? $config['id_user'] : '';

			$data = array(
					'remote_addr' => $_SERVER['REMOTE_ADDR']
				);
			// To json string
			$data = json_encode($data);

			$values = array(
					'id_attachment' => $this->id,
					'timestamp' => date('Y-m-d H:i:s'),
					'id_user' => safe_input($userID),
					'action' => $action,
					'data' => safe_input($data)
				);
			$result = process_sql_insert(self::$dbTableTrack, $values);
		}

		return $result;
	}

	protected function trackingCreation () {
		return $this->tracking('creation');
	}

	protected function trackingDeletion () {
		return $this->tracking('deletion');
	}

	public function trackingDownload () {
		return $this->tracking('download');
	}

	public function getTracking ($filter = false) {
		$result = false;

		if (isset($this->id) && !empty($this->id)) {
			if ($filter === false) {
				$filter = array();
			}
			$filter['id_attachment'] = $this->id;

			$result = get_db_all_rows_filter(self::$dbTableTrack, $filter);

			if ($result !== false && is_array($result)) {
				$trackingInfo = array();

				foreach ($result as $row) {
					if (isset($row['data']) && !empty($row['data'])) {
						$row['data'] = safe_output($row['data']);
						$row['data'] = json_decode($row['data'], true);
					}
					if (isset($row['id_user']) && !empty($row['id_user'])) {
						$row['id_user'] = safe_output($row['id_user']);
					}
					$trackingInfo[] = $row;
				}

				$result = $trackingInfo;
			}
		}

		return $result;
	}

	public function getTrackingDownload () {
		return $this->getTracking(array('action' => 'download'));
	}

	public function loadTrackingDownload () {
		$this->downloads = $this->getTrackingDownload();
	}

	public function getId () {
		return $this->id;
	}

	public function getDescription () {
		return $this->description;
	}

	public function getUploader () {
		return $this->uploader;
	}

	public function getFullpath () {
		return $this->fullpath;
	}

	public function getDirname () {
		return $this->dirname;
	}

	public function getPublicKey () {
		return $this->publicKey;
	}

	public function getBasename () {
		return $this->basename;
	}

	public function getFilename () {
		return $this->filename;
	}

	public function getName () {
		return $this->name;
	}

	public function getExtension () {
		return $this->extension;
	}

	public function getSize () {
		return $this->size;
	}

	public function getCreated () {
		return $this->created;
	}

	public function getModified () {
		return $this->modified;
	}

	public function exists () {
		return $this->exists;
	}

	public function isReadable () {
		return $this->readable;
	}

	public function toArray ($showSystemPaths = false) {
		$fileSharingFile = array();

		if (isset($this->id))
			$fileSharingFile['id'] = $this->id;
		if (isset($this->description))
			$fileSharingFile['description'] = $this->description;
		if (isset($this->uploader))
			$fileSharingFile['uploader'] = $this->uploader;

		if (isset($this->publicKey))
			$fileSharingFile['publicKey'] = $this->publicKey;
		if (isset($this->downloads))
			$fileSharingFile['downloads'] = $this->downloads;

		// By default this information are hidden on this function
		// cause it's designed to give information to the client code (javascript)
		if ($showSystemPaths) {
			if (isset($this->fullpath))
				$fileSharingFile['fullpath'] = $this->fullpath;
			if (isset($this->dirname))
				$fileSharingFile['dirname'] = $this->dirname;
		}
		if (isset($this->basename))
			$fileSharingFile['basename'] = $this->basename;
		if (isset($this->filename))
			$fileSharingFile['filename'] = $this->filename;
		if (isset($this->name))
			$fileSharingFile['name'] = $this->name;
		if (isset($this->extension))
			$fileSharingFile['extension'] = $this->extension;

		if (isset($this->size))
			$fileSharingFile['size'] = $this->size;
		if (isset($this->created))
			$fileSharingFile['created'] = $this->created;
		if (isset($this->modified))
			$fileSharingFile['modified'] = $this->modified;

		if (isset($this->exists))
			$fileSharingFile['exists'] = $this->exists;
		if (isset($this->readable))
			$fileSharingFile['readable'] = $this->readable;

		return $fileSharingFile;
	}

	public function toJSON () {
		return json_encode($this->toArray());
	}
}

?>