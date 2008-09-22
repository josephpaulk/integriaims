
/* Function to hide/unhide a specific Div id */
function toggleDiv (id_div) {
	$("#" + id_div).toggle ();
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
	$(".result").empty ().append ($("<h3></h3>").addClass ("error").append (message)).fadeIn ();
}

/**
 * Show an success message in result div.
 * 
 * @param string message Message to show
 */
function result_msg_success (message) {
	$(".result").empty ().append ($("<h3></h3>").addClass ("suc").append (message)).fadeIn ();
}
