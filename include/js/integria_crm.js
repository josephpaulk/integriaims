
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
