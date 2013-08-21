<?php

// INTEGRIA IMS
// http://www.integriaims.com
// ===========================================================
// Copyright (c) 2007-2012 Artica, info@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License
// (LGPL) as published by the Free Software Foundation; version 2

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.


global $config;

/**
 * Prints the print_r with < pre > tags
 */
function debugPrint ($var, $file = '') {
	$more_info = '';
	if (is_string($var)) {
		$more_info = 'size: ' . strlen($var);
	}
	elseif (is_bool($var)) {
		$more_info = 'val: ' . 
			($var ? 'true' : 'false');
	}
	elseif (is_null($var)) {
		$more_info = 'is null';
	}
	elseif (is_array($var)) {
		$more_info = count($var);
	}
	
	if ($file === true)
		$file = '/tmp/logDebug';
	
	if (strlen($file) > 0) {
		$f = fopen($file, "a");
		ob_start();
		echo date("Y/m/d H:i:s") . " (" . gettype($var) . ") " . $more_info . "\n";
		print_r($var);
		echo "\n\n";
		$output = ob_get_clean();
		fprintf($f,"%s",$output);
		fclose($f);
	}
	else {
		echo "<pre>" .
			date("Y/m/d H:i:s") . " (" . gettype($var) . ") " . $more_info .
			"</pre>";
		echo "<pre>";print_r($var);echo "</pre>";
	}
}

/**
 * Prints an array of fields in a popup menu of a form.
 *
 * Based on choose_from_menu() from Moodle
 *
 * $fields Array with dropdown values. Example: $fields["value"] = "label"
 * $name Select form name
 * $selected Current selected value.
 * $script Javascript onChange code.
 * $nothing Label when nothing is selected.
 * $nothing_value Value when nothing is selected
 */

function print_select ($fields, $name, $selected = '', $script = '', $nothing = 'select', $nothing_value = '0', $return = false, $multiple = 0, $sort = true, $label = false, $disabled = false, $style='') {

	$output = "\n";
	
	if ($label) {
		$output .= print_label ($label, $name, 'select', true);
	}
	
	$attributes = ($script) ? 'onchange="'. $script .'"' : '';
	if ($multiple) {
		$attributes .= ' multiple="yes" size="'.$multiple.'" ';
	}
	
	if ($disabled) {
		$disabledText = 'disabled="disabled"';
	}
	else {
		$disabledText = '';
	}

	if ($style == "")
		$output .= '<select style="width: 170px" ' . $disabledText . ' id="'.$name.'" name="'.$name.'" '.$attributes.">\n";
	else
		$output .= '<select style="'.$style.'" ' . $disabledText . ' id="'.$name.'" name="'.$name.'" '.$attributes.">\n";

	if ($nothing != '') {
		$output .= '   <option value="'.$nothing_value.'"';
		if ($nothing_value == $selected) {
			$output .= " selected";
		}
		$output .= '>'.$nothing."</option>\n";
	}

	if (!empty ($fields)) {
		if ($sort)
			asort ($fields);
		foreach ($fields as $value => $label) {
			$optlabel = $label;
			if(is_array($label)){
				if(!isset($lastopttype) || ($label['optgroup'] != $lastopttype)) {
					if(isset($lastopttype) && ($lastopttype != '')) {
						$output .=  '</optgroup>';
					}
					$output .=  '<optgroup label="'.$label['optgroup'].'">';
					$lastopttype = $label['optgroup'];
				}				
				$optlabel = $label['name'];
			}
			
			$output .= '   <option value="'. $value .'"';
			if (safe_output($value) == safe_output($selected)) {
				$output .= ' selected';
			}
			if ($optlabel === '') {
				$output .= '>'. $value ."</option>\n";
			} else {
				$output .= '>'. $optlabel ."</option>\n";
			}
		}
	}

	$output .= "</select>\n";
	if ($return)
		return $output;

	echo $output;
}

/**
 * Convert a html color like #FF00FF into the rgb values like (255,0,255).
 *
 * @param string color in format #FFFFFF, FFFFFF, #FFF or FFF
 */
