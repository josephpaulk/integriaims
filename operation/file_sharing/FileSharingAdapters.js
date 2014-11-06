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

FileSharingFile = function (fileData) {
	var file = {};

	if (typeof fileData != 'undefined') {
		file = {
			id: fileData.id,
			description: fileData.description,
			uploader: fileData.uploader,
			fullpath: fileData.fullpath,
			dirname: fileData.dirname,
			basename: fileData.basename,
			filename: fileData.filename,
			extension: fileData.extension,
			size: fileData.size,
			created: fileData.created,
			modified: fileData.modified,
			exists: fileData.exist,
			readable: fileData.readable
		}
	}

	return file;
}

FileSharingPackage = function (fileData) {
	var file = new FileSharingFile(fileData);

	file.isPackage = true;
	file.files = [];

	return file;
}