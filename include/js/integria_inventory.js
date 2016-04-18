
// Show the modal window of inventory search
function show_inventory_search(search_free, id_object_type_search, owner_search, id_manufacturer_search, id_contract_search, search, object_fields_search, last_update_search, offset, inventory_status_search, id_company_search,associated_user_search) {

	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/inventories&get_inventory_search=1&search_free="+search_free+"&id_object_type_search="+id_object_type_search+"&owner_search="+owner_search+"&id_manufacturer_search="+id_manufacturer_search+"&id_contract_search="+id_contract_search+"&object_fields_search="+object_fields_search+"&search=1&offset="+offset+"&last_update_search="+last_update_search+"&inventory_status_search="+inventory_status_search+"&id_company="+id_company_search+"&associated_user_search="+associated_user_search,
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
			
			$("a[class^='page']").click(function(e) {

				e.preventDefault();

				var id = $(this).attr("class");
								
				offset = id.substr(5,id.length);

				show_inventory_search(search_free, id_object_type_search, owner_search, id_manufacturer_search, id_contract_search, search, object_fields_search, last_update_search, offset, inventory_status_search, id_company_search, associated_user_search);
			});
			
			var idUser = "<?php echo $config['id_user'] ?>";

			bindAutocomplete ("#text-owner_search", idUser);
			bindAutocomplete ("#text-associated_user_search", idUser);

		}
	});
}

// Show the modal window of external table
function show_external_query(table_name, id_table, element_name, id_object_type_field, label_parent_enco, id_parent_table, external_label) {

	if (label_parent_enco != "") {
		id_parent_value = $("input[name='"+label_parent_enco+"']").val();
	} else {
		id_parent_value = 0;
	}
			
	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/inventories&get_external_data=1&table_name="+table_name+"&id_table="+id_table+"&element_name="+element_name+"&id_object_type_field="+id_object_type_field+"&id_parent_value="+id_parent_value+"&id_parent_table="+id_parent_table+"&external_label="+external_label,
		//~ data: "page=include/ajax/inventories&get_external_data=1&table_name="+table_name+"&id_table="+id_table+"&element_name="+element_name+"&id_object_type_field="+id_object_type_field+"&id_parent_value="+id_parent_value+"&id_parent_table="+id_parent_table,
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

function refresh_external_id(id_object_type_field, id_inventory, id_value, data_name) {

	if (id_inventory != 0) {
		$.ajax({
			type: "POST",
			url: "ajax.php",
			data: "page=operation/inventories/inventory_detail&update_external_id=1&id_object_type_field=" + id_object_type_field +"&id_inventory=" + id_inventory+ "&id_value="+data_name, 
			dataType: "html",
			success: function(data){
				show_fields();
			}
		});
	} else {
		$("#"+id_object_type_field).val(data_name);
	}

}

function enviar(data, element_name, id_object_type_field, data_name) {
	$('#'+element_name).val(data);
	
	id_inventory = $('#text-id_object_hidden').val();
	
	refresh_external_id(id_object_type_field, id_inventory, data, data_name);
	
	$("#external_table_window").dialog('close');
} 

//function to pass the clicked parameter in the modal to the input
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
						
						element.readOnly = true;
						id_object_type_field = value['id'];
						
						a = document.createElement('a');
						a.title = __("Show table");
						a2 = document.createElement('a');
						a2.title = __("Delete");
						table_name = value['external_table_name'];
						id_table = value['external_reference_field'];
						parent_name_enco = value['label_parent_enco'];
						external_label = value['external_label'];
						if (value['id_parent_table'] == false) {
							id_parent_table = "";
						} else {
							id_parent_table = value['id_parent_table'];
						}
					
						//~ a.href = 'javascript: show_external_query("'+table_name+'","'+id_table+'","'+i+'", "'+id_object_type_field+'",'+'"'+parent_name_enco+'",'+'"'+id_parent_table+'")';
						a.href = 'javascript: show_external_query("'+table_name+'","'+id_table+'","'+i+'", "'+id_object_type_field+'",'+'"'+parent_name_enco+'",'+'"'+id_parent_table+'","'+external_label+'")';
						a2.href = 'javascript: removeExternal(id="'+element.id+'")';
						
						img=document.createElement('img');
						img.id='img_show_external_table';
						img.height='16';
						img.width='16';
						img.src='images/lupa.gif';
						
						img2=document.createElement('img');
						img2.id='img_delete_external_table';
						img2.src='images/cross.png';
						
						a.appendChild(img);
						lbl.appendChild(a);
						
						a2.appendChild(img2);
						lbl.appendChild(a2);
						
						id_inventory = $('#text-id_object_hidden').val(value['data']);
					}
					
					i++;
					/*
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
					*/	
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

