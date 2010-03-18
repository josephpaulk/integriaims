var dialog = "";
var parent_dialog = "";

function configure_user_search_form () {
	$("#user_search_result_table").tablesorter ();
	$("#user_search_form").submit (function () {
		$("#user_search_result_table tbody").hide ();
		values = get_form_input_values ("user_search_form");
		values.push ({name: "page",
			value: "operation/users/user_search"});
		jQuery.post ("ajax.php",
			values,
			function (data, status) {
				$("#user_search_result_table").removeClass ("hide");
				$("#user_search_result_table tbody").empty ().append (data);
				$("#user_search_result_table tbody tr").click (function () {
					user_id = this.id.slice (7); /* Remove "result-" */
					user_realname = $(this).children (":eq(1)").text ();
					$(dialog+"#button-usuario_name").attr ("value", user_realname);
					$(dialog+"#hidden-usuario_form").attr ("value", user_id);
					$("#dialog-user-search").dialog ("close").empty ();
				});
				$("#user_search_result_table").trigger ("update")
					.tablesorterPager ({
						container: $("#users-pager"),
						size: 10
					});
				$("#user_search_result_table tbody").show ();
				$("#users-pager").removeClass ("hide").show ();
			},
			"html");
		
		return false;
	});
}

function show_user_search_dialog (title) {
	$("#dialog-user-search").remove ();
	$("body").append ($("<div></div>").attr ("id", "dialog-user-search").addClass ("dialog"));
	values = Array ();
	values.push ({name: "page",
				value: "operation/users/user_search"});
	jQuery.get ("ajax.php",
		values,
		function (data, status) {
			$("#dialog-user-search").empty ().append (data);
			$("#dialog-user-search").dialog ({"title" : title,
					minHeight: 300,
					minWidth: 500,
					height: 550,
					width: 700,
					modal: true,
					bgiframe: true
					});
			configure_user_search_form ();
		},
		"html"
	);
}

function configure_inventory_buttons (form, dialog) {
	$(dialog+"#button-search_inventory").click (function () {
		show_inventory_search_dialog (__("Search inventory object"),
			function (id, name) {
				var exists = false
				$(parent_dialog+".selected-inventories").each (function () {
					if (this.value == id) {
						exists = true;
						return;
					}
				});
				
				if (exists) {
					$("#dialog-search-inventory #inventory_search_result").empty ()
						.append ('<h3 class="error">'+__("Already added")+'</h3>').show ();
					return;
				}
				$(parent_dialog+"#incident_inventories").append ($('<option value="'+id+'">'+name+'</option>'));
				$(parent_dialog+"#"+form).append ($('<input type="hidden" value="'+id+'" class="selected-inventories" name="inventories[]" />'));
				$("#dialog-search-inventory #inventory_search_result").empty ()
					.append ('<h3 class="suc">'+__("Added")+'</h3>').show ();
			}
		);
	});
	
	$(dialog+"#button-delete_inventory").click (function () {
		var s;
		
		s = $(dialog+"#incident_inventories").attr ("selectedIndex");
		selected_id = $(dialog+"#incident_inventories").children (":eq("+s+")").attr ("value");
		$(dialog+"#incident_inventories").children (":eq("+s+")").remove ();
		$(dialog+".selected-inventories").each (function () {
			if (this.value == selected_id)
				$(this).remove ();
		});
	});
}

