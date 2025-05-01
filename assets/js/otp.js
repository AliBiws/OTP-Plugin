jQuery(document).ready(function($) {
	function toggleExtraField() {
	  if ($('.ols input[name="otp_export_csv"]:checked').length > 0 ||
		  $('.ols input[name="otp_export_excel"]:checked').length > 0) {
		$('.ols #extra-field').show();
	  } else {
		$('.ols #extra-field').hide();
	  }
	}
	toggleExtraField();
	$('.ols input[name="otp_export_csv"], .ols input[name="otp_export_excel"]').on('change', toggleExtraField);
	
	function toggleExcelField() {
	  if ($('.ols input[name="otp_export_excel"]:checked').length > 0) {
		$('.ols #excel-field').show();
		  } else {
			$('.ols #excel-field').hide();
		  }
		}
	  toggleExcelField();
	  $('.ols input[name="otp_export_excel"]').on('change', toggleExcelField);

	const exportButtons = [];

	if (otp_obj.excel) {
		exportButtons.push({
			extend: 'excelHtml5',
			text: otp_obj.btntxt + ' Excel' + otp_obj.excel_icon,
			filename: otp_obj.filename,
			customize: function(xlsx) {
                       const sheet = xlsx.xl.worksheets['sheet1.xml'];
                       $('row c[r="A1"] t', sheet).text(otp_obj.header);
            }
		});
	}

	if (otp_obj.csv) {
		exportButtons.push({
			extend: 'csvHtml5',
			text: otp_obj.btntxt +' CSV' + otp_obj.csv_icon,
			filename: otp_obj.filename
		});
	}
	$('#otp-logs-table').DataTable({
		scrollX: true,
		info: false,
		paging: true,
		scrollCollapse: true,
		columnDefs: [{ searchable: false, targets: [1, 2, 3] }],
		language: {
			search: otp_obj.search,
			lengthMenu: '_MENU_' + otp_obj.entries 
		},
		layout: {
			topEnd: {
				buttons: exportButtons
			},
			topStart: 'search',
			bottomStart: {
				pageLength: {
					menu: [10, 25, 50, 100]
				}
			}
		}
	});
	$('.addnewbtn').on('click',function(){
		$('#otp-form').toggle('slow');
		$('.addnewbtn').toggle('fade');
		$('.closeform').toggle('fade');
		$('#phone_number').focus();
	});
	$('.closeform').on('click',function(){
		$('#otp-form').toggle('slow');
		$('.closeform').toggle('fade');
		$('.addnewbtn').toggle('fade');
	});
	$("#phone_number").on('input', function () {
    	this.value = this.value.replace(/\D/g, '');
	});
	$(".submition").on('click', function(e) {
		e.preventDefault();
		var phone_number = $("#phone_number").val().trim();

		if (!phone_number) {
			Swal.fire({
				icon: 'warning',
				title: otp_obj.alerts.empty_title,
				text: otp_obj.alerts.empty_text,
				confirmButtonText: otp_obj.alerts.ok
			});
			return;
		}

		if (!/^\d+$/.test(phone_number)) {
			Swal.fire({
				icon: 'warning',
				title: otp_obj.alerts.invalid_title,
				text: otp_obj.alerts.invalid_text,
				confirmButtonText: otp_obj.alerts.ok
			});
			return;
		}

		$.ajax({
			url: otp_obj.ajax_url,
			type: 'POST',
			data: {
				action: 'send_otp_request',
				phone_number: phone_number,
				nonce: otp_obj.nonce
			},
			success: function(response) {
				if (response.success) {
					Swal.fire({
						icon: 'success',
						title: otp_obj.alerts.success_title,
						text: otp_obj.alerts.success_text.replace('%s', phone_number),
						confirmButtonText: otp_obj.alerts.ok
					}).then(() => {
						location.reload();
					});
				} else {
					Swal.fire({
						icon: 'error',
						title: otp_obj.alerts.error_title,
						text: response.data.message,
						confirmButtonText: otp_obj.alerts.ok
					});
				}
			},
			error: function() {
				Swal.fire({
					icon: 'error',
					title: otp_obj.alerts.network_title,
					text: otp_obj.alerts.network_text,
					confirmButtonText: otp_obj.alerts.ok
				});
			}
		});
	});



});
