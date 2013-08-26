
/* Function to hide/unhide a specific Div id */
function toggleDiv (div) {
	if (typeof div == "string") {
		$("#" + div).toggle ();
	} else {
		$(div).toggle ();
	}
}

function winopeng (url, wid) {
	open (url, wid,"width=570,height=310,status=no,toolbar=no,menubar=no,scrollbar=no");
	// WARNING !! Internet Explorer DOESNT SUPPORT "-" CARACTERS IN WINDOW HANDLE VARIABLE
	status =wid;
}

function integria_help(help_id) {
	open ("general/integria_help.php?id="+help_id, "integriahelp", "width=650,height=500,status=0,toolbar=0,menubar=0,scrollbars=1,location=0");
}

/**
 * Decode HTML entities into characters. Useful when receiving something from AJAX
 *
 * @param str String to convert
 *
 * @retval str with entities decoded
 */
function html_entity_decode (str) {
	if (! str)
		return "";
	var ta = document.createElement ("textarea");
	ta.innerHTML = str.replace (/</g, "&lt;").replace (/>/g,"&gt;");
	return ta.value;
}

/**
 * Refresh odd an even rows in a table.
 *
 * @param table_id If of the table to refresh.
 */
function refresh_table (table_id) {
	$("#" + table_id + " > tbody > tr:odd td").removeClass("datos").addClass("datos2");
	$("#" + table_id + " > tbody > tr:even td").removeClass("datos2").addClass("datos");
}

/**
 * Get all values of an form into an array.
 *
 * @param form Form to get the values. It can be an object or an HTML id
 *
 * @retval The input values of the form into an array.
 */
function get_form_input_values (form) {
	if (typeof form == "string") {
		return $("#" + form).formToArray ();
	} else {
		return $(form).formToArray ();
	}
}

/**
 * Show an error message in result div.
 * 
 * @param string message Message to show
 */
function result_msg_error (message) {
	$(".result").fadeOut ("fast", function () {
		$(this).empty ().append ($("<h3></h3>").addClass ("error").append (message)).fadeIn ();
	});
}

/**
 * Show an success message in result div.
 * 
 * @param string message Message to show
 */
function result_msg_success (message) {
	$(".result").fadeOut ("fast", function () {
		$(this).empty ().append ($("<h3></h3>").addClass ("suc").append (message)).fadeIn ();
	});
}

/**
 * Show an message in result div.
 * 
 * @param string message Message to show
 */
function result_msg (message) {
	$(".result").fadeOut ("fast", function () {
		$(this).empty ().append ($("<h3></h3>").append (message)).fadeIn ();
	});
}

/**
 * Pulsate an HTML element to get user attention.
 *
 * @param element HTML element to animate.
 */
function pulsate (element) {
	$(element).fadeIn ("normal", function () {
		$(this).fadeOut ("normal", function () {
			$(this).fadeIn ("normal", function () {
				$(this).fadeOut ("normal", function () {
					$(this).fadeIn ().focus ();
				});
			});
		});
	});
}


function load_form_values (form_id, values) {
	$("#"+form_id+" :input").each (function () {
		if (values[this.name] != undefined) {
			if (this.type == 'checkbox' && values[this.name])
				this.checked = "1";
			else
				this.value = values[this.name];
		}
	});
}

function __(str) {
	r = lang[str];
	
	if (r == undefined)
		return str
	return r;
}

function cancel_msg(id) {
	$('#msg_'+id).fadeOut('slow');

}

/**
 * Exclude in a input the not alphanumeric characters
 *
 * @param id identifier of the input element to control
 * @param exceptions string with the exceptions to exclusion
 * @param lower bool true if the lowercase are allowed, false otherwise
 * @param upper bool true if the uppercase are allowed, false otherwise
 * @param numbers bool true if the numbers are allowed, false otherwise
 */
