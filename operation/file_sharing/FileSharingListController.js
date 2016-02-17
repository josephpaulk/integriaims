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

FileSharingListController = {
	controllers: [],
	getController: function () {
		var controller = {
			index: -1,
			domSelector: 0,
			files: [],
			downloadURL: '',
			getRow: function (file) {
				if (this.domSelector.length == 0 || file.length == 0) {
					return '';
				}
				var $row = $("<div class=\"table_row file_sharing_row\"></div>");
				var $cell = $("<div class=\"table_cell file_sharing_cell\"></div>");

				// Filename
				var $filename = $("<div>" + file.name + "</div>"); // Put the download link here
				$filename.addClass("file_sharing_filename")

				$cell.hover(function(e) {
						$filename.animate({
							backgroundColor: "#6A6A6A"
						}, 75);
					}, function(e) {
						$filename.animate({
							backgroundColor: "#505050"
						}, 75);
					});

				var $fileData = $("<div></div>");
				$fileData.addClass("file_sharing_data");

				// Size
				if (typeof file.size != 'undefined') {
					var $fileDataSize = $("<div>" + formatFileSize(file.size) + "</div>");
					$fileDataSize
						.addClass("file_sharing_data_item")
						.addClass("size");

					$fileData.append($fileDataSize);
				}

				// Date
				if (typeof file.modified != 'undefined') {
					var dateModified = new Date(file.modified * 1000); // The js Date class works with ms
					
					var $fileDataDate = $("<div>" + dateModified.toLocaleString() + "</div>");
					$fileDataDate
						.addClass("file_sharing_data_item")
						.addClass("date");

					$fileData.append($fileDataDate);
				}

				// Downloads tracking info
				if (typeof file.downloads != 'undefined' && file.downloads !== false && file.downloads.length > 0) {
					var $fileDataDownloads = $("<div>" + file.downloads.length + "</div>");
					$fileDataDownloads
						.addClass("file_sharing_data_item")
						.addClass("downloads")
						.data("downloads", file.downloads);

					$fileData.append($fileDataDownloads);
				}

				if (typeof file.publicKey != 'undefined' && file.publicKey.length > 0) {
					// Copy to clipboard button
					var $fileClipboard = $("<div></div>");
					$fileClipboard
						.addClass("file_sharing_data_item")
						.addClass("clipboard")
						.data("publicKey", file.publicKey)
						.hover(function(e) {
							$(this).animate({
								backgroundColor: "#F3F3F3"
							}, 75);
						}, function(e) {
							$(this).animate({
								backgroundColor: "#FFFFFF"
							}, 75);
						})
						.append("<img src='images/clipboard.png' />")
						.css("cursor", "pointer");

					$fileData.append($fileClipboard);

					// Download button
					var $fileDownload = $("<div></div>");
					$fileDownload
						.addClass("file_sharing_data_item")
						.addClass("download")
						.data("publicKey", file.publicKey)
						.hover(function(e) {
							$(this).animate({
								backgroundColor: "#F3F3F3"
							}, 75);
						}, function(e) {
							$(this).animate({
								backgroundColor: "#FFFFFF"
							}, 75);
						})
						.append("<img src='images/archive.png' />")
						.css("cursor", "pointer");

					$fileData.append($fileDownload);
				}

				// Delete button
				var $fileDelete = $("<div></div>");
				$fileDelete
					.addClass("file_sharing_data_item")
					.addClass("delete")
					.data("attachmentID", file.id)
					.hover(function(e) {
						$(this).animate({
							backgroundColor: "#F3F3F3"
						}, 75);
					}, function(e) {
						$(this).animate({
							backgroundColor: "#FFFFFF"
						}, 75);
					})
					.append("<img src='images/cross.png' />")
					.css("cursor", "pointer");

				$fileData.append($fileDelete);

				// Package files
				if (typeof file.files != 'undefined' && file.files.length > 0) {
					var $filePackageFilesData = $("<div></div>");
					$filePackageFilesData.hide();
					
					file.files.forEach(function(element, index) {
						$filePackageFilesData.append("<div>" + element.basename + "</div>");
					});

					var $filePackageFiles = $("<div></div>");
					$filePackageFiles
						.addClass("file_sharing_data_item")
						.addClass("package-files")
						.append("<img src='images/arrow_right.png' />")
						.append($filePackageFilesData)
						.hover(function(e) {
							$(this).animate({
								backgroundColor: "#F3F3F3"
							}, 75);
						}, function(e) {
							$(this).animate({
								backgroundColor: "#FFFFFF"
							}, 75);
						})
						.css("cursor", "pointer")
						.click(function(e) {
							if ($filePackageFiles.find("img").hasClass("rotated")) {
								$filePackageFiles.find("img").removeClass("rotated");
								$filePackageFiles.find("img").attr("src","images/arrow_right.png");
							}
							else {
								$filePackageFiles.find("img").addClass("rotated");
								$filePackageFiles.find("img").attr("src","images/arrow_down.png");
							}
							$filePackageFilesData.slideToggle();
						});

					$fileData.append($filePackageFiles);
				}

				$cell.append($filename);
				$cell.append($fileData);

				$row.append($cell);

				return $row;
			},
			addRow: function (file) {
				if (this.domSelector.length == 0 || file.length == 0) {
					return;
				}
				$(this.domSelector).append(this.getRow(file));
			},
			prependRow: function (file) {
				if (this.domSelector.length == 0 || file.length == 0) {
					return;
				}
				$(this.domSelector).prepend(this.getRow(file));
			},
			emptyMessage: 'Empty',
			reloadCallback: false,
			reload: function () {
				$(this.domSelector).empty();

				if (this.domSelector.length == 0 || this.files.length == 0) {
					$(this.domSelector).append("<div>" + this.emptyMessage + "</div>");
					return;
				}
				
				this.files.forEach(function(element, index) {
					this.addRow(element);
				}, this);

				if (this.reloadCallback != false) {
					this.reloadCallback();
				}
			},
			changeFiles: function (files) {
				this.files = files;
				this.reload();
			},
			addFile: function (file) {
				this.files.unshift(file);
				this.reload();
			},
			removeFile: function (fileID) {
				if (fileID != 0 && this.files.length > 0) {
					this.files.splice(fileID, 1);
					this.reload();
				}
			},
			init: function (data) {
				if (typeof data.domSelector != 'undefined' && data.domSelector.length > 0) {
					this.domSelector = data.domSelector;
				}
				if (typeof data.files != 'undefined' && data.files.length > 0) {
					this.files = data.files;
				}
				if (typeof data.downloadURL != 'undefined' && data.downloadURL.length > 0) {
					this.downloadURL = data.downloadURL;
				}
				if (typeof data.reloadCallback != 'undefined') {
					this.reloadCallback = data.reloadCallback;
				}
				if (typeof data.emptyMessage != 'undefined') {
					this.emptyMessage = data.emptyMessage;
				}
				this.index = FileSharingListController.controllers.push(this) - 1;
				this.reload();
			},
			remove: function () {
				$(this.domSelector).empty();
				if (this.index > -1) {
					FileSharingListController.controllers.splice(this.index, 1);
				}
			}
		}
		return controller;
	}
}