<?php

// Pandora FMS - the Free monitoring system
// ========================================
// Copyright (c) 2004-2007 Sancho Lerena, slerena@gmail.com
// Main PHP/SQL code development and project architecture and management
// Copyright (c) 2004-2007 Raul Mateos Martin, raulofpandora@gmail.com
// CSS and some PHP additions
// Copyright (c) 2006-2007 Jonathan Barajas, jonathan.barajas[AT]gmail[DOT]com
// Javascript Active Console code.
// Copyright (c) 2006 Jose Navarro <contacto@indiseg.net>
// Additions to Pandora FMS 1.2 graph code and new XML reporting template management
// Copyright (c) 2005-2007 Artica Soluciones Tecnologicas, info@artica.es
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.


/**
* Clean output string
*
* This function clean a user string to be rendered in HTML output.
* This replace all conflictive characters.
*
* $string	string	Output string to be cleaned
*/

function clean_output  ($string){
	return clean_input($string);
}

function salida_ascii ($string){
	$texto_ok = htmlspecialchars($string, ENT_QUOTES, "UTF-8");
	return $texto_ok;
}

/**
* Clean input text
*
* This function clean a user string to be used of SQL operations or 
* other kind of sensible string operations (like XSS)
* This replace all conflictive characters.
*
* $text	string	Inputstring to be cleaned*/


function clean_input ($texto){
return $texto;
	$filtro0 = utf8_decode($texto);
	$filtro1 =  htmlentities($filtro0, ENT_QUOTES); 
	return $filtro1;							
}

/**
* Search item in a given array.
*
* @exampleArray	array 	Source of items to search in
* $item		varchar	Data to search
*/

function array_in($exampleArray, $item){
	$result = 0;
	foreach ($exampleArray as $key => $value){
  		if ($value == $item){
   			$result = 1;
		}
  	}
	return $result;
}

function give_parameter_get ( $name, $default = "" ){
	$output = $default;
	if (isset ($_GET[$name])){
		$output = clean_input ($_GET[$name]);
	}
	return $output;
}

function give_parameter_post ( $name, $default = "" ){
	$output = $default;
	if (isset ($_POST[$name])){
		$output = clean_input ($_POST[$name]);
	}
	return $output;
}

// ---------------------------------------------------------------
// Esta funcion lee una cadena y la da "limpia", para su uso con 
// parametros pasados a funcion de abrir fichero. Usados en sec y sec2
// ---------------------------------------------------------------

function parametro_limpio($texto){
	// Metemos comprobaciones de seguridad para los includes de paginas pasados por parametro
	// Gracias Raul (http://seclists.org/lists/incidents/2004/Jul/0034.html)
	// Consiste en purgar los http:// de las cadenas
	$pos = strpos($texto,"://");	// quitamos la parte "fea" de http:// o ftp:// o telnet:// :-)))
	if ($pos <> 0)
	$texto = substr_replace($texto,"",$pos,+3);   
	// limitamos la entrada de datos por parametros a 125 caracteres
	$texto = substr_replace($texto,"",125);
	$safe = preg_replace('/[^a-z0-9_\/]/i','',$texto);
	return $safe;
}

// ---------------------------------------------------------------
// Return string with time-threshold in secs, mins, days or weeks
// ---------------------------------------------------------------

function give_human_time ($int_seconds){
   $key_suffix = 's';
   $periods = array(
                   'year'        => 31556926,
                   'month'        => 2629743,
                   'day'        => 86400,
                   'hour'        => 3600,
                   'minute'    => 60,
                   'second'    => 1
                   );

   // used to hide 0's in higher periods
   $flag_hide_zero = true;

   // do the loop thang
   foreach( $periods as $key => $length )
   {
       // calculate
       $temp = floor( $int_seconds / $length );

       // determine if temp qualifies to be passed to output
       if( !$flag_hide_zero || $temp > 0 )
       {
           // store in an array
           $build[] = $temp.' '.$key.($temp!=1?'s':null);

           // set flag to false, to allow 0's in lower periods
           $flag_hide_zero = false;
       }

       // get the remainder of seconds
       $int_seconds = fmod($int_seconds, $length);
   }

   // return output, if !empty, implode into string, else output $if_reached
   return ( !empty($build)?implode(', ', $build):$if_reached );
}

