<?php session_start(); ?>
<!doctype html>
<head>
<meta charset="utf-8">
<title>Pusher Application</title>
<meta name="description" content="Pusher Application" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<link rel="stylesheet" href="jquery_UI/css/custom-theme/jquery-ui-1.8.16.custom.css">
<link rel="stylesheet" href="pusher.css">
<input type="hidden" id="page" value="dashboard" />
<input type="hidden" id="client_id" value="<?php echo $_SESSION['client_id']; ?>" />
<input type="hidden" id="uid" value="<?php echo $_SESSION['uid']; ?>" />
<input type="hidden" id="role" value="<?php echo $_SESSION['role']; ?>" />
<input type="hidden" id="username" value="<?php echo $_SESSION['username']; ?>" />
</head>
<body>
<input type="hidden" id="temp_vars" />
<input type="hidden" id="change_caller" />
<input type="hidden" id="lading_bill_id_master" value="false"/>

<div id="wrapper">
  	<div id="content" class="box">
  		<div id="client_welcome"></div>
		<div id="vessels"></div>
		 <div id="left_title" style="display:none;">On Ship</div>
		 <div id="right_title" style="display:none;">Create Truck Load</div>
		<div id="trucks" style="display:none;">
			<div id="material_arrow_up" class="circle_up"><div class="arrow">^</div></div><div id="truck_left"><div id="materials"></div></div><div id="material_arrow_down" class="circle_down"><div class="arrow">^</div></div>
			<div id="truck_center"><div id="warning"><b style="font-size:12px;">Warning!</b><br>This shipment is being edited by another user. If you make any changes, they will be overwritten.</div>
				<div id="navigation_wrapper">
					<input type="tiny_text" value="100" id="input_quantity"/><div id="arrows"><img id="up" src="img/up.png"/><img id="down" src="img/down.png"/></div>
					<div class="arrow_left"><div id="arrow_left_text">Assign</div></div>
					<div class="arrow_right"><div id="arrow_right_text">Remove</div></div>
				</div>
			</div>
			<div id="truck_arrow_up" class="circle_up"><div class="arrow">^</div></div><div id="truck_right"></div><div id="truck_arrow_down" class="circle_down"><div class="arrow">^</div></div>
		</div>	
			<div id="truck_bottom"  style="display:none;">
				<!--
				<div id="carrier_types_wrapper">
					<input id="input_radio_inhouse" class="radio_carrier" name="carrier" sytle="background:#CCC;" type="radio" value="inhouse" title="In-House Driver" />In-House Driver<br>
					<br><input id="input_radio_outside"  selected="true" class="radio_carrier" name="carrier" sytle="background:#CCC;" type="radio" value="outside" title="Outside Carrier" />Outside Carrier<br>
				</div>
				-->
				<div id="back_button">
					<input type="button" class="box" style="width:auto;left:0;position:absolute;margin-left:10px;margin-top:22px;z-index:99;" id="back_submit" value="< Back"  />
				</div>
				<div id="finish_button">
					<input type="button" class="box" style="width:200px;" id="finish_submit" value="Finish Load"  />
				</div>
				<div id="truck_info_wrapper">
					<h4 class="h4" style="margin-bottom:5px;"><b>Select Trucking Company</b></h4>
					<select id="input_truck_company" title="Trucking Company"></select>
					<h4 class="h4" style="margin-bottom:-5px;"><b>Select Truck</b></h4>
					<select id="input_truck_number" title="Truck Number"></select>
				</div>
			</div>
		</div>
	</div>

</body>
<script src="libs/jquery-1.7.2.js"></script>
<script src="jquery_UI/js/jquery-ui-1.8.16.custom.min.js"></script>
<script src="libs/foundation.js"></script>
<script src="pusher.js"></script>
</html>