function configure_incident_form (enable_ajax_form) {
	//Function for change the select group, test if it's hard limit or soft.
	$("#grupo_form").change (function() {
		$("#submit-accion").attr("disabled", "disabled");
		$("#group_spinner").empty().append('<img src="images/spinner.gif" />');
		
		id_user = $("#id_user").val();
		
		values = Array();
		values.push ({name: "page", value: "operation/group/group"});
		values.push ({name: "id_group", value: $("#grupo_form").val()});
		values.push ({name: "id_user", value: $("#id_user").val()});
		
		//Check the limits of incidents, and show div popup with error message.
		jQuery.ajax({
			type: "POST",
			url: "ajax.php",
			data: values,
			async: false,
			success: function (data, status) {
				//un serialize data as type//title_window//message_window
				dataUnserialize = data.split('//');
				$("#group_spinner").empty();
				var enableButton = true;
				
				status = dataUnserialize[0];
				
				if (status != "correct") {
					$("#test").remove();
					$("body").append ($("<div></div>").attr("id", "alert_limits").addClass ("dialog"));
					
					$("#alert_limits").empty().append('<img src="images/spinner.gif">');
					$("#alert_limits").dialog({"title": dataUnserialize[1],
						position: ['center', 100],
						resizable: false,
						height: 140,
						width: 350,
						beforeclose: function(event, ui) { return false; }
					});
					
					enableButtonParam = dataUnserialize[3];
					if (enableButtonParam != 'enable_button')
						enableButton = false;
					
					$("#alert_limits").empty().append(dataUnserialize[2]);
				
					$("#alert_limits").dialog('close');
					$("#alert_limits").bind('dialogbeforeclose', function(event, ui) {
						$("#alert_limits").dialog('destroy'); $("#alert_limits").remove(); return true;
					});
				}
				else {
					//Correct
					idInventory = dataUnserialize[1];
					if (idInventory != 'null') {
						nameInventory = dataUnserialize[2];
						$(parent_dialog+"#incident_inventories").empty();
						$(parent_dialog+".selected-inventories").remove();
						$(parent_dialog+"#incident_inventories").append ($('<option value="' + idInventory + '">' + nameInventory + '</option>'));
						$(parent_dialog+"#incident_status_form").append ($('<input type="hidden" value="'+idInventory+'" class="selected-inventories" name="inventories[]" />'));
					}
				}
				
				if (enableButton) {
					$("#submit-accion").removeAttr("disabled");
				}
			},
			dataType: "text"
		});
	});
	
	if ($("#hidden-action").val() == 'insert') {
		$("#submit-accion").attr("disabled", "disabled");
	}
	
	
	$("form.delete").submit (function () {
		if (! confirm (__("Are you sure?")))
			return false;
	});
	$(dialog+"#textarea-description").TextAreaResizer ();
	$(dialog+"#textarea-epilog").TextAreaResizer ();
	$(dialog+"#button-search_parent").click (function () {
		show_incident_search_dialog (__("Search parent incident"),
			function (id, name) {
				$("#dialog-search-incident").dialog ("close");
				$(dialog+"#button-search_parent").attr ("value", "Incident #"+id);
				$(dialog+"#hidden-id_parent").attr ("value", id);
			}
		);
	});
	
	$(dialog+"#button-usuario_name").click (function () {
		show_user_search_dialog (__("User search"));
	});
	
	$(dialog+"#incident_status").change (function () {
		/* Verified, see tincident_status table id */
		if (this.value == 6 || this.value == 7) {
			$(dialog+"#incident-editor-6").css ('display', '');
		} else {
			$(dialog+"#incident-editor-6").css ('display', 'none');
		}
	});
	$(dialog+"#grupo_form").change (function () {
		values = Array ();
		values.push ({name: "page",
			value: "godmode/grupos/lista_grupos"});
		values.push ({name: "id",
			value: this.value});
		values.push ({name: "get_group_details",
			value: 1});
		
		jQuery.get ("ajax.php",
			values,
			function (data, status) {
				if (! data["user_real_name"])
					return;
				$(dialog+"#hidden-usuario_form").attr ("value", data["id_user_default"]);
				$(dialog+"#button-usuario_name").attr ("value", data["user_real_name"]);
				if (data["forced_email"] == 1)
					$(dialog+"#checkbox-email_notify").attr ("checked", "checked");
				else
					$(dialog+"#checkbox-email_notify").removeAttr ("checked");
			},
			"json"
		);
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
	
	configure_inventory_buttons ("incident_status_form", dialog);
	configure_contact_buttons ("incident_status_form", dialog);
	
	if (enable_ajax_form) {
		$(dialog+"#incident_status_form").submit (function () {
			if ($(".selected-inventories", this).length == 0) {
				$(dialog+"#incident_inventories").fadeOut ('normal',function () {
					pulsate (this);
				});
				result_msg_error (__("There's no affected inventory object"));

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
						if ($("#incident_status").attr ("value") == 6) {
							$("[name=kb_form]").show ();
						} else {
							$("[name=kb_form]").hide ();
						}
					});
				},
				"html"
			);
			return false;
		});
	} else {
		$(dialog+"#incident_status_form").submit (function () {
			if ($(".selected-inventories", this).length == 0) {
				$(dialog+"#incident_inventories").fadeOut ('normal',function () {
					pulsate (this);
				});
				result_msg_error (__("There's no affected object"));
			
				return false;
			}
		});
	}
}

