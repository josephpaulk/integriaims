// Integria 1.0 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2008 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

/* Function to hide/unhide a specific Div id */
function toggleDiv (id_div) {
	$("#" + id_div).toggle ();
}

function refresh_table (table_id) {
	$("#" + table_id + " > tbody > tr:odd td").removeClass("datos").addClass("datos2");
	$("#" + table_id + " > tbody > tr:even td").removeClass("datos2").addClass("datos");
}

function get_form_input_values (form_id) {
	values = Object ();
	$("#" + form_id + " :input").each (function () {
		if (this.type != 'checkbox')
			values[this.name] = this.value;
		else if (this.selected)
			values[this.name] = this.value;
	});
	return values;
}
