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

global $config;
require_once($config['homedir']."/operation/file_sharing/FileSharingFile.class.php");

class FileSharingPackage extends FileSharingFile {
	
	public $isPackage = true;

	private $files;

	public function loadWithID ($id) {
		$result = parent::loadWithID($id);

		if ($result) {
			$result = $this->loadFiles();
		}

		return $result;
	}

	public function loadWithArray ($params) {
		$result = parent::loadWithArray($params);

		if ($result) {
			if (isset($params['files']))
			$this->files = $params['files'];
		}

		return $result;
	}

	/**
	 * Load the files from the zip package.
	 */
	public function loadFiles () {
		$result = false;

		if (isset($this->fullpath)) {
			$this->files = array();

			// Load the file info if not loaded
			if (!isset($this->filename))
				$this->loadFileInfo($this->fullpath);

			if ($this->exists && $this->readable) {
				// Package open
				if (class_exists("ZipArchive")) {

					$zip = new ZipArchive();
					// Zip creation
					$res = $zip->open($this->fullpath);

					if ($res == true) {
						$numFiles = $zip->numFiles;

						if (!empty($numFiles)) {
							$filenames = array();

							for ($i = 0; $i < $numFiles; $i++) { 
								$values = $zip->statIndex($i);

								if (!empty($values)) {
									$file_data = array();

									$file_data['id'] = $values['index'];
									if (isset($this->uploader))
										$file_data['uploader'] = $this->uploader;

									$file_data['basename'] = $values['name'];
									$file_data['size'] = $values['size'];
									$file_data['created'] = $this->created;
									$file_data['modified'] = $values['modified'];
									$file_data['exists'] = true;
									$file_data['readable'] = true;

									$file = new FileSharingFile($file_data);
									$this->files[] = $file;
								}
							}
						}
						$zip->close();
						
						$result = true;
					}
				}
			}
		}

		return $result;
	}

	public function getFiles () {
		// Load the files if they're not loaded yet
		if (!isset($this->files))
			$this->loadFiles();

		return $this->files;
	}

	public function setFiles ($files) {
		if (!empty($files) && is_array($files))
			$this->files = $files;
	}

	public function isPackage () {
		return $this->isPackage;
	}

	/**
	 * Create a zip package with the /tmp files in the user folder on tattachment/file_sharing
	 * and delete the original files.
	 * Fill the files with FileSharingFile objects is required. This objects should have filled
	 * the params 'fullpath' and 'basename'.
	 * 
	 * @return array The index 'status' shows the result of the operation, the index 'message'
	 * returns a message and the index 'bad_files' returns an array with the not created files.
	 */
	public function save () {
		global $config;

		$result = array(
				'status' => false,
				'message' => '',
				'badFiles' => array()
			);

		if (isset($this->files) && !empty($this->files) && is_array($this->files)) {
			if (isset($this->id)) {
				// Do nothing. At this moment the package edition is not supported
				$result['message'] = __('At this moment the package edition is not supported');
			}
			else {
				// Package creation
				if (class_exists("ZipArchive")) {

					// The admin can manage the file uploads as any user
					$user_is_admin = (bool) dame_admin($config['id_user']);
					if ($user_is_admin) {
						$id_user = get_parameter("id_user", $config['id_user']);
						// If the user doesn't exist get the current user
						$user_data = get_user($id_user);
						if (empty($user_data))
							$id_user = $config['id_user'];

						$this->uploader = $id_user;
					}
					else {
						$this->uploader = $config['id_user'];
					}

					if (!isset($this->filename) || empty($this->filename))
						$this->filename = 'IntegriaIMS-SharedFile';
					if (!isset($this->description))
						$this->description = '';
					if (!isset($this->created))
						$this->created = time();

					$this->filename .= ".zip";

					// Insert the package info into the tattachment table
					$values = array();
					$values['id_usuario'] = safe_input($this->uploader);
					$values['filename'] = safe_input($this->filename);
					$values['timestamp'] = date("Y-m-d", $this->created);
					$values['public_key'] = hash("sha256", $id.$this->uploader.$this->filename.$this->created);
					$values['file_sharing'] = 1;
					$id = process_sql_insert(FileSharingFile::$dbTable, $values);

					if (!empty($id)) {
						$this->id = $id;

						if (!file_exists(self::$fileSharingDir) && !is_dir(self::$fileSharingDir)) {
							mkdir(self::$fileSharingDir);
						}
						$userDir = self::$fileSharingDir . "/" . $this->uploader;
						if (!file_exists($userDir) && !is_dir($userDir)) {
							mkdir($userDir);
						}

						$this->fullpath = $userDir . "/" . $this->id . "_" . $this->filename;

						// Zip creation
						$zip = new ZipArchive();
						$res = $zip->open($this->fullpath, ZipArchive::CREATE);
						if ($res === true) {
							foreach ($this->files as $file) {
								if (is_array($file)) {
									$file = new FileSharingFile($file);
								}

								$fullpath = $file->getFullpath();
								$basename = $file->getBasename();
								if ($file->isReadable() && !empty($fullpath) && !empty($basename)) {
									// Add the file to the package
									if (!$zip->addFile($fullpath, $basename)) {
										$result['badFiles'][] = $file;
									}
								}
								else {
									$result['badFiles'][] = $file;
								}
							}
							$zip->close();

							$filesCount = count($this->files);
							$badFilesCount = count($result['badFiles']);

							if ($badFilesCount == 0) {
								$result['status'] = true;
							}
							else if ($badFilesCount < $filesCount) {
								$result['status'] = true;
								$result['message'] = __('Not all the files where added to the package');
							}
							else {
								$result['message'] = __('An error occurred while building the package');
							}

							// Remove the original files
							foreach ($this->files as $file) {
								if (is_array($file)) {
									$file = new FileSharingFile($file);
								}

								$file->deleteFromDisk();
							}

							// Reload the data and recheck the package
							if ($result['status']) {
								$this->loadWithID($this->id);

								if (!$this->exists || !$this->readable) {
									$result['status'] = false;
									$result['message'] = __('An error occurred while building the package');
									$result['badFiles'] = array();

									$this->delete();
								}
								else {
									// The file was created successsfully
									$this->trackingCreation();
								}
							}
						}
					}
					else {
						$result['message'] = __('An error occurred while creating the package');

						foreach ($this->files as $file) {
							if (is_array($file)) {
								$file = new FileSharingFile($file);
							}

							$file->deleteFromDisk();
						}
					}
				}
				else {
					if (get_admin_user($config['id_user']))
						$result['message'] = __("Impossible to handle the package. You have to install the PHP's Zip extension");
					else
						$result['message'] = __('An error occurred while building the package');
				}
			}
		}
		else {
			$result['message'] = __('This package has no files');
		}

		return $result;
	}

	public function toArray () {
		$fileSharingPackage = parent::toArray();

		$files = array();
		if (isset($this->files)) {
			foreach ($this->files as $id => $file) {
				if (is_array($file)) {
					$files[] = $file;
				}
				else {
					$files[] = $file->toArray();
				}
			}
		}
		$fileSharingPackage['files'] = $files;

		return $fileSharingPackage;
	}
}

?>