<?php
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008-2010 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.



global $config;

define ('ENTERPRISE_NOT_HOOK', -1);

//Important: The menu modes codes must be incremental
define('MENU_HIDDEN', 0);
define('MENU_MINIMAL',1);
define('MENU_LIMITED', 2);
define('MENU_FULL',3);

define('GROUP_ALL',1);

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
	return preg_replace ('/&#x0a;/', "<br>", $string);
}


function replace_breaks ($string) {
	$string = preg_replace ('/<br \/>/', "\n", $string);
	$string = preg_replace ('/<br\/>/', "\n", $string);
	$string = preg_replace ('/<br>/', "\n", $string);
	return $string;
}

function safe_input_array ($value) {
	return safe_input($value);
}

/** 
 * Cleans a string by decoding from UTF-8 and replacing the HTML
 * entities.
 * 
 * @param value String or array of strings to be cleaned.
 * 
 * @return The cleaned string.
 */
 
 function safe_input_html($value) {
	//Stop!! Are you sure to modify this critical code? Because the older
	//versions are serius headache in many places of Pandora.
	
	if (is_numeric($value))
		return $value;
		
	if (is_array($value)) {
		array_walk($value, "safe_input");
		return $value;
	}
	
	//Clean the trash mix into string because of magic quotes.
	if (get_magic_quotes_gpc() == 1) {
		$value = stripslashes($value);
	}
	
	if (! mb_check_encoding ($value, 'UTF-8'))
		$value = utf8_encode ($value);
	
	return $value;
}

/** 
 * Cleans a string by encoding to UTF-8 and replacing the HTML
 * entities. UTF-8 is necessary for foreign chars like asian 
 * and our databases are (or should be) UTF-8
 * 
 * @param mixed String or array of strings to be cleaned.
 * 
 * @return mixed The cleaned string or array.
 */
function safe_input($value) {
	//Stop!! Are you sure to modify this critical code? Because the older
	//versions are serius headache in many places of Pandora.
	
	if (is_numeric($value))
		return $value;
		
	if (is_array($value)) {
		array_walk($value, "safe_input_array");
		return $value;
	}
	
	//Clean the trash mix into string because of magic quotes.
	if (get_magic_quotes_gpc() == 1) {
		$value = stripslashes($value);
	}
	
	if (! mb_check_encoding ($value, 'UTF-8'))
		$value = utf8_encode ($value);
	
	$valueHtmlEncode =  htmlentities ($value, ENT_QUOTES, "UTF-8");
	
	//Replace the character '\' for the equivalent html entitie
	$valueHtmlEncode = str_replace('\\', "&#92;", $valueHtmlEncode);
	
	// First attempt to avoid SQL Injection based on SQL comments
	// Specific for MySQL.
	$valueHtmlEncode = str_replace('/*', "&#47;&#42;", $valueHtmlEncode);
	$valueHtmlEncode = str_replace('*/', "&#42;&#47;", $valueHtmlEncode);
	
	//Replace ( for the html entitie
	$valueHtmlEncode = str_replace('(', "&#40;", $valueHtmlEncode);
	
	//Replace ( for the html entitie
	$valueHtmlEncode = str_replace(')', "&#41;", $valueHtmlEncode);	
	
	//Replace some characteres for html entities
	for ($i=0;$i<33;$i++) {
		$valueHtmlEncode = str_ireplace(chr($i),ascii_to_html($i), $valueHtmlEncode);			
	}
	
	return $valueHtmlEncode;
}


/** 
 * Convert ascii char to html entitines
 * 
 * @param int num of ascci char
 * 
 * @return string String of html entitie
 */
function ascii_to_html($num) {
	
	if ($num <= 15) {
		return "&#x0".dechex($num).";";
	} else {
		return "&#x".dechex($num).";";
	}
}

/** 
 * Convert hexadecimal html entity value to char
 * 
 * @param string String of html hexadecimal value
 * 
 * @return string String with char
 */
function html_to_ascii($hex) {
		
	$dec = hexdec($hex);
	
	return chr($dec);
}


/**
 * Convert the $value encode in html entity to clear char string. This function 
 * should be called always to "clean" HTML encoded data; to render to a text
 * plain ascii file, to render to console, or to put in any kind of data field
 * who doesn't make the HTML render by itself.
 * 
 * @param mixed String or array of strings to be cleaned.
 * @param boolean $utf8 Flag, set the output encoding in utf8, by default true.
 * 
 * @return unknown_type
 */
