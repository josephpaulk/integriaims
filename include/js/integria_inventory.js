
// Show the modal window of inventory search
function show_inventory_search(search_free, id_object_type_search, owner_search, id_manufacturer_search, id_contract_search, search, object_fields_search, last_update_search, offset) {
	
	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/inventories&get_inventory_search=1&search_free="+search_free+"&id_object_type_search="+id_object_type_search+"&owner_search="+owner_search+"&id_manufacturer_search="+id_manufacturer_search+"&id_contract_search="+id_contract_search+"&object_fields_search="+object_fields_search+"&search=1&offset="+offset+"&last_update_search="+last_update_search,
		dataType: "html",
		success: function(data){	
			
			$("#inventory_search_window").html (data);
			$("#inventory_search_window").show ();

			$("#inventory_search_window").dialog ({
					resizable: true,
					draggable: true,
					modal: true,
					overlay: {
						opacity: 0.5,
						background: "black"
					},
					width: 920,
					height: 600
				});
			$("#inventory_search_window").dialog('open');
			
			$("a[id^='page']").click(function(e) {

				e.preventDefault();
				var id = $(this).attr("id");
								
				offset = id.substr(5,id.length);

				show_inventory_search(search_free, id_object_type_search, owner_search, id_manufacturer_search, id_contract_search, search, object_fields_search, last_update_search, offset)
			});
			
			var idUser = "<?php echo $config['id_user'] ?>";
		
			bindAutocomplete ("#text-owner_search", idUser);
		}
	});
}

// Show the modal window of external table
function show_external_query(table_name, id_table, element_name, id_object_type_field) {

	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/inventories&get_external_data=1&table_name="+table_name+"&id_table="+id_table+"&element_name="+element_name+"&id_object_type_field="+id_object_type_field,
		dataType: "html",
		success: function(data){	
			$("#external_table_window").html (data);
			$("#external_table_window").show ();

			$("#external_table_window").dialog ({
					resizable: true,
					draggable: true,
					modal: true,
					overlay: {
						opacity: 0.5,
						background: "black"
					},
					position: "center top",
					width: 620,
					height: 500
				});
			$("#external_table_window").dialog('open');
		}
	});
}

function refresh_external_id(id_object_type_field, id_inventory, id_value) {
	value_id = $('#'+id_value).val();

	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=operation/inventories/inventory_detail&update_external_id=1&id_object_type_field=" + id_object_type_field +"&id_inventory=" + id_inventory+ "&id_value="+value_id, 
		dataType: "html",
		success: function(data){
			show_fields();
		}
	});

}

function enviar(data, element_name, id_object_type_field) {

	$('#'+element_name).val(data);
	
	id_inventory = $('#text-id_object_hidden').val();
	
	if (id_inventory != 0) {
		refresh_external_id(id_object_type_field, id_inventory, element_name);
		//$("#external_table_window").dialog('close');
	}
	
	$("#external_table_window").dialog('close');
} 

function loadInventory(id_inventory) {
	
	$('#hidden-id_parent').val(id_inventory);
	
	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/inventories&get_inventory_name=1&id_inventory="+ id_inventory,
		dataType: "text",
		success: function (name) {
			$('#text-parent_name').val(name);
		}
	});	

	$("#inventory_search_window").dialog('close');
}

