function ltrim(str){return str.replace(/^\s+/, '');}
function rtrim(str){return str.replace(/\s+$/, '');}
function trim(str) {return str.replace(/^\s+|\s+$/g, '');}
function getUrlVars() {
	var vars = {};
	var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
   		vars[key] = value;
	});
	return vars;
}
function instr(str,word)
{
	
if (str == null || word == null){return false};
   if( str.indexOf( word ) != -1 )
   {
   	return true;
   }
   else
   {
   	return false;
   }
}

function IsNumeric(strString)
//check for valid numeric strings	
{
	var strValidChars = "0123456789.-";
	var strChar;
	var blnResult = true;
	
	if (strString.length == 0) 
	{return false;}
	
	//  test strString consists of valid characters listed above
	for (i = 0; i < strString.length && blnResult == true; i++)
		{
			strChar = strString.charAt(i);
			if (strValidChars.indexOf(strChar) == -1)
	 	{
	  		blnResult = false;
	  	}
		}
		
	return blnResult;
}
function validateEmail(txtEmail)
{
	var a = txtEmail
	var filter = /^[a-zA-Z0-9_.-]+@[a-zA-Z0-9]+[a-zA-Z0-9.-]+[a-zA-Z0-9]+.[a-z]{1,4}$/;
	 if(filter.test(a))
	 {
	 	return true;
	 }
	  else
	  {
	      return false;
	  }
}
function validate_values(form)
{
	var message = true;
	$('#'+form).each(function(index) {    
		
		$(this).find('select,input,textarea').each(function() {
			
			if ($(this).hasClass('NoValiation') == false){
				var error_text = '';
				if ($(this).val() == '')
				{
					error_text = 'Required';
				}
				if ($(this).hasClass('validate_numeric') && IsNumeric($(this).val()) == false)
				{
					error_text = 'Enter a number';
				}
				if ($(this).hasClass('validate_email') && validateEmail($(this).val()) == false)
				{
					error_text = 'Enter an email address';
				}
				if (error_text != ''){
					var id = $(this).attr('id')
					var error_id = $(this).attr('error')
					$('#'+error_id).addClass('on_error');
					$('#'+error_id).removeClass('off_error');
					$('#'+error_id+'_text').text(error_text).show();
					message = false;	
				}
			}			
		}); 
		
	}); 
	$('.spinner').hide();
	return message;
}
function all_fields_have_values(form)
{
	var found_error = 'false';
	$('#'+form).each(function(index) {     
		$(this).find('input,select,textarea').each(function() {

		if ($(this).val() == '' && $(this).css('display') == 'block')
		{
			var id = $(this).attr('id')
			var error_id = $(this).attr('error')
			$('#'+error_id).addClass('form_error');
			$('#'+error_id+'_text').text('Required')		
			found_error = 'true';
		}
									
		}); 
	}); 
	
	return found_error
}
function clear_errors(form)
{
	var found_error = 'false';
	$('#'+form).each(function(index) {     
		$(this).find('input,select,textarea').each(function() {
			var id = $(this).attr('id')
			var error_id = $(this).attr('error')
			$('#'+error_id).removeClass('on_error');
			$('#'+error_id).addClass('off_error');
			$('#'+error_id+'_text').text('')		
			found_error = 'false';
		}); 
	});						
}
function form_values()
{
	var msg = '';
	$('#wrapper').each(function(index) {     
		$(this).find('input,select,fancy_radio,fancy_checkbox').each(function() {
			var id = $(this).attr('id')	
			msg = msg+'&'+id+'='+$(this).val();
		}); 
	});	
	return msg;					
}
function all_values()
{
	var msg = '';
	$('html').each(function(index) {     
		$(this).find('*').each(function() {
			var id = $(this).attr('id')	
			if ($('#'+id).length){
				msg = msg+'&'+id+'='+$(this).val();
			}
		}); 
	});	
	return msg;					
}
/* ERRORS */

