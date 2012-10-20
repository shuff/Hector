$(document).ready(function(e){
	
function flush_motal(){ // When the modal window is opened, the following actions are started.
	$('#entry_modal').dialog({
    	modal: true,
    	width:410,
    	minHeight:200,
    	resizable: false,
   		autoOpen:false,
		open: function() 
		{	
			$(this).parent().find('.ui-dialog-titlebar').append('<img tabindex="1" id="btn_cancel" class="btn_cancel" style="position:absolute;top:-10px;left:95%;width:30px;" src="img/btn-cancel.png"/>');

			$("#entry_modal").html('<div id="modal_title">'+$('#entry_modal').val()+'</div><textarea maxlength="255" id="modal_input" class="tiny_textarea" type="tiny_text"></textarea>'+		
			'<div id="button_modal" style="float:left;"><input type="tiny_button" name="login_submit" id="entry_modal_submit" value="Add Event"  /></div>'+
			'<div id="existing_entries"></div><div id="spinner" class="modal_spinner"></div></div>')
					
			$("#btn_cancel").click(function(e){
				$("#entry_modal").dialog("close");
				$("#entry_modal").dialog("destroy")
				flush_motal()
			});
			
			$("#entry_modal_submit").on('click',function(e){ 				
				var doit = $.db_json({
					"function_name":"add_calendar_entry",
					"date":$('#entry_modal').val(),	
					"title":$('#modal_input').val(),	
				})				
				load_calendar()	
				$("#btn_cancel").click()			
			});
			
			function get_entries(){
				var all_exisiting_entries = $.db_json({
				"function_name":"existing_entries",
				"day":$('#entry_modal').val()
				}).message;
				for ( i = 0; i < all_exisiting_entries.length; i++ )
				{		
					var tabindex = i+4;		
					$("#existing_entries").append('<div class="del_entry" id="item--'+all_exisiting_entries[i].id+'">'
					+'<div class="del_entry_title">'+all_exisiting_entries[i].title+'</div>'+
					'<input type="tiny_button" style="left:4px;" class="del_entry_button" value="Delete Event" pkey="'+all_exisiting_entries[i].pkey+'"/></div>');						
				}
				$(".del_entry_button").on('click',function(e){
					var pkey = $(this).attr('pkey')
					var doit = $.db_json({"function_name":"delete_calendar_entry","pkey":pkey}).message
					$("#existing_entries").html('')
					get_entries();
					load_calendar()
				});
			}
			get_entries();
			$('#modal_input').focus()		
		}
 	});
}		
flush_motal();	
jQuery.extend({
	db_json:function(options)
	{
	var sURL="http://dev.thewoodlandsumc.org/Hector/v2/calendar/calendar.php";	
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


function load_calendar()
{
$('.calendar').html('');
// initialize the calendar(s), any class with the class calendar will have the inner html replaced...
$('body').find('.calendar').each(function(e){	
	
	$(this).fullCalendar({	
		dayClick: function(date, allDay, jsEvent, view) {	
		var sDate = date.toString()
		clean_date = sDate.split('00')
		clean_date = clean_date[0];
		$("#entry_modal").val(clean_date)	
		$("#entry_modal").dialog("open")//at this point the modal is openend to add or delete from the day.
	},
		eventTextColor:'white',
		editable: true,
		events: $.db_json({"function_name":"cal"}).message,// this is where all the calender entries are loaded.
		
		 eventClick: function(calEvent, jsEvent, view) {

		var sDate = calEvent.start.toString()
		clean_date = sDate.split('00')
		clean_date = clean_date[0];
		$("#entry_modal").val(clean_date)	
		$("#entry_modal").dialog("open")
		
		//at this point the modal is openend to add or delete from the day.
			// this will change the event. $(this) is the inner div of the event.
			//$(this).css({'padding':'1px','margin-top':'2px','padding-bottom':'2px'})
		},
		
		loading: function(bool) { // this shows a spinner if it takes a long time to load from the database
			if (bool) $('#spinner').show();
			else $('#spinner').hide();
		}
		
	});
	$(this).prepend('<h1 style="width:100%;text-align:center;" class="h1"><div clsss="calendar_title">Click a day to add/delete an event.</div></h1>')
});

}
load_calendar()
}); //Ready
