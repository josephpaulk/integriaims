<?php
// Integria Enterprise
// ============================================
// Copyright (c) 2008 Artica Soluciones Tecnologicas, http://www.artica.es
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation for version 2.
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

define ('FREE_USER', 'INTEGRIA-FREE');



function update_manager_main() {
	global $config;
	
	echo "<p><b>";
	if ($config['current_package'] == 0) 
		echo "<h3 class='update'>".__('You do not have installed any updates of Integria IMS Enterprise')."</h3>";
	else
		echo "<h3 class='update'>".__('Your Integria IMS Enterprise version number is') . ' ' .
			$config['current_package']."</h3>";
	echo "</b></p>";
	
	echo '<p class="info_update">' .
			__('This is a automatilly update Integria IMS. Be careful if you have changed any php file, please make a backup this modified files php. Because the update action ovewrite all php files in Integria IMS.') .
		'</p>' .
		'<p class="info_update">' .
			__('Update Manager sends anonymous information about Integria IMS usage (number of users). To disable it, just remove remote server address from Update Manager in main setup.') .
		'</p>';
		
	echo "<h3 class='update_online'>" . __('Online') . "</h3>";
	
	echo "<div id='box_online' style='width: 100%; background: #ccc; padding: 10px;'>";
	echo "<div class='loading' style='width:100%; text-align: center;'>";
	print_image("images/wait.gif");
	echo "</div>";
	echo "<div class='download_package' style='width:100%; text-align: center; display: none;'>";
	echo __('Downloading the package.');
	echo "</div>";
	echo "<div class='content'></div>";
	echo "<div class='progressbar' style='display: none;'><img class='progressbar_img' src='' /></div>";
	echo "</div>";
	
	
	$loginhash_data = md5($loginhash_user . $config["dbpass"]);
	$loginhash_user = $config['id_user'];
	
	?>
	<script type="text/javascript">
		var version_update = "";
		<?php
		echo 'var loginhash_data = "' . $loginhash_data . '";' . "\n";
		echo 'var loginhash_user = "' . $loginhash_user . '";' . "\n";
		?>
		
		$(document).ready(function() {
			check_online_packages();
			
			
		});
		
		function check_online_packages() {
			var parameters = {};
			parameters['page'] = 'godmode/setup/update_manager';
			parameters['check_online_free_packages'] = 1;
			parameters['loginhash_data'] = loginhash_data;
			parameters['loginhash_user'] = loginhash_user;
			
			jQuery.post(
				"ajax.php",
				parameters,
				function (data) {
					$("#box_online .loading").hide();
					$("#box_online .content").html(data);
				},
				"html"
			);
		}
		
		function update_last_package(package, version) {
			version_update = version;
			
			$("#box_online .content").html("");
			$("#box_online .loading").show();
			$("#box_online .download_package").show();
			
			
			var parameters = {};
			parameters['page'] = 'godmode/setup/update_manager';
			parameters['update_last_free_package'] = 1;
			parameters['package'] = package;
			parameters['loginhash_data'] = loginhash_data;
			parameters['loginhash_user'] = loginhash_user;
			parameters['version'] = version;
			
			jQuery.post(
				"ajax.php",
				parameters,
				function (data) {
					if (data['in_progress']) {
						$("#box_online .loading").hide();
						$("#box_online .download_package").hide();
						
						$("#box_online .content").html(data['message']);
						
						check_progress_update();
					}
					else {
						$("#box_online .content").html(data['message']);
					}
				},
				"json"
			);
		}
		
		function check_progress_update() {
			var parameters = {};
			parameters['page'] = 'godmode/setup/update_manager';
			parameters['check_update_free_package'] = 1;
			parameters['loginhash_data'] = loginhash_data;
			parameters['loginhash_user'] = loginhash_user;
			
			jQuery.post(
				"ajax.php",
				parameters,
				function (data) {
					if (data['correct']) {
						if (data['end']) {
							$("#box_online .content").html(data['message']);
						}
						else {
							$("#box_online .progressbar").show();
							
							$("#box_online .progressbar .progressbar_img").attr('src',
								data['progressbar']);
							
							setTimeout(check_progress_update, 1000);
						}
					}
					else {
						$("#box_online .content").html(data['message']);
					}
				},
				"json"
			);
		}
	</script>
	<?php
}

