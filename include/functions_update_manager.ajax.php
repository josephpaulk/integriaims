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

function update_get_packages_online_ajax($ajax = true) {
	global $config;
	
	global $conf_update;
	if (empty($conf_update))
		$conf_update = update_get_conf();
	
	$last = get_parameter('last', 0);
	
	clean_cache_db();
	$settings = um_db_load_settings ();
	$user_key = get_user_key ($settings);
	
	$params = array(
		new xmlrpcval((int)$conf_update['last_contact'], 'int'),
		new xmlrpcval($user_key, 'string'),
		new xmlrpcval($settings->customer_key, 'string'));
	
	$result = @um_xml_rpc_client_call($settings->update_server_host,
		$settings->update_server_path,
		$settings->update_server_port,
		$settings->proxy,
		$settings->proxy_port,
		$settings->proxy_user,
		$settings->proxy_pass,
		'get_lastest_package_update_open',
		$params);
	
	if ($result == false) {
		$return['last'] = $last;
		$return['correct'] = 0;
		$return['package'] = __('Error download packages.');
		$return['end'] = 1;
	}
	else {
		$value = $result->value();
		list($k,$v) = $value->structeach();
		if ($k == 'package') {
			$package = $v->scalarval();
		}
		list($k,$v) = $value->structeach();
		if ($k == 'timestamp') {
			$timestamp = (int)$v->scalarval();
		}
		
		$return['correct'] = 1;
		if (empty($package)) {
			$return['correct'] = 0;
		}
		
		$return['last'] = $last;
		$return['package'] = $package;
		$return['timestamp'] = date($config["date_format"], $timestamp);
		$return['text_adv'] = print_image('images/world.png', true);
		$return['end'] = 1;
	}
	
	if ($ajax)
		echo json_encode($return);
	else
		return $return;
}

function update_download_package() {
	global $config;
	global $conf_update;
	if (empty($conf_update))
		$conf_update = update_get_conf();
	
	$package = get_parameter('package', '');
	
	$params = array(new xmlrpcval($package, 'string'));
	
	$settings = um_db_load_settings ();
	
	$result = @um_xml_rpc_client_call ($settings->update_server_host,
		$settings->update_server_path,
		$settings->update_server_port,
		$settings->proxy,
		$settings->proxy_port,
		$settings->proxy_user,
		$settings->proxy_pass,
		'get_lastest_package_url_update_open',
		$params);
	
	if ($result == false) {
		$info_json = json_encode(array('correct' => 0));
		
		file_put_contents('/tmp/' . $package . '.info.txt', $info_json, LOCK_EX);
		
		$return = array('correct' => 0);
	}
	else {
		$value = $result->value();
		$package_url = $value->scalarval();
		
		if ($conf_update['download_mode'] == 'wget') {
			$command = "wget " .
				$package_url . " -O " . $conf_update['dir'] . $package .
				" -o /tmp/" . $package . ".info.txt";
			
			$return = array('correct' => 0);
			
			exec($command);
			unlink('/tmp/' . $package . '.info.txt');
			
			$return['correct'] = 1;
		}
		else {
			if (empty($package_url)) {
				$info_json = json_encode(array('correct' => 0));
				
				file_put_contents('/tmp/' . $package . '.info.txt',
					$info_json, LOCK_EX);
				
				$return = array('correct' => 0);
			}
			else {
				$targz = $package;
				$url = $package_url;
				
				$file = fopen($conf_update['dir'] . $targz, "w");
				
				$mch = curl_multi_init();
				$c = curl_init();
				
				curl_setopt($c, CURLOPT_URL, $url);
				curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($c, CURLOPT_FILE, $file);
				
				curl_multi_add_handle($mch ,$c);
				$running = null;
				do {
					curl_multi_exec($mch ,$running);
					if ($running == 0) {
						fclose($file);
					}
					
					$info = curl_getinfo ($c);
					debugPrint($info, true);
					
					$data = array();
					$data['correct'] = 1;
					$data['filename'] = $targz;
					$data['size'] = $info['download_content_length'];
					$data['size_download'] = $info['size_download'];
					$data['speed_download'] = $info['speed_download'];
					$data['total_time'] = $info['total_time'];
					
					$info_json = json_encode($data);
					
					file_put_contents('/tmp/' . $package . '.info.txt',
						$info_json, LOCK_EX);
					
					sleep(1);
				}
				while($running > 0);
				
				$return = array('correct' => 1);
			}
		}
		
		if ($return['correct']) {
			$conf_update['last_contact'] = time();
			update_update_conf();
		}
	}
	
	$return['mode'] = $conf_update['download_mode'];
	$return['filename'] = $package;
	
	echo json_encode($return);
}