function safe_output($value, $utf8 = true)
{
	if (is_numeric($value))
		return $value;
		
	if (is_array($value)) {
		array_walk($value, "safe_output");
		return $value;
	}
	
	if (! mb_check_encoding ($value, 'UTF-8'))
		$value = utf8_encode ($value);
	
	if ($utf8) {
		$valueHtmlEncode =  html_entity_decode ($value, ENT_QUOTES, "UTF-8");
	}
	else {
		$valueHtmlEncode =  html_entity_decode ($value, ENT_QUOTES);
	}
	
	//Replace the html entitie of ( for the char
	$valueHtmlEncode = str_replace("&#40;", '(', $valueHtmlEncode);
	
	//Replace the html entitie of ) for the char
	$valueHtmlEncode = str_replace("&#41;", ')', $valueHtmlEncode);		
	
	//Revert html entities to chars
	for ($i=0;$i<33;$i++) {
		$valueHtmlEncode = str_ireplace("&#x".dechex($i).";",html_to_ascii(dechex($i)), $valueHtmlEncode);			
	}	
	
	return $valueHtmlEncode;	
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

/** 
* Display a notice about no access and close output stream
*
*/

function no_permission () {
	global $config;
	include $config["homedir"]."/general/noaccess.php";
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
 * Render numeric data for a graph. It adds magnitude suffix to the number 
 * (M for millions, K for thousands...) base-10
 *
 * TODO: base-2 multiplication
 * 
 * @param float $number Number to be rendered
 * @param int $decimals Numbers after comma. Default value: 1
 * @param dec_point Decimal separator character. Default value: .
 * @param thousands_sep Thousands separator character. Default value: ,
 * 
 * @return string A string with the number and the multiplier
 */
function format_for_graph ($number , $decimals = 1, $dec_point = ".", $thousands_sep = ",", $divisor = 1000) {
        $shorts = array ("","K","M","G","T","P");
        $pos = 0;
        while ($number >= $divisor) { //as long as the number can be divided by divisor
                $pos++; //Position in array starting with 0
                $number = $number / $divisor;
        }

        return format_numeric ($number, $decimals). $shorts[$pos]; //This will actually do the rounding and the decimals
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
	global $config;

	if ($config['enterprise_installed']) {
		
		if (file_exists('enterprise/include/functions_translate_string.php')) {
			include_once('enterprise/include/functions_translate_string.php');

			$tranlateString = get_defined_translation($string);

			if ($tranlateString !== false) {
				return $tranlateString;
			}
		}
	}
 
	if ($string == '') {
		return $string;
	}

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


function integria_sendmail ($to, $subject = "[INTEGRIA]", $body,  $attachments = false, $code = "", $from = "", $remove_header_footer =0 ) {
	global $config;

	if ($to == '')
		return false;

	$to = trim(safe_output ($to));
	$from = trim(safe_output ($from));

	$config["mail_from"] = trim($config["mail_from"]);

	$current_date = date ("Y/m/d H:i:s");

	// We need to convert to pure ASCII here to use carriage returns

	$body = safe_output ($body);
	$subject = ascii_output ($subject);

	if ($remove_header_footer == 0)
		// Add global header and footer to mail
		$body = safe_output($config["HEADER_EMAIL"]). "\r\n". $body . "\r\n". safe_output ($config["FOOTER_EMAIL"]);

	// Add custom code to the end of message subject (to put there ID's).
	if ($code != ""){
		$subject = "[$code] ".$subject;
		// $body = $body."\r\nNOTICE: Please don't alter the SUBJECT when answer to this mail, it contains a special code who makes reference to this issue.";
	}

	// This is a special scenario... we store all the information "ready" in the database, 
	// without HTML encoding. THis is because it is not to be rendered on a browser, 
	// it will be directly to a SMTP connection.

	process_sql ("INSERT INTO tpending_mail (date, attempts, status, recipient, subject, body, attachment_list, `from`) VALUES ('".$current_date."', 0, 0, '".$to."', '".mysql_real_escape_string($subject)."', '".mysql_real_escape_string($body)."', '".$attachments."', '".$from."')");
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
	$incidents[4] = __('Very serious');
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

function ellipsize_string ($string, $len = 25) {
	$string = ascii_output($string);
	return substr ($string, 0, $len).'(..)'.substr ($string, strlen ($string) - $len, $len);
}

/** Cut string if bigger than $len. Put three dots after point of cut in the string
*/
function short_string ($string, $len = 15) {
	$string = ascii_output($string);
	if (strlen($string) > $len)
		return substr ($string, 0, $len).'...';
	else
		return $string;
}

/** Clean FLASH string strips non-valid characters for flashchart
*/
function clean_flash_string ($string) {
	$string = ascii_output($string);
	$temp =  str_replace("&", "", $string);
	return str_replace ("\"", "", $temp);
}

function print_priority_flag_image ($priority, $return = false) {
	$output = '';
	
	$output .= '<img class="priority-color" height="15" width="15" ';
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
	case PROJECT_TASK_DELETED:
		return __('Task deleted');
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

function template_process ($filename, $macroarray) {

/* USAGE:

$MACROS["_fullname_"] = "My taylor is rich";
$msg = template_process ( "messages/mytemplate.tpl", $MACROS);

Will replace all _fullname_ with "My taylor is rich" in the template and return the template
contents altered on function return

*/
        $fh = fopen ($filename, "r");

        // Empty string
        if (! $fh){
                return "";
        }

        $contents = fread($fh, filesize($filename));
        fclose ($fh);

        foreach ($macroarray as $key => $value) {
                $contents = str_replace($key, $value, $contents);
        }
        return $contents;
}

function update_config_token ($cfgtoken, $cfgvalue) {
	global $config;
	process_sql ("DELETE FROM tconfig WHERE token = '$cfgtoken'");
	process_sql ("INSERT INTO tconfig (token, value) VALUES ('$cfgtoken', '$cfgvalue')");
}

/**
 * Avoid magic_quotes protection
 *
 * @param string Text string to be stripped of magic_quotes protection
 */

function unsafe_string ($string){
	if (get_magic_quotes_gpc() == 1) 
    	$string = stripslashes ($string);
	return $string;
}

function returnMIMEType($filename){

        preg_match("|\.([a-z0-9]{2,4})$|i", $filename, $fileSuffix);

        if(!isset($fileSuffix[1]))
                $fileSuffix[1]="";
        if(!isset($fileSuffix[0]))
                $fileSuffix[0]="";

        switch(strtolower($fileSuffix[1]))
        {
            case "js" :
                return "application/x-javascript";

            case "json" :
                return "application/json";

            case "jpg" :
            case "jpeg" :
            case "jpe" :
                return "image/jpg";

            case "png" :
            case "gif" :
            case "bmp" :
            case "tiff" :
                return "image/".strtolower($fileSuffix[1]);

            case "css" :
                return "text/css";

            case "xml" :
                return "application/xml";

case "doc" :
            case "docx" :
                return "application/msword";

            case "xls" :
            case "xlt" :
            case "xlm" :
            case "xld" :
            case "xla" :
            case "xlc" :
            case "xlw" :
            case "xll" :
                return "application/vnd.ms-excel";

            case "ppt" :
            case "pps" :
                return "application/vnd.ms-powerpoint";

            case "rtf" :
                return "application/rtf";

            case "pdf" :
                return "application/pdf";

            case "html" :
            case "htm" :
            case "php" :
                return "text/html";

            case "txt" :
                return "text/plain";

            case "mpeg" :
            case "mpg" :
            case "mpe" :
                return "video/mpeg";

            case "mp3" :
                return "audio/mpeg3";

            case "wav" :
                return "audio/wav";

            case "aiff" :
            case "aif" :
                return "audio/aiff";

            case "avi" :
                return "video/msvideo";

            case "wmv" :
                return "video/x-ms-wmv";
            case "mov" :
                return "video/quicktime";

            case "zip" :
                return "application/zip";

            case "tar" :
                return "application/x-tar";

            case "swf" :
                return "application/x-shockwave-flash";

            default :
                return "text/plain";
        }
        return "text/plain";
}

function get_user_language ($id_user = false) {
	global $config;

	$quick_language = get_parameter('quick_language_change', 0);

	if($quick_language) {
		$language = get_parameter('language', 0);
		
		if($language === 'default') {
			return $config['language'];
		}

		if($language !== 0) {
			return $language;
		}
	}
	
	if($id_user === false && isset($config['id_user'])) {
		$id_user = $config['id_user'];
	}
	
	if($id_user !== false) {
		$userinfo = get_user_info ($id_user);
		if ($userinfo['language'] != 'default'){
			return $userinfo['language'];
		}
	}
	
	return $config['language'];
}

// This function delete all files present in directory.
// Is not recursive. It only delete the files

function delete_all_files_in_dir ($tmp_path){
	
	$handle = opendir ($tmp_path); 
	while ($tmp = readdir ($handle)){
		if ($tmp != '..' && $tmp!='.' && $tmp != ''){
			$myfile = $tmp_path."/".$tmp;
			if (is_writeable ($myfile) && is_file($myfile)){
				unlink ($myfile);
			}
		}
	}
	closedir($handle); 
}

// Writes string in integria log file (at integria.log)

function integria_logwrite ($string){
	global $config;
	
	$current_date = date ("Y/m/d H:i:s");

	$logfile = $config["homedir"]."/integria.log";
	file_put_contents ( $logfile, "$current_date ".safe_output($string) ."\n", FILE_APPEND);
	
}

// Check using regexp if a given string is a valid email address

function check_email_address($email) {

	// First, we check that there's one @ symbol, 
	// and that the lengths are right.
	
	if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
		// Email invalid because wrong number of characters 
		// in one section or wrong number of @ symbols.
    	return false;  	
  	}
  	
	// Split it into sections to make life easier
	
	$email_array = explode("@", $email);
	$local_array = explode(".", $email_array[0]);
	
	for ($i = 0; $i < sizeof($local_array); $i++) {
    	if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
			return false;
		}
	}
	
	// Check if domain is IP. If not, 
	// it should be valid domain name
	
	if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) {
		$domain_array = explode(".", $email_array[1]);
		if (sizeof($domain_array) < 2) {
			return false; // Not enough parts to domain
		}

		for ($i = 0; $i < sizeof($domain_array); $i++) {
			if 	(!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$",$domain_array[$i])) {
				return false;
			}
		}
	}

	return true;
}

// Generate a ASCII random string (useful for hashes or similar)
function random_string (){

    for ($i = 0; $i < 25; $i++) 
        {
            $randstring .= chr (rand(48, 122));
        }
    return $randstring;
}	

?>