function html2rgb($htmlcolor)
{
	if ($htmlcolor[0] == '#') {
		$htmlcolor = substr($htmlcolor, 1);
	}

	if (strlen($htmlcolor) == 6) {
		$r = hexdec($htmlcolor[0].$htmlcolor[1]);
		$g = hexdec($htmlcolor[2].$htmlcolor[3]);
		$b = hexdec($htmlcolor[4].$htmlcolor[5]);
		return array($r, $g, $b);
	}
	elseif (strlen($htmlcolor) == 3) {
		$r = hexdec($htmlcolor[0].$htmlcolor[0]);
		$g = hexdec($htmlcolor[1].$htmlcolor[1]);
		$b = hexdec($htmlcolor[2].$htmlcolor[2]);
		return array($r, $g, $b);
	}
	else {
		return false;
	}
}

/**
 * Prints an array of fields in a popup menu of a form based on a SQL query.
 * The first and second columns of the query will be used.
 *
 * Based on choose_from_menu() from Moodle
 *
 * $sql SQL sentence, the first field will be the identifier of the option.
 *      The second field will be the shown value in the dropdown.
 * $name Select form name
 * $selected Current selected value.
 * $script Javascript onChange code.
 * $nothing Label when nothing is selected.
 * $nothing_value Value when nothing is selected
 */
function print_select_from_sql ($sql, $name, $selected = '', $script = '', $nothing = 'select', $nothing_value = '0', $return = false, $multiple = false, $sort = true, $label = false, $disabled = false) {

	$fields = array ();
	$result = mysql_query ($sql);
	if (! $result) {
		echo mysql_error ();
		return "";
	}

	while ($row = mysql_fetch_array ($result)) {
		$fields[$row[0]] = $row[1];
	}

	$output = print_select ($fields, $name, $selected, $script, $nothing, $nothing_value, true, $multiple, $sort, $label, $disabled);

	if ($return)
		return $output;

	echo $output;
}

/**
 * Render an input text element. Extended version, use print_input_text() to simplify.
 *
 * @param string Input name.
 * @param string Input value.
 * @param string Alternative HTML string.
 * @param int Size of the input.
 * @param int Maximum length allowed.
 * @param bool Disable the button (optional, button enabled by default).
 * @param string Alternative HTML string.
 * @param bool Whether to return an output string or echo now (optional, echo by default).
 */
function print_input_text_extended ($name, $value, $id, $alt, $size, $maxlength, $disabled, $script, $attributes, $return = false, $password = false, $label = false) {
	$type = $password ? 'password' : 'text';
	
	$output = '';
	
	if ($label) {
		$output .= print_label ($label, $id, '', true);
	}
	
	if (empty ($name)) {
		$name = 'unnamed';
	}

	if (empty ($alt)) {
		$alt = 'textfield';
	}

	if (! empty ($maxlength)) {
		$maxlength = ' maxlength="'.$maxlength.'" ';
	}
	
	if (! empty ($script)) {
		$script = ' onClick="'.$script.'" ';
	}

	$output .= '<input name="'.$name.'" type="'.$type.'" value="'.$value.'" size="'.$size.'" '.$maxlength.' alt="'.$alt.'" '.$script;
	$output .= ' id="'.$id.'"';
	
	if ($disabled)
		$output .= ' disabled';
	
	if (is_array($attributes)) {
		foreach ($attributes as $name => $value) {
			$output .= ' ' . $name . '="' . $value . '"';
		}
	}
	else {
		if ($attributes != '')
			$output .= ' '.  $attributes;
	}
	$output .= ' />';
	
	if ($return)
		return $output;
	echo $output;
}

/**
 * Prints an image HTML element.
 *
 * @param string $src Image source filename.
 * @param bool $return Whether to return or print
 * @param array $options Array with optional HTML options to set. At this moment, the 
 * following options are supported: alt, style, title, width, height, class, pos_tree.
 * @param bool $return_src Whether to return src field of image ('images/*.*') or complete html img tag ('<img src="..." alt="...">'). 
 *
 * @return string HTML code if return parameter is true.
 */