/**
 * The update copy entirire the tgz or fail (leave some parts copies and some part not).
 * This does make any thing with the BD.
 */
function update_manager_starting_update() {
	global $config;
	
	$path_package = $config['attachment_store'] .
		"/downloads/last_package.tgz";
	
	try {
		$phar = new PharData($path_package);
		$phar->extractTo($config['attachment_store'] . "/downloads/temp_update");
	}
	catch (Exception $e) {
		// handle errors
		
		process_sql_update('tconfig',
			array('value' => json_encode(
					array(
						'status' => 'fail',
						'message' => __('Failed extracting the package to temp directory.')
					)
				)
			),
			array('token' => 'progress_update_status'));
	}
	
	process_sql_update('tconfig',
		array('value' => 50),
		array('token' => 'progress_update'));
	
	$full_path = $config['attachment_store'] . "/downloads/temp_update/trunk";
	
	$result = update_manager_recurse_copy($full_path, $config['homedir'],
		array('install.php'));
	
	if (!$result) {
		process_sql_update('tconfig',
			array('value' => json_encode(
					array(
						'status' => 'fail',
						'message' => __('Failed the copying of the files.')
					)
				)
			),
			array('token' => 'progress_update_status'));
	}
	else {
		process_sql_update('tconfig',
			array('value' => 100),
			array('token' => 'progress_update'));
		process_sql_update('tconfig',
			array('value' => json_encode(
					array(
						'status' => 'end',
						'message' => __('Package extracted successfully.')
					)
				)
			),
			array('token' => 'progress_update_status'));
	}
}


function update_manager_recurse_copy($src, $dst, $black_list) { 
	$dir = opendir($src); 
	@mkdir($dst);
	@trigger_error("NONE");
	
	//debugPrint("mkdir(" . $dst . ")", true);
	while (false !== ( $file = readdir($dir)) ) { 
		if (( $file != '.' ) && ( $file != '..' ) && (!in_array($file, $black_list))) { 
			if ( is_dir($src . '/' . $file) ) { 
				if (!update_manager_recurse_copy($src . '/' . $file,$dst . '/' . $file, $black_list)) {
					return false;
				}
			}
			else { 
				//debugPrint($src . '/' . $file.",".$dst . '/' . $file, true);
				$result = copy($src . '/' . $file,$dst . '/' . $file);
				debugPrint($result, true);
				$error = error_get_last();
				debugPrint($error, true);
				
				if (strstr($error['message'], "copy(") ) {
					return false;
				}
			} 
		} 
	} 
	closedir($dir);
	
	return true;
} 

function update_manager_count_files($path) {
	$count = 0;
	$black_list = array('.', '..');
	$files = scandir($path);
	
	foreach ($files as $file) {
		if (in_array($file, $black_list))
			continue;
		
		if (is_dir(rtrim($path, '/') . '/' . $file)) {
			$count += update_manager_count_files(rtrim($path, '/') . '/' . $file);
		}
		else {
			$count++;
		}
	}
	
	return $count;
}

////////////////////////////////////////////////////////////////////////
// OLD
////////////////////////////////////////////////////////////////////////

function get_user_key ($settings) {
	global $config;
	
	//DISABLED AT THE MOMENT
	/*
	if ($settings->customer_key != FREE_USER) {
		if (! file_exists ($settings->keygen_path)) {
			echo '<h3 class="error">';
			echo __('Keygen file does not exists');
			echo '</h3>';
			
			return '';
		}
		if (! is_executable ($settings->keygen_path)) {
			echo '<h3 class="error">';
			echo __('Keygen file is not executable');
			echo '</h3>';
			
			return '';
		}
		
		$user_key = exec (escapeshellcmd ($settings->keygen_path.
				' '.$settings->customer_key.' '.$config['dbhost'].
				' '.$config['dbuser'].' '.$config['dbpass'].
				' '.$config['dbname']));
				
		return $user_key;
	}
	*/
	
	/* Free users.
	 We only want to know this for statistics records.
	Feel free to disable this extension if you want.
	*/
	
	$users = (int) get_db_value ('COUNT(`id_usuario`)', 'tusuario', 'disabled', 0);
	$incidents = (int) get_db_value ('COUNT(`id_incidencia`)', 'tincidencia');
	$proyects = (int) get_db_value ('COUNT(`id`)', 'tproject', 'disabled', 0);
	$tasks = (int) get_db_value ('COUNT(`id`)', 'ttask');
	$companies = (int) get_db_value ('COUNT(`id`)', 'tcompany');
	
	$user_key = array (
		'users' => $users,
		'incidents' => $incidents,
		'proyects' => $proyects,
		'tasks' => $tasks,
		'companies' => $companies,
		'build' => $config["build"],
		'version' => $config["version"]);
	
	return json_encode ($user_key);
}

