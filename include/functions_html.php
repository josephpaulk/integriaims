<?php

// INTEGRIA IMS v1.2
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
function print_select ($fields, $name, $selected = '', $script = '', $nothing = 'select', $nothing_value = '0', $return = false, $multiple = 0, $sort = true, $label = false) {
	$output = "\n";
	
	if ($label) {
		$output .= print_label ($label, $name, 'select', true);
	}
	
	$attributes = ($script) ? 'onchange="'. $script .'"' : '';
	if ($multiple) {
		$attributes .= ' multiple="yes" size="'.$multiple.'" ';
	}

	$output .= '<select id="'.$name.'" name="'.$name.'" '.$attributes.">\n";

	if ($nothing != '') {
		$output .= '   <option value="'.$nothing_value.'"';
		if ($nothing_value == $selected) {
			$output .= " selected";
		}
		$output .= '>'.lang_string ($nothing)."</option>\n";
	}

	if (!empty ($fields)) {
		if ($sort)
			asort ($fields);
		foreach ($fields as $value => $label) {
			$output .= '   <option value="'. $value .'"';
			if ($value == $selected) {
				$output .= ' selected';
			}
			if ($label === '') {
				$output .= '>'. $value ."</option>\n";
			} else {
				$output .= '>'. $label ."</option>\n";
			}
		}
	}

	$output .= "</select>\n";

	if ($return)
		return $output;

	echo $output;
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
function print_select_from_sql ($sql, $name, $selected = '', $script = '', $nothing = 'select', $nothing_value = '0', $return = false, $multiple = false, $sort = true, $label = false) {

	$fields = array ();
	$result = mysql_query ($sql);
	if (! $result) {
		echo mysql_error ();
		return "";
	}

	while ($row = mysql_fetch_array ($result)) {
		$fields[$row[0]] = $row[1];
	}

	$output = print_select ($fields, $name, $selected, $script, $nothing, $nothing_value, true, $multiple, $sort, $label);

	if ($return)
		return $output;

	echo $output;
}

/**
 * Render an input text element. Extended version, use print_input_text() to simplify.
 *
 * @param string $name Input name.
 * @param string $value Input value.
 * @param string $alt Alternative HTML string.
 * @param int $size Size of the input.
 * @param int $maxlength Maximum length allowed.
 * @param bool $disabled Disable the button (optional, button enabled by default).
 * @param string $alt Alternative HTML string.
 * @param bool $return Whether to return an output string or echo now (optional, echo by default).
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

	$output .= '<input name="'.$name.'" type="'.$type.'" value="'.$value.'" size="'.$size.'" '.$maxlength.' alt="'.$alt.'" ';
	$output .= ' id="'.$id.'"';
	
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
 * Render an input password element.
 *
 * @param string $name Input name.
 * @param string $value Input value.
 * @param string $alt Alternative HTML string (optional).
 * @param int $size Size of the input (optional).
 * @param int $maxlength Maximum length allowed (optional).
 * @param bool $return Whether to return an output string or echo now (optional, echo by default).
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
 * @param string $name Input name.
 * @param string $value Input value.
 * @param string $alt Alternative HTML string (optional).
 * @param int $size Size of the input (optional).
 * @param int $maxlength Maximum length allowed (optional).
 * @param bool $return Whether to return an output string or echo now (optional, echo by default).
 */
function print_input_text ($name, $value, $alt = '', $size = 50, $maxlength = 0, $return = false, $label = false) {
	$output = print_input_text_extended ($name, $value, 'text-'.$name, $alt, $size, $maxlength, false, '', '', true, false, $label);

	if ($return)
		return $output;
	echo $output;
}


/**
 * Render an input hidden element.
 *
 * @param string $name Input name.
 * @param string $value Input value.
 * @param bool $return Whether to return an output string or echo now (optional, echo by default).
 * @param string $class HTML class to be added. Useful in javascript code.
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
 * @param array $table is an object with several properties:
 *     $table->head - An array of heading names.
 *     $table->align - An array of column alignments
 *     $table->valign - An array of column alignments
 *     $table->size  - An array of column sizes
 *     $table->wrap - An array of "nowrap"s or nothing
 *     $table->style  - An array of personalized style for each column.
 *     $table->rowstyle  - An array of personalized style of each row.
 *     $table->rowclass  - An array of personalized classes of each row (odd-evens classes will be ignored).
 *     $table->colspan  - An array of colspans of each column.
 *     $table->data[] - An array of arrays containing the data.
 *     $table->width  - A percentage of the page
 *     $table->border  - Border of the table.
 *     $table->tablealign  - Align the whole table
 *     $table->cellpadding  - Padding on each cell
 *     $table->cellspacing  - Spacing between cells
 *     $table->class  - CSS table class
 * @param  bool $return whether to return an output string or echo now
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
		$table->cellpadding = '4';
	}

	if (empty ($table->cellspacing)) {
		$table->cellspacing = '4';
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

				$output .= '<td id="'.$tableid.'-'.$keyrow.'-'.$key.'" style="'. $style[$key].$valign[$key].$align[$key].$size[$key].$wrap[$key] .'" '.$colspan[$keyrow][$key].' class="'.$class.'">'. $item .'</td>'."\n";
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
 * @param string $name Input name.
 * @param string $value Input value.
 * @param string $checked Set the button to be marked (optional, unmarked by default).
 * @param bool $disabled Disable the button (optional, button enabled by default).
 * @param string $script Script to execute when onClick event is triggered (optional).
 * @param string $attributes Optional HTML attributes. It's a free string which will be
	inserted into the HTML tag, use it carefully (optional).
 * @param bool $return Whether to return an output string or echo now (optional, echo by default).
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
 * @param string $name Input name.
 * @param string $value Input value.
 * @param string $label Label to add after the radio button (optional).
 * @param string $checkedvalue Checked and selected value, the button will be selected if it matches $value (optional).
 * @param bool $return Whether to return an output string or echo now (optional, echo by default).
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
 * @param string $label Label to add.
 * @param string $id Input id to refer.
 * @param string $input_type Input type of the element. The id of the elements using print_* functions add a prefix, this
 *               variable helps with that. Values: text, password, textarea, button, submit, hidden, select. Default: text.
 * @param bool $return Whether to return an output string or echo now (optional, echo by default).
 */
function print_label ($label, $id, $input_type = 'text', $return = false) {
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
	case 'select':
	default:
		break;
	}
	
	$output .= '<label id="label-'.$id.'" for="'.$id.'">';
	$output .= $label;
	$output .= '</label>';
	
	if ($return)
		return $output;
	
	echo $output;
}