function show_fields() {

	only_read = 0;
	
	if ($("#text-show_object_hidden").val() == 1) { //users with only read permissions
		only_read = 1;
		id_object_type = $("#text-id_object_type_hidden").val();
	} else { //users with write permissions
		id_object_type = $("#id_object_type").val();
	}

	id_inventory = $("#text-id_object_hidden").val();

	$('#table_fields').remove();

	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=operation/inventories/inventory_detail&show_type_fields=1&id_object_type=" + id_object_type +"&id_inventory=" +id_inventory,
		dataType: "json",
		success: function(data){
			
			fi=document.getElementById('table1-4-0');
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
					
					if (only_read) {
						element.disabled = true;
					}
					
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
				
				if ((value['type'] == "text") || (value['type'] == "numeric") || (value['type'] == "external")) {
				
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
					element.id=value['id'];
					element.className="object_type_field";
					//element.id=i;
					//element.id=value['label_enco'];
					element.name=value['label_enco'];
					element.value=value['data'];
					if ((value['type'] == 'text') || (value['type'] == 'external')) {
						element.type='text';

					} else if (value['type'] == 'numeric') {
						element.type='number';
					} 
					
					if (only_read) {
						element.disabled = true;
					}
					
					element.size=40;
					lbl.appendChild(element);
				
					if (value['type'] == 'external') {
						
						id_object_type_field = value['id'];
						
						a = document.createElement('a');
						a.title = __("Show table");
						table_name = value['external_table_name'];
						id_table = value['external_reference_field'];
						//element_name = value['label_enco'];
						//a.href = 'javascript: show_external_query("'+table_name+'","'+id_table+'","'+element_name+'")';
						a.href = 'javascript: show_external_query("'+table_name+'","'+id_table+'","'+i+'", "'+id_object_type_field+'")';
						
						img=document.createElement('img');
						img.id='img_show_external_table';
						img.height='16';
						img.width='16';
						img.src='images/lupa.gif';
						
						a.appendChild(img);
						lbl.appendChild(a);
						
						id_inventory = $('#text-id_object_hidden').val();
					}
					
					i++;
					
					if (value['type'] == 'external') {
						if (value['data'] != '') {
							
							external_table_name = value['external_table_name'];
							external_reference_field = value['external_reference_field'];
							id_external_table = value['data'];
							
							$.ajax({
								type: "POST",
								url: "ajax.php",
								data: "page=operation/inventories/inventory_detail&show_external_data=1&external_table_name=" + external_table_name +"&external_reference_field=" + external_reference_field +'&id_external_table='+id_external_table, 
								dataType: "json",
								success: function(data_external){
									resto_ext = 0;
									
									jQuery.each (data_external, function (id_ext, value_ext) {
										resto_ext = i % 2;
										
										if (resto_ext == 0) {
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
										lbl.innerHTML = value_ext['label']+' ';
										objTr.appendChild(objTd1);
										objTd1.appendChild(lbl);
										
										txt = document.createElement('br');
										lbl.appendChild(txt);

										
										element=document.createElement('input');
										element.id=value_ext['label'];
										element.name=value_ext['label_enco'];
										element.value=value_ext['data'];
										element.type='text';
										element.readOnly=true
										
										element.size=40;
										lbl.appendChild(element);
										i++;
									});
								}
							});
						}
					}
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
					
					if (only_read) {
						element.disabled = true;
					}
					
					lbl.appendChild(element);
					i++;
				}
			});
		}
	});
}

function loadParams() {
	
	search_free = $('#text-search_free').val();
	id_object_type_search = $('#id_object_type_search').val();
	owner_search = $('#text-owner_search').val();
	id_manufacturer_search = $('#id_manufacturer_search').val();
	id_contract_search = $('#id_contract_search').val();
	
	if ($("#checkbox-last_update_search").is(":checked")) {
		last_update_search = 1;
	} else {
		last_update_search = 0;
	}

	offset = 0;
	search = 1;
	
	object_fields_search = $("select[name='object_fields_search[]']").val();
		
	show_inventory_search(search_free, id_object_type_search, owner_search, id_manufacturer_search, id_contract_search, search, object_fields_search, last_update_search, offset);
}

//Show custom fields
function show_type_fields() {

	$("select[name='object_fields_search[]']").empty();
	
	id_object_type = $("#id_object_type_search").val();

	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/inventories&select_fields=1&id_object_type=" + id_object_type,
		dataType: "json",
		success: function(data){
				$("#object_fields_search").empty();
				jQuery.each (data, function (id, value) {
					field = value;
					$("select[name='object_fields_search[]']").append($("<option>").val(id).html(field));
				});	
			}
	});
}


// Show the modal window of company associated
function show_company_associated() {

	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/inventories&get_company_associated=1",
		dataType: "html",
		success: function(data){	
			$("#company_search_modal").html (data);
			$("#company_search_modal").show ();

			$("#company_search_modal").dialog ({
					resizable: true,
					draggable: true,
					modal: true,
					overlay: {
						opacity: 0.5,
						background: "black"
					},
					width: 520,
					height: 350
				});
			$("#company_search_modal").dialog('open');
		}
	});
}

function cleanParentInventory() {
	$("#text-parent_name").val(__("None"));
	$("#hidden-id_parent").attr("value", "");	
}

