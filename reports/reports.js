$(document).ready(function(e){
	
	jQuery.extend({
	
		db_json:function(options)
		{
		var sURL="http://dev.thewoodlandsumc.org/moon/hector/reports/reports.php";	
		$.ajax({
	   	url: sURL,
	   	data: options,
	   	async: false,
	   	dataType: "jsonp",
	   	success: function (data) {
	   	if (data.error_msg != null){data.message = '';	
	   		alert(
			   		'Type: '+data.error_msg.type +'\n' +
			   		'Msg:  '+data.error_msg.message +'\n' + 
			   		'Line  '+data.error_msg.line +'\n'
		   		); 	
	   	}
	   	else{
	   		msg = data
	   	}
		
		}
	  });
	  return msg
	  } 
	});	

$('#dispatch_dates').html('<select id="dispatch_dates_list">'+
	$.db_json({"function_name":"weekly_report_list"}).message+
'</select>')

$('#dispatch_dates_list').on('change',function(){
	if ( $('#dispatch_dates_list').val() != '')
	{
		var choosen_date = $('#dispatch_dates_list').val().split('_');
		var start_date = choosen_date[0];
		var start_date = start_date.split('.');
		var start_date = start_date[2]+'-'+start_date[0]+'-'+start_date[1];
		var end_date = choosen_date[1];	
		var end_date = end_date.split('.');
		var end_date = end_date[2]+'-'+end_date[0]+'-'+end_date[1];
		var reports = $.db_json({"function_name":"weekly_reports","start_date":start_date,"end_date":end_date}).message
		
		$('#dispatch_reports').html(
			'<a class="report_link" target="_blank" href="'+reports.report_gross_path+'"><img src="libs/excel.png" class="excel_icon" />'+reports.report_gross_name+'</a><br><br>'+
			'<a class="report_link" target="_blank" href="'+reports.report_earnings_path+'"><img src="libs/excel.png" class="excel_icon" />'+reports.report_earnings_name+'</a><br><br>'+
			'<a class="report_link" target="_blank" href="'+reports.report_driver_path+'"><img src="libs/excel.png" class="excel_icon" />'+reports.report_driver_name+'</a><br><br>'+
			'<a class="report_link" target="_blank" href="'+reports.report_outside_path+'"><img src="libs/excel.png" class="excel_icon" />'+reports.report_outside_name+'</a><br>'
		).fadeIn('slow', function() {});
	}
});

}); //Ready