function check_installation_open() {
	global $config;
	
	$conf_update = get_db_row('tconfig', 'token', 'update_conf_url');
	
	$dir = $config['homedir'] .  'attachment/update/';
	
	if (empty($conf_update) || !is_dir($dir))
		return false;
	else
		return true;
}

function update_installation() {
	global $config;
	
	$return_var = array('return' => true, 'text' => null);
	
	$row = get_db_row_filter('tconfig', array('token' => 'update_conf_url'));
	
	if (empty($row)) {
		//The url of update manager.
		$conf_update = array('url' => '',
			'last_installed' => '',
			'last_contact' => '',
			'download_mode' => 'curl');
		
		$values = array('token' => 'update_conf_url',
			'value' => $conf_update['url']);
		$return = process_sql_insert('tconfig', $values);
		$values = array('token' => 'update_conf_last_installed',
			'value' => $conf_update['last_installed']);
		$return = process_sql_insert('tconfig', $values);
		$values = array('token' => 'update_conf_last_contact',
			'value' => $conf_update['last_contact']);
		$return = process_sql_insert('tconfig', $values);
		$values = array('token' => 'update_conf_download_mode',
			'value' => $conf_update['download_mode']);
		$return = process_sql_insert('tconfig', $values);
		
		if (!$return) {
			$return_var['return'] = false;
			$return_var['text'][] = __('Unsuccesful store conf data in DB.');
		}
	}
	
	$dir = $config['homedir'] .  'attachment/update/';
	
	if (!is_dir($dir)) {
		$return = mkdir($dir);
		
		if (!$return) {
			$return_var['return'] = false;
			$return_var['text'][] = __('Unsuccesful create a dir to save package in Integria');
		}
	}
	
	return $return_var;
}

function update_get_conf() {
	global $config;
	
	$conf = array();
	$row = get_db_row('tconfig', 'token', 'update_conf_url');
	$conf['url'] = $row['value'];
	$row = get_db_row('tconfig', 'token', 'update_conf_last_installed');
	$conf['last_installed'] = $row['value'];
	$row = get_db_row('tconfig', 'token', 'update_conf_last_contact');
	$conf['last_contact'] = $row['value'];
	$row = get_db_row('tconfig', 'token', 'update_conf_download_mode');
	$conf['download_mode'] = $row['value'];
	
	$conf['dir'] =  $config['homedir'] .  'attachment/update/';
	
	return $conf;
}

function update_update_conf() {
	global $config;
	global $conf_update;
	
	$values = array('value' => $conf_update['last_installed']);
	$return = process_sql_update('tconfig', $values,
		array('token' => 'update_conf_last_installed'));
	$values = array('value' => $conf_update['last_contact']);
	$return = process_sql_update('tconfig', $values,
		array('token' => 'update_conf_last_contact'));
	$values = array('value' => $conf_update['download_mode']);
	$return = process_sql_update('tconfig', $values,
		array('token' => 'update_conf_download_mode'));
	
	return $return;
}

function update_get_list_downloaded_packages($mode = 'operation') {
	global $config;
	global $conf_update;
	
	$dir = dir($conf_update['dir']);
	if ($dir === false) {
		ui_print_error_message(sprintf(
			__('Error reading the dir of packages in "%s".'), $conf_update['dir']));
		return;
	}
	
	$packages = array();
	while (false !== ($entry = $dir->read())) {
		if (is_file($conf_update['dir'] . $entry)
			&& is_readable($conf_update['dir'] . $entry)) {
			if (strstr($entry, '.tar.gz') !== false) {
				if ($mode == 'operation') {
					$packages[] = $entry;
				}
				else {
					$time_file = date($config["date_format"],
						filemtime($conf_update['dir'] . $entry));
					
					if ($conf_update['last_installed'] == $entry) {
						$packages[] = array('name' => $entry,
							'current' => true,
							'time' => $time_file);
					}
					else {
						$packages[] = array('name' => $entry,
							'current' => false,
							'time' => $time_file);
					}
				}
			}
		}
	}
	
	if (empty($packages)) {
		if ($mode == 'operation') {
			$packages[] = array('name' => 
				__('There are not downloaded packages in your Integria.'),
				'time' => '');
		}
		else {
			$packages[] = array('empty' => true, 'name' =>
				__('There are not downloaded packages in your Integria.'),
				'time' => '');
		}
	}
	
	return $packages;
}