function print_image ($src, $return = false, $options = false, $return_src = false) {
	global $config;
	
	// path to image 
	$src = $config["base_url"] . '/' . $src;
	
	// Only return src field of image
	if ($return_src){
		if (!$return){ 
			echo safe_input($src); 
			return; 
		}
		return safe_input($src);
	}
	
	$output = '<img src="'.safe_input ($src).'" '; //safe input necessary to strip out html entities correctly
	$style = '';
	
	if (!empty ($options)) {
		//Deprecated or value-less attributes
		if (isset ($options["align"])) {
			$style .= 'align:'.$options["align"].';'; //Align is deprecated, use styles.
		}
		
		if (isset ($options["border"])) {
			$style .= 'border:'.$options["border"].'px;'; //Border is deprecated, use styles
		}
				
		if (isset ($options["hspace"])) {
			$style .= 'margin-left:'.$options["hspace"].'px;'; //hspace is deprecated, use styles
			$style .= 'margin-right:'.$options["hspace"].'px;';
		}
		
		if (isset ($options["ismap"])) {
			$output .= 'ismap="ismap" '; //Defines the image as a server-side image map
		}
		
		if (isset ($options["vspace"])) {
			$style .= 'margin-top:'.$options["vspace"].'px;'; //hspace is deprecated, use styles
			$style .= 'margin-bottom:'.$options["vspace"].'px;';
		}
				
		if (isset ($options["style"])) {
			$style .= $options["style"]; 
		}
		
		//Valid attributes (invalid attributes get skipped)
		$attrs = array ("height", "longdesc", "usemap","width","id","class","title","lang","xml:lang", 
						"onclick", "ondblclick", "onmousedown", "onmouseup", "onmouseover", "onmousemove", 
						"onmouseout", "onkeypress", "onkeydown", "onkeyup","pos_tree");
		
		foreach ($attrs as $attribute) {
			if (isset ($options[$attribute])) {
				$output .= $attribute.'="'.safe_input ($options[$attribute]).'" ';
			}
		}
	} else {
		$options = array ();
	}
	
	if (!isset ($options["alt"]) && isset ($options["title"])) {
		$options["alt"] = safe_input($options["title"]); //Set alt to title if it's not set
	} elseif (!isset ($options["alt"])) {
		$options["alt"] = "";
	}

	if (!empty ($style)) {
		$output .= 'style="'.$style.'" ';
	}
	
	$output .= 'alt="'.safe_input ($options['alt']).'" />';
	
	if (!$return) {
		echo $output;
	}

	return $output;
}

/**
 * Render an input text element. Extended version, use print_input_text() to simplify.
 *
 * @param string Input name.
 * @param int Size of the input.
 * @param bool Wheter to disable the input or not.
 * @param string Optional HTML attributes.
 * @param bool Whether to return an output string or echo now (optional, echo by default).
 * @param string HTML label element to add (none by default).
 */
function print_input_file ($name, $size, $disabled = false, $attributes = '', $return = false, $label = false) {
	$output = '';
	
	if ($label) {
		$output .= print_label ($label, $name, 'file', true);
	}
	
	if (empty ($name)) {
		$name = 'unnamed';
	}
	
	$output .= '<input name="'.$name.'" type="file" value="" size="'.$size.'"  ';
	$output .= ' id="file-'.$name.'"';
	
	if ($disabled)
		$output .= ' disabled';

	if ($attributes != '')
		$output .= ' '.$attributes;
	$output .= ' />';

	if ($return)
		return $output;
	echo $output;
}

/**
 * Render an input file element with progress bar system using jquery
 * This function uses jQuery, the library AXuploader and the file include/file_uploader.php
 *
 * @param string form action where the uploading will be processed and copied the file from temp to destiny.
 * @param string code to print into the form
 * @param string attributes extra to form
 * @param string default button extra class
 * @param string button id of the submit button
 * @param bool Whether to return an output string or echo now (optional, echo by default).
 * @param string macro to place the upload control. I will be placed at first of all with no macro (false)
 */
