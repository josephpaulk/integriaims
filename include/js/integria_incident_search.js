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

function incident_limit(button_id, id_user, id_group) {
		$("#group_spinner").empty().append('<img src="images/spinner.gif" />');
		
		values = Array();
		values.push ({name: "page", value: "operation/group/group"});
		values.push ({name: "id_group", value: id_group});
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
				status = status.trim();//Important I don't no why but if you don't trim the string it has a blank and doesn't match
				if (status != "correct") {
					
					$("body").append ($("<div></div>").attr("id", "alert_limits").addClass ("dialog"));
					
					$("#alert_limits").empty().append('<img src="images/spinner.gif">');
					$("#alert_limits").dialog({"title": dataUnserialize[1],
						position: ['center', 100],
						resizable: false,
						height: 150,
						width: 380,
						beforeclose: function(event, ui) { return false; }
					});
					
					enableButtonParam = dataUnserialize[3];
					
			// DEBUG
			//window.alert(enableButtonParam);
					
					if (enableButtonParam != 'enable_button')
						$(button_id).attr("disabled", "disabled");
					
					$("#alert_limits").empty().append(dataUnserialize[2]);
				
					$("#alert_limits").bind('dialogbeforeclose', function(event, ui) {
						$("#alert_limits").dialog('destroy'); $("#alert_limits").remove();
					});
				}
				else {
					//Correct
					$(button_id).removeAttr("disabled");
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
	values.push ({name: "mode",
				value: "list"});
	values.push ({name: "popup",
				value: 1});
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
					scroll: true,
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
	var task;
	var parent_ticket;
	var notify_changes;

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
		task = $("#task_user").attr("value");
		parent_ticket_name = $("#text-search_parent").attr("value");
		parent_ticket_split = parent_ticket_name.split('#');
		parent_ticket_id = parent_ticket_split[1];	
		notify_changes = $("#checkbox-mass_email_notify:checked").val();
		
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
			if(task != 0) {
				values.push ({name: "id_task",
						value: task});
			}
			if(parent_ticket != 0) {
				values.push ({name: "id_parent",
						value: parent_ticket_id});
			}
			if(notify_changes == 1) {
				values.push ({name: "mass_email_notify",
						value: 1});
			} else {
				values.push ({name: "mass_email_notify",
						value: 0});
			}
			values.push ({name: "massive_number_loop",
					value: i});
			values.push ({name: "action",
						value: 'update'});
			
			jQuery.get ("ajax.php",
				values,
				function (data, status) {
					
					// We refresh the interface in the last loop
					if(data >= (checked_ids.length - 1)) {
						// This takes the user to the top of the page
						//window.location.href="index.php?sec=incidents&sec2=operation/incidents/incident_search";
						// This takes the user to the same place before reload
						location.reload();
					}
				},
				"json"
			);
		}
	}	
	
}

function show_incident_type_fields(numRow) {

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

			//FIRST DELETE OLD ROWS CREATED BY THIS FUNCTION BEFORE
			$("[id^='new_row']").remove();

			//Now create new elements
			table = document.getElementById("incident-editor");
			
			var i = 0;
			var resto = 0;
			var row;
			if (numRow === undefined) {
				row = 3;
			} else {
				row = numRow;
			}
			var textarea_elements = new Array();
			var objTr;
			var pos = 0;
			jQuery.each (data, function (id, value) {
				
				//This loops prints combos and text fields
				//textare fields are printed later.
				if (value["type"] == "textarea") {
					textarea_elements.push(value);
					return true; //Skip this iteration;
				} 

				resto = i % 3;

				//Check if we need to create a new row or not
				if (resto == 0) {
					row++;
					objTr = table.insertRow(row); //create row
					objTr.id = 'new_row_'+row;
					pos = 0;
				} else {
					pos++;
				}
				
				//Create td and label elements
				var objTd = objTr.insertCell(pos); //create column for label

				//Create label and only add content if the element is not a texarea
				lbl = document.createElement('label');
				lbl.innerHTML = value['label']+' ';

				txt = document.createElement('br');
				lbl.appendChild(txt);
				
				if (value['type'] == "combo") {
								
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
			
				}
				
				if ((value['type'] == "text")) {
				
					element=document.createElement('input');
					element.id=value['label'];
					element.name=value['label_enco'];
					element.value=value['data'];
					element.type='text';
					element.size=40;
					
				}
				
				lbl.appendChild(element);

				objTd.appendChild(lbl);

				i++;
			});
			
			//Now we print text areas
			jQuery.each (textarea_elements, function (id, value) {
						
				//Create label
				lbl = document.createElement('label');
				lbl.innerHTML = value['label']+' ';
				
				//Create text area element
				element=document.createElement("textarea");
				element.id=value['label'];
				element.name=value['label_enco'];
				element.value=value['data'];
				element.type='text';
				element.rows='7';
				element.cols='80';
				
				lbl.appendChild(element);

				//Create new row for the table and then add the element
				//Row+1 to insert after last row of elements
				objTr = table.insertRow(row+1);
				objTr.id = 'new_row_'+row;

				objTd = objTr.insertCell();

				objTd.colSpan=3;

				objTd.appendChild(lbl);

				row++;
			});
		}
	});
}

