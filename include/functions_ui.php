<?php

// INTEGRIA IMS 
// http://www.integriaims.com
// ===========================================================
// Copyright (c) 2007-2009 Artica, info@artica.es
// Copyright (c) 2009 Esteban Sanchez, estebans@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License
// (LGPL) as published by the Free Software Foundation; version 2

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.


/**
 * Powerful debug function that also shows a backtrace.
 * 
 * This functions need to have active $config['debug'] variable to work.
 *
 * @param mixed Variable name to debug
 * @param bool Wheter to print the backtrace or not.
 * 
 * @return bool Tru if the debug was actived. False if not.
 */
function debug ($var, $backtrace = true) {
	global $config;
	if (! isset ($config['debug']))
		return false;
	
	static $id = 0;
	static $trace_id = 0;
	
	$id++;
	
	if ($backtrace) {
		echo '<div class="debug">';
		echo '<a href="#" onclick="$(\'#trace-'.$id.'\').toggle ();return false;">Backtrace</a>';
		echo '<div id="trace-'.$id.'" class="backtrace invisible">';
		echo '<ol>';
		$traces = debug_backtrace ();
		/* Ignore debug function */
		unset ($traces[0]);
		foreach ($traces as $trace) {
			$trace_id++;
		
			/* Many classes are used to allow better customization. Please, do not
			  remove them */
			echo '<li>';
			if (isset ($trace['class']))
				echo '<span class="class">'.$trace['class'].'</span>';
			if (isset ($trace['type']))
				echo '<span class="type">'.$trace['type'].'</span>';
			echo '<span class="function">';
			echo '<a href="#" onclick="$(\'#args-'.$trace_id.'\').toggle ();return false;">'.$trace['function'].'()</a>';
			echo '</span>';
			if (isset ($trace['file'])) {
				echo ' - <span class="filename">';
				echo str_replace ($config['homedir'].'/', '', $trace['file']);
				echo ':'.$trace['line'].'</span>';
			} else {
				echo ' - <span class="filename"><em>Unknown file</em></span>';
			}
			echo '<pre id="args-'.$trace_id.'" class="invisible">';
			echo '<div class="parameters">Parameter values:</div>';
			echo '<ol>';
			foreach ($trace['args'] as $arg) {
				echo '<li>';
				print_r ($arg);
				echo '</li>';
			}
			echo '</ol>';
			echo '</pre>';
			echo '</li>';
		}
		echo '</ol>';
		echo '</div></div>';
	}
	
	/* Actually print the variable given */
	echo '<pre class="debug">';
	print_r ($var);
	echo '</pre>';
	return true;
}
?>
