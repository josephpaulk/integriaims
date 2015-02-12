
// Show the modal window of inventory search
function show_company_search(search_text, search_role, search_manager, search_parent, search_date_begin, search_date_end, search, offset) {

	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/crm&get_company_search=1&search_text="+search_text+"&search_role="+search_role+"&search_manager="+search_manager+"&search_date_begin="+search_date_begin+"&search_date_end="+search_date_end+"&offset="+offset+"&search=1&search_parent="+search_parent,
		dataType: "html",
		success: function(data){	
			
			$("#company_search_window").html (data);
			$("#company_search_window").show ();

			$("#company_search_window").dialog ({
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
			$("#company_search_window").dialog('open');
			
			$("a[id^='page']").click(function(e) {

				e.preventDefault();
				var id = $(this).attr("id");
								
				offset = id.substr(5,id.length);
				
				show_company_search(search_text, search_role, search_manager, search_parent, search_date_begin, search_date_end, search, offset)
			});
			
			var idUser = "<?php echo $config['id_user'] ?>";
		
			bindAutocomplete ("#text-user", idUser);
			
			$("#text-search_date_begin").datepicker ({
				beforeShow: function () {
					return {
						maxDate: $("#text-search_date_begin").datepicker ("getDate")
					};
				}
			});
			
			$("#text-search_date_end").datepicker ({
				beforeShow: function () {
					return {
						maxDate: $("#text-search_date_end").datepicker ("getDate")
					};
				}
			});
		}
	});
}

function loadParamsCompany() {

	search_text = $('#text-search_text').val();
	search_role = $('#search_role').val();
	search_manager = $('#text-search_user').val();
	search_parent = $('#search_parent').val();
	search_date_begin = $('#text-search_date_begin').val();
	search_date_end = $('#text-search_date_end').val();
	search = 1;
		
	show_company_search(search_text, search_role, search_manager, search_parent, search_date_begin, search_date_end, search);
}

function loadCompany(id_company) {
	
	$('#hidden-id_parent').val(id_company);
	
	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/crm&get_company_name=1&id_company="+ id_company,
		dataType: "text",
		success: function (name) {
			$('#text-parent_name').val(name);
		}
	});	

	$("#company_search_window").dialog('close');
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

function delete_item (mode, id, id_company, offset, search_params) {

	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/crm&delete_item=1&mode="+ mode+"&id="+id,
		dataType: "html",
		async:false,
		success: function (data) {

			switch (mode) {
				case 'delete_company':
					window.location.assign("index.php?sec=customers&sec2=operation/companies/company_detail&id=0&offset="+offset+"&search_params="+search_params+"message="+data);
				break;
				case 'delete_contract':
					window.location.assign("index.php?sec=customers&sec2=operation/contracts/contract_detail&search_params="+search_params+"&message="+data);
				break;
				case 'delete_invoice':
					window.location.assign("index.php?sec=customers&sec2=operation/invoices/invoice_detail&offset="+offset+"&search_params="+search_params+"&message="+data);
				break;
				case 'delete_company_invoice':
					window.location.assign("index.php?sec=customers&sec2=operation/companies/company_detail&id="+id_company+"&op=invoices&offset="+offset+"&message="+data);
				break;
				case 'delete_lead':
					window.location.assign("index.php?sec=customers&sec2=operation/leads/lead&tab=search&offset="+offset+"&message="+data);
				break;
			}
		}
	});
}

function show_validation_delete (mode, id, id_company, offset, search_params) {

	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/crm&get_delete_validation=1",
		dataType: "html",
		success: function(data){
			$("#item_delete_window").html (data);
			$("#item_delete_window").show ();

			$("#item_delete_window").dialog ({
					resizable: true,
					draggable: true,
					modal: true,
					overlay: {
						opacity: 0.5,
						background: "black"
					},
					width: 210,
					height: 180
				});
			$("#item_delete_window").dialog('open');
			$("#validation_delete_form").submit(function (e){
				e.preventDefault();
				delete_item (mode, id, id_company, offset, search_params);
			});
		}
	});
}
