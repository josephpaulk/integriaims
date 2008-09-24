var dialog = "";
var parent_dialog = "";

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
						$(dialog+"#button-usuario_name").attr ("value", user_realname);
						$(dialog+"#hidden-usuario_form").attr ("value", user_id);
						$("#dialog-user-search").dialog ("close").empty ();
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
	$("#dialog-user-search").remove ();
	$("body").append ($("<div></div>").attr ("id", "dialog-user-search").addClass ("dialog"));
	values = [];
	values.push ({name: "page",
				value: "operation/users/user_search"});
	jQuery.get ("ajax.php",
		values,
		function (data, status) {
			$("#dialog-user-search").empty ().append (data);
			$("#dialog-user-search").dialog ({"title" : title,
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
	$(dialog+"#button-search_parent").click (function () {
		show_incident_search_dialog ("Search parent incident",
			function (id, name) {
				$("#dialog-search-incident").dialog ("close");
				$(dialog+"#button-search_parent").attr ("value", "Incident #"+id);
				$(dialog+"#hidden-id_parent").attr ("value", id);
			}
		);
	});
	
	$(dialog+"#button-usuario_name").click (function () {
		show_user_search_dialog ("User search");
	});
	
	$(dialog+"#incident_status").change (function () {
		/* Verified, see tincident_status table id */
		if (this.value == 6 || this.value == 7) {
			$(dialog+"#incident-editor-6").css ('display', '');
		} else {
			$(dialog+"#incident-editor-6").css ('display', 'none');
		}
	});
	
	$(dialog+"#incident_status").children ().each (function () {
		switch (this.value) {
		case 1:
			break;
		}
	});
	
	$(dialog+"#priority_form").change (function () {
		switch (this.value) {
		case "0":
			img = "images/pixel_gray.png";
			break;
		case "1":
			img = "images/pixel_green.png";
			break;
		case "2":
			img = "images/pixel_yellow.png";
			break;
		case "3":
			img = "images/pixel_orange.png";
			break;
		case "4":
			img = "images/pixel_red.png";
			break;
		case "10":
			img = "images/pixel_blue.png";
			break;
		default:
			img = "images/pixel_gray.png";
		}
		$(dialog+".priority-color").attr ("src", img);
	});
	
	$(dialog+"#button-search_inventory").click (function () {
		show_inventory_search_dialog ("Search inventory object",
			function (id, name) {
				var exists = false
				$(parent_dialog+".selected-inventories").each (function () {
					if (this.value == id) {
						exists = true;
						return;
					}
				});
				
				if (exists) {
					$("#dialog-search-inventory #inventory_search_result").fadeOut ('normal',
						function () {
						$(this).empty ().append ('<h3 class="error">Already added</h3>').fadeIn ();
					});
					return;
				}
				$(parent_dialog+"#incident_inventories").append ($('<option value="'+id+'">'+name+'</option>'));
				$(parent_dialog+"#incident_status_form").append ($('<input type="hidden" value="'+id+'" class="selected-inventories" name="inventories[]" />'));
				$("#dialog-search-inventory #inventory_search_result").fadeOut ('normal',
					function () {
						$(this).empty ().append ('<h3 class="suc">Added</h3>').fadeIn ();
				});
			}
		);
	});
	
	$(dialog+"#button-delete_inventory").click (function () {
		selected = $(dialog+"#incident_inventories").attr ("selectedIndex");
		
		$(dialog+"#incident_inventories").children (":eq("+selected+")").remove ();
	});
	
	if (enable_ajax_form) {
		$(dialog+"#incident_status_form").submit (function () {
			if ($(this).children (".selected-inventories").length == 0) {
				$(dialog+"#incident_inventories").fadeOut ('normal',function () {
					pulsate (this);
				});
				result_msg_error ("There's no affected object");
				
				return false;
			}
			values = get_form_input_values (this);
			values.push ({name: "page",
				value: "operation/incidents/incident_detail"});
			jQuery.post ("ajax.php",
				values,
				function (data, status) {
					$(".result").slideUp ('fast', function () {
						$(".result").empty ().append (data).slideDown ();
						$("#dialog-incident").dialog ("close");
					});
				},
				"html"
			);
			return false;
		});
	}
	
	$(dialog+"#incident_status_form").submit (function () {
		if ($(this).children (".selected-inventories").length == 0) {
			$(dialog+"#incident_inventories").fadeOut ('normal',function () {
				pulsate (this);
			});
			result_msg_error ("There's no affected object");
			
			return false;
		}
	});
}

function configure_incident_search_form (page_size, row_click_callback) {
	$(dialog+"#search_incident_form").submit (function () {
		$(dialog+"#incident_search_result_table").removeClass ("hide");
		values = get_form_input_values (this);
		values.push ({name: "page",
				value: "operation/incidents/incident_search"});
		$(dialog+"table#incident_search_result_table tbody").fadeOut ('normal', function () {
			$(this).empty ();
			jQuery.post ("ajax.php",
				values,
				function (data, status) {
					$(dialog+"table#incident_search_result_table tbody").empty ().append (data);
					$(dialog+"#incident_search_result_table tbody tr").click (function () {
						id = this.id.split ("-").pop ();
						name = $(this).children (":eq(2)").html ();
						row_click_callback (id, name);
					});
					$(dialog+"table#incident_search_result_table").trigger ("update")
						.tablesorterPager ({container: $(dialog+"#pager"), size: page_size});
					$(dialog+"table#incident_search_result_table tbody").fadeIn ();
					$(dialog+"#pager").removeClass ("hide").fadeIn ();
				},
				"html"
				);
		});
		return false;
	});
	$(dialog+"#incident_search_result_table tr th :eq(0)").addClass ("{sorter: 'text'}");
	$(dialog+"#incident_search_result_table").tablesorter ({ cancelSelection : true});
	$(dialog+"#button-inventory_name").click (function () {
		show_inventory_search_dialog ("Search inventory",
					function (id, name) {
						$(parent_dialog+"#hidden-search_id_inventory").attr ("value", id);
						$(parent_dialog+"#button-inventory_name").attr ("value", name);
						$("#dialog-search-inventory").dialog ("close");
					}
		);
	});
}

function show_add_incident_dialog () {
	$("#dialog-incident").remove ();
	$("body").append ($("<div></div>").attr ("id", "dialog-incident").addClass ("dialog"));
	
	values = Array ();
	values.push ({name: "page",
				value: "operation/incidents/incident_detail"});
	
	jQuery.get ("ajax.php",
		values,
		function (data, status) {
			$("#dialog-incident").empty ().append (data);
			$("#dialog-incident").dialog ({"title" : "Create incident",
					minHeight: 300,
					minWidth: 500,
					height: 750,
					width: 800,
					modal: true,
					open: function () {
						parent_dialog = dialog;
						dialog = "#dialog-incident ";
					},
					close: function () {
						dialog = parent_dialog;
						parent_dialog = "";
					}
				});
			configure_incident_form (true);
		},
		"html"
	);
}

function configure_inventory_search_form (page_size, incident_click_callback) {
	$(dialog+"#inventory_search_result_table").tablesorter ();
	$(dialog+"#inventory_search_form").submit (function () {
		$(dialog+"#inventory_search_result_table tbody").fadeOut ('normal',
			function () {
			values = get_form_input_values ("inventory_search_form");
			values.push ({name: "page",
				value: "operation/inventories/inventory_search"});
			jQuery.post ("ajax.php",
				values,
				function (data, status) {
					$(dialog+"#inventory_search_result_table").removeClass ("hide");
					$(dialog+"#inventory_search_result_table tbody").empty ().append (data);
					refresh_table ($(dialog+"#inventory_search_result_table"));
					$(dialog+"#inventory_search_result_table").trigger ("update")
						.tablesorterPager ({container: $(dialog+"#inventory-pager"), size: page_size});
					$(dialog+"#inventory_search_result_table tbody tr").click (function () {
						id = this.id.split ("-").pop ();
						name = $(this).children (":eq(0)").text ();
						incident_click_callback (id, name);
					});
					$(dialog+"#inventory_search_result_table tbody").fadeIn ();
					$(dialog+"#inventory-pager").removeClass ("hide").fadeIn ();
				},
				"html"
			);
		});
		return false;
	});
}

function show_incident_search_dialog (title, callback_incident_click) {
	$("#dialog-search-incident").remove ();
	$("body").append ($("<div></div>").attr ("id", "dialog-search-incident").addClass ("dialog"));
	values = Array ();
	values.push ({name: "page",
				value: "operation/incidents/incident_search"});
	values.push ({name: "search_form",
				value: 1});
	jQuery.get ("ajax.php",
		values,
		function (data, status) {
			$("#dialog-search-incident").empty ().append (data);
			$("#dialog-search-incident").dialog ({"title" : title,
					minHeight: 500,
					minWidth: 600,
					height: 600,
					width: 750,
					modal: true,
					open: function () {
						parent_dialog = dialog;
						dialog = "#dialog-search-incident ";
					},
					close: function () {
						dialog = parent_dialog;
						parent_dialog = "";
					}
					});
			configure_incident_search_form (5, callback_incident_click);
		},
		"html"
	);
}

function show_inventory_search_dialog (title, callback_incident_click) {
	$("#dialog-search-inventory").remove ();
	$("body").append ($("<div></div>").attr ("id", "dialog-search-inventory").addClass ("dialog"));
	values = Array ();
	values.push ({name: "page",
				value: "operation/inventories/inventory_search"});
	jQuery.get ("ajax.php",
		values,
		function (data, status) {
			$("#dialog-search-inventory").empty ().append (data);
			$("#dialog-search-inventory").dialog ({"title" : title,
					minHeight: 500,
					minWidth: 600,
					height: 700,
					width: 700,
					modal: true,
					buttons: {
						"X": function() { 
							$(this).dialog("close"); 
						} 
					},
					open: function () {
						parent_dialog = dialog;
						dialog = "#dialog-search-inventory ";
					},
					close: function () {
						dialog = parent_dialog;
						parent_dialog = "";
					}
					});
			configure_inventory_search_form (5, callback_incident_click);
		},
		"html"
	);
}

function configure_workunit_form () {
	$("#form-add-workunit").submit (function () {
		values = get_form_input_values ("form-add-workunit");
		values.push ({name: "page",
			value: "operation/incidents/incident_detail"});
		jQuery.post ("ajax.php",
			values,
			function (data, status) {
				$(".result").slideUp ('fast', function () {
					$(".result").empty ().append (data).slideDown ();
				});
				$("#dialog-add-workunit").dialog ("close");
			},
			"html"
		);
		return false;
	});
}

function show_add_workunit_dialog (id_incident) {
	$("#dialog-add-workunit").remove ();
	$("body").append ($("<div></div>").attr ("id", "dialog-add-workunit").addClass ("dialog"));
	values = Array ();
	values.push ({name: "page",
				value: "operation/incidents/incident_create_work"});
	values.push ({name: "id",
				value: id_incident});
	jQuery.get ("ajax.php",
		values,
		function (data, status) {
			$("#dialog-add-workunit").empty ().append (data);
			$("#dialog-add-workunit").dialog ({"title" : "Add workunit",
					minHeight: 300,
					minWidth: 300,
					height: 400,
					width: 600,
					modal: true
					});
			configure_workunit_form ();
		},
		"html"
	);
}

function configure_file_form () {
	$("#form-add-file").submit (function () {
		values = get_form_input_values ("form-add-file");
		values.push ({name: "page",
			value: "operation/incidents/incident_detail"});
		jQuery.post ("ajax.php",
			values,
			function (data, status) {
				$(".result").slideUp ('fast', function () {
					$(".result").empty ().append (data).slideDown ();
				});
				$("#dialog-add-file").dialog ("close");
			},
			"html"
		);
		return false;
	});
}

function show_add_file_dialog (id_incident) {
	$("#dialog-add-file").remove ();
	$("body").append ($("<div></div>").attr ("id", "dialog-add-file").addClass ("dialog"));
	
	values = Array ();
	values.push ({name: "page",
				value: "operation/incidents/incident_attach_file"});
	values.push ({name: "id",
				value: id_incident});
	jQuery.get ("ajax.php",
		values,
		function (data, status) {
			$("#dialog-add-file").empty ().append (data);
			$("#dialog-add-file").dialog ({"title" : "Upload file",
					minHeight: 500,
					minWidth: 200,
					height: 200,
					width: 600,
					modal: true
					});
			configure_file_form ();
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
	
	$("#incident-menu #incident-create-work a").click ( function () {
		show_add_workunit_dialog (id_incident);
		return false;
	});
	
	$("#incident-menu #incident-attach-file a").click ( function () {
		show_add_file_dialog (id_incident);
		return false;
	});
}

function configure_inventory_form (enable_ajax_form) {
	$(dialog+"#button-parent_search").click (function () {
		show_inventory_search_dialog ("Search parent inventory",
					function (id, name) {
						$("#button-parent_search").attr ("value", name);
						$("#hidden-id_parent").attr ("value", id);
						$("#dialog-search-inventory").dialog ("close");
					}
		);
	});
	
	$(dialog+"#id_contract").change (function () {
		id_contract = this.value;
		
		if (id_contract == 0) {
			$(dialog+"#id_sla").fadeOut ('normal', function () {
				$(this).children (":eq(0)").attr ("selected", "selected");
				$(this).fadeIn ();
			});
			return;
		}
		values = Array ();
		values.push ({name: "page",
					value: "operation/contracts/contract_detail"});
		values.push ({name: "id",
			value: id_contract});
		values.push ({name: "get_sla",
			value: 1});
		jQuery.get ("ajax.php",
			values,
			function (data, status) {
				$(dialog+"#id_sla").fadeOut ('normal', function () {
					$(this).children ().each (function () {
						if (this.value == data.id)
							$(this).attr ("selected", "selected");
					});
					$(this).fadeIn ();
				});
			},
			"json"
		);
		
	});
	
	$("#id_product").change (function () {
		id_product = this.value;
		
		$("#product-icon").fadeOut ('normal', function () {
			if (id_product == 0) {
				return;
			}
			values = Array ();
			values.push ({name: "page",
						value: "operation/kb/manage_prod"});
			values.push ({name: "id",
						value: id_product});
			values.push ({name: "get_icon",
						value: 1});
			jQuery.get ("ajax.php",
				values,
				function (data, status) {
					$("#product-icon").attr ("src", "images/products/"+data).fadeIn ();
				},
				"html"
			);
		});
		
	});
	
	if (enable_ajax_form) {
		$(dialog+"#inventory_status_form").submit (function () {
			values = get_form_input_values (this);
			values.push ({name: "page",
				value: "operation/inventories/inventory_detail"});
			jQuery.post ("ajax.php",
				values,
				function (data, status) {
					$(".result").slideUp ('fast', function () {
						$(".result").empty ().append (data).slideDown ();
					});
				},
				"html"
			);
			return false;
		});
	}
}