function print_input_file_progress($form_action, $into_form = '', $attr = '', $extra_button_class = '', $button = false,  $return = false, $control_macro = false) {
	$output = "";
	
	$control_layer = "<div class='upfile'></div>";
	
	// If no control macro defined, we put the upload control first of all
	if($control_macro === false) {
		// Layer to the input control through jquery
		$output .= $control_layer;
	}
	
	// Form to fill and submit from javascript
	$output .= "<form method='post' $attr class='upfile_form' action='$form_action' enctype='multipart/form-data'>";
	$output .= "<input type='hidden' id='upfile' name='upfile' value='' class='upfile_input'>";
	$output .= $into_form;
	$output .= "</form>";
	
	$output .= "<script type='text/javascript'>";

	$output .= "$(document).ready(function(){";	
		$output .= "$('.upfile').axuploader({";
			$output .= "url:'include/file_uploader.php',";
			$output .= "finish:function(x,files){";
				$output .= "$('#upfile').val(files[0]);";
				$output .= "$('.upfile_form').submit();";
			$output .=  "},";
			$output .= "enable:true,";
			$output .= "showSize:'Kb',";
			$output .= "remotePath:function(){";
				$output .= "return '".sys_get_temp_dir()."/';";
			$output .= "}";
		$output .= "});";
				
		$output .= "$('.ax-clear').hide();";
		$output .= "$('#ax-table-header').hide();";
		$output .= "$('.ax-uploadall').val('".__('Upload')."');";
		$output .= "$('.ax-uploadall').addClass('".$extra_button_class."');";
		$output .= "$('input[type=\"file\"]').addClass('sub file');";
		// If a button is defined, hide the default button and trigger the action to the
		// defined button. If file upload is empty, the form is sended without it
		if($button !== false) {
			$output .= "$('.ax-uploadall').hide();";
			$output .= "$('#$button').click(function() { $('.ax-uploadall').trigger('click');if($('.ax-file-name').html() == null)$('#form-add-file').submit();});";
		}
	$output .= "});";
	$output .= "</script>";

	if($control_macro !== false) {
		$output = str_replace($control_macro,$control_layer,$output);
	}

	if ($return) {
		return $output;
	}
	
	echo $output;
}

/**
 * Render an input password element.
 *
 * @param string Input name.
 * @param string Input value.
 * @param string Alternative HTML string (optional).
 * @param int Size of the input (optional).
 * @param int Maximum length allowed (optional).
 * @param bool Whether to return an output string or echo now (optional, echo by default).
 */

function print_input_password ($name, $value, $alt = '', $size = 50, $maxlength = 0, $return = false, $label = false) {
	$output = print_input_text_extended ($name, $value, 'password-'.$name, $alt, $size, $maxlength, false, '', '', true, true, $label);

	if ($return)
		return $output;
	echo $output;
}

/**
 * Render an input text element.
 *
 * @param string Input name.
 * @param string Input value.
 * @param string Alternative HTML string (optional).
 * @param int Size of the input (optional).
 * @param int Maximum length allowed (optional).
 * @param bool Whether to return an output string or echo now (optional, echo by default).
 */
function print_input_text ($name, $value, $alt = '', $size = 50, $maxlength = 0, $return = false, $label = false, $disabled = false) {
	$output = print_input_text_extended ($name, $value, 'text-'.$name, $alt, $size, $maxlength, $disabled, '', '', true, false, $label);

	if ($return)
		return $output;
	echo $output;
}


/**
 * Render an input hidden element.
 *
 * @param string Input name.
 * @param string Input value.
 * @param bool Whether to return an output string or echo now (optional, echo by default).
 * @param string HTML class to be added. Useful in javascript code.
 */
function print_input_hidden ($name, $value, $return = false, $class = '', $label = false) {
	if ($label) {
		$output .= print_label ($label, $name, 'hidden', true);
	}
	
	$output = '<input id="hidden-'.$name.'" name="'.$name.'" type="hidden"';
	if ($class != '')
		$output .= ' class="'.$class.'"';
	$output .=' value="'.$value.'" />';

	if ($return)
		return $output;
	echo $output;
}