//Send modal parameters that need inventory
function loadParams() {
	
	search_free = $('#text-search_free').val();
	id_object_type_search = $('#id_object_type_search').val();
	owner_search = $('#text-owner_search').val();
	id_manufacturer_search = $('#id_manufacturer_search').val();
	id_contract_search = $('#id_contract_search').val();
	inventory_status_search = $('#inventory_status_search').val();
	id_company_search = $('#id_company').val();
	associated_user_search = $('#text-associated_user_search').val();

	if ($("#checkbox-last_update_search").is(":checked")) {
		last_update_search = 1;
	} else {
		last_update_search = 0;
	}
	
	offset = 0;
	search = 1;
	
	object_fields_search = $("select[name='object_fields_search[]']").val();
	
	show_inventory_search(search_free, id_object_type_search, owner_search, id_manufacturer_search, id_contract_search, search, object_fields_search, last_update_search, offset, inventory_status_search, id_company_search, associated_user_search);

}

//Show custom fields for inventory modal
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

//This function changes and orders both custom fields as common and runs the checkbox checking if they are on
function enable_table_order_check(pure) {
	$("select[name='object_fields_search[]']").empty();
	
	id_object_type = $("#id_object_type_search").val();
	
	//variable result contiene los campos del tipo de objeto
	result = $('#hidden-object_fields').val();
	if(result == "{}" || result == ""){
		result = "";
	} else {
		result = jQuery.parseJSON(result.replace(/&quot;/g, '"'));
	}

	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/inventories&select_fields=1&id_object_type=" + id_object_type,
		dataType: "json",
		success: function(data){
			$("#object_fields_search_check").empty();
			i=1;
			jQuery.each (data, function (label, field) {

				var list = $('<ul></ul>');
				var list_file = $('<li></li>');
				var list_file_label = $('<label></label>'). attr('for','check' + i);
				var list_file_label_input = $('<input/>'). attr('id','check' + i).attr('type', 'checkbox').attr('name', field);
				$("#object_fields_search_check").append(list.append(list_file.append(list_file_label.html(label).append(list_file_label_input))));
				i++;
				//if not selected field selected by default the first
				if(result == ""){
					$("#object_fields_search_check input[type=checkbox]").on("change", function(e) {
						var id = $(this).attr("id");
						id = id.replace('check','');
						var col = $("#inventory_list_table table tr th:nth-child("+id+"), #inventory_list_table table tr td:nth-child("+id+")");
						$(this).is(":checked") ? col.show() : col.hide();
						change_object_field();
					}).prop("checked", true).change();
				} else {
					//if you select any field checkbox selects all off
					$("#object_fields_search_check input[type=checkbox]").on("change", function(e) {
						var id = $(this).attr("id");
						id = id.replace('check','');
						var col = $("#inventory_list_table table tr th:nth-child("+id+"), #inventory_list_table table tr td:nth-child("+id+")");
						$(this).is(":checked") ? col.show() : col.hide();
						change_object_field();
					}).change();
				}
			});
			//checks that should be on
			if(result){
				jQuery.each(result, function (k, v) {
					$("#object_fields_search_check input[name='"+k+"']").on("change", function(e) {
					}).prop("checked", true).change();
				});
			}
			enable_table_ajax_headers(pure);
		}
	});
}