// ---------------------------------------------------------------
// This function show a popup window using a help_id (unused)
// ---------------------------------------------------------------

function popup_help ($help_id){
	echo "<a href='javascript:help_popup(".$help_id.")'>[H]</a>";
}

// ---------------------------------------------------------------
// no_permission () - Display no perm. access
// ---------------------------------------------------------------

function no_permission () {
	global $config;
	global $lang_label;
	require ($config["homedir"]."/include/languages/language_".$config["language_code"].".php");
	echo "<h3 class='error'>".$lang_label["no_permission_title"]."</h3>";
	echo "<img src='".$config["base_url"]."/images/noaccess.gif' width='120'><br><br>";
	echo "<table width=550>";
	echo "<tr><td>";
	echo $lang_label["no_permission_text"];
	echo "</table>";
	echo "<tr><td><td><td><td>";
	include $config["homedir"]."/general/footer.php";
	exit;
}

function list_files($directory, $stringSearch, $searchHandler, $outputHandler) {
 	$errorHandler = false;
 	$result = array();
 	if (! $directoryHandler = @opendir ($directory)) {
  		echo ("<pre>\nerror: directory \"$directory\" doesn't exist!\n</pre>\n");
 		return $errorHandler = true;
 	}
 	if ($searchHandler == 0) {
		while (false !== ($fileName = @readdir ($directoryHandler))) {
			@array_push ($result, $fileName);
		}
 	}
 	if ($searchHandler == 1) {
  		while(false !== ($fileName = @readdir ($directoryHandler))) {
   			if(@substr_count ($fileName, $stringSearch) > 0) {
   				@array_push ($result, $fileName);
   			}
  		}
 	}
 	if (($errorHandler == true) &&  (@count ($result) === 0)) {
  		echo ("<pre>\nerror: no filetype \"$fileExtension\" found!\n</pre>\n");
 	}
 	else {
  		sort ($result);
  		if ($outputHandler == 0) {
   			return $result;
  		}
  		if ($outputHandler == 1) {
  	 		echo ("<pre>\n");
   			print_r ($result);
   			echo ("</pre>\n");
  		}
 	}
}


function pagination ($count, $url, $offset ) {
	global $config;
	require ($config["homedir"]."/include/languages/language_".$config["language_code"].".php");
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

		echo "<div>";
		// Show GOTO FIRST button
		echo '<a href="'.$url.'&offset=0">';
		echo "<img src='".$config["base_url"]."/images/control_start_blue.png' class='bot'>";
		echo "</a>";
		echo "&nbsp;";
		// Show PREVIOUS button
		if ($index_page > 0){
			$index_page_prev= ($index_page-(floor($block_limit/2)))*$block_size;
			if ($index_page_prev < 0)
				$index_page_prev = 0;
			echo '<a href="'.$url.'&offset='.$index_page_prev.'"><img src="'.$config["base_url"].'/images/control_rewind_blue.png" class="bot"></a>';
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
				echo "<img class='bot' src='images/control_fastforward_blue.png'></a> ";
				$i = $index_counter;
		}
		// if exists more registers than i can put in a page (defined by $block_size config parameter)
		// get offset for index calculation
		// Draw "last" block link, ajust for last block will be the same
		// as painted in last block (last integer block).	
		if (($count - $block_size) > 0){
			$myoffset = floor(($count-1)/ $block_size)* $block_size;
			echo '<a href="'.$url.'&offset='.$myoffset.'">';
			echo "<img class='bot' src='images/control_end_blue.png'>";
			echo "</a>";
		}
	// End div and layout
	echo "</div>";
	}
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