function update_check_download_package() {
	global $config;
	
	global $conf_update;
	if (empty($conf_update))
		$conf_update = update_get_conf();
	
	ob_start();
	require_once ($config["homedir"] . '/include/functions_graph.php');
	ob_get_clean();
	
	sleep(1);
	
	if ($conf_update['download_mode'] == 'wget') {
		$return = array('correct' => 1,
			'info_download' => __('In progress...') .
				print_image('images/spinner.gif', true),
			'mode' => 'wget');
	}
	else {
		
		$package = get_parameter('package', '');
		$return = array('correct' => 1,
			'info_download' => "<b>" . __('Size') . ":</b> %s/%s " . __('bytes') . " " .
				"<b>" . __('Speed') . ":</b> %s " . __('bytes/second') ."<br />" .
				"<b>" . __('Time') . ":</b> %s",
			'progres_bar' => progress_bar(0, 300, 20, '0%', 1, false, "#00ff00"),
			'progres_bar_text' => '0%',
			'percent' => 0,
			'mode' => 'curl');
		
		$info_json = @file_get_contents('/tmp/' . $package . '.info.txt');
		
		$info = json_decode($info_json, true);
		
		if ($info['correct'] == 0) {
			$return['correct'] = 0;
			unlink('/tmp/' . $package . '.info.txt');
		}
		else {
			$percent = 0;
			$size_download = 0;
			$size = 0;
			$speed_download = 0;
			$total_time = 0;
			if ($info['size_download'] > 0) {
				$percent = format_numeric(
					($info['size_download'] / $info['size']) * 100, 2);
				$return['percent'] = $percent;
				$size_download = $info['size_download'];
				$size = $info['size'];
				$speed_download = $info['speed_download'];
				$total_time = $info['total_time'];
				
				$return['info_download'] = sprintf($return['info_download'],
					format_for_graph($size_download, 2), format_for_graph($size, 2),
					format_for_graph($speed_download, 2),
					human_time_description_raw($total_time));
			}
			else {
				$return['info_download'] = __('<b>Starting: </b> connect to server');
			}
			
			$img = progress_bar($percent, 300, 20, $percent . '%', 1, false, "#00ff00");
			$return['progres_bar'] = $img;
			preg_match_all('/src=[\'\"]([^\"^\']*)[\'\"]/i', $img, $attr);
			$return['progres_bar_src'] = $attr[1];
			preg_match_all('/alt=[\'\"]([^\"^\']*)[\'\"]/i', $img, $attr);
			$return['progres_bar_alt'] = $attr[1];
			preg_match_all('/title=[\'\"]([^\"^\']*)[\'\"]/i', $img, $attr);
			$return['progres_bar_title'] = $attr[1];
			$return['filename'] = $info['filename'];
		}
	}
	
	echo json_encode($return);
}

function update_check_install_package() {
	global $config;
	
	ob_start();
	require_once ($config["homedir"] . '/include/functions_graph.php');
	ob_get_clean();
	
	sleep(1);
	
	$package = get_parameter('package', '');
	$filename = get_parameter('filename', '');
	
	$files = @file('/tmp/' . $package . '.files.info.txt');
	if (empty($files))
		$files = array();
	$total = (int)@file_get_contents('/tmp/' . $package . '.info.txt');
	
	$return = array('correct' => 1,
		'info' => "<div id='list_files_install'
			style='text-align: left; margin: 10px; padding: 5px; width: 90%%; height: 100px;
			overflow: scroll; border: 1px solid #666'>%s</div>",
		'src' => progress_bar(0, 300, 20, '0%', 1, false, "#0000ff"),
		'alt' => '0%',
		'percent' => 0);
	
	$percent = 0;
	if ((count($files) > 0) && ($total > 0)) {
		$percent = format_numeric((count($files) / $total) * 100, 2);
		if ($percent > 100)
			$percent = 100;
	}
	
	$files_txtbox = (string)implode("<br />", $files);
	$return['info'] = sprintf($return['info'], $files_txtbox);
	$img = progress_bar($percent, 300, 20, $percent . '%', 1, false, "#0000ff");
	$return['percent'] = $percent;
	preg_match_all('/src=[\'\"]([^\"^\']*)[\'\"]/i', $img, $attr);
	$return['src'] = $attr[1];
	preg_match_all('/alt=[\'\"]([^\"^\']*)[\'\"]/i', $img, $attr);
	$return['alt'] = $attr[1];
	preg_match_all('/title=[\'\"]([^\"^\']*)[\'\"]/i', $img, $attr);
	$return['title'] = $attr[1];
	
	if ($percent == 100) {
		unlink('/tmp/' . $package . '.files.info.txt');
		unlink('/tmp/' . $package . '.info.txt');
	}
	
	echo json_encode($return);
}

function update_install_package() {
	global $config;
	
	ob_start();
	require_once ($config["homedir"] . '/include/functions_graph.php');
	ob_get_clean();
	
	sleep(1);
	
	$package = get_parameter('package', '');
	$filename = get_parameter('filename', '');
	
	$files = @file('/tmp/' . $package . '.files.info.txt');
	if (empty($files))
		$files = array();
	$total = (int)@file_get_contents('/tmp/' . $package . '.info.txt');
	
	$return = array('correct' => 1,
		'info' => "<div id='list_files_install'
			style='text-align: left; margin: 10px; padding: 5px; width: 90%%; height: 100px;
			overflow: scroll; border: 1px solid #666'>%s</div>",
		'src' => progress_bar(0, 300, 20, '0%', 1, false, "#0000ff"),
		'alt' => '0%',
		'percent' => 0);
	
	$percent = 0;
	if ((count($files) > 0) && ($total > 0)) {
		$percent = format_numeric((count($files) / $total) * 100, 2);
		if ($percent > 100)
			$percent = 100;
	}
	
	$files_txtbox = (string)implode("<br />", $files);
	$return['info'] = sprintf($return['info'], $files_txtbox);
	$img = progress_bar($percent, 300, 20, $percent . '%', 1, false, "#0000ff");
	$return['percent'] = $percent;
	preg_match_all('/src=[\'\"]([^\"^\']*)[\'\"]/i', $img, $attr);
	$return['src'] = $attr[1];
	preg_match_all('/alt=[\'\"]([^\"^\']*)[\'\"]/i', $img, $attr);
	$return['alt'] = $attr[1];
	preg_match_all('/title=[\'\"]([^\"^\']*)[\'\"]/i', $img, $attr);
	$return['title'] = $attr[1];
	
	if ($percent == 100) {
		unlink('/tmp/' . $package . '.files.info.txt');
		unlink('/tmp/' . $package . '.info.txt');
	}
	
	echo json_encode($return);
}
?>
