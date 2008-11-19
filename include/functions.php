<?php

// INTEGRIA IMS v2.0
// http://www.integriaims.com
// ===========================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2008 Artica, info@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License (LGPL)
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

define ('ENTERPRISE_NOT_HOOK', -1);

/*

Note about clean_input, clean_output and other string functions
----------------------------------------------------------------

ALL Data stored in database SHOULD have been parsed with clean_input()
to encode all conflictive characters, like <, >, & or ' and ".

ALL Data used to output in a different way than HTML render, (in PDF,
Graphs or HTML input controls ) SHOULD parse before with clean_output()
to decode HTML characters.

*/

/**
* Returns a single string with HTML characters decoded
*
* $input    string  Input string
*/

function ascii_output ($string){
	return clean_output ($string);
}

function clean_output ($string) {
	return html_entity_decode ($string, ENT_QUOTES, "UTF-8");
}

function remove_locale_chars ($string){
	$filtro0 = utf8_decode($string);
	$filtro1 = str_replace ('&aacute;',"a", $filtro0);
	$filtro2 = str_replace ('&eacute;',"e", $filtro1);
	$filtro3 = str_replace ('&iacute;',"i", $filtro2);
	$filtro4 = str_replace ('&oacute;',"o", $filtro3);
	$filtro5 = str_replace ('&uacute;',"u", $filtro4);
	$filtro6 = str_replace ('&ntilde;',"n", $filtro5);
	return $filtro6;
}

/**
* Clean input text
*
* This function clean a user string to be used of SQL operations or
* other kind of sensible string operations (like XSS)
* This replace all conflictive characters.
*
* $value	string	Input string to be cleaned*/

function clean_input ($value) {
	if (is_numeric ($value))
		return $value;
	if (is_array ($value)) {
		array_walk ($value, 'clean_input');
		return $value;
	}
	return htmlentities (utf8_decode ($value), ENT_QUOTES); 						
}

/**
* Returns a single string replacing new lines for <br> HTML tag
*
* $input    string  Input string
*/

function clean_output_breaks ($string) {
	return preg_replace ('/\n/', "<br />", $string);
}

/** 
 * Cleans a string by decoding from UTF-8 and replacing the HTML
 * entities.
 * 
 * @param value String or array of strings to be cleaned.
 * 
 * @return The cleaned string.
 */
function safe_input ($value) {
	if (is_numeric ($value))
		return $value;
	if (is_array ($value)) {
		array_walk ($value, 'safe_input');
		return $value;
	}
	return htmlentities (utf8_decode ($value), ENT_QUOTES); 
}

/** 
 * Get a parameter from get request array.
 * 
 * @param name Name of the parameter
 * @param default Value returned if there were no parameter.
 * 
 * @return Parameter value.
 */
function get_parameter_get ($name, $default = "") {
	if ((isset ($_GET[$name])) && ($_GET[$name] != ""))
		return safe_input ($_GET[$name]);

	return $default;
}

/** 
 * Get a parameter from post request array.
 * 
 * @param name Name of the parameter
 * @param default Value returned if there were no parameter.
 * 
 * @return Parameter value.
 */
function get_parameter_post ($name, $default = "") {
	if ((isset ($_POST[$name])) && ($_POST[$name] != ""))
		return safe_input ($_POST[$name]);

	return $default;
}

/** 
 * Get a paramter from a request.
 *
 * It checks first on post request, if there were nothing defined, it
 * would return get request
 * 
 * @param name 
 * @param default 
 * 
 * @return 
 */
function get_parameter ($name, $default = '') {
	// POST has precedence
	if (isset($_POST[$name]))
		return get_parameter_post ($name, $default);

	if (isset($_GET[$name]))
		return get_parameter_get ($name, $default);

	return $default;
}