//add id table is not used is implemented by whether to order
function add_id_th_table(label, field){
	$("#inventory_list_table table tr th").each(function(){
		nameth = $(this).text();
		if(nameth == label){
			$(this).attr('id', field);
		}
	});
}


//function to sort x field
function enable_table_ajax_headers(pure){
	$('#inventory_list th').each(function (column) {
		type_column = $('#hidden-sort_mode').val();
		num_column = $('#hidden-sort_field').val();
		if (column == 0 || column == 1 || column == 8 || column == 7) {
			if(!pure){
				if (column == num_column && type_column == 'asc'){
					$(this).addClass('sortable sorted-asc');
					$(this).attr("onclick","ajax_headers_sort(this);");

				} else if (column == num_column && type_column == 'desc'){
					$(this).addClass('sortable sorted-desc');
					$(this).attr("onclick","ajax_headers_sort(this);");
				} else {
					$(this).addClass('sortable');
					$(this).attr("onclick","ajax_headers_sort(this);");
				}
			}
		}

		// show or hide based on checkbox

		// Avoid to erase actions & select boxes
		if ($("#check"+(column+1)).val() == null){
			return false;
		}

		asociated_checkbox = $("#check"+(column+1)).is(":checked");
		
		col = $("#tmp_data table tr th:nth-child("+(column+1)+"), #tmp_data table tr td:nth-child("+(column+1)+")");
		col.hide();
		if (!asociated_checkbox){
			col.hide();
		}
		else {
			col.show();
		}

    });

    $('#inventory_list_table').html($('#tmp_data').html());
    $('#tmp_data').html(null);
}

//changes the values ​​of the inputs to the ordering is maintained for the search
function ajax_headers_sort(field){
    $(field).removeClass('sortable');
    var field_value = field.className.split(" ")[1].replace('c','');
    if ($('#hidden-sort_mode').val() == 'asc'){
    	$('#hidden-sort_mode').val('desc');
    }
    else {
    	$('#hidden-sort_mode').val('asc');
    }
    console.log(field_value);
    $('#hidden-sort_field').val(field_value);
    tree_search_submit();
}

//changes to tree view
function change_view_tree(){
	$('#hidden-mode').val('tree');
	tree_search_submit();
}

//switch to list view
function change_view_list(){
	$('#hidden-mode').val('list');
	tree_search_submit();
}

//Pure view changes	
function change_view_pure() {
	$('#header').hide();
	$('#sidebar').hide();
	$('.inventory_type_object_container').hide();
	$('.inventory_column_container').hide();
	$('.inventory_form_container').hide();
	$('.view_normal_button').css('display', 'none');
	$('.view_pure_button').css('display', 'inline');
	pure = 1;
	tree_search_submit(pure);
}

//Return to pure a list_view
function change_return_view() {
	$('#sidebar').show();
	$('#header').show();
	$('.inventory_type_object_container').show();
	$('.inventory_column_container').show();
	$('.inventory_form_container').show();
	$('.view_normal_button').css('display', 'inline');
	$('.view_pure_button').css('display', 'none');
	pure = 0;
	tree_search_submit(pure);
}

//funcion para cambiar el tipo de objeto
function change_object_type(){
	id_object_type = $("#id_object_type_search_check").val();
	$("#id_object_type_search").val(id_object_type);
	//val offset 0
	$('#hidden-offset').val(0);
	tree_search_submit();
}

//send checkbox on
function change_object_field(){
	$("#hidden-object_fields").val(JSON.stringify($('#form_object_field').serializeObject()));
}

