
function configure_user_search_form () {
	$("#user_search_form").submit (function () {
		$("#user_search_result_table tbody").fadeOut ('normal', function (){
			values = get_form_input_values ("user_search_form");
			values.page = "operation/users/user_search";
			jQuery.post ("ajax.php",
				values,
				function (data, status) {
					$("#user_search_result_table").removeClass ("hide");
					$("#user_search_result_table tbody").empty ().append (data);
					refresh_table ("user_search_result_table");
					$("#user_search_result_table").trigger ("update")
						.tablesorterPager ({container: $("#users-pager"), size: 3});
					$("#user_search_result_table tbody tr").click (function () {
						user_id = this.id.slice (7);
						$("#dialog").close ();
					});
					$("#user_search_result_table tbody").fadeIn ();
					$("#users-pager").removeClass ("hide").fadeIn ();
				},
				"html"
			);
		});
		return false;
	});
	$("#user_search_result_table").tablesorter ();
}


function show_user_search_dialog (dialog_id, title) {
	selector = "#" + dialog_id;
	
	$(selector).empty ();
	values = Object ();
	values.page = "operation/users/user_search";
	jQuery.get ("ajax.php",
		values,
		function (data, status) {
			$(selector).append (data);
			$(selector).dialog ({"title" : title,
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

/* This may only work in operation/incidents/incident.php */
function configure_incident_form () {
	img = $("<img />").attr ("src", "images/zoom.png").css ("cursor", "pointer").click (search_user_clicked);
	$("#incident_status_form tbody td#table1-2-1").prepend (img);
	$("#incident_status_form").submit (function () {
		values = get_form_input_values ("incident_status_form");
		values.page = "operation/incidents/incident_detail";
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