// ---------------------------------------------------------------
// no_permission () - Display no perm. access
// ---------------------------------------------------------------
function no_permission () {
	global $config;
	
	echo "<h3 class='error'>".__('You don\'t have access')."</h3>";
	echo "<img src='".$config["base_url"]."/images/noaccess.gif' width='120'><br><br>";
	echo "<table width=550>";
	echo "<tr><td>";
	echo __('You don\'t have enough permission to access this resource');
	echo "</table>";
	echo "<tr><td><td><td><td>";
	include $config["homedir"]."/general/footer.php";
	exit;
}

/** 
 * List files in a directory in the local path.
 * 
 * @param directory Local path.
 * @param stringSearch String to match the values.
 * @param searchHandler Pattern of files to match.
 * @param return Flag to print or return the list.
 * 
 * @return The list if $return parameter is true.
 */
function list_files ($directory, $stringSearch, $searchHandler, $return = true, $inverse_filter = "") {
	$errorHandler = false;
	$result = array ();
	if (! $directoryHandler = @opendir ($directory)) {
		echo ("<pre>\nerror: directory \"$directory\" doesn't exist!\n</pre>\n");
		return $errorHandler = true;
	}
	if ($searchHandler == 0) {
		while (false !== ($fileName = @readdir ($directoryHandler))) {
			if (is_dir ($directory.'/'.$fileName))
				continue;
			if ($fileName[0] != ".")
				$result[$fileName] = $fileName;
		}
	}
	if ($searchHandler == 1) {
		while(false !== ($fileName = @readdir ($directoryHandler))) {
			if (is_dir ($directory.'/'.$fileName))
				continue;
			if(@substr_count ($fileName, $stringSearch) > 0) {
				if ($inverse_filter != "") {
					if (strpos($fileName, $inverse_filter) == 0)
						if ($fileName[0] != ".")
							$result[$fileName] = $fileName;
				} else {
					 if ($fileName[0] != ".")
						$result[$fileName] = $fileName;
				}
				
			}
		}
	}
	if (($errorHandler == true) &&  (@count ($result) === 0)) {
		echo ("<pre>\nerror: no filetype \"$fileExtension\" found!\n</pre>\n");
	} else {
		asort ($result);
		return $result;
	}
}

/**
 * Add magnitude to a byte quantity.
 *
 * @param int $bytes Bytes to add magnitude
 *
 * @retval Bytes amount in KiB, MiB, GiB, etc.
 */