function loadCompany() {

	id_company = $('#id_company').val();
	$('#inventory_status_form').append ($('<input type="hidden" value="'+id_company+'" class="selected-companies" name="companies[]" />'));

	$("#company_search_modal").dialog('close');

	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=operation/inventories/inventory_detail&get_company_name=1&id_company="+ id_company,
		dataType: "json",
		success: function (name) {
			$('#inventory_companies').append($('<option></option>').html(name).attr("value", id_company));
		}
	});
}

function removeCompany() {

	s= $("#inventory_companies").prop ("selectedIndex");

	selected_id = $("#inventory_companies").children (":eq("+s+")").attr ("value");

	$("#inventory_companies").children (":eq("+s+")").remove ();
	$(".selected-companies").each (function () {
		if (this.value == selected_id)
			$(this).remove();
	});
}

// Show the modal window of company associated
function show_user_associated() {

	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/inventories&get_user_associated=1",
		dataType: "html",
		success: function(data){	
			$("#user_search_modal").html (data);
			$("#user_search_modal").show ();

			$("#user_search_modal").dialog ({
					resizable: true,
					draggable: true,
					modal: true,
					overlay: {
						opacity: 0.5,
						background: "black"
					},
					width: 520,
					height: 350
				});
			$("#user_search_modal").dialog('open');
			
			var idUser = "<?php echo $config['id_user'] ?>";
		
			bindAutocomplete ("#text-inventory_user", idUser);
		}
	});
}

function loadUser() {

	id_user = $('#text-inventory_user').val();

	$('#inventory_status_form').append ($('<input type="hidden" value="'+id_user+'" class="selected-users" name="users[]" />'));

	$("#user_search_modal").dialog('close');

	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=operation/inventories/inventory_detail&get_user_name=1&id_user="+ id_user,
		dataType: "json",
		success: function (name) {
			$('#inventory_users').append($('<option></option>').html(name).attr("value", id_user));
		}
	});
}

function removeUser() {

	s= $("#inventory_users").prop ("selectedIndex");

	selected_id = $("#inventory_users").children (":eq("+s+")").attr ("value");

	$("#inventory_users").children (":eq("+s+")").remove ();
	$(".selected-users").each (function () {
		if (this.value == selected_id)
			$(this).remove();
	});
}

// Show the modal window of inventory search in incident detail
function incident_show_inventory_search(search_free, id_object_type_search, owner_search, id_manufacturer_search, id_contract_search, search, object_fields_search) {

	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/inventories&get_inventory_search=1&search_free="+search_free+"&id_object_type_search="+id_object_type_search+"&owner_search="+owner_search+"&id_manufacturer_search="+id_manufacturer_search+"&id_contract_search="+id_contract_search+"&object_fields_search="+object_fields_search+"&search=1",
		dataType: "html",
		success: function(data){	
			$("#inventory_search_modal").html (data);
			$("#inventory_search_modal").show ();

			$("#inventory_search_modal").dialog ({
					resizable: true,
					draggable: true,
					modal: true,
					overlay: {
						opacity: 0.5,
						background: "black"
					},
					width: 920,
					height: 850
				});
			$("#inventory_search_modal").dialog('open');
			
			var idUser = "<?php echo $config['id_user'] ?>";
		
			bindAutocomplete ("#text-owner_search", idUser);
		}
	});
}

/**
 * loadSubTree asincronous load ajax the agents or modules (pass type, id to search and binary structure of branch),
 * change the [+] or [-] image (with same more or less div id) of tree and anime (for show or hide)
 * the div with id "div[id_father]_[type]_[div_id]"
 *
 * type string use in js and ajax php
 * div_id int use in js and ajax php
 * less_branchs int use in ajax php as binary structure 0b00, 0b01, 0b10 and 0b11
 * id_father int use in js and ajax php, its useful when you have a two subtrees with same agent for diferent each one
 */