function print_submit_button ($value = 'OK', $name = '', $disabled = false, $attributes = '', $return = false, $label = false) {
	$output = '';
	
	if ($label) {
		$output .= print_label ($label, $name, 'submit', true);
	}
	
	$output .= '<input type="submit" id="submit-'.$name.'" name="'.$name.'" value="'. $value .'" '. $attributes;
	if ($disabled)
		$output .= ' disabled="disabled"';
	$output .= ' />';
	if ($return)
		return $output;

	echo $output;
}

/**
 * Render an input image element.
 * 
 * @param string Input name.
 * @param string Image source.
 * @param string Input value.
 * @param string HTML style property.
 * @param bool Whether to return an output string or echo now (optional, echo by default).
 */
function print_input_image ($name, $src, $value, $style = '', $return = false, $label = false, $options = false) {
	$output = '';
	
	//Valid attributes (invalid attributes get skipped)
	$attrs = array ("alt", "accesskey", "lang", "tabindex",
		"title", "xml:lang", "onclick", "ondblclick", "onmousedown",
		"onmouseup", "onmouseover", "onmousemove", "onmouseout",
		"onkeypress", "onkeydown", "onkeyup");	
	
	if ($label) {
		$output .= print_label ($label, $name, 'image', true);
	}
	$output .= '<input id="image-'.$name.'" src="'.$src.'" style="'.$style.'" name="'.$name.'" type="image"';
	
	
	foreach ($attrs as $attribute) {
		if (isset ($options[$attribute])) {
			$output .= ' '.$attribute.'="'.safe_input_html ($options[$attribute]).'"';
		}
	}	
	
	$output .= ' value="'.$value.'" />';
	
	if ($return)
		return $output;
	echo $output;
}

function print_button ($value = 'OK', $name = '', $disabled = false, $script = '', $attributes = '', $return = false, $label = false) {
	$output = '';
	
	if ($label) {
		$output .= print_label ($label, $name, 'button', true);
	}
	
	$output .= '<input type="button" id="button-'.$name.'" name="'.$name.'" value="'. $value .'" onClick="'. $script.'" '.$attributes;
	if ($disabled)
		$output .= ' disabled="disabled"';
	$output .= ' />';
	if ($return)
		return $output;

	echo $output;
}

function print_textarea ($name, $rows, $columns, $value = '', $attributes = '', $return = false, $label = false) {
	$output = '';
	
	if ($label) {
		$output .= print_label ($label, $name, 'textarea', true);
	}
	
	$output .= '<textarea id="textarea-'.$name.'" name="'.$name.'" cols="'.$columns.'" rows="'.$rows.'" '.$attributes.' >';
	$output .= $value;
	$output .= '</textarea>';

	if ($return)
		return $output;
	echo $output;
}

/**
 * Print a nicely formatted table. Code taken from moodle.
 *
 * @param object is an object with several properties:
 *     $table->head - An array of heading names.
 *     $table->align - An array of column alignments
 *     $table->valign - An array of column alignments
 *     $table->size  - An array of column sizes
 *     $table->wrap - An array of "nowrap"s or nothing
 *     $table->style  - An array of personalized style for each column.
 *     $table->rowstyle  - An array of personalized style of each row.
 *     $table->rowclass  - An array of personalized classes of each row (odd-evens classes will be ignored).
 *     $table->colspan  - An array of colspans of each column.
 *     $table->rowspan  - An array of rowspans of each column.
 *     $table->data[] - An array of arrays containing the data.
 *     $table->width  - A percentage of the page
 *     $table->border  - Border of the table.
 *     $table->tablealign  - Align the whole table
 *     $table->cellpadding  - Padding on each cell
 *     $table->cellspacing  - Spacing between cells
 *     $table->class  - CSS table class
 * @param  bool whether to return an output string or echo now
 */