function configure_incident_search_form (page_size, row_click_callback, search_callback) {
	$(dialog+".show_advanced_search").click (function () {
		table = $(dialog+"#search_incident_form").children ("table");
		$("tr", table).show ();
		$(this).remove ();
		return false;
	});
	$(dialog+"#text-search_first_date").datepicker ({
		beforeShow: function () {
			return {
				maxDate: $(dialog+"#text-search_last_date").datepicker ("getDate")
			};
		}
	});
	$(dialog+"#text-search_last_date").datepicker ({
		beforeShow: function () {
			return {
				minDate: $(dialog+"#text-search_first_date").datepicker ("getDate")
			};
		}
	});
	$(dialog+"#search_incident_form").submit (function () {
		$(dialog+"div#loading").show ();
		$(dialog+"#incident_search_result_table").removeClass ("hide");
		values = Array ();
		values = get_form_input_values (this);
		values.push ({name: "page",
				value: "operation/incidents/incident_search"});
		$(dialog+"table#incident_search_result_table tbody").hide ().empty ();
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
					.tablesorterPager ({
						container: $(dialog+"#pager"),
						size: page_size,
						headers: {
							0: "currency"
						}
					});
				$(dialog+"table#incident_search_result_table tbody").show ();
				$(dialog+"#pager").removeClass ("hide").show ();
				$(dialog+"div#loading").hide ();
				if (search_callback)
					search_callback ($(dialog+"#search_incident_form"));
			},
			"html"
			);
		
		return false;
	});
	$(dialog+"#incident_search_result_table tr th :eq(0)").addClass ("{sorter: 'text'}");
	$(dialog+"#incident_search_result_table").tablesorter ({ cancelSelection : true});
	$(dialog+"#button-inventory_name").click (function () {
		show_inventory_search_dialog (__("Search inventory object"),
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
					bgiframe: true,
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

function configure_inventory_search_form (page_size, incident_click_callback, search_callback) {
	$(dialog+".show_advanced_search").click (function () {
		table = $(dialog+"#inventory_search_form").children ("table");
		$("tr", table).show ();
		$(this).remove ();
		return false;
	});
	$(dialog+"#inventory_search_result_table").tablesorter ();
	$(dialog+"#inventory_search_form").submit (function () {
		$(dialog+"div#loading").show ();
		$(dialog+"#inventory_search_result_table tbody").hide ();
		
		values = get_form_input_values ("inventory_search_form");
		values.push ({name: "page",
			value: "operation/inventories/inventory_search"});
		if (dialog != "") {
			values.push ({name: "short_table",
				value: 1});
		}
		jQuery.post ("ajax.php",
			values,
			function (data, status) {
				$(dialog+"#inventory_search_result_table").removeClass ("hide");
				$(dialog+"#inventory_search_result_table tbody").empty ().append (data);
				$(dialog+"#inventory_search_result_table tbody tr").click (function () {
					id = this.id.split ("-").pop ();
					name = $(this).children (":eq(1)").text ();
					incident_click_callback (id, name);
				});
				$(dialog+"#inventory_search_result_table").trigger ("update")
					.tablesorterPager ({
						container: $(dialog+"#inventory-pager"),
						size: page_size,
						headers: {
							0: "currency"
						}
					});
				$(dialog+"#inventory_search_result_table tbody").show ();
				$(dialog+"#inventory-pager").removeClass ("hide").show ();
				$(dialog+"div#loading").hide ();
				if (search_callback)
					search_callback ($(dialog+"#inventory_search_form"));
			},
			"html");
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
					bgiframe: true,
					open: function () {
						parent_dialog = dialog;
						dialog = "#dialog-search-incident ";
					},
					close: function () {
						dialog = parent_dialog;
						parent_dialog = "";
					}
					});
			configure_incident_search_form (5, callback_incident_click, null);
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
					minHeight: 400,
					minWidth: 600,
					height: 600,
					width: 900,
					modal: true,
					bgiframe: true,
					open: function () {
						parent_dialog = dialog;
						dialog = "#dialog-search-inventory ";
					},
					close: function () {
						dialog = parent_dialog;
						parent_dialog = "";
					}
					});
			configure_inventory_search_form (10, callback_incident_click, false);
		},
		"html"
	);
}