function update_print_javascript_admin() {
	$extension_php_file = 'godmode/updatemanager/main';
	
	?>
	<script type="text/javascript">
		var disabled_download_package = false;
		var last = 0;
		
		$(document).ready(function() {
			ajax_get_online_package_list_admin();
			
			$("#submit-hide_download_dialog").click (function () {
				//Better than fill the downloaded packages
				location.reload();
				//$("#dialog_download" ).dialog('close');
			});
		});
		
		function delete_package(package) {
			url = window.location + "&delete_package=1"
				+ '&package=' + package;
			
			window.location.replace(url);
		}
		
		function ajax_start_install_package(package) {
			$(".package_name").html(package);
			
			$("#dialog_download").dialog({
					resizable: false,
					draggable: false,
					modal: true,
					height: 400,
					width: 650,
				});
			$(".ui-dialog-titlebar-close").hide();
			$("#dialog_download").show();
			
			$("#title_downloading_update_pandora").hide();
			$("#title_downloaded_update_pandora").show();
			$("#title_installing_update_pandora").show();
			
			install_package(package, package);
		}
		
		function ajax_start_download_package(package) {
			$(".package_name").html(package);
			
			$("#dialog_download").dialog({
					resizable: false,
					draggable: true,
					modal: true,
					height: 400,
					width: 650,
					overlay: {
							opacity: 0.5,
							background: "black"
						},
				});
			$(".ui-dialog-titlebar-close").hide();
			$("#dialog_download").show();
			
			var parameters = {};
			parameters['page'] = "<?php echo $extension_php_file;?>";
			parameters['download_package'] = 1;
			parameters['package'] = package;
			
			$.ajax({
				type: 'POST',
				url: 'ajax.php',
				data: parameters,
				dataType: "json",
				success: function(data) {
					if (data['correct'] == 1) {
						if (data['mode'] == 'wget') {
							disabled_download_package = true;
							$("#progress_bar_img img").show();
							install_package(package, data['filename']);
						}
					}
				}
			});
			
			check_download_package(package);
		}
		
		function check_download_package(package) {
			var parameters = {};
			parameters['page'] = "<?php echo $extension_php_file;?>";
			parameters['check_download_package'] = 1;
			parameters['package'] = package;
			
			$.ajax({
				type: 'POST',
				url: 'ajax.php',
				data: parameters,
				dataType: "json",
				success: function(data) {
					if (disabled_download_package)
						return;
					
					if (data['correct'] == 1) {
						if (data['mode'] == 'wget') {
							$("#info_text").show();
							$("#info_text").html(data['info_download']);
							$("#progress_bar_img img").hide();
						}
						else {
							$("#info_text").show();
							$("#info_text").html(data['info_download']);
							
							$("#progress_bar_img img").attr('src', data['progres_bar_src']);
							$("#progress_bar_img img").attr('alt', data['progres_bar_alt']);
							$("#progress_bar_img img").attr('title', data['progres_bar_title']);
							
							if (data['percent'] < 100) {
								check_download_package(package);
							}
							else {
								install_package(package, data['filename']);
							}
						}
					}
					else {
						$("#title_downloading_update_pandora").hide();
						$("#title_error_update_pandora").show();
						$("#progress_bar_img").hide();
						
						$("#info_text").html('');
						
						$("#button_close_download_disabled").hide();
						$("#button_close_download").show();
					}
				}
			});
		}
		
		function install_package(package, filename) {
			var parameters = {};
			parameters['page'] = "<?php echo $extension_php_file;?>";
			parameters['install_package'] = 1;
			parameters['package'] = package;
			parameters['filename'] = filename;
			
			$.ajax({
				type: 'POST',
				url: 'ajax.php',
				data: parameters,
				dataType: "json",
				success: function(data) {
				}
			});
			
			$("#title_downloading_update_pandora").hide();
			$("#title_downloaded_update_pandora").show();
			$("#title_installing_update_pandora").show();
			
			check_install_package(package, filename);
		}
		
		function check_install_package(package, filename) {
			var parameters = {};
			parameters['page'] = "<?php echo $extension_php_file;?>";
			parameters['check_install_package'] = 1;
			parameters['package'] = package;
			parameters['filename'] = filename;
			
			$.ajax({
				type: 'POST',
				url: 'ajax.php',
				data: parameters,
				dataType: "json",
				success: function(data) {
					if (data['correct'] == 1) {
						$("#info_text").show();
						$("#info_text").html(data['info']);
						$("#list_files_install").scrollTop(
							$("#list_files_install").attr("scrollHeight"));
						
						$("#progress_bar_img img").attr('src', data['src']);
						$("#progress_bar_img img").attr('alt', data['alt']);
						$("#progress_bar_img img").attr('title', data['title']);
						
						if (data['percent'] < 100) {
							check_install_package(package, filename);
						}
						else {
							$("#title_installing_update_pandora").hide();
							$("#title_installed_update_pandora").show();
							$("#button_close_download_disabled").hide();
							$("#button_close_download").show();
						}
					}
				}
			})
		}
		
		function ajax_get_online_package_list_admin() {
			var buttonUpdateTemplate = '<?php
				print_button(__('Update'), 'update', false,
					'ajax_start_download_package(\\\'pandoraFMS\\\');', 'class="sub upd"');
				?>';
			
			var parameters = {};
			parameters['page'] = "<?php echo $extension_php_file;?>";
			parameters['get_packages_online'] = 1;
			parameters['last'] = last;
			
			$.ajax({
				type: 'POST',
				url: 'ajax.php',
				data: parameters,
				dataType: "json",
				success: function(data) {
					if (data['correct'] == 1) {
						buttonUpdate = buttonUpdateTemplate.replace('pandoraFMS', data['package']);
						
						$("tbody", "#online_packages").append(
							'<tr class="package_' + data['package'] + '">' + 
								'<td style=" text-align:left; width:50%;" valign="top" class="name_package">' + 
									'<?php echo '<b>' . __('There is a new version:') . '</b><br><br> '; ?>' +
									data['package'] +
								'</td>' +
								'<td style=" text-align:left; width:30%;" valign="bottom" class="timestamp_package">' +
									data['timestamp'] +
								'</td>' +
								'<td style=" text-align:center; width:50px;" valign="bottom">' +
								buttonUpdate +
								'</td>' +
							'</tr>');
						
						last = data['last'];
						
						if (data['end'] == 1) {
							$(".spinner_row", "#online_packages").remove();
						}
						else {
							ajax_get_online_package_list_admin();
						}
					}
					else {
						$(".spinner_row", "#online_packages").remove();
						row_html = '<tr class="package_' + data['package'] + '">' + 
							'<td style=" text-align:left; width:80%;" class="name_package">' +
							data['package'] +
							'</td>' +
							'<td style=" text-align:center; width:50px;"></td>' +
							'</tr>';
						console.log(row_html);
						$("tbody", "#online_packages").append(row_html); return;
						$("tbody", "#online_packages").append(
							);
					}
				}
			});
		}
	</script>
	<?php
}