function print_table (&$table, $return = false) {
	$output = '';
	static $table_count = 0;

	$table_count++;
	if (isset ($table->align)) {
		foreach ($table->align as $key => $aa) {
			if ($aa) {
				$align[$key] = ' text-align:'. $aa.';';
			} else {
				$align[$key] = '';
			}
		}
	}
	if (isset ($table->valign)) {
		foreach ($table->valign as $key => $aa) {
			if ($aa) {
				$valign[$key] = ' vertical-align:'. $aa.';';
			} else {
				$valign[$key] = '';
			}
		}
	}
	if (isset ($table->size)) {
		foreach ($table->size as $key => $ss) {
			if ($ss) {
				$size[$key] = ' width:'. $ss .';';
			} else {
				$size[$key] = '';
			}
		}
	}
	if (isset ($table->style)) {
		foreach ($table->style as $key => $st) {
			if ($st) {
				$style[$key] = ' '. $st .';';
			} else {
				$style[$key] = '';
			}
		}
	}
	if (isset ($table->rowstyle)) {
		foreach ($table->rowstyle as $key => $st) {
			$rowstyle[$key] = ' '. $st .';';
		}
	}
	if (isset ($table->rowclass)) {
		foreach ($table->rowclass as $key => $class) {
			$rowclass[$key] = $class;
		}
	}
	if (isset ($table->colspan)) {
		foreach ($table->colspan as $keyrow => $cspan) {
			foreach ($cspan as $key => $span) {
				$colspan[$keyrow][$key] = ' colspan="'.$span.'"';
			}
		}
	}
	if (isset ($table->rowspan)) {
		foreach ($table->rowspan as $keyrow => $cspan) {
			foreach ($cspan as $key => $span) {
				$rowspan[$keyrow][$key] = ' rowspan="'.$span.'"';
			}
		}
	}
	
	if (empty ($table->width)) {
		$table->width = '80%';
	}

	if (empty ($table->border)) {
		$table->border = '0px';
	}


	if (empty ($table->tablealign)) {
		$table->tablealign = 'center';
	}

	if (empty ($table->cellpadding)) {
		$table->cellpadding = '0';
	}

	if (empty ($table->cellspacing)) {
		$table->cellspacing = '0';
	}

	if (empty ($table->class)) {
		$table->class = 'databox';
	}

	$tableid = empty ($table->id) ? 'table'.$table_count : $table->id;

	$output .= '<table width="'.$table->width.'" ';
	$output .= " cellpadding=\"$table->cellpadding\" cellspacing=\"$table->cellspacing\" ";
	$output .= " border=\"$table->border\" class=\"$table->class\" id=\"$tableid\" >\n";
	$countcols = 0;

	$output .= '<thead>';
	if (!empty ($table->head)) {
		$countcols = count ($table->head);
		$output .= '<tr>';
		foreach ($table->head as $key => $heading) {
			if (!isset ($size[$key])) {
				$size[$key] = '';
			}
			if (!isset ($align[$key])) {
				$align[$key] = '';
			}

			$output .= '<th class="header c'.$key.'" scope="col">'. $heading .'</th>';
		}
		$output .= '</tr>'."\n";
	}
	$output .= "</thead>\n<tbody>\n";
	if (!empty ($table->data)) {
		$oddeven = 1;
		foreach ($table->data as $keyrow => $row) {

			if (!isset ($rowstyle[$keyrow])) {
				$rowstyle[$keyrow] = '';
			}
			$oddeven = $oddeven ? 0 : 1;
			$class = 'datos'.($oddeven ? "" : "2");
			if (isset ($rowclass[$keyrow])) {
				$class = $rowclass[$keyrow];
			}
			$output .= '<tr id="'.$tableid."-".$keyrow.'" style="'.$rowstyle[$keyrow].'" class="'.$class.'">'."\n";
			/* Special separator rows */
			if ($row == 'hr' and $countcols) {
				$output .= '<td colspan="'. $countcols .'"><div class="tabledivider"></div></td>';
				continue;
			}
			/* It's a normal row */
			foreach ($row as $key => $item) {
				if (!isset ($size[$key])) {
					$size[$key] = '';
				}
				if (!isset ($colspan[$keyrow][$key])) {
					$colspan[$keyrow][$key] = '';
				}
				if (!isset ($rowspan[$keyrow][$key])) {
					$rowspan[$keyrow][$key] = '';
				}
				if (!isset ($align[$key])) {
					$align[$key] = '';
				}
				if (!isset ($valign[$key])) {
					$valign[$key] = '';
				}
				if (!isset ($wrap[$key])) {
					$wrap[$key] = '';
				}
				if (!isset ($style[$key])) {
					$style[$key] = '';
				}

				$output .= '<td id="'.$tableid.'-'.$keyrow.'-'.$key.
					'" style="'. $style[$key].$valign[$key].$align[$key].$size[$key].$wrap[$key].
					'" '.$colspan[$keyrow][$key].' '.$rowspan[$keyrow][$key].
					' class="'.$class.'">'. $item .'</td>'."\n";
			}
			$output .= '</tr>'."\n";
		}
	}
	$output .= '</tbody>'."\n";
	$output .= '</table>'."\n";

	if ($return)
		return $output;

	echo $output;
}

