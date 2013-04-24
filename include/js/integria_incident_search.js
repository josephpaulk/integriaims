var dialog = "";
var parent_dialog = "";

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

function incident_limit() {
		$("#group_spinner").empty().append('<img src="images/spinner.gif" />');
		
		id_user = $("#id_user").html();
		
		values = Array();
		values.push ({name: "page", value: "operation/group/group"});
		values.push ({name: "id_group", value: $("#grupo_form").val()});
		values.push ({name: "id_user", value: id_user});
	
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
				status = dataUnserialize[0];
				
				if (status != "correct") {
					$("body").append ($("<div></div>").attr("id", "alert_limits").addClass ("dialog"));
					
					$("#alert_limits").empty().append('<img src="images/spinner.gif">');
					$("#alert_limits").dialog({"title": dataUnserialize[1],
						position: ['center', 100],
						resizable: true,
						height: 150,
						width: 380,
						beforeclose: function(event, ui) { return false; }
					});
					
					enableButtonParam = dataUnserialize[3];
					
			// DEBUG
			//window.alert(enableButtonParam);
					
					if (enableButtonParam != 'enable_button')
						$("#submit-accion").attr("disabled", "disabled");
					
					$("#alert_limits").empty().append(dataUnserialize[2]);
				
					$("#alert_limits").dialog('close');
					$("#alert_limits").bind('dialogbeforeclose', function(event, ui) {
						$("#alert_limits").dialog('destroy'); $("#alert_limits").remove();
					});
				}
				else {
					//Correct
					$("#submit-accion").removeAttr("disabled");
					idInventory = dataUnserialize[1];
					if (idInventory != 'null') {
						nameInventory = dataUnserialize[2];
						$(parent_dialog+"#incident_inventories").empty();
						$(parent_dialog+"#incident_inventories").append ($('<option value="' + idInventory + '">' + nameInventory + '</option>'));
						$(parent_dialog+".selected-inventories").remove();
						$(parent_dialog+"#incident_status_form").append ($('<input type="hidden" value="'+idInventory+'" class="selected-inventories" name="inventories[]" />'));
					}
				}
				
			},
			dataType: "text"
		});
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
					resizable: false,
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
		$("#sending_data").css('display','');
		$("input[name=addnote]").css('display', 'none');
		
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
				// If the tracking tab is selected we update it
				if (tabs != undefined && tabs.data ("selected.tabs") == 3)
					$("#tabs > ul").tabs ("load", 3);
				// If the workunits tab is selected we update it
				if (tabs != undefined && tabs.data ("selected.tabs") == 7)
					$("#tabs > ul").tabs ("load", 7);
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
					bgiframe: true,
					resizable: false
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
			// If the tracking tab is selected we update it
			if (tabs != undefined && tabs.data ("selected.tabs") == 3)
				$("#tabs > ul").tabs ("load", 3);
			// If the workunits tab is selected we update it
			if (tabs != undefined && tabs.data ("selected.tabs") == 7)
				$("#tabs > ul").tabs ("load", 7);
			// If the files tab is selected we update it
			if (tabs != undefined && tabs.data ("selected.tabs") == 8)
				$("#tabs > ul").tabs ("load", 8);
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
					minHeight: 400,
					minWidth: 200,
					height: 400,
					width: 600,
					modal: true,
					bgiframe: true,
					resizable: false
					});
			configure_file_form ();
		},
		"html"
	);
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
				resizable: false,
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
				resizable: false,
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
			$("#company_name").html('');
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
				$(dialog+"#id_sla").children ().each (function () {
						if (this.value == data.id)
							$(this).attr ("selected", "selected");
					}).show ();
			},
			"json"
		);
		
		values = Array ();
		values.push ({name: "page",
					value: "operation/contracts/contract_detail"});
		values.push ({name: "id",
			value: id_contract});
		values.push ({name: "get_company_name",
			value: 1});
		jQuery.get ("ajax.php",
			values,
			function (data, status) {
				$("#company_name").html(data);
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

function process_massive_updates () {
	var checked_ids = new Array();
	var status;
	var priority;
	var resolution;
	var assigned_user;

	$(".cb_incident").each(function() {
		id = this.id.split ("-").pop ();
		checked = $(this).attr('checked');
		if(checked) {
			$(this).attr('checked', false);
			checked_ids.push(id);
		}
	});

	if(checked_ids.length == 0) {
		alert(__("No items selected"));
	}
	else {
		status = $("#mass_status").attr("value");
		priority = $("#mass_priority").attr("value");
		resolution = $("#mass_resolution").attr("value");
		assigned_user = $("#mass_assigned_user").attr("value");
		if(status == -1 && priority == -1 && resolution == -1 && assigned_user == -1) {
			alert(__("Nothing to update"));
		}
		else {		
			for(var i=0;i<checked_ids.length;i++){
				values = Array ();
				values.push ({name: "page",
							value: "operation/incidents/incident_detail"});
				values.push ({name: "id",
							value: checked_ids[i]});
				if(status != -1) {
					values.push ({name: "incident_status",
							value: status});
				}
				if(priority != -1) {
					values.push ({name: "priority_form",
							value: priority});
				}
				if(resolution != -1) {
					values.push ({name: "incident_resolution",
							value: resolution});
				}
				if(assigned_user != -1) {
					values.push ({name: "id_user",
							value: assigned_user});
				}
				values.push ({name: "massive_number_loop",
						value: i});
				values.push ({name: "action",
							value: 'update'});
				
				jQuery.get ("ajax.php",
					values,
					function (data, status) {
						
						// We refresh the interface in the last loop
						if(data == (checked_ids.length - 1)) {
							window.location.href="index.php?sec=incidents&sec2=operation/incidents/incident_search";
						}
					},
					"html"
				);
			}		
		}
	}	
	
}

function show_incident_type_fields() {

	id_incident_type = $("#id_incident_type").val();

	id_incident = $("#text-id_incident_hidden").val();

	//$('.new_row').remove();
	$('#table_fields').remove();

	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=operation/incidents/incident_detail&show_type_fields=1&id_incident_type=" + id_incident_type +"&id_incident=" +id_incident,
		dataType: "json",
		success: function(data){
			
			fi=document.getElementById('incident-editor-4-0');
			var table = document.createElement("table"); //create table
			table.id='table_fields';
			table.className = 'databox_color_without_line';
			table.width='98%';
			fi.appendChild(table); //append table to row
			
			var i = 0;
			var resto = 0;
			jQuery.each (data, function (id, value) {
				
				resto = i % 2;

				if (value['type'] == "combo") {
					if (resto == 0) {
						var objTr = document.createElement("tr"); //create row
						objTr.id = 'new_row_'+i;
						objTr.width='98%';
						table.appendChild(objTr);
					} else {
						pos = i-1;
						objTr = document.getElementById('new_row_'+pos);
					}
					
					var objTd1 = document.createElement("td"); //create column for label
					objTd1.width='50%';
					lbl = document.createElement('label');
					lbl.innerHTML = value['label']+' ';
					
					objTr.appendChild(objTd1);
					objTd1.appendChild(lbl);
					
					txt = document.createElement('br');
					lbl.appendChild(txt);
					
					element=document.createElement('select');
					element.id=value['label']; 
					element.name=value['label_enco'];
					element.value=value['label'];
					element.style.width="170px";
					element.class="type";
					
					var new_text = value['combo_value'].split(',');
					jQuery.each (new_text, function (id, val) {
						element.options[id] = new Option(val);
						element.options[id].setAttribute("value",val);
						if (value['data'] == val) {
							element.options[id].setAttribute("selected",'');
						}
					});
			
					lbl.appendChild(element);
					i++;
				}
				
				if ((value['type'] == "text")) {
					
					if (resto == 0) {
						var objTr = document.createElement("tr"); //create row
						objTr.id = 'new_row_'+i;
						objTr.width='98%';
						table.appendChild(objTr);
					} else {
						pos = i-1;
						objTr = document.getElementById('new_row_'+pos);
					}
					
					var objTd1 = document.createElement("td"); //create column for label
					objTd1.width='50%';
					lbl = document.createElement('label');
					lbl.innerHTML = value['label']+' ';
					objTr.appendChild(objTd1);
					objTd1.appendChild(lbl);
					
					txt = document.createElement('br');
					lbl.appendChild(txt);

					
					element=document.createElement('input');
					element.id=value['label'];
					element.name=value['label_enco'];
					element.value=value['data'];
					element.type='text';
					element.size=40;
					
					lbl.appendChild(element);
					i++;
				}
				
				if ((value['type'] == "textarea")) {
					
					if (resto == 0) {
						var objTr = document.createElement("tr"); //create row
						objTr.id = 'new_row_'+i;
						table.appendChild(objTr);
					} else {
						pos = i-1;
						objTr = document.getElementById('new_row_'+pos);
					}
					
					var objTd1 = document.createElement("td"); //create column for label
					
					lbl = document.createElement('label');
					lbl.innerHTML = value['label']+' ';
					objTr.appendChild(objTd1);
					objTd1.appendChild(lbl);
					
					element=document.createElement("textarea");
					element.id=value['label'];
					element.name=value['label_enco'];
					element.value=value['data'];
					element.type='text';
					element.rows='3';
					
					lbl.appendChild(element);
					i++;
				}
			});
		}
	});
}
