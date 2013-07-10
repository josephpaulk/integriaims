
// Show the modal window of alerts
function openAlerts() {
	
	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/header&get_alerts=1",
		dataType: "html",
		success: function(data){	
			
			$("#alert_window").html (data);
			$("#alert_window").show ();

			$("#alert_window").dialog ({
					resizable: true,
					draggable: true,
					modal: true,
					overlay: {
						opacity: 0.5,
						background: "black"
					},
					width: 620,
					height: 400
				});
			$("#alert_window").dialog('open');
			
		}
	});
}