function loadSubTree(type, div_id, less_branchs, id_father, sql_search) {

	hiddenDiv = $('#tree_div'+id_father+'_'+type+'_'+div_id).attr('hiddenDiv');
	loadDiv = $('#tree_div'+id_father+'_'+type+'_'+div_id).attr('loadDiv');
	pos = parseInt($('#tree_image'+id_father+'_'+type+'_'+div_id).attr('pos_tree'));

	//If has yet ajax request running
	if (loadDiv == 2)
		return;
	
	if (loadDiv == 0) {

		//Put an spinner to simulate loading process

		$('#tree_div'+id_father+'_'+type+'_'+div_id).html("<img style='padding-top:10px;padding-bottom:10px;padding-left:20px;' src=images/spinner.gif>");
		$('#tree_div'+id_father+'_'+type+'_'+div_id).show('normal');
		$('#tree_div'+id_father+'_'+type+'_'+div_id).attr('loadDiv', 2);
	
		$.ajax({
			type: "POST",
			url: "ajax.php",
			data: "page=operation/inventories/inventory_search&print_subtree=1&type=" + 
				type + "&id_item=" + div_id + "&less_branchs=" + less_branchs+ "&sql_search=" + sql_search,
			success: function(msg){
				if (msg.length != 0) {
					
					$('#tree_div'+id_father+'_'+type+'_'+div_id).hide();
					$('#tree_div'+id_father+'_'+type+'_'+div_id).html(msg);
					$('#tree_div'+id_father+'_'+type+'_'+div_id).show('normal');
					
					//change image of tree [+] to [-]
					
					var icon_path = 'images/tree';
					
					switch (pos) {
						case 0:
							$('#tree_image'+id_father+'_'+type+'_'+div_id).attr('src',icon_path+'/first_expanded.png');
							break;
						case 1:
							$('#tree_image'+id_father+'_'+type+'_'+div_id).attr('src',icon_path+'/one_expanded.png');
							break;
						case 2:
							$('#tree_image'+id_father+'_'+type+'_'+div_id).attr('src',icon_path+'/expanded.png');
							break;
						case 3:
							$('#tree_image'+id_father+'_'+type+'_'+div_id).attr('src',icon_path+'/last_expanded.png');
							break;
					}

					$('#tree_div'+id_father+'_'+type+'_'+div_id).attr('hiddendiv',0);
					$('#tree_div'+id_father+'_'+type+'_'+div_id).attr('loadDiv', 1);
				}
				
			}
		});
	}
	else {

		var icon_path = 'images/tree';
		
		if (hiddenDiv == 0) {

			$('#tree_div'+id_father+'_'+type+'_'+div_id).hide('normal');
			$('#tree_div'+id_father+'_'+type+'_'+div_id).attr('hiddenDiv',1);
			
			//change image of tree [-] to [+]
			switch (pos) {
				case 0:
					$('#tree_image'+id_father+'_'+type+'_'+div_id).attr('src',icon_path+'/first_closed.png');
					break;
				case 1:
					$('#tree_image'+id_father+'_'+type+'_'+div_id).attr('src',icon_path+'/one_closed.png');
					break;
				case 2:
					$('#tree_image'+id_father+'_'+type+'_'+div_id).attr('src',icon_path+'/closed.png');
					break;
				case 3:
					$('#tree_image'+id_father+'_'+type+'_'+div_id).attr('src',icon_path+'/last_closed.png');
					break;
			}
		}
		else {
			//change image of tree [+] to [-]
			switch (pos) {
				case 0:
					$('#tree_image'+id_father+'_'+type+'_'+div_id).attr('src',icon_path+'/first_expanded.png');
					break;
				case 1:
					$('#tree_image'+id_father+'_'+type+'_'+div_id).attr('src',icon_path+'/one_expanded.png');
					break;
				case 2:
					$('#tree_image'+id_father+'_'+type+'_'+div_id).attr('src',icon_path+'/expanded.png');
					break;
				case 3:
					$('#tree_image'+id_father+'_'+type+'_'+div_id).attr('src',icon_path+'/last_expanded.png');
					break;
			}

			$('#tree_div'+id_father+'_'+type+'_'+div_id).show('normal');
			$('#tree_div'+id_father+'_'+type+'_'+div_id).attr('hiddenDiv',0);
		}
	}
}

function loadTable(type, div_id, less_branchs, id_father, sql_search) {
	id_item = div_id;

	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/inventories&id_item=" + id_item + "&printTable=1&type="+ type+"&id_father=" + id_father +"&sql_search="+sql_search,
		success: function(data){
			$('#cont').html(data);
		}
	});
	loadSubTree(type, div_id, less_branchs, id_father, sql_search);		
}

function show_issue_date() {
	
	if ($("#inventory_status option:selected").val() === 'issued') {
		$("#label-text-issue_date").css("display", "block");
		$("#text-issue_date").css("display", "block");
	} else {
		$("#label-text-issue_date").css("display", "none");
		$("#text-issue_date").css("display", "none");
		
	}
}
