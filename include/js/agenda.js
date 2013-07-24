// Show the modal window of an agenda entry
function show_agenda_entry(id_entry, selected_date, min_date, refresh) {
	
	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: {
			page: "operation/agenda/entry",
			show_agenda_entry: 1,
			id: id_entry,
			date: selected_date
		},
		dataType: "html",
		success: function(data) {	
			
			$("#agenda_entry").html (data);
			add_datepicker ("#text-entry_date", min_date);
			$("#agenda_entry").show ();

			$("#agenda_entry").dialog ({
				title: "Agenda",
				resizable: true,
				draggable: true,
				modal: true,
				overlay: {
					opacity: 0.5,
					background: "black"
				},
				width: 550,
				height: 425
			});
			
			$("#agenda_entry").dialog ('open');
			
			$("#button-cancel").click(function(e) {
				$("#agenda_entry").dialog ('close');
			});
			
			$("#calendar_entry").submit(function() {
				
				var public;
				if ($("#checkbox-entry_public").is(":checked")) {
					public = 1;
				} else {
					public = 0;
				}
				
				$.ajax({
					type: "POST",
					url: "ajax.php",
					data: {
						page: "operation/agenda/entry",
						update_agenda_entry: 1,
						id: id_entry,
						title: $("#text-entry_title").val(),
						duration: $("#text-entry_duration").val(),
						alarm: $("#entry_alarm").val(),
						public: public,
						date: $("#text-entry_date").val(),
						time: $("#text-entry_time").val(),
						description: $("#textarea-entry_description").val()
					},
					dataType: "html",
					success: function(data) {
						$("#agenda_entry").html (data);
						
						$("#agenda_entry").on("dialogclose", function(event, ui) {
							if (refresh == true) {
								location.reload();
							}
						});
						$("#button-OK").click(function(e) {
							$("#agenda_entry").dialog ('close');
							if (refresh == true) {
								location.reload();
							}
						});
					}
				});
				return false;
			});
			
		}
	});
}