function configure_workunit_form () {
	$(dialog+"#textarea-nota").TextAreaResizer ();
	$("#form-add-workunit").submit (function () {
		values = get_form_input_values ("form-add-workunit");
		values.push ({name: "page",
			value: "operation/incidents/incident_detail"});
		jQuery.post ("ajax.php",
			values,
			function (data, status) {
				$(".result").slideUp ("fast", function () {
					$(".result").empty ().append (data).slideDown ();
				});
				$("#dialog-add-workunit").dialog ("close");
				if (tabs != undefined && tabs.data ("selected.tabs") == 5)
					$("#tabs > ul").tabs ("load", 5);
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
			$("#dialog-add-workunit").dialog ({"title" : __("Add workunit"),
					minHeight: 280,
					minWidth: 300,
					height: 440,
					width: 600,
					modal: true,
					bgiframe: true
					});
			configure_workunit_form ();
		},
		"html"
	);
}

function configure_file_form () {
	$('#form-add-file').ajaxForm ({
		beforeSubmit: function (a, f, o) {
			o.dataType = "html";
			$('#upload_result').html (__("Submitting")+'...');
		},
		success: function (data) {
			$('#upload_result').hide ().empty ().html (data).show ();
			if (tabs != undefined && tabs.data ("selected.tabs") == 6)
				$("#tabs > ul").tabs ("load", 6);
		}
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
			$("#dialog-add-file").dialog ({"title" : __("Upload file"),
					minHeight: 350,
					minWidth: 200,
					height: 350,
					width: 600,
					modal: true,
					bgiframe: true
					});
			configure_file_form ();
		},
		"html"
	);
}

function configure_incident_side_menu (id_incident, refresh_menu) {
	$(".id-incident-menu").empty ().append (id_incident);
	
	$("#incident-menu-actions #incident-create-work")
		.attr ('href', "index.php?sec=incidents&sec2=operation/incidents/incident_create_work&id="+id_incident)
		.unbind ("click")
		.click (function () {
			show_add_workunit_dialog (id_incident);
			return false;
		});
	
	$("#incident-menu-actions #incident-attach-file")
		.attr ('href', "index.php?sec=incidents&sec2=operation/incidents/incident_attach_file&id="+id_incident)
		.unbind ("click")
		.click (function () { 
			show_add_file_dialog (id_incident);
			return false;
		});
	
	if (refresh_menu) {
		values = Array ();
		values.push ({name: "page",
					value: "operation/incidents/incident_detail"});
		values.push ({name: "id",
					value: id_incident});
		values.push ({name: "action",
					value: 'get-users-list'});
		jQuery.get ("ajax.php",
			values,
			function (data, status) {
				$("#incident-menu-users #incident-users").empty ().append (data);
			},
			"html"
		);
		
		values = Array ();
		values.push ({name: "page",
					value: "operation/incidents/incident_detail"});
		values.push ({name: "id",
					value: id_incident});
		values.push ({name: "action",
					value: 'get-details-list'});
		jQuery.get ("ajax.php",
			values,
			function (data, status) {
				$("#incident-menu-details #incident-details").empty ().append (data);
			},
			"html"
		);
	}
}

function configure_inventory_side_menu (id_inventory, refresh_menu) {
	$(".id-inventory-menu").empty ().append (id_inventory);
	
	$("#inventory-menu-actions #inventory-create-incident")
		.attr ('href', "index.php?sec=incidents&sec2=operation/incidents/incident_detail&id_inventory="+id_inventory);
}

function configure_contact_search_form (page_size, contact_click_callback) {
	$(dialog+"#contact_search_result_table").tablesorter ();
	$(dialog+"#contact_search_form").submit (function () {
		$(dialog+"#contact_search_result_table tbody").hide ();
		values = get_form_input_values ("contact_search_form");
		values.push ({name: "page",
			value: "operation/inventories/inventory_contacts_search"});
		if (dialog != "") {
			values.push ({name: "short_table",
				value: 1});
		}
		jQuery.post ("ajax.php",
			values,
			function (data, status) {
				$(dialog+"#contact_search_result_table").removeClass ("hide");
				$(dialog+"#contact_search_result_table tbody").empty ().append (data);
				$(dialog+"#contact_search_result_table tbody tr").click (function () {
					id = this.id.split ("-").pop ();
					name = $(this).children (":eq(0)").text ();
					contact_click_callback (id, name);
				});
				$(dialog+"#contact_search_result_table").trigger ("update")
					.tablesorterPager ({
						container: $(dialog+"#contact-pager"),
						size: page_size
					});
				$(dialog+"#contact_search_result_table tbody").show ();
				$(dialog+"#contact-pager").removeClass ("hide").show ();
			},
			"html");
		
		return false;
	});
}

function configure_contact_create_form (callback_contact_created) {
	$(dialog+"#contact_form").submit (function () {
		var name = $(dialog+"#text-fullname").attr ("value");
		if (name == "") {
			pulsate ($(dialog+"#text-fullname"));
			return false;
		}
		values = Array ();
		values = get_form_input_values (this);
		values.push ({name: "page",
					value: "operation/contacts/contact_detail"});
		jQuery.post ("ajax.php",
			values,
			function (data, status) {
				$("#dialog-create-contact").dialog ("close");
				callback_contact_created (data, name);
			},
			"json"
		);
		return false;
	});
}

function show_contact_create_dialog (title, callback_contact_created) {
	$("#dialog-create-contact").remove ();
	$("body").append ($("<div></div>").attr ("id", "dialog-create-contact").addClass ("dialog"));
	values = Array ();
	values.push ({name: "page",
				value: "operation/contacts/contact_detail"});
	values.push ({name: "new_contact",
				value: 1});
	values.push ({name: "id_contract",
				value: $(dialog+"#id_contract").attr ("value")});
	jQuery.get ("ajax.php",
		values,
		function (data, status) {
			$("#dialog-create-contact").empty ().append (data);
			$("#dialog-create-contact").dialog ({"title" : title,
				minHeight: 500,
				minWidth: 600,
				height: 600,
				width: 700,
				modal: true,
				bgiframe: true,
				open: function () {
					parent_dialog = dialog;
					dialog = "#dialog-create-contact ";
				},
				close: function () {
					dialog = parent_dialog;
					parent_dialog = "";
				}
			});
			configure_contact_create_form (callback_contact_created);
		},
		"html"
	);
}

function show_contact_search_dialog (title, callback_contact_click) {
	$("#dialog-search-contact").remove ();
	$("body").append ($("<div></div>").attr ("id", "dialog-search-contact").addClass ("dialog"));
	values = Array ();
	values.push ({name: "page",
				value: "operation/inventories/inventory_contacts_search"});
	jQuery.get ("ajax.php",
		values,
		function (data, status) {
			$("#dialog-search-contact").empty ().append (data);
			$("#dialog-search-contact").dialog ({"title" : title,
				minHeight: 500,
				minWidth: 600,
				height: 600,
				width: 700,
				modal: true,
				bgiframe: true,
				open: function () {
					parent_dialog = dialog;
					dialog = "#dialog-search-contact ";
				},
				close: function () {
					dialog = parent_dialog;
					parent_dialog = "";
				}
			});
			configure_contact_search_form (10, callback_contact_click);
		},
		"html"
	);
} 

function configure_contact_buttons (form, dialog) {
	$(dialog+"#button-search_contact").click (function () {
		show_contact_search_dialog (__("Search contact"),
			function (id, name) {
				var exists = false
				$(parent_dialog+".selected-contacts").each (function () {
					if (this.value == id) {
						exists = true;
						return;
					}
				});
				
				if (exists) {
					$("#dialog-search-contact #contact_search_result").empty ()
						.append ('<h3 class="error">'+__("Already added")+'</h3>').show ();
					
					return;
				}
				$(parent_dialog+"#select_contacts").append ($('<option value="'+id+'">'+name+'</option>'));
				$(parent_dialog+"#"+form).append ($('<input type="hidden" value="'+id+'" class="selected-contacts" name="contacts[]" />'));
				$("#dialog-search-contact #contact_search_result").empty ()
					.append ('<h3 class="suc">'+__("Added")+'</h3>').show ();
			}
		);
	});
	
	$(dialog+"#button-delete_contact").click (function () {
		var s;
		
		s = $(dialog+"#select_contacts").attr ("selectedIndex");
		selected_id = $(dialog+"#select_contacts").children (":eq("+s+")").attr ("value");
		$(dialog+"#select_contacts").children (":eq("+s+")").remove ();
		$(dialog+".selected-contacts").each (function () {
			if (this.value == selected_id)
				$(this).remove ();
		});
	});
	
	$(dialog+"#button-create_contact").click (function () {
		show_contact_create_dialog (__("Create contact"),
			function (id, name) {
				$(parent_dialog+"#select_contacts").append ($('<option value="'+id+'">'+name+'</option>'));
				$(parent_dialog+"#"+form).append ($('<input type="hidden" value="'+id+'" class="selected-contacts" name="contacts[]" />'));
			}
		);
	});
}

function configure_inventory_form (enable_ajax_form) {
	$("form.delete").submit (function () {
		if (! confirm (__("Are you sure?")))
			return false;
	});
	$(dialog+"#textarea-description").TextAreaResizer ();
	$(dialog+"#button-parent_search").click (function () {
		show_inventory_search_dialog (__("Search parent inventory"),
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
			$(dialog+"#id_sla").hide ().children (":eq(0)").attr ("selected", "selected");
			$(dialog+"#id_sla").show ();
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
				$(dialog+"#id_sla").hide ().children ().each (function () {
						if (this.value == data.id)
							$(this).attr ("selected", "selected");
					}).show ();
			},
			"json"
		);
		
		values = Array ();
		values.push ({name: "page",
					value: "operation/contacts/contact_detail"});
		values.push ({name: "id",
			value: id_contract});
		values.push ({name: "get_contacts",
			value: 1});
		jQuery.get ("ajax.php",
			values,
			function (data, status) {
				$(dialog+"#select_contacts").hide ().empty ();
				$(dialog+".selected-contacts").remove ();
				$(data).each (function () {
					$(dialog+"#select_contacts").append ($('<option value="'+this.id+'">'+this.fullname+'</option>'));
					$(dialog+"#inventory_status_form").append ($('<input type="hidden" value="'+this.id+'" class="selected-contacts" name="contacts[]" />'));
				});
				$(dialog+"#select_contacts").show ();
			},
			"json"
		);
	});
	
	$(dialog+"#id_product").change (function () {
		id_product = this.value;
		
		$(dialog+"#product-icon").hide ()
		if (id_product == 0) {
			return;
		}
		values = Array ();
		values.push ({name: "page",
					value: "operation/inventories/manage_prod"});
		values.push ({name: "id",
					value: id_product});
		values.push ({name: "get_icon",
					value: 1});
		jQuery.get ("ajax.php",
			values,
			function (data, status) {
				$(dialog+"#product-icon").attr ("src", "images/products/"+data).show ();
			},
			"html"
		);;
		
	});
	
	configure_contact_buttons ("inventory_status_form", dialog);
	
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