function human_time_comparation ( $timestamp ){
	global $config;
	require ($config["homedir"]."/include/languages/language_".$config["language_code"].".php");
	$ahora=date("Y/m/d H:i:s");
	if (strtotime($ahora) < strtotime($timestamp)){
		$seconds = strtotime($timestamp) - strtotime($ahora) ;
		$direction = "> ";
	}
	else {
		$seconds = strtotime($ahora) - strtotime($timestamp);
		$direction = "< ";
	}
	if ($seconds < 3600)
		$render = format_numeric($seconds/60,1)." ".$lang_label["minutes"];
	elseif (($seconds >= 3600) and ($seconds < 86400))
		$render = format_numeric ($seconds/3600,1)." ".$lang_label["hours"];
	elseif (($seconds >= 86400) and ($seconds < 604800))
		$render = format_numeric ($seconds/86400,1)." ".$lang_label["days"];
	elseif (($seconds >= 604800) and ($seconds <2592000))
		$render = format_numeric ($seconds/604800,1)." ".$lang_label["weeks"];
	elseif ($seconds >= 2592000)
		$render = format_numeric ($seconds/2592000,1)." ".$lang_label["months"];
	return $direction.$render;
}

function clean_output_breaks ($string){
	//$myoutput = clean_output ($string);
	$myoutput = clean_input($string);
	return preg_replace ('/\n/',"<br>", $myoutput);
}

function lang_string ($string){
	global $config;
	require ($config["homedir"]."/include/languages/language_".$config["language_code"].".php");
	if (isset ($lang_label[$string]))
		return $lang_label[$string];
	else
		return "[".$string."]";
}

function render_priority ($pri){
	global $config;
	require ($config["homedir"]."/include/languages/language_".$config["language_code"].".php");
	switch ($pri){
		case 0: return lang_string ("very low");
		case 1: return lang_string ("low");
		case 2: return lang_string ("medium");
		case 3: return lang_string ("high");
		case 4: return lang_string ("very high");
		default: return lang_string ("other");
	}
}

function topi_sendmail ( $destination, $msg_subject = "[TOPI] Automatic email notification", $msg_text) {
	global $config;
	if ($destination != ""){
		$real_text = $config["HEADER_EMAIL"];
		$real_text = $config["HEADER_EMAIL"]."\n\n".$msg_text."\n\n".$config["FOOTER_EMAIL"];
		mail ($destination, $msg_subject, $real_text );
	}
}

function working_days ($month = "", $year = "" ){
	if (($month == "") OR ($year == "")){
        	$date = date('Y-m-d');
        	$year = substr($date, 0,4);
        	$month = substr($date, 5, 2);
	}

        $d_daysinmonth = date('t', mktime(0,0,0,$month,1,$year));  // how many days in month
        $full_weeks = ceil ($d_daysinmonth / 7);
        $festive_days = floor(($d_daysinmonth / 7) * 2);
        $total_working_days = $d_daysinmonth - $festive_days;
        $total_working_hours = $total_working_days * 8;
        return $total_working_days;
}

function working_weeks_combo () {
        $date = date('Y-m-d');
        $year = substr($date, 0,4);
        $month = substr($date, 5, 2);
        $day = substr($date, 8, 2);

        $d_daysinmonth = date('t', mktime(0,0,0,$month,1,$year));  // how many days in month
        $full_weeks = ceil ($d_daysinmonth / 7);
        $d_firstdow = date('w', mktime(0,0,0,$month,'1',$year));     // FIRST falls on what day of week (0-6)
        $ajuste = $d_firstdow -1;
        $new_date = date('Y-m-d', strtotime("$year-$month-01 - $ajuste days"));
        echo '<select name="working_week">';
        for ($ax=0; $ax < $full_weeks; $ax++){
                echo "<option>".date('Y-m-d', strtotime($new_date. "+ $ax week"));
        }
        echo "</select>";
}


function first_working_week (){
	$date = date('Y-m-d');
        $year = substr($date, 0,4);
        $month = substr($date, 5, 2);
        $day = substr($date, 8, 2);

        $d_daysinmonth = date('t', mktime(0,0,0,$month,1,$year));  // how many days in month
        $full_weeks = ceil ($d_daysinmonth / 7);
        $d_firstdow = date('w', mktime(0,0,0,$month,'1',$year));     // FIRST falls on what day of week (0-6)
        $ajuste = $d_firstdow -1;
        $new_date = date('Y-m-d', strtotime("$year-$month-01 - $ajuste days"));
	return $new_date;
}

function return_unixtime () {
	return strtotime("now");
}

function return_value ($var){
	if (isset($var))
		return $var;
	else	
		return "";
}

function getmonth($m=0) {
	return (($m==0 ) ? date("F") : date("F", mktime(0,0,0,$m)));
} 

?>