function paint_open_update_manager() {
	global $config;
	global $conf_update;
	
	// Session check
	check_login ();
	
	if (! give_acl ($config['id_user'], 0, 'PM')) {
		audit_db ($config['id_user'], $REMOTE_ADDR, "ACL Violation",
			"Trying to use Open Update Manager extension");
		
		include ("general/noaccess.php");
		
		exit;
	}
	
	if (!check_installation_open()) {
		ui_print_error_message(__('First execution of Update'));
		update_installation();
	}
	
	$delete_package = (bool)get_parameter('delete_package');
	if ($delete_package) {
		$package = get_parameter('package');
		
		$dir = $config['homedir'] .  'attachment/update/';
		
		$result = unlink($dir . $package);
	}
	
	$conf_update = update_get_conf();
	
	if (!empty($conf_update['last_installed'])){
		echo '<h4>';
		echo __('Your Integria open source package installed is') .
			' ' . $conf_update['last_installed'];
		echo "</h4>";
	}
	else {
		echo '<h4>';
		echo __('Your Integria does not have any update installed yet');
		echo "</h4>";
	}
	
	echo "<br><br>";
	
	echo "<h4>". __('Online') . '</h4>';
	
	echo '<table id="online_packages" class="databox" width="95%" cellspacing="4" cellpadding="4" border="0" style="">' .
			'<tbody>
				<tr id="online_packages-0" class="spinner_row" style="">
					<td id="online_packages-0-0" style=" text-align:left; width:80%;">' .
						__('Get list online Package') . " " . print_image('images/spinner.gif', true) . 
					'</td>
					<td id="online_packages-0-1" style=" text-align:center; width:50px;"></td>
				</tr>
			</tbody>' .
		'</table>';
	
	
	
	////////////////////Float dialog
	?>
	<div id="dialog_download" class="dialog" title="<?php echo __('Process packge'); ?>"
		style="display: none;">
		<div style="position:absolute; top:10%; text-align: center; left:0%; right:0%; width:600px;">
			<?php
			echo '<h4 id="title_downloading_update">' . __('Downloading <span class="package_name">package</span> in progress') . " ";
			print_image('images/spinner.gif');
			echo '</h4>';
			echo '<h4 style="display: none;" id="title_downloaded_update">' . __('Downloaded <span class="package_name">package</span>') . '</h2>';
			echo '<h4 style="display: none;" id="title_installing_updatea">' . __('Installing <span class="package_name">package</span> in progress') . " ";
			print_image('images/spinner.gif');
			echo '</h4>';
			echo '<h4 style="display: none;" id="title_installed_update">' . __('Installed <span class="package_name">package</span> in progress') . '</h2>';
			echo '<h4 style="display: none;" id="title_error_update">' . __('Fail download <span class="package_name">package</span>') . '</h2>';
			echo '<br /><br />';
			echo "<div id='progress_bar_img'>";
				echo progress_bar(0, 300, 20, 0 . '%', 1, false, "#00ff00");
			echo "</div>";
			
			echo "<div style='padding-top: 10px; display: none;' id='info_text'>
					<b>Size:</b> 666/666 kbytes <b>Speed:</b> 666 bytes/second
				</div>";
			
			?>
			<div id="button_close_download_disabled" style="position: absolute; top:280px; right:43%;">
				<?php
				print_submit_button(__("Close"), 'hide_download_disabled_dialog', true, 'class="ui-button-dialog ui-widget ui-state-default ui-corner-all ui-button-text-only" style="width:100px;"');
				?>  
			</div>
			<div id="button_close_download" style="display: none; position: absolute; top:280px; right:43%;">
				<?php
				print_submit_button(__("Close"), 'hide_download_dialog', false, 'class="ui-button-dialog ui-widget ui-state-default ui-corner-all ui-button-text-only" style="width:100px;"');
				?>  
			</div>
		 </div> 
	</div>
	<?php
	
	echo '<h4>' . __('Downloaded Packages') . '</h4>';
	$tableMain = null;
	$tableMain->width = '95%';
	$tableMain->data = array();
	
	$list_downloaded_packages = update_get_list_downloaded_packages('administration');
	if (empty($list_downloaded_packages))
		$list_downloaded_packages = array();
	$table = null;
	$table->width = '100%';
	$table->size = array('50%', '25%', '25%');
	$table->align = array('left', 'center');
	$table->data = array();
	foreach ($list_downloaded_packages as $package) {
		$actions = '';
		if (!isset($package['empty'])) {
			if (!$package['current']) {
				$actions =  print_button(__('Install'),
					'install_' . uniqid(), false,
					'ajax_start_install_package(\'' . $package['name'] . '\');',
					'class="sub next" style="width: 40%;"', true);
			}
			else {
				$actions = print_button(__('Reinstall'),
					'reinstall_' . uniqid(), false,
					'ajax_start_install_package(\'' . $package['name'] . '\');',
					'class="sub upd" style="width: 40%;"', true);
			}
			$actions .= ' ' . print_button(__('Delete'),
				'delete' . uniqid(), false,
				'delete_package(\'' . $package['name'] . '\');',
				'class="sub delete" style="width: 40%;"', true);
		}
		$table->data[] = array($package['name'], $package['time'], $actions);
	}
	$tableMain->data[1][0] = print_table($table, true);
	
	print_table($tableMain);
	
	update_print_javascript_admin();
}
?>