//form search inventory
function tree_search_submit(pure){
	if(pure==1){
		pure = 1;
	} else {
		pure = 0;
	}
	var id_object = $('#tree_search').serialize();
	$("#inventory_list_table").html("<img id='inventory_loading' src='images/carga.gif' />");
	$.ajax({	
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/inventories&pure="+ pure +"&change_table=1&" + id_object,
		dataType: "html",
		success: function(data){
			mode = $('#hidden-mode').val();
			if(mode = 'list'){
				$("#tmp_data").empty();
				$("#tmp_data").html(data).hide();
				
				//sort the table
				enable_table_order_check(pure);

				//JS for massive operations
				$("#checkbox-inventorycb-all").change(function() {
					$(".cb_inventory").prop('checked', $("#checkbox-inventorycb-all").prop('checked'));
				});

				$(".cb_inventory").click(function(event) {
					event.stopPropagation();
				});

				/*
					$("#text-search_free").keyup(function() {
		    			tree_search_submit();
					});
				*/
				
			} else {
				$('#tmp_data').empty();
				$('#tmp_data').html (data);
				$('#inventory_tree_table').show();
			}
		}
	});
}

//serialize form in Json
$.fn.serializeObject = function(){
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

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
	
	$('input[name^="companies"][value="'+selected_id+'"]').remove();
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
					width: 300,
					height: 150
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

	$('input[name^="users"][value="'+selected_id+'"]').remove();
}