function byte_convert ($bytes) {
	$symbol = array ('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
	if ($bytes < 0)
		return '0 B';
	$exp = 0;
	$converted_value = 0;
	if ($bytes > 0) {
		$exp = floor (log ($bytes) / log (1024));
		$converted_value = ($bytes / pow(1024, floor ($exp)));
	}

	return sprintf ('%.2f '.$symbol[$exp], $converted_value );
}

function pagination ($count, $url, $offset ) {
	global $config;

	$block_size = $config["block_size"];

	/* 	URL passed render links with some parameter
			&offset - Offset records passed to next page
	  		&counter - Number of items to be blocked
	   	Pagination needs $url to build the base URL to render links, its a base url, like
	   " http://pandora/index.php?sec=godmode&sec2=godmode/admin_access_logs "

	*/
	$block_limit = 15; // Visualize only $block_limit blocks
	if ($count > $block_size){
		// If exists more registers than I can put in a page, calculate index markers
		$index_counter = ceil($count/$block_size); // Number of blocks of block_size with data
		$index_page = ceil($offset/$block_size)-(ceil($block_limit/2)); // block to begin to show data;
		if ($index_page < 0)
			$index_page = 0;

		// This calculate index_limit, block limit for this search.
		if (($index_page + $block_limit) > $index_counter)
			$index_limit = $index_counter;
		else
			$index_limit = $index_page + $block_limit;

		// This calculate if there are more blocks than visible (more than $block_limit blocks)
		if ($index_counter > $block_limit )
			$paginacion_maxima = 1; // If maximum blocks ($block_limit), show only 10 and "...."
		else
			$paginacion_maxima = 0;

		// This setup first block of query
		if ( $paginacion_maxima == 1)
			if ($index_page == 0)
				$inicio_pag = 0;
			else
				$inicio_pag = $index_page;
		else
			$inicio_pag = 0;

		echo "<div><p>";
		// Show GOTO FIRST button
		echo '<a href="'.$url.'&offset=0">';
		echo "<img src='".$config["base_url"]."/images/control_start_blue.png' border=0 valign='bottom'>";
		echo "</a>";
		echo "&nbsp;";
		// Show PREVIOUS button
		if ($index_page > 0){
			$index_page_prev= ($index_page-(floor($block_limit/2)))*$block_size;
			if ($index_page_prev < 0)
				$index_page_prev = 0;
			echo '<a href="'.$url.'&offset='.$index_page_prev.'"><img src="'.$config["base_url"].'/images/control_rewind_blue.png" border=0 valign="bottom"></a>';
		}
		echo "&nbsp;";echo "&nbsp;";
		// Draw blocks markers
		// $i stores number of page
		for ($i = $inicio_pag; $i < $index_limit; $i++) {
			$inicio_bloque = ($i * $block_size);
			$final_bloque = $inicio_bloque + $block_size;
			if ($final_bloque > $count){ // if upper limit is beyond max, this shouldnt be possible !
				$final_bloque = ($i-1)*$block_size + $count-(($i-1) * $block_size);
			}
			echo "<span>";

			$inicio_bloque_fake = $inicio_bloque + 1;
			// To Calculate last block (doesnt end with round data,
			// it must be shown if not round to block limit)
			echo '<a href="'.$url.'&offset='.$inicio_bloque.'">';
			if ($inicio_bloque == $offset)
				echo "<b>[ $i ]</b>";
			else
				echo "[ $i ]";
			echo '</a> ';
			echo "</span>";
		}
		echo "&nbsp;";echo "&nbsp;";
		// Show NEXT PAGE (fast forward)
		// Index_counter stores max of blocks
		if (($paginacion_maxima == 1) AND (($index_counter - $i) > 0)) {
				$prox_bloque = ($i+ceil($block_limit/2))*$block_size;
				if ($prox_bloque > $count)
					$prox_bloque = ($count -1) - $block_size;
				echo '<a href="'.$url.'&offset='.$prox_bloque.'">';
				echo "<img border=0 valign='bottom' src='images/control_fastforward_blue.png'></a> ";
				$i = $index_counter;
		}
		// if exists more registers than i can put in a page (defined by $block_size config parameter)
		// get offset for index calculation
		// Draw "last" block link, ajust for last block will be the same
		// as painted in last block (last integer block).
		if (($count - $block_size) > 0){
			$myoffset = floor(($count-1)/ $block_size)* $block_size;
			echo '<a href="'.$url.'&offset='.$myoffset.'">';
			echo "<img border=0 valign='bottom' src='images/control_end_blue.png'>";
			echo "</a>";
		}
	// End div and layout
	echo "</p></div>";
	}
}

function print_array_pagination ($array, $url, $offset = 0){
	global $config;

	if (!is_array($array))
		return array();

	$count = sizeof($array);
	$offset = get_parameter ("offset", 0);	
	$output =  pagination ($count, $url, $offset );
	$array = array_slice ($array, $offset, $config["block_size"]);
	return $array;
}

// Render data in a fashion way :-)
function format_numeric ( $number, $decimals=1, $dec_point=".", $thousands_sep=",") {
	if (is_numeric($number)){
		if ($number == 0)
			return 0;
		// If has decimals
		if (fmod($number , 1)> 0)
			return number_format ($number, $decimals, $dec_point, $thousands_sep);
		else
			return number_format ($number, 0, $dec_point, $thousands_sep);
	} else
 	return 0;
}

/** 
 * Get a translated string
 * 
 * @param string String to translate
 * 
 * @return The translated string. If not defined, the same string will be returned
 */
function __ ($string) {
	global $l10n;

	if (is_null ($l10n))
		return $string;

	return $l10n->translate ($string);
}

function lang_string ($string) {
	return __($string);
}

function render_priority ($pri) {
	global $config;
	
	switch ($pri) {
	case 0:
		return __('Very low');
	case 1:
		return __('Low');
	case 2:
		return __('Medium');
	case 3:
		return __('High');
	case 4:
		return __('Very high');
	default:
		return __('Other');
	}
}

function integria_sendmail_attach ( $name, $email, $from, $subject, $fileatt, $fileatttype, $texto ){
	$to = "$name <$email>";
	$fileattname = "$fileatt";
	$headers = "From: $from";
	$file = fopen( $fileatt, 'rb' ); 
	$data = fread( $file, filesize( $fileatt ) ); 
	fclose( $file );
	$semi_rand = md5( time() ); 
	$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x"; 

	$headers .= "\nMIME-Version: 1.0\n" . 
				"Content-Type: multipart/mixed;\n" . 
				" boundary=\"{$mime_boundary}\"";

	$message = "This is a multi-part message in MIME format.\n\n" . 
			"--{$mime_boundary}\n" . 
			"Content-Type: text/plain; charset=\"iso-8859-1\"\n" . 
			"Content-Transfer-Encoding: 7bit\n\n" . 
			$texto . "\n\n";

	$data = chunk_split (base64_encode ($data));
	$message .= "--{$mime_boundary}\n" . 
			 "Content-Type: {$fileatttype};\n" . 
			 " name=\"{$fileattname}\"\n" . 
			 "Content-Disposition: attachment;\n" . 
			 " filename=\"{$fileattname}\"\n" . 
			 "Content-Transfer-Encoding: base64\n\n" . 
			 $data . "\n\n" . 
			 "--{$mime_boundary}--\n"; 
	$message .= "\n".$texto;
	mail( $to, $subject, $message, $headers );
}

function integria_sendmail ($destination, $msg_subject = "[INTEGRIA] Automatic email notification", $msg_text) {
	global $config;
	if ($destination != "") {
		$msg_text = ascii_output ($msg_text);
		$msg_subject = ascii_output ($msg_subject);
		$real_text = $config["HEADER_EMAIL"].$msg_text."\n\n".$config["FOOTER_EMAIL"];
		$from = $config["mail_from"];
		$headers = "From: $from\nX-Mailer: Integria IMS\n";
		mail ($destination, $msg_subject, $real_text, $headers);
	}
}

function topi_rndcode ($length = 6) {
	$chars = " abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789 ";
	$code = "";
	$clen = strlen ($chars) - 1;  //a variable with the fixed length of chars correct for the fence post issue
	while (strlen ($code) < $length) {
		$code .= $chars[mt_rand (0, $clen)];  //mt_rand's range is inclusive - this is why we need 0 to n-1
	}
	return $code;
}

/* Given a local URL, compose a internet valid URL
   with quicklogin HASH data, and enter it on DB
*/
function topi_quicksession ($url, $id_user = "") {
	global $config;
	if ($id_user == "")
		$id_user = $config["id_user"];
	$today = date ('Y-m-d H:i:s');

	// Build quicksession data and URL
	$id_user = $config["id_user"];
	$cadena = topi_rndcode (16).$id_user.$today;
	$cadena_md5 = substr (md5 ($cadena), 1, 8);
	$param = "&quicksession=$cadena_md5&quickuser=$id_user";
	$myurl = $config["base_url"].$url.$param;
	//Insert quicksession data in DB
	$sql = sprintf ('INSERT INTO tquicksession (id_user, timestamp, pwdhash)
		VALUES ("%s", "%s", "%s")',
		$id_user, $today, $cadena_md5);
	process_sql ($sql);
	return $myurl;
}


function return_value ($var) {
	if (isset ($var))
		return $var;
	return "";
}

function get_priorities () {
	$incidents = array ();

	$incidents[0] = __('Informative');
	$incidents[1] = __('Low');
	$incidents[2] = __('Medium');
	$incidents[3] = __('Serious');
	$incidents[4] = __('Very Serious');
	$incidents[10] = __('Maintenance');

	return $incidents;
}

function get_periodicities () {
	$periodicites = array ();
	
	$periodicites['none'] = __('None');
	$periodicites['weekly'] = __('Weekly');
	$periodicites['15days'] = __('15 days');
	$periodicites['monthly'] = __('Monthly');
	$periodicites['60days'] = __('60 days');
	$periodicites['90days'] = __('90 days');
	$periodicites['year'] = __('Annual');
	
	return $periodicites;
}

function get_periodicity ($recurrence) {
	$recurrences = get_periodicities ();
	
	return isset ($recurrences[$recurrence]) ? $recurrences[$recurrence] : __('Unknown');
}

// FIXME: This both functions need to be updated to use values FROM datatabase, not fixed ones

function get_indicent_status () {
	$status = array ();

	$status[1] = __('New');
	$status[2] = __('Unconfirmed');
	$status[3] = __('Assigned');
	$status[4] = __('Re-opened');
	$status[5] = __('Verified');
	$status[6] = __('Resolved');
	$status[7] = __('Closed');

	return $status;
}

function get_incident_resolutions () {
	$status = array ();

	$status[1] = __('Fixed');
	$status[2] = __('Invalid');
	$status[3] = __('Wont fix');
	$status[4] = __('Duplicate');
	$status[5] = __('Works for me');
	$status[6] = __('Incomplete');
	$status[7] = __('Expired');
    $status[8] = __('Moved');
	$status[9] = __('In process');

	return $status;
}

function ellipsize_string ($string, $len = 2) {
	return substr ($string, 0, $len).'(..)'.substr ($string, strlen ($string) - $len, $len);
}

function print_priority_flag_image ($priority, $return = false) {
	$output = '';
	
	$output .= '<img class="priority-color" height="15" width="30" ';
	switch ($priority) {
	case 0:
		// Informative
		$output .= 'src="images/pixel_gray.png" title="'.__('Informative').'" ';
		break;
	case 1:
		// Low
		$output .= 'src="images/pixel_green.png" title="'.__('Low').'" ';
		break;
	case 2:
		// Medium
		$output .= 'src="images/pixel_yellow.png" title="'.__('Medium').'" ';
		break;
	case 3:
		// Serious
		$output .= 'src="images/pixel_orange.png" title="'.__('Serious').'" ';
		break;
	case 4:
		// Very serious
		$output .= 'src="images/pixel_red.png" title="'.__('Very serious').'" ';
		break;
	case 10:
		// Maintance
		$output .= 'src="images/pixel_blue.png" title="'.__('Maintance').'" ';
		break;
	default:
		// Default
		$output .= 'src="images/pixel_gray.png" title="'.__('Unknown').'" ';
	}

	$output .= ' />';
	if ($return)
		return $output;
	echo $output;
}

function get_project_tracking_state ($state) {
	switch ($state) {
	case PROJECT_CREATED:
		return __('Project created');
	case PROJECT_UPDATED:
		return __('Project updated');
	case PROJECT_DISABLED:
		return __('Project disabled');
	case PROJECT_ACTIVATED:
		return __('Project activated');
	case PROJECT_DELETED:
		return __('Project deleted');
	case PROJECT_TASK_ADDED:
		return __('Task added');
	default:
		return __('Unknown');
	}
}

function enterprise_hook ($function_name, $parameters = false) {
	if (function_exists ($function_name)) {
		if (!is_array ($parameters))
			return call_user_func ($function_name);
		return call_user_func_array ($function_name, $parameters);
	}
	return ENTERPRISE_NOT_HOOK;
}

function enterprise_include ($filename) {
	global $config;
	
	// Load enterprise extensions
	$filepath = realpath ($config["homedir"].'/'.ENTERPRISE_DIR.'/'.$filename);
	if ($filepath === false)
		return ENTERPRISE_NOT_HOOK;
	if (file_exists ($filepath)) {
		include ($filepath);
		return true;
	}
	return ENTERPRISE_NOT_HOOK;
}

function round_number ($number, $rounder = 5) {
	return (int) ($number / $rounder + 0.5) * $rounder;
}
?>