function clear_all_cookies(form)
{		
	//note: every input field in this application will have the attribute "error", so i am going to use that
   $('#'+form).find('*[error]').each(function(i){
  
   Set_Cookie( 'twumc_gt_'+$(this).attr('id'),'', '2592000', '/', '', '' )  
   clear_all_inputs(form)
});
}
function clear_all_inputs(form)
{	
	
	//note: every input field in this application will have the attribute "error", so i am going to use that
           $('#'+form).find('*[error]').each(function(i){
          $(this).val('');
});
}
function Eat_Cookie(name,value)
{
	Set_Cookie( name, value, '2592000', '/', '', '' )  
}
function Set_Cookie( name, value, expires, path, domain, secure )
{
//set time, it's in milliseconds
var today = new Date();
today.setTime( today.getTime() );

/*
if the expires variable is set, make the correct
expires time, the current script below will set
it for x number of days, to make it for hours,
delete * 24, for minutes, delete * 60 * 24
*/

if ( expires )
{
expires = expires * 1000 * 60 * 60 * 24;
}
var expires_date = new Date( today.getTime() + (expires) );

document.cookie = name + "=" +escape( value ) +
( ( expires ) ? ";expires=" + expires_date.toGMTString() : "" ) +
( ( path ) ? ";path=" + path : "" ) +
( ( domain ) ? ";domain=" + domain : "" ) +
( ( secure ) ? ";secure" : "" );
}
function cookie_me(div)
{	
	Set_Cookie( 'twumc_gt_'+div, $('#'+div).val(), '2592000', '/', '', '' )  
}
function Get_Cookie( check_name ) {
	// first we'll split this cookie up into name/value pairs
	// note: document.cookie only returns name=value, not the other components
	var a_all_cookies = document.cookie.split( ';' );
	var a_temp_cookie = '';
	var cookie_name = '';
	var cookie_value = '';
	var b_cookie_found = false; // set boolean t/f default f

	for ( i = 0; i < a_all_cookies.length; i++ )
	{
		// now we'll split apart each name=value pair
		a_temp_cookie = a_all_cookies[i].split( '=' );


		// and trim left/right whitespace while we're at it
		cookie_name = a_temp_cookie[0].replace(/^\s+|\s+$/g, '');

		// if the extracted name matches passed check_name
		if ( cookie_name == check_name )
		{
			b_cookie_found = true;
			// we need to handle case where cookie has no value but exists (no = sign, that is):
			if ( a_temp_cookie.length > 1 )
			{
				cookie_value = unescape( a_temp_cookie[1].replace(/^\s+|\s+$/g, '') );
			}
			// note that in cases where cookie is initialized but no value, null is returned
			return cookie_value;
			break;
		}
		a_temp_cookie = null;
		cookie_name = '';
	}
	if ( !b_cookie_found )
	{
		return null;
	}
}
var BrowserDetect = {
	init: function () {
		this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
		this.version = this.searchVersion(navigator.userAgent)
			|| this.searchVersion(navigator.appVersion)
			|| "an unknown version";
		this.OS = this.searchString(this.dataOS) || "an unknown OS";
	},
	searchString: function (data) {
		for (var i=0;i<data.length;i++)	{
			var dataString = data[i].string;
			var dataProp = data[i].prop;
			this.versionSearchString = data[i].versionSearch || data[i].identity;
			if (dataString) {
				if (dataString.indexOf(data[i].subString) != -1)
					return data[i].identity;
			}
			else if (dataProp)
				return data[i].identity;
		}
	},
	searchVersion: function (dataString) {
		var index = dataString.indexOf(this.versionSearchString);
		if (index == -1) return;
		return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
	},
	dataBrowser: [
		{
			string: navigator.userAgent,
			subString: "Chrome",
			identity: "Chrome"
		},
		{ 	string: navigator.userAgent,
			subString: "OmniWeb",
			versionSearch: "OmniWeb/",
			identity: "OmniWeb"
		},
		{
			string: navigator.vendor,
			subString: "Apple",
			identity: "Safari",
			versionSearch: "Version"
		},
		{
			prop: window.opera,
			identity: "Opera",
			versionSearch: "Version"
		},
		{
			string: navigator.vendor,
			subString: "iCab",
			identity: "iCab"
		},
		{
			string: navigator.vendor,
			subString: "KDE",
			identity: "Konqueror"
		},
		{
			string: navigator.userAgent,
			subString: "Firefox",
			identity: "Firefox"
		},
		{
			string: navigator.vendor,
			subString: "Camino",
			identity: "Camino"
		},
		{		// for newer Netscapes (6+)
			string: navigator.userAgent,
			subString: "Netscape",
			identity: "Netscape"
		},
		{
			string: navigator.userAgent,
			subString: "MSIE",
			identity: "Explorer",
			versionSearch: "MSIE"
		},
		{
			string: navigator.userAgent,
			subString: "Gecko",
			identity: "Mozilla",
			versionSearch: "rv"
		},
		{ 		// for older Netscapes (4-)
			string: navigator.userAgent,
			subString: "Mozilla",
			identity: "Netscape",
			versionSearch: "Mozilla"
		}
	],
	dataOS : [
		{
			string: navigator.platform,
			subString: "Win",
			identity: "Windows"
		},
		{
			string: navigator.platform,
			subString: "Mac",
			identity: "Mac"
		},
		{
			   string: navigator.userAgent,
			   subString: "iPhone",
			   identity: "iPhone/iPod"
	    },
		{
			string: navigator.platform,
			subString: "Linux",
			identity: "Linux"
		}
	]

};
BrowserDetect.init();
//alert(BrowserDetect.browser)

function make_session_id() {
    var dd = new Date();
    var yy = dd.getYear();
    var mm = dd.getMonth();
    var d = dd.getDay();
    var hh = dd.getHours();
    var mm = dd.getMinutes();
    var ss = dd.getSeconds();
    var ms = dd.getMilliseconds();
    return yy + "." +  mm + "." +  d + "." + hh + "." + mm + "." + ss + "." + ms ;
}
function flush_dialog(div)
{
 	$(div).dialog({
    minHeight: 200,
    modal: true,
    resizable: false,
   	autoOpen:false,
	open: function() 
	{	
		$(this).parent().find('.ui-dialog-titlebar').append('<img tabindex="1" id="btn_cancel" class="btn_cancel" style="position:absolute;top:-10px;left:93%;width:30px;" src="/img/btn-modalcancel.png"/>');

		$("#btn_cancel").click(function(e){
		$(div).dialog("close");
		$(div).dialog("destroy")
		flush_dialog('#grid_modal')
		});
	}
 });
}

jQuery.extend({
	db_json_async:function(options)
	{
	var sURL="foundation.php";	
	$.ajax({
  	url: sURL,
   data: options,
   async: true,
   dataType: "jsonp"
   });
  } 
});	

jQuery.extend({

	db_json:function(options)
	{
	var sURL="pusher.php";	
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
jQuery.extend({
	db_json_login:function(options)
	{
	var sURL="login.php";	
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