function parent_search_form(filter) {
	
	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/incidents&get_incidents_search=1&ajax=1&"+filter,
		dataType: "html",
		success: function(data){	
			$("#parent_search_window").html (data);
			$("#parent_search_window").show ();

			$("#parent_search_window").dialog ({
					resizable: true,
					draggable: true,
					modal: true,
					overlay: {
						opacity: 0.5,
						background: "black"
					},
					width: 920,
					height: 700
				});
			$("#parent_search_window").dialog('open');
			
			//JS to catch incident search submit request
			$("#search_incident_form").submit(function (){
				var filter = $("#search_incident_form").formSerialize();
				parent_search_form(filter);
				return false;
			});
						
			$("a[id^='page']").click(function(e) {

				e.preventDefault();
				var id = $(this).attr("id");
								
				offset = id.substr(5,id.length);
				
				var filter = $("#search_incident_form").formSerialize();
				filter = filter+"&offset="+offset;
				parent_search_form(filter);
			});
		}
	});
}

function update_parent(id_parent) {

	var str_parent = __('Ticket') +" #"+id_parent;
	
	$("#text-search_parent").attr("value", str_parent); 
	
	$("#hidden-id_parent").attr("value", id_parent);
	
	$("#parent_search_window").dialog('close');
}

function clean_parent_field () {
	$("#text-search_parent").val(__("None"));
	$("#hidden-id_parent").attr("value", "");	
}

function readMoreWU(id_workunit) {
	$('#short_wu_'+id_workunit).hide();
	$('#long_wu_'+id_workunit).show();
}

function reload_sla_slice_graph(id) {

	var period = $('#period').val();
	
	values = Array ();
	values.push ({name: "type",
		value: "sla_slicebar"});
	values.push ({name: "id_incident",
		value: id});
	values.push ({name: "period",
		value: period});
	values.push ({name: "is_ajax",
		value: 1});
	values.push ({name: "width",
		value: 155});		
	values.push ({name: "height",
		value: 15});		

	jQuery.get ('include/functions_graph.php',
		values,
		function (data) {
			$('#slaSlicebarField').html(data);
		},
		'html'
	);
}

function get_group_info (group) {
	
	var result;
	
	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/users&get_group_info=1&ajax=1&group="+group,
		dataType: "json",
		async: false, //Important if not you cannot handle responseText!
		success: function(data){	
			result = data;
		}
	});	

	return result;
}

function inventory_contact_details(phone, mobile, email) {
	console.log("PHONE "+phone+" ;; MOBILE "+mobile+" ;; EMAIL "+email);
	var content = "";

	content = "<table class='advanced_details_table alternate'>";
	content += "<tr>";
	content += "<td>";
	content += __("Phone");
	content += "</td>";
	content += "<td>";
	content += phone;
	content += "</td>";
	content += "</tr>";
	content += "<tr>";
	content += "<td>";
	content += __("Mobile");
	content += "</td>";
	content += "<td>";
	content += mobile;
	content += "</td>";
	content += "</tr>";
        content += "<tr>";
        content += "<td>"; 
        content += __("Email");
        content += "</td>";
        content += "<td>";
        content += email;
        content += "</td>";
        content += "</tr>";
	content += "</table>";

	$("#detail_info").html(content);

	$("#detail_info").dialog();
}

function incident_show_contact_search (filter) {
$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/incidents&get_contact_search=1&ajax=1&"+filter,
		dataType: "html",
		success: function(data){	
			$("#contact_search_window").html (data);
			$("#contact_search_window").show ();

			$("#contact_search_window").dialog ({
					resizable: true,
					draggable: true,
					modal: true,
					overlay: {
						opacity: 0.5,
						background: "black"
					},
					width: 920,
					height: 700
				});
			$("#contact_search_window").dialog('open');
			
			//JS to catch incident search submit request
			$("#contact_search_form").submit(function (){
				var filter = $("#contact_search_form").formSerialize();
				incident_show_contact_search(filter);
				return false;
			});
						
			$("a[id^='page']").click(function(e) {

				e.preventDefault();
				var id = $(this).attr("id");
								
				offset = id.substr(5,id.length);
				
				var filter = $("#contact_search_form").formSerialize();
				filter = filter+"&offset="+offset;
				incident_show_contact_search(filter);
			});
		}
	});	
}

