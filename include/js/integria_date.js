function configure_range_dates (range_callback) {
	$("#text-start_date").datepicker ({
		beforeShow: function () {
			maxdate = null;
			if ($("#text-end_date").datepicker ("getDate") > $(this).datepicker ("getDate"))
				maxdate = $("#text-end_date").datepicker ("getDate");
			return {
				maxDate: maxdate
			};
		},
		onSelect: function (datetext) {
			end = $("#text-end_date").datepicker ("getDate");
			start = $(this).datepicker ("getDate");
			if (end <= start) {
				pulsate ($("#text-end_date"));
			}
		}
	});
	$("#text-end_date").datepicker ({
		beforeShow: function () {
			return {
				minDate: $("#text-start_date").datepicker ("getDate")
			};
		},
		onSelect: range_callback
	});

}
