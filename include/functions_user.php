<?php

global $config;
require_once ('include/functions_db.php');

function user_print_autocomplete_input($parameters) {
	
	if (isset($parameters['input_name'])) {
		$input_name = $parameters['input_name'];
	}
	
	$input_value = '';
	if (isset($parameters['input_value'])) {
		$input_value = $parameters['input_value'];
	}
	
	if (isset($parameters['input_id'])) {
		$input_id = $parameters['input_id'];
	}
	
	$return = false;
	if (isset($parameters['return'])) {
		$return = $parameters['return'];
	}
	$input_size = 15;
	if (isset($parameters['size'])) {
		$input_size = $parameters['size'];
	}
	
	$input_maxlength = 30;
	if (isset($parameters['maxlength'])) {
		$input_maxlength = $parameters['maxlength'];
	}
	
	$src_code = print_image('images/group.png', true, false, true);
	if (isset($parameters['image'])) {
		$src_code = print_image($parameters['image'], true, false, true);
	}
	
	if (isset($parameters['title'])) {
		$title = $parameters['title'];
	}
	
	$help_message = "Type at least two characters to search";
	if (isset($parameters['help_message'])) {
		$help_message = $parameters['help_message'];
	}
	$return_help = true;
	if (isset($parameters['return_help'])) {
		$return_help = $parameters['return_help'];
	}

	$attributes = array('style' => 'background: url(' . $src_code . ') no-repeat right;');
		
	return print_input_text_extended ($input_name, $input_value, $input_id, '', $input_size, $input_maxlength, false, '', $attributes, $return, '', __($title)). print_help_tip (__($help_message), $return_help);
	
}


?>