function loadContactEmail(email) {
	var content = $("#text-email_copy").val();

	if (!content) {
		content = email;
	} else {
		content += ", "+email;
	}

	$("#text-email_copy").val(content);

	$("#contact_search_window").dialog("close");
}

function setPriority(id_ticket) {
	
	var id_priority = $('#priority_editor').val();

	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/incidents&set_priority=1&id_ticket="+ id_ticket +"&id_priority=" + id_priority,
		dataType: "text",
		success: function (data) {
			//location.reload();
			var url = $("#hidden-base_url_homedir").val()+"/index.php?sec=incidents&sec2=operation/incidents/incident_dashboard_detail&id="+id_ticket;
			window.location = url;
		}
	});	
}

function setResolution(id_ticket) {
	
	var id_resolution = $('#incident_resolution').val();
	
	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/incidents&set_resolution=1&id_ticket="+ id_ticket +"&id_resolution=" + id_resolution,
		dataType: "text",
		success: function (data) {
			//location.reload();
			var url = $("#hidden-base_url_homedir").val()+"/index.php?sec=incidents&sec2=operation/incidents/incident_dashboard_detail&id="+id_ticket;
			window.location = url;
		}
	});	
}

function setStatus(id_ticket) {
	
	var id_status = $('#incident_status').val();
	
	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/incidents&set_status=1&id_ticket="+ id_ticket +"&id_status=" + id_status,
		dataType: "text",
		success: function (data) {
			//location.reload();
			var url = $("#hidden-base_url_homedir").val()+"/index.php?sec=incidents&sec2=operation/incidents/incident_dashboard_detail&id="+id_ticket;
			window.location = url;
		}
	});	
}

function setOwner(id_ticket) {
	var id_user = $('#text-owner_editor').val();

	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/incidents&set_owner=1&id_ticket="+ id_ticket +"&id_user=" + id_user,
		dataType: "text",
		success: function (data) {
			//location.reload();
			var url = $("#hidden-base_url_homedir").val()+"/index.php?sec=incidents&sec2=operation/incidents/incident_dashboard_detail&id="+id_ticket;
			window.location = url;
		}
	});	
}

function setTicketScore(id_ticket, score) {
	var id_user = $('#text-owner_editor').val();

	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/incidents&set_ticket_score=1&id_ticket="+ id_ticket +"&score=" + score,
		dataType: "text",
		success: function (data) {
			location.reload();
		}
	});	
}

function openUserInfo(id_user) {

	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/incidents&get_user_info=1&id_user="+id_user,
		dataType: "html",
		success: function(data){
			
			$("#user_info_window").html (data);
			$("#user_info_window").show ();
			
			$("#user_info_window").dialog ({
					resizable: true,
					draggable: true,
					modal: true,
					overlay: {
						opacity: 0.5,
						background: "black"
					},
					width: 420,
					height: 400
				});
			$("#user_info_window").dialog('open');

		}
	});
}

function hours_to_dms(type) {
	
	if (type == 'min') {
		var hours = $("#text-min_response").val();
	}
	if (type == 'max') {
		var hours = $("#text-max_response").val();
	}
	if (type == 'inactivity') {
		var hours = $("#text-max_inactivity").val();
	}
	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/incidents&hours_to_dms=1&hours="+ hours,
		dataType: "json",
		success: function (data) {
			if (type == 'min') {
				$('#text-min_response_time').val(data);
			}
			if (type == 'max') {
				$('#text-max_response_time').val(data);
			}
			if (type == 'inactivity') {
				$('#text-max_inactivity_time').val(data);
			}
		}
	});	
}

function delete_massive_tickets () {
	var checked_ids = new Array();
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
		for(var i=0;i<checked_ids.length;i++){
				values = Array ();
				values.push ({name: "page",
							value: "operation/incidents/incident_detail"});
				values.push ({name: "quick_delete",
							value: checked_ids[i]});
				values.push ({name: "massive_number_loop",
						value: i});
				jQuery.get ("ajax.php",
					values,
					function (data, status) {
						
						// We refresh the interface in the last loop
						if(data >= (checked_ids.length - 1)) {
							// This takes the user to the top of the page
							//window.location.href="index.php?sec=incidents&sec2=operation/incidents/incident_search";
							// This takes the user to the same place before reload
							location.reload();
						}
					},
					"json"
				);
			}
	}
}
