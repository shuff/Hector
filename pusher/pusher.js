$(document).ready(function(e){

function ini_modal()
{
	
	$('#grid_modal').dialog({
    	minHeight: 250,
    	minWidth: 410,
    	modal: true,
    	resizable: false,
   		autoOpen:false,
		open: function() 
		{	
			$(this).parent().find('.ui-dialog-titlebar').append('<img tabindex="1" id="btn_cancel" class="btn_cancel" style="position:absolute;top:-10px;left:94.5%;width:30px;" src="img/btn-modalcancel.png"/>');
		
			$('#inner_modal').show()
			$("#btn_close").click(function(e){
				$("#btn_cancel").click()
			});
			$("#btn_cancel").click(function(e){
				$("#grid_modal").dialog("close");
				$("#grid_modal").dialog("destroy")
				flush_dialog('#grid_modal')
				$('#inner_modal').hide()
			});
		}
 	});
}		

	
$.fn.getHexColor = function() {
    var rgb = $(this).css('color');
    rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
    function hex(x) {return ("0" + parseInt(x).toString(16)).slice(-2);}
    return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
}
function check_arrows(){
		var trucks_right_height = Number($('#truck_right').height())
		var trucks_trucks_tb_height = Number($('#trucks_tb').height())

		if ((trucks_trucks_tb_height - 100) >= trucks_right_height){
			$('#truck_arrow_up').show()
			$('#truck_arrow_down').show()
		}
		else{
			$('#truck_arrow_up').hide()
			$('#truck_arrow_down').hide()
		}
		
		var materials_height = Number($('#materials').height())
		var materials_tb_height = Number($('#materials_tb').height())

		if ((materials_tb_height - 100) >= materials_height){
			$('#material_arrow_up').show()
			$('#material_arrow_down').show()
		}
		else{
			$('#material_arrow_up').hide()
			$('#material_arrow_down').hide()
		}
}
function materials_count(){

		$('#vessels').find('.ship_clicked').each(function(e){
		var freight_bills_material_count = ''
		var cur_count = ''
		var new_count = ''
		lading_bill_id = $(this).attr('lading_bill_id')
		id = $(this).attr('ship_uid')
		if (lading_bill_id != '' && lading_bill_id != null){
			freight_bills_material_count = Number($.db_json({"function_name":"freight_bills_material_count","lading_bill_id":lading_bill_id}).message)
			cur_count = Number($('#vessel_bol_count--'+id).html())		
			new_count = cur_count - freight_bills_material_count
			$('#vessel_bol_count--'+id).html(new_count)		
			freight_bills_per_weight = Number($('#vessel_bol_weight--'+id).html()) / cur_count
			$('#vessel_bol_weight--'+id).html(Math.round(freight_bills_per_weight * new_count))
		}
		
	});
}
//*****************************************************************
//							EVENTS 
//*****************************************************************
function events()
{
	$("*").unbind('click')
	$("*").unbind('change')
	
	$(".logout").on('click',function(e){ 	 	
		if ($('#lading_bill_id_master').val() == 'true')
		{
   			$('#materials').find('.tb_rec').each(function(e){
				var lading_bill_id = $(this).attr('lading_bill_id')
			});	
			var lading_bill_status_update = $.db_json({"function_name":"lading_bill_lock","lading_bill_id":lading_bill_id,"lock_status":"false"}).message 	
		}
		var doit = $.db_json({"function_name":"logout"}).message
		var t = setTimeout("window.open('./','_self')", 1000);
	});	
	window.onunload = function(){
		if ($('#lading_bill_id_master').val() == 'true')
		{
   			$('#materials').find('.tb_rec').each(function(e){
				var lading_bill_id = $(this).attr('lading_bill_id')
			});	
			var lading_bill_status_update = $.db_json({"function_name":"lading_bill_lock","lading_bill_id":lading_bill_id,"lock_status":"false"}).message 	
		}
	};	
	$("#back_submit").on('click',function(e){
		
	if ($('#lading_bill_id_master').val() == 'true'){
		$('#materials').find('.tb_rec').each(function(e){
			var lading_bill_id = $(this).attr('lading_bill_id')
		});	
		var lading_bill_status_update = $.db_json({"function_name":"lading_bill_lock","lading_bill_id":lading_bill_id,"lock_status":"false"}).message 	
	}
		$('#trucks,#left_title,#right_title,#truck_bottom').fadeOut('fast',function(e){
			$('#client_welcome').html('<div id="logout">Welcome ' + $('#role').val() + ' ' + $('#username').val() +' <a class="logout"> [logout]</a></div>');
			$('#vessels').html($.db_json({"function_name":"vessels"}).message)
			$('#vessels').css('z-index','99')
			events()
			materials_count()	
		});	
		
		

	});	
	$(".ship_clicked").on('click',function(e){
		$('#input_truck_company').html($.db_json({"function_name":"carrier_list","carrier_type":$(this).val()}).message)
		var vessel_date_id = $(this).attr('vessel_date_id')
		var doit = $.db_json({"function_name":"materials","vessel_date_id":vessel_date_id}).message		
		$('#vessels').html('')
		$('#trucks,#left_title,#right_title,#truck_bottom').show()
			
		$('#input_truck_number').html($.db_json({"function_name":"truck_list","carrier_id":$('#input_truck_company').val()}).message)
		$('#materials').html(doit)
		$('#materials').find('.tb_rec').each(function(e){
			lading_bill_id = $(this).attr('lading_bill_id');
		});
		var id = 'not found'
		$('#truck_right').html($.db_json({"function_name":"trucks","lading_bill_id":lading_bill_id}).message)
		
		var lading_bill_is_locked = $.db_json({"function_name":"lading_bill_is_locked","lading_bill_id":lading_bill_id}).message
		
		if (lading_bill_is_locked == 'true'){
			$('#warning').show()
		}
		else
		{
			$('#warning').hide()
			var lading_bill_status_update = $.db_json({"function_name":"lading_bill_lock","lading_bill_id":lading_bill_id,"lock_status":"true"}).message
			$('#lading_bill_id_master').val('true')
		}
		check_arrows()
		//$('#trucks_arrow_down').css('margin-top',div_height + 'px')
	
		$('#input_quantity').val('0')
	
		$('#materials').find('.tb_rec').each(function(e){
			
			if ($(this).is(':visible')){
				bol_id = $(this).attr('bol_id')
				org_pieces = $("#pieces--"+bol_id).html()
				org_weight = $("#ship_weight--"+bol_id).html()
				ship_material_id = $(this).attr('parent_id')
				
				var subtract_weight = 0;			
				var subtract_pieces = 0;
				
				$('#trucks_tb').find('.trucks_tr').each(function(e){
				
				if ($(this).is(':visible')){	
					truck_material_id = $(this).attr('parent_id')		
					trucks_bol_id = $(this).attr('bol_id')
				//	alert(bol_id)
				//	alert(trucks_bol_id)
					
					row_id = $(this).attr('row_id')
					truck_pieces = $('#quantity--'+row_id).html()
					truck_weight = $('#weight--'+row_id).html()
					if (trucks_bol_id == bol_id){
						subtract_weight = subtract_weight + Number(truck_weight)
						subtract_pieces = subtract_pieces + Number(truck_pieces)
					}
					}	
				});
			}
			//new adjused weight		
			$('#ship_weight--'+bol_id).html(Number(org_weight) - subtract_weight)
			$('#pieces--'+bol_id).html(Number(org_pieces) - subtract_pieces)
		});
	
		events();
	//	$('#input_truck_company').change()
		$('#input_quantity').focus()	

		
	});
	
	
	//truck row selected
	$(".trucks_tr").on('click',function(e){
	
		
		$('.tb_col_header').css("color","black")		
		
		
		$('#trucks_tb').find('.trucks_tr').each(function(e){
			$(this).css("color","#862633").css("background-color","transparent")
			$(this).removeClass('selected_row')	
		});
		$(this).addClass('selected_row')
		$(this).css("color","white").css("background-color","#99CCFF")
		row_id = $(this).attr('row_id')	
		truck_id = $(this).attr('truck_id')
		carrier_id = $(this).attr('carrier_id')
		$('#input_truck_company').val(carrier_id)
		$('#input_truck_number').html($.db_json({"function_name":"truck_list","carrier_id":carrier_id}).message)
		$('#input_truck_number').val(truck_id)
		var quantity_id = '#quantity--'+$(this).attr('bol_id')+carrier_id+truck_id
		$('#input_quantity').val($(quantity_id).html())
		
	flush_dialog('#grid_modal')	
	ini_modal()
	$("#grid_modal").dialog("open")
	$('.arrow_left').hide()
	$('.arrow_right').show()
	$('#arrows').hide()
	$('#btn_close').hide()
	$('#truck_info_wrapper').hide()
	$('#input_quantity').css('position','relative')
	$('#input_quantity').css('top','60px')
	//$('#inner_motal').addClass('inner_modal_truck_selected')
 

 	

	});
		
	//materials row selected
	$(".materials_item").on('click',function(e){
		
		$('#materials').find('.tb_rec').each(function(e){
			$(this).css("color","#862633").css("background-color","transparent")
			$(this).removeClass('selected_row')	
		});
		
		$('.tb_col_header').css("color","black")
		
		$(this).css("color","white").css("background-color","#99CCFF")	
		$(this).addClass('selected_row')
		$('#temp_vars').val($(this).attr('weight_per'))	
		
		id = $(this).attr('bol_id')
		$('#input_quantity').val($('#pieces--'+id).html())
	
	flush_dialog('#grid_modal')	
	ini_modal()
	$('#grid_modal').dialog('option', 'minHeight', 410);
	$('.arrow_left').show()
	$('.arrow_show').show()
	$('#arrows').show()
	$('#btn_close').hide()
	$('.arrow_right').hide()
 	$("#grid_modal").dialog("open") 
 	$('#truck_info_wrapper').show()	
 	$('#input_quantity').css('position','relative')
	$('#input_quantity').css('top','40px')

 	//$('#inner_motal').removeClass('inner_modal_truck_selected')
 //	$('#input_quantity').removeClass('input_quantity_truck_selected')
	});

	//add up arrow clicked
	$("#up").on('click',function(e){
		var bol_num = 0
		var bol_org = 0
		$('#materials').find('.tb_rec').each(function(e){
			if ($(this).getHexColor()=='#ffffff'){
				bol_org = Number($(this).attr('bol_org'))
				bol_num = Number($(this).attr('bol_num'))
			}
		});
		var bol_cur = Number($('#input_quantity').val())

		if (bol_cur + 1 <= bol_org){
			$('#input_quantity').val(bol_cur + 1)
		}		
	});
	//remove down arrow clicked
	$("#down").on('click',function(e){

		var bol_cur = Number($('#input_quantity').val())

		if (bol_cur - 1 >= 0){
			$('#input_quantity').val(bol_cur - 1)
		}		
	});
	
	//Carrier changes
	$('#input_truck_company').on('change',function(e){
		
			var change_caller = $('#change_caller').val()
			$('#change_caller').val('')	
			
		var found_truck = false
		$('#input_truck_number').html($.db_json({"function_name":"truck_list","carrier_id":$('#input_truck_company').val()}).message)		
		$('.tb_col_header').css("color","black")
		
		$('#materials').find('.tb_rec').each(function(e){
			if ($(this).hasClass('selected_row') == true){
				ship_bol_id = $(this).attr('bol_id')
				parent_id = $(this).attr('parent_id')
				lading_bill_id = $(this).attr('lading_bill_id')
				//ship_bol_id = bol_id+parent_id
				description = $('#desc--'+ship_bol_id).html()
			}
		});
				
	$('#trucks_tb').find('.trucks_tr').each(function(e){	
		$('#trucks_tb').find('.trucks_tr').each(function(e){
				$(this).css("color","#862633").css("background-color","transparent")
				$(this).removeClass('selected_row')	
		});

			
				//This is an existing truck in the list
				if ($(this).attr('row_id') == ship_bol_id+$("#input_truck_company").val()+$("#input_truck_number").val() && change_caller == 'arrow_click'){
					found_truck = true
					var row_id = $(this).attr('row_id')
				
					if (($('#quantity--'+row_id).html() != '0' && $('#quantity--'+row_id).html() != '' && 
					$('#quantity--'+row_id).html() != null) || (Number($('#input_quantity').val())>=1))
					{
						$(this).show()
						
						$(this).css("color","white").css("background-color","#99CCFF")	
						var weight_per = $(this).attr('weight_per')
						var cur_quantity = Number($('#quantity--'+row_id).html())
						var add_quantity = Number($('#input_quantity').val())
						var new_quantity = cur_quantity + add_quantity
						
						$('#truck_description--'+row_id).html(description)
						$('#quantity--'+row_id).html(new_quantity)
						$('#weight--'+row_id).html(Math.round(new_quantity * Number(weight_per)))
						$('#input_quantity').val('0')
						var highlightcolor = '#33FF33'
						$(this).effect( "highlight",{color:highlightcolor},1000,function(e){	
						$(this).css("color","white").css("background-color","#99CCFF").addClass('selected_row')
			
						});
					}
				}
			});
				

		if (found_truck == false && change_caller == 'arrow_click' && Number($('#input_quantity').val())>=1){
	
		$('#trucks_tb').find('.trucks_tr').each(function(e){
				$(this).css("color","#862633").css("background-color","transparent")
				$(this).removeClass('selected_row')	
		});
			var weight_per = $('#temp_vars').val()
			$('#temp_vars').val('')
			var row_id = ship_bol_id+$("#input_truck_company").val()+$("#input_truck_number").val()
			
			$('#trucks_tb').append('<tr class="trucks_tr" parent_id="'+parent_id+'" id="'+row_id+
			'" carrier_id="'+$("#input_truck_company").val()+'" truck_id="'+$("#input_truck_number").val()+'" bol_id="'+ship_bol_id+'" weight_per="'+weight_per+' "row_id="'+row_id+'">'+
			'<td id="tb_col_a" class="tb_col" style="width:5%;padding-right:5px;text-align:left;"> <div id="bol_id--'+row_id+'" class="tb_row">'+lading_bill_id+'</div></td>'+
			'<td id="tb_col_d" class="tb_col" style="width:25%;"><div id="description--'+row_id+'" class="tb_row">'+description+'</div></td>'+		
			'<td id="tb_col_b" class="tb_col" style="width:50%;text-align:center;"> <div id="carrier--'+row_id+'" class="tb_row">'+$("#input_truck_company option:selected").text()+'</div></td>'+
			'<td id="tb_col_c" class="tb_col" style="width:20%;text-align:center;"> <div id="truck--'+row_id+'" class="tb_row">'+$("#input_truck_number option:selected").text()+'</div></td>'+
			'<td id="tb_col_d" class="tb_col" style="width:15%;text-align:center;"> <div id="quantity--'+row_id+'" class="tb_row">'+$('#input_quantity').val()+'</div></td>'+
			'<td id="tb_col_d" class="tb_col" style="width:15%;text-align:right;"> <div id="weight--'+row_id+'" class="tb_row">'+Math.round(Number($('#input_quantity').val())*Number(weight_per))+'</div></td>'+			
			'</tr>')
			
			if ($('#input_quantity').val()=='0' || $('#input_quantity').val()=='' || $('#input_quantity').val()== null ){
				$('#'+row_id).removeClass('selected_row')
				$('#'+row_id).val().hide()
			}
			else{
				$('#'+row_id).effect("highlight",{color:"#33FF33"},1000,function(e){				
					$('#'+row_id).css("color","white").css("background-color","#99CCFF").addClass('selected_row')
				});
			}		
			
			$('#input_quantity').val('0')	
		}			
			//events()
										
	});	

	$('#finish_submit').on('click',function(e){
	//	var doit = $db_json({"function_name":"finish_load"})
		
		var i=0;
		var looks_good = false
		$('#materials').find('.tb_rec').each(function(e){
			var lading_bill_id = $(this).attr('lading_bill_id')
		});
		$('#trucks_tb').find('.trucks_tr').each(function(e){
					
				var row_id = $(this).attr('row_id')
				var bol_id = $('#bol_id--'+row_id).html()
				var carrier_id = $(this).attr('carrier_id')
				truck_id = $(this).attr('truck_id')
				var quantity = $('#quantity--'+row_id).html()
				var weight = $('#weight--'+row_id).html()
				parent_id = $(this).attr('parent_id')
				//alert("row_id:"+row_id+"\n"+"parent_id:"+parent_id+"\n"+"bol_id:"+bol_id+"\n"+"carrier_id:"+carrier_id+"\n"+"truck_id:"+truck_id+"\n"+"quantity_id:"+quantity+"\n"+"weight_id:"+weight+"\n")		
				if(i==0){//clear old shipment/trucking record
					var doit_del = $.db_json({"function_name":"clear_shipment","bol_id":bol_id,"carrier_id":carrier_id,"truck_id":truck_id,"quantity":quantity,"weight":weight,"parent_id":parent_id});
				i++
				}
				i++
				var doit = $.db_json({"function_name":"finish","bol_id":bol_id,"carrier_id":carrier_id,"truck_id":truck_id,"quantity":quantity,"weight":weight,"parent_id":parent_id}).message;				
				$('#truck_right').effect("highlight",{color:"#33FF33"},1000,function(e){});		
		});

		var lading_bill_status_update = $.db_json({"function_name":"lading_bill_lock","lading_bill_id":lading_bill_id,"lock_status":"false"}).message
		$('#lading_bill_id_master').val('false')
		$("#back_submit").click()
		
	});
	
	$('.arrow_right').on('click',function(e){
		//this is actually the left arrow....Remove from truck
			
		$('#trucks_tb').find('.trucks_tr').each(function(e){
			if ($(this).hasClass('selected_row')){
				truck_bol_id = $(this).attr('bol_id')
				var row_id = $(this).attr('row_id')
				selected_qty = $('#quantity--'+row_id).html()
				$('#quantity--'+row_id).html('0')
				var highlightcolor = '#FF0000'
				$(this).css("color","white").css("background-color","#FF0000")
				$(this).effect("highlight",{color:highlightcolor},1000,function(e){												
				$(this).fadeOut('slow')
				$(this).removeClass('selected_row')
				});		

		$('#materials').find('.tb_rec').each(function(e){
			$(this).css("color","#862633").css("background-color","transparent")
			$(this).removeClass('selected_row')	
		});
		$('#materials').find('.tb_rec').each(function(e){
			var rec_id = $(this).attr('rec_id')
			var bol_num = $(this).attr('bol_num')
			var ship_bol_id = $(this).attr('bol_id')
			var weight_per = $(this).attr('weight_per')
			if (truck_bol_id == ship_bol_id){

				var cur_bol_num = $('#pieces--'+ship_bol_id).html()
				var new_bol_num = Number(cur_bol_num) + Number(selected_qty)
				var new_weight = new_bol_num * Number(weight_per)
				$('#pieces--'+ship_bol_id).html(new_bol_num)
				$('#ship_weight--'+ship_bol_id).html(Math.round(new_weight))
				$(this).effect( "highlight",{color:"#33FF33"},1000,function(e){				
				$(this).css("color","white").css("background-color","#99CCFF").addClass('selected_row')});
			}					
		});
				}						
		});	
		$("#btn_cancel").click()			
		check_arrows()
	});
	$('.arrow_left').on('click',function(e){
		//this is actually the right arrow....Add to truck
		var found_err = false
		if ($('#input_quantity').val() <= 0 || IsNumeric($('#input_quantity').val()) != true){
			alert("Quantity must be number greater that 0")
			found_err = true
		}
		/*
		var found_truck = false
		$('#trucks_tb').find('.trucks_tr').each(function(e){
			if ($(this).hasClass('selected_row')){
				found_truck == true	
			}						
		});
		if (found_truck == false){
			alert("Select a Truck")
			found_err = true
		}
		*/
		if (found_err == false){
			var found_selected = false
			$('#materials').find('.tb_rec').each(function(e){
				if ($(this).hasClass('selected_row')){
					found_selected = true
					var bol_num = $(this).attr('bol_num')
					var bol_org = $(this).attr('bol_org')
					var rec_id = $(this).attr('rec_id')
					var weight_per = $(this).attr('weight_per')
					var selected_qty = $('#input_quantity').val()
					var trucking_company = $('#input_truck_company').val()
					var truck_num = $('#input_truck_number').val()					
					//var id = $(this).attr('id')
			
					bol_id = $(this).attr('bol_id')
					var rec_id = $(this).attr('rec_id') 
					var cur_bol_num = $('#pieces--'+bol_id).html()
					var subtract_quantity = $('#input_quantity').val()
					var new_bol_num = Number(cur_bol_num) - Number(subtract_quantity)
					var new_weight = Number(weight_per) * new_bol_num
						
					if (new_bol_num < 0 ){
						alert('Choosen quantity is more than available, lower the quantity.')
					}
					else
					{
						//new left side quantity
						$('#pieces--'+bol_id).html(new_bol_num)
						$('#ship_weight--'+bol_id).html(Math.round(new_weight))
						$('#temp_vars').val(weight_per)
						$('#change_caller').val('arrow_click') // do this so the change event knows 
						// that the arrow click called it and not just a change of carrer. There must be a different way of doing this
						//	but im tired and this works.
						$('#input_truck_company').change()
					}
					
				}
			});
			if (found_selected == false){
				alert('Select a Shipment.')
			}
		}
		check_arrows()	
	});
	
}
var page = $('#page').val();
//*****************************************************************
// 							LOGIN PAGE
//*****************************************************************			
if (page == 'login'){

$('#wrapper').fadeIn('slow')


	$('#login_error').html('');
	$('#login_error').hide();
	
	if (Get_Cookie('pusher_input_username') != null)
	{$('#input_username').val(Get_Cookie('pusher_input_username'));}
	$('#spinner').hide();

$('#input_username').focus()

	$('input').bind('keypress', function(e) {
    	if (e.keyCode==13){
            $("#login_submit").click();
   		 }
	});

	$("#login_submit").on('click',function(e){

		//******************************************************
		//				VALIDATE
		//******************************************************
		cookie_me('input_username');
		$('#login_error').hide();
		$('#login_error').html('');
		$('#spinner').show();
		

	// username or password is missing
	
		if ($('#input_password').val() == '' || $('#input_email').val() == '')
		{
			$('#login_error').html('The username or password was incorrect.');
			$('#login_error').show();
			$('#spinner').hide();
			$('#login_submit').show();
			return
		}

// validate username and password

		var valid_login = $.db_json_login({"function_name":"log_me_in","username":$('#input_username').val(),"password":$('#input_password').val()}).message
		
// It's a good login !! 

		if (valid_login == 'true')
		{				
			var t = setTimeout("window.open('dashboard.php','_self')", 1000);
		}	
// It's bad login :(
		else 
		{	
			$('#login_error').html('The username or password was incorrect');
			$('#login_error').show();
			$('#spinner').hide();
			$('#login_submit').show();
			return
		}
	});

}
//*****************************************************************
// 							DASHBOARD PAGE
//*****************************************************************		
if (page == 'dashboard'){
	
$('#wrapper').fadeIn('slow')


	$('#client_welcome').html('<div id="logout">Welcome ' + $('#role').val() + ' ' + $('#username').val() +' <a class="logout"> [logout]</a></div>');
	$('#vessels').html($.db_json({"function_name":"vessels"}).message)
	
	materials_count()



	events()
}
}); //Ready
