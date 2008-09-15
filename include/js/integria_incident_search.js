
function configure_user_search_form () {
	$("#user_search_result_table").tablesorter ();
	$("#user_search_form").submit (function () {
		$("#user_search_result_table tbody").fadeOut ('normal', function (){
			values = get_form_input_values ("user_search_form");
			values.push ({name: "page",
				value: "operation/users/user_search"});
			jQuery.post ("ajax.php",
				values,
				function (data, status) {
					$("#user_search_result_table").removeClass ("hide");
					$("#user_search_result_table tbody").empty ().append (data);
					refresh_table ("user_search_result_table");
					$("#user_search_result_table").trigger ("update")
						.tablesorterPager ({container: $("#users-pager"), size: 3});
					$("#user_search_result_table tbody tr").click (function () {
						user_id = this.id.slice (7); /* Remove "result-" */
						user_realname = $(this).children (":eq(1)").text ();
						$("#button-usuario_name").attr ("value", user_realname);
						$("#hidden-usuario_form").attr ("value", user_id);
						$("#dialog").dialog ("close").empty ();
					});
					$("#user_search_result_table tbody").fadeIn ();
					$("#users-pager").removeClass ("hide").fadeIn ();
				},
				"html"
			);
		});
		return false;
	});
}

function show_user_search_dialog (title) {
	values = [];
	values.push ({name: "page",
				value: "operation/users/user_search"});
	jQuery.get ("ajax.php",
		values,
		function (data, status) {
			$("#dialog").empty ().append (data);
			$("#dialog").dialog ({"title" : title,
					minHeight: 300,
					minWidth: 300,
					height: 500,
					width: 450,
					modal: true
					});
			configure_user_search_form ();
		},
		"html"
	);
}

function configure_incident_form (enable_ajax_form) {
	$("#button-usuario_name").click (function () {
		show_user_search_dialog ("User search");
	});
	
	$("#button-search_inventory"). click (function () {
		show_inventory_search_dialog ("Search inventory object",
			function (id, name) {
				var exists = false
				$(".selected-inventories").each (function () {
					if (this.value == id) {
						exists = true;
						return
					}
				});
				if (exists) {
					$("#inventory_search_result").fadeOut ('normal', function () {
						$(this).empty ().append ('<h3 class="error">Already added</h3>').fadeIn ();
					});
					return;
				}
				$("#incident_inventories").append ($('<option value="'+id+'">'+name+'</option>'));
				$("#incident_status_form").append ($('<input type="hidden" value="'+id+'" class="selected-inventories" name="inventories[]" />'));
				$("#inventory_search_result").fadeOut ('normal', function () {
					$(this).empty ().append ('<h3 class="suc">Added</h3>').fadeIn ();
				});
			}
		);
	});
	$("#button-delete_inventory").click (function () {
		$($("#incident_inventories")[0].options).each (function () {
			if (! this.selected)
				return;
			$(".selected-inventories[value="+this.value+"]").remove ();
			$(this).remove ();
		});
	});
	
	if (enable_ajax_form) {
		$("#incident_status_form").submit (function () {
			values = get_form_input_values ("incident_status_form");
			values.push ({name: "page",
				value: "operation/incidents/incident_detail"});
			values.action = "update"; /* Only update operation is possible */
			jQuery.post ("ajax.php",
				values,
				function (data, status) {
					$("#result").slideUp ('fast', function () {
						$("#result").empty ().append (data).slideDown ();
					});
				},
				"html"
			);
			return false;
		});
	}
}

function configure_inventory_search_form (callback_incident_click) {
	$("#inventory_search_result_table").tablesorter ();
	$("#inventory_search_form").submit (function () {
		$("#inventory_search_result_table tbody").fadeOut ('normal', function (){
			values = get_form_input_values ("inventory_search_form");
			values.push ({name: "page",
				value: "operation/inventory/inventory_search"});
			jQuery.post ("ajax.php",
				values,
				function (data, status) {
					$("#inventory_search_result_table").removeClass ("hide");
					$("#inventory_search_result_table tbody").empty ().append (data);
					refresh_table ("inventory_search_result_table");
					$("#inventory_search_result_table").trigger ("update")
						.tablesorterPager ({container: $("#inventory-pager"), size: 3});
					$("#inventory_search_result_table tbody tr").click (function () {
						id = this.id.split ("-").pop ();
						name = $(this).children (":eq(0)").text ();
						callback_incident_click (id, name);
					});
					$("#inventory_search_result_table tbody").fadeIn ();
					$("#inventory-pager").removeClass ("hide").fadeIn ();
				},
				"html"
			);
		});
		return false;
	});
}

function show_inventory_search_dialog (title, callback_incident_click) {
	values = Array ();
	values.push ({name: "page",
				value: "operation/inventory/inventory_search"});
	jQuery.get ("ajax.php",
		values,
		function (data, status) {
			$("#dialog").empty ().append (data);
			$("#dialog").dialog ({"title" : title,
					minHeight: 300,
					minWidth: 300,
					height: 500,
					width: 450,
					modal: true
					});
			configure_inventory_search_form (callback_incident_click);
		},
		"html"
	);
}

function configure_incident_side_menu (id_incident) {
	$("#incident-menu h3").empty ()
		.append ("Incident #"+id_incident);
	
	$("#incident-menu #incident-create-work").empty ()
		.append ($('<a></a>').attr ('href', "index.php?sec=incidents&sec2=operation/incidents/incident_create_work&id="+id_incident)
			.html ("Add workunit"));
	
	$("#incident-menu #incident-attach-file").empty ()
		.append ($('<a></a>').attr ('href', "index.php?sec=incidents&sec2=operation/incidents/incident_attach_file&id="+id_incident)
			.html ("Add file"));
}