/**
 * Render a radio button input. Extended version, use print_radio_button() to simplify.
 *
 * @param string Input name.
 * @param string Input value.
 * @param string Set the button to be marked (optional, unmarked by default).
 * @param bool Disable the button (optional, button enabled by default).
 * @param string Script to execute when onClick event is triggered (optional).
 * @param string Optional HTML attributes. It's a free string which will be
	inserted into the HTML tag, use it carefully (optional).
 * @param bool Whether to return an output string or echo now (optional, echo by default).
 */
function print_radio_button_extended ($name, $value, $label, $checkedvalue, $disabled, $script, $attributes, $return = false) {
	static $idcounter = 0;

	$output = '';

	$output = '<input type="radio" name="'.$name.'" value="'.$value.'"';
	$htmlid = 'radiobtn'.sprintf ('%04d', ++$idcounter);
	$output .= ' id="'.$htmlid.'"';

	if ($value == $checkedvalue) {
		 $output .= ' checked="checked"';
	}
	if ($disabled) {
		 $output .= ' disabled';
	}
	if ($script != '') {
		 $output .= ' onClick="'. $script . '"';
	}
	$output .= ' ' . $attributes ;
	$output .= ' />';

	if ($label != '') {
		$output .= '<label for="'.$htmlid.'">'.  $label .'</label>' . "\n";
	}
	
	if ($return)
		return $output;
	
	echo $output;
}

/**
 * Render a radio button input.
 *
 * @param string Input name.
 * @param string Input value.
 * @param string  Label to add after the radio button (optional).
 * @param string Checked and selected value, the button will be selected if it matches $value (optional).
 * @param bool Whether to return an output string or echo now (optional, echo by default).
 */
function print_radio_button ($name, $value, $label = '', $checkedvalue = '', $return = false, $label = false) {
	$output = print_radio_button_extended ($name, $value, $label, $checkedvalue, false, '', '', true, $label);

	if ($return)
		return $output;

	echo $output;
}

/**
 * Render a label for a input elemennt.
 *
 * @param string Label to add.
 * @param string Input id to refer.
 * @param string Input type of the element. The id of the elements using print_* functions add a prefix, this
 * variable helps with that. Values: text, password, textarea, button, submit, hidden, select. Default: text.
 * @param bool Whether to return an output string or echo now (optional, echo by default).
 * @param string Extra HTML to add after the label.
 */
function print_label ($label, $id, $input_type = 'text', $return = false, $html = false) {
	$output = '';
	
	switch ($input_type) {
	case 'text':
		$id = 'text-'.$id;
		break;
	case 'password':
		$id = 'password-'.$id;
		break;
	case 'textarea':
		$id = 'textarea-'.$id;
		break;
	case 'button':
		$id = 'button-'.$id;
		break;
	case 'submit':
		$id = 'submit-'.$id;
		break;
	case 'hidden':
		$id = 'hidden-'.$id;
		break;
	case 'checkbox':
		$id = 'checkbox-'.$id;
		break;
	case 'file':
		$id = 'file-'.$id;
		break;
	case 'image':
		$id = 'image-'.$id;
		break;
	case 'select':
	default:
		break;
	}
	
	$output .= '<label id="label-'.$id.'" for="'.$id.'">';
	$output .= $label;
	$output .= '</label>';
	
	if ($html)
		$output .= $html;
	
	if ($return)
		return $output;
	
	echo $output;
}