function removeExternal(id) {
	$("#"+id).attr("value", "");	
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
function loadSubTree(type, div_id, less_branchs, id_father, sql_search, ref_tree, end, last_update) {
	
	hiddenDiv = $('#tree_div'+ref_tree+'_'+type+'_'+div_id).attr('hiddenDiv');
	loadDiv = $('#tree_div'+ref_tree+'_'+type+'_'+div_id).attr('loadDiv');
	pos = parseInt($('#tree_image'+ref_tree+'_'+type+'_'+div_id).attr('pos_tree'));

	//If has yet ajax request running
	if (loadDiv == 2)
		return;
	
	if (loadDiv == 0) {

		//Put an spinner to simulate loading process

		$('#tree_div'+ref_tree+'_'+type+'_'+div_id).html("<img style='padding-top:10px;padding-bottom:10px;padding-left:20px;' src=images/spinner.gif>");
		$('#tree_div'+ref_tree+'_'+type+'_'+div_id).slideDown();
		$('#tree_div'+ref_tree+'_'+type+'_'+div_id).attr('loadDiv', 2);

		$.ajax({
			type: "POST",
			url: "ajax.php",
			data: "page=operation/inventories/inventory_search&print_subtree=1&type=" + 
				type + "&id_item=" + div_id + "&less_branchs=" + less_branchs+ "&sql_search=" + sql_search + "&id_father=" + id_father + "&ref_tree=" + ref_tree + "&end=" + end + "&last_update=" + last_update,
			success: function(msg){
				if (msg.length != 0) {
					
					$('#tree_div'+ref_tree+'_'+type+'_'+div_id).hide();
					$('#tree_div'+ref_tree+'_'+type+'_'+div_id).html(msg);
					$('#tree_div'+ref_tree+'_'+type+'_'+div_id).slideDown();
					
					//change image of tree [+] to [-]
					
					var icon_path = 'images/tree';
					
					switch (pos) {
						case 0:
							$('#tree_image'+ref_tree+'_'+type+'_'+div_id).attr('src',icon_path+'/first_expanded.png');
							break;
						case 1:
							$('#tree_image'+ref_tree+'_'+type+'_'+div_id).attr('src',icon_path+'/one_expanded.png');
							break;
						case 2:
							$('#tree_image'+ref_tree+'_'+type+'_'+div_id).attr('src',icon_path+'/expanded.png');
							break;
						case 3:
							$('#tree_image'+ref_tree+'_'+type+'_'+div_id).attr('src',icon_path+'/last_expanded.png');
							break;
					}

					$('#tree_div'+ref_tree+'_'+type+'_'+div_id).attr('hiddendiv',0);
					$('#tree_div'+ref_tree+'_'+type+'_'+div_id).attr('loadDiv', 1);
				}
				
			}
		});
	}
	else {

		var icon_path = 'images/tree';
		
		if (hiddenDiv == 0) {

			$('#tree_div'+ref_tree+'_'+type+'_'+div_id).slideUp();
			$('#tree_div'+ref_tree+'_'+type+'_'+div_id).attr('hiddenDiv',1);
			
			//change image of tree [-] to [+]
			switch (pos) {
				case 0:
					$('#tree_image'+ref_tree+'_'+type+'_'+div_id).attr('src',icon_path+'/first_closed.png');
					break;
				case 1:
					$('#tree_image'+ref_tree+'_'+type+'_'+div_id).attr('src',icon_path+'/one_closed.png');
					break;
				case 2:
					$('#tree_image'+ref_tree+'_'+type+'_'+div_id).attr('src',icon_path+'/closed.png');
					break;
				case 3:
					$('#tree_image'+ref_tree+'_'+type+'_'+div_id).attr('src',icon_path+'/last_closed.png');
					break;
			}
		}
		else {
			//change image of tree [+] to [-]
			switch (pos) {
				case 0:
					$('#tree_image'+ref_tree+'_'+type+'_'+div_id).attr('src',icon_path+'/first_expanded.png');
					break;
				case 1:
					$('#tree_image'+ref_tree+'_'+type+'_'+div_id).attr('src',icon_path+'/one_expanded.png');
					break;
				case 2:
					$('#tree_image'+ref_tree+'_'+type+'_'+div_id).attr('src',icon_path+'/expanded.png');
					break;
				case 3:
					$('#tree_image'+ref_tree+'_'+type+'_'+div_id).attr('src',icon_path+'/last_expanded.png');
					break;
			}

			$('#tree_div'+ref_tree+'_'+type+'_'+div_id).slideDown();
			$('#tree_div'+ref_tree+'_'+type+'_'+div_id).attr('hiddenDiv',0);
		}
	}
}

function loadTable(type, div_id, less_branchs, id_father, sql_search, ref_tree, end, last_update) {
	id_item = div_id;
	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: {
			'page': 'include/ajax/inventories',
			'get_item_info': 1,
			'id_item': id_item,
			'id_father': id_father
		},
		success: function (data) {
			data = JSON.parse(data);
			
			var name = data.name || 'N/A';
			var items = data.data;
			
			var editImg = '<a href="index.php?sec=inventory&sec2=operation/inventories/inventory_detail&id=' + id_item + '">'
							+ '<img class="inventory_table_edit" src="images/application_edit_white.png">'
						+ '</a>';
			
			var rows = '';
			items.forEach(function (item) {
				rows += '<tr><td><strong>' + item.label + '</strong></td>' + '<td>' + item.data + '</td></tr>';
			});
			var table = '<table class="clean">' + rows + '</table>';
			
			// Assing the new variable to the window scope
			if (typeof window.inventoryInfoBox === 'undefined')
				window.inventoryInfoBox = $.fixedBottomBox({ width: 320 });
			var width = $(table).width();
			window.inventoryInfoBox
				.render(name + ' ' + editImg, table)
				.open();
		}
	});

	loadSubTree(type, div_id, less_branchs, id_father, sql_search, ref_tree, end, last_update);		
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

//add
function delete_massive_inventory () {
	var checked_ids = new Array();
	$(".cb_inventory").each(function() {
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
							value: "operation/inventories/inventory"});
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
							//window.location.href="index.php?sec=inventory&sec2=operation/inventories/inventory";
							// This takes the user to the same place before reload
							location.reload();
						}
					},
					"json"
				);
			}
	}
}


/**
 * Send formulary through AJAX instead of submit
 */

function send_form_ajax(id_form, id_response, url, operation){
	$.ajax({
		type: "POST",
		url: "ajax.php?" + $(id_form).serialize()+"&"+operation,
		data: {
			'page': url,
		},
		dataType: "json",
		statusCode: {
			200: function(data){
				$("#"+id_response.id).html(data.responseText);
			}
		}
	});
}