/**
 * Render a checkbox button input. Extended version, use print_checkbox() to simplify.
 *
 * @param string $name Input name.
 * @param string $value Input value.
 * @param string $checked Set the button to be marked (optional, unmarked by default).
 * @param bool $disabled Disable the button  (optional, button enabled by default).
 * @param string $script Script to execute when onClick event is triggered (optional).
 * @param string $attributes Optional HTML attributes. It's a free string which will be
	inserted into the HTML tag, use it carefully (optional).
 * @param bool $return Whether to return an output string or echo now (optional, echo by default).
 */
function print_checkbox_extended ($name, $value, $checked, $disabled, $script, $attributes, $return = false, $label = false) {
	$output = '<input name="'.$name.'" type="checkbox" value="'.$value.'" '. ($checked ? 'checked': '');
	$output .= ' id="checkbox-'.$name.'"';

	if ($script != '') {
		 $output .= ' onClick="'. $script . '"';
	}

	if ($disabled) {
		 $output .= ' disabled';
	}

	$output .= ' />';
	$output .= "\n";
	if ($label) {
		$output .= ' ';
		$output .= print_label ($label, $name, 'checkbox', true);
	}
	if ($return)
		return $output;
	echo $output;
}

/**
 * Render a checkbox button input.
 *
 * @param string $name Input name.
 * @param string $value Input value.
 * @param string $checked Set the button to be marked (optional, unmarked by default).
 * @param bool $return Whether to return an output string or echo now (optional, echo by default).
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
 * @param  string $text Complete text to show in the tip
 * @param  bool $return whether to return an output string or echo now
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
 * @param id Help id
 * @param return Flag to return or output the result
 *
 * @return The help tip if return flag was active.
 */
function integria_help ($help_id, $return = false) {
	global $config;
	$output = '&nbsp;<img class="img_help" src="images/help.png" onClick="integria_help(\''.$help_id.'\')">';
	if ($return)
		return $output;
	echo $output;
}

?>