/**
 * Render a checkbox button input. Extended version, use print_checkbox() to simplify.
 *
 * @param string Input name.
 * @param string Input value.
 * @param string Set the button to be marked (optional, unmarked by default).
 * @param bool Disable the button  (optional, button enabled by default).
 * @param string Script to execute when onClick event is triggered (optional).
 * @param string Optional HTML attributes. It's a free string which will be
	inserted into the HTML tag, use it carefully (optional).
 * @param bool Whether to return an output string or echo now (optional, echo by default).
 */
function print_checkbox_extended ($name, $value, $checked, $disabled, $script, $attributes, $return = false, $label = false) {
	$output = '';

	if ($label) {
		$output .= ' ';
		$output .= print_label ($label, $name, 'checkbox', true);
	}

	$output .= '<input name="'.$name.'" type="checkbox" '.$attributes.' value="'.$value.'" '. ($checked ? 'checked="1"': '');
	$output .= ' id="checkbox-'.$name.'"';

	if ($script != '') {
		 $output .= ' onClick="'. $script . '"';
	}

	if ($disabled) {
		 $output .= ' disabled="1"';
	}

	$output .= ' />';
	$output .= "\n";

	if ($return)
		return $output;
	echo $output;
}

/**
 * Render a checkbox button input.
 *
 * @param string Input name.
 * @param string Input value.
 * @param string Set the button to be marked (optional, unmarked by default).
 * @param bool Whether to return an output string or echo now (optional, echo by default).
 */
function print_checkbox ($name, $value, $checked = false, $return = false, $label = false) {
	$output = print_checkbox_extended ($name, $value, (bool) $checked, false, '', '', true, $label);

	if ($return)
		return $output;
	echo $output;
}

/**
 * Prints only a tip button which shows a text when the user puts the mouse over it.
 *
 * @param string Complete text to show in the tip
 * @param bool whether to return an output string or echo now
 *
 * @return
 */
function print_help_tip ($text, $return = false, $tip_class = 'tip') {
	$output = '<a href="#" class="'.$tip_class.'">&nbsp;<span>'.$text.'</span></a>';
	
	if ($return)
		return $output;
	echo $output;
}

/**
 * Prints a help tip icon.
 *
 * @param int Help id
 * @param bool Flag to return or output the result
 *
 * @return string The help tip if return flag was active.
 */
function integria_help ($help_id, $return = false) {
	global $config;
	$output = '&nbsp;<img class="img_help" src="images/help.png" onClick="integria_help(\''.$help_id.'\')">';
	if ($return)
		return $output;
	echo $output;
}


function print_container($id, $title, $content, $open = 'open', $return = true) {
	$style = '';
	$arrow = '';
	$onclick = 'toggleDiv (\'' . $id . '_div\')';
	$h2_style = '';

	switch($open) {
		case 'open':
			$arrow = '&nbsp;&nbsp;' . print_image('images/arrow_down.png', true, array('class' => 'arrow_down')) . '</h2>';
			break;
		case 'closed':
			$arrow = '&nbsp;&nbsp;' . print_image('images/arrow_right.png', true, array('class' => 'arrow_right')) . '</h2>';
			$style = 'display: none;';
			break;
		case 'no':
		default:
			$onclick = '';
			$h2_style = 'cursor: auto;';
			break;
	}
	
	$container = '<div class="container ' . $id . '_container">';
	$container .= '<h2 id="' . $id . '" class="dashboard_h2" onclick="' . $onclick . '" style="' . $h2_style . '">' . $title;
	$container .= $arrow;
	$container .= '</h2>';
	$container .= '<div id="' . $id . '_div" style="' . $style . '">';
	$container .= $content;
	$container .= '</div>';
	$container .= '</div>'; // container
	
	if ($return) {
		return $container;
	}
	else {
		echo $container;
	}
}

?>