function inputControl(id,exceptions,lower,upper,numbers) {
	if(typeof(exceptions) == 'undefined') {
		exceptions = '_.';
	}
	if(typeof(lower) == 'undefined') {
		lower = true;
	}
	if(typeof(upper) == 'undefined') {
		upper = true;
	}
	if(typeof(numbers) == 'undefined') {
		numbers = true;
	}
	
	var regexpStr = '';
	
	if(lower) {
		regexpStr+= 'a-z';
	}
	if(upper) {
		regexpStr+= 'A-Z';
	}
	if(numbers) {
		regexpStr+= '0-9';
	}
	
	// Scape the special chars
	exceptions = exceptions.replace(/\./gi,"\\.");
	exceptions = exceptions.replace(/\-/gi,"\\-");
	exceptions = exceptions.replace(/\^/gi,"\\^");
	exceptions = exceptions.replace(/\$/gi,"\\$");
	exceptions = exceptions.replace(/\[/gi,"\\[");
	exceptions = exceptions.replace(/\]/gi,"\\]");
	exceptions = exceptions.replace(/\(/gi,"\\(");
	exceptions = exceptions.replace(/\)/gi,"\\)");
	exceptions = exceptions.replace(/\+/gi,"\\+");
	exceptions = exceptions.replace(/\*/gi,"\\*");
	
	regexpStr+= exceptions;
	$("#"+id).keyup(function() {
		var text = $(this).val();
		var regexp = new RegExp("[^"+regexpStr+"]","g");
		text = text.replace(regexp,'');
		$("#"+id).val(text);
	});
}

function toggleInventoryInfo(id_inventory) {
	display = $('.inventory_more_info_' + id_inventory).css('display');
	
	if (display != 'none') {
		$('.inventory_more_info_' + id_inventory).css('display', 'none');
	}
	else {
		$('.inventory_more_info_' + id_inventory).css('display', '');
	}
}

/**
 * Binds autocomplete behaviour to an input tag 
 * 
 * !!!jquery.ui.autocomplete.js must be loaded in the page before use this function!!!
 *
 * @param idTag String tag's id to bind autocomplete behavior
 * @param idUser String user's id
 * @param byProject Boolean flag to search by users in a project
 */
function bindAutocomplete (idTag, idUser, idProject, onChange) {
	
	var ajaxUrl = "ajax.php?page=include/ajax/users&search_users=1&id_user="+idUser;
	
	if (idProject) {
		ajaxUrl = "ajax.php?page=include/ajax/users&search_users_role=1&id_user="+idUser+"&id_project="+idProject;
	}
	
	$(idTag).autocomplete ({
		source: ajaxUrl,
		minLength: 2,
		delay: 200,
		change: onChange
	});
}

// Show the modal window of license info
function show_license_info() {

	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/license&get_license_info=1",
		dataType: "html",
		success: function(data){	
			$("#dialog_show_license").html (data);
			$("#dialog_show_license").show ();

			$("#dialog_show_license").dialog ({
					resizable: true,
					draggable: true,
					modal: true,
					overlay: {
						opacity: 0.5,
						background: "black"
					},
					width: 500,
					height: 350
				});
			$("#dialog_show_license").dialog('open');
			
		}
	});
}

$(document).ready (function () {
	

	if ($('#license_error_msg_dialog').length) {

		$( "#license_error_msg_dialog" ).dialog({
					resizable: true,
					draggable: true,
					modal: true,
					height: 290,
					width: 800,
					overlay: {
								opacity: 0.5,
								background: "black"
							}
		});
		
		$("#submit-hide-license-error-msg").click (function () {
			$("#license_error_msg_dialog" ).dialog('close')
		});
	
	}
	
});

$(document).ready(function() {
	// Containers open/close logic
	$('.container h2.clickable').click(function() {
		var arrow = $('#' + $(this).attr('id') + ' img').attr('src');
		var arrow_class = $('#' + $(this).attr('id') + ' img').attr('class');
		var new_arrow = '';
		
		if($('#' + $(this).attr('id') + '_div').css('display') == 'none') {
			new_arrow = arrow.replace(/_down/gi, "_right");
			$('#' + $(this).attr('id') + ' img').attr('class', 'arrow_right');
		}
		else {
			new_arrow = arrow.replace(/_right/gi, "_down");
			$('#' + $(this).attr('id') + ' img').attr('class', 'arrow_down');
		}
		
		$('#' + $(this).attr('id') + ' img').attr('src', new_arrow);
	});
});
