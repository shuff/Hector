
<?php
session_start();

//TRAP ALL ERRORS
ini_set( 'display_errors', 1 );
error_reporting( -1 );
set_error_handler( array( 'Error', 'captureNormal' ) );
set_exception_handler( array( 'Error', 'captureException' ) );
register_shutdown_function( array( 'Error', 'captureShutdown' ) );

//******************************************************************************
//						INCOMMING REQUESTS
// 		USING JSON FOR ALL INCOMMING AND OUTGOING COMMUNICATIONS.
//******************************************************************************
$doit = '';
$pusher = new pusher;

//SECURITY CHECK
$doit = $pusher->validate_user($_SESSION['client_id'],$_SESSION['uid']);
if ($doit != 'true'){		
	$server_response['message'] = 'Login Required';	
	header("Content-type: text/javascript");
	header("access-control-allow-origin: *");	
	print ($_GET['callback']. '('. json_encode($server_response) . ')'); 
	die();	// bad user exit.
}
///**********************************************************************

// PASSED SECURITY CHECK 

if (isset($_GET['function_name']))
{	
	$client_input = array();
	$server_response = array();
	$doit = '';
	foreach ($_GET as $key => $value)  
	{
		$function_name = mysql_real_escape_string($_GET['function_name']); 
		if ($key != 'function_name')
		{
				
			$value = mysql_real_escape_string($value);  
			$client_input[$key] = $value;
		}
	}
	if (method_exists($pusher, $function_name))
	{
		$doit = $pusher->$function_name($client_input);
	}

	$server_response['message'] = $doit;	
	header("Content-type: text/javascript");
	header("access-control-allow-origin: *");	
	print ($_GET['callback']. '('. json_encode($server_response) . ')'); 
	
}
//*****************************************************************************

// I pass all options as a single array "$input". 
//ex: $start_date = $input['start_date'];
//$end_date = $input['end_date];

class pusher
{	
	public function validate_user($username,$password)
	{		
		require_once "database.php";

			$foundit = 'false';
			$result = mysql_query("SELECT * FROM users WHERE id = '$username'");
			
			while($row = mysql_fetch_array($result))
			{
				if ($password == $row['password'])
				{				
					$foundit = 'true';
				}		
			}		
		return $foundit;
	}		
	
	public function logout()
	{
		$_SESSION['uid']='';
		$_SESSION['username']='';
		$_SESSION['client_id']='';
	}
	
	public function vessels()
	{
		require_once "database.php";
		$result = mysql_query("SELECT * ,SUM(materials.weight) AS total_weight,SUM(materials.quantity) AS total_quantity FROM vessel_dates
		INNER JOIN vessels ON vessels.id = vessel_dates.vessel_id
		INNER JOIN lading_bills ON lading_bills.vessel_date_id = vessel_dates.id
		INNER JOIN lading_bills_materials ON lading_bills_materials.lading_bill_id = lading_bills.id
		INNER JOIN materials ON materials.id = lading_bills_materials.material_id
		WHERE vessel_dates.completed is null AND vessel_dates.arrival_act is not null
		GROUP BY vessels.name
		");
		
		$message = '<table class="tb" style="width:100%;">';
		$head_vessel = '<tr style="" id="vessel" class="tb_rec">
						<td id="tb_col_a" class="tb_col" >
						<div class="tb_col_header">Vessel ID</div></td>';
							
		$head_arrival = '<td id="tb_col_d" class="tb_col style="text-align:center;"> 
						<div class="tb_col_header">Arrival</div></td>';				
	

		$head_count = '<td id="tb_col_d" class="tb_col" style="text-align:left;position:relative;left:3%;"> 
						<div class="tb_col_header">BOL Count</div></td>';
							
							
		$head_weight = '<td id="tb_col_e" style="text-align:right;position:relative;left:4%;" class="tb_col"> 
						<div class="tb_col_header">Rem. Wt.</div></td></tr>';
		
		$header = $head_vessel . $head_arrival . $head_count . $head_weight;
	
		$message = $message.$header;
		
		$dark_row = false;
		while($row = mysql_fetch_array($result))
		{
			if ($dark_row == true)
			{
				$color = '#F0F0F0;';
				$dark_row = false;
			}	
			else {
				$color = 'transparent;';
				$dark_row = true;
			}
				
				$id = $row['id'];
				$vessel_id = $row['vessel_id'];
				$vessel_date_id = $row['vessel_date_id'];
				$arrival_est = $row['arrival_est'];
				$arrival_act = $row['arrival_act'];
				$lading_bill_id = $row['lading_bill_id'];
				$vessel_name = $row['name'];
				$bol = $row['quantity'];
				$weight = $row['weight'];
				$name = $row['name'];
				
				$weight = $row['total_weight'];
				$bol = $row['total_quantity'];
			
				$rec = '<tr ship_uid="'.$id.'" id="rec--'.$id.'" rec_id="'.$id.'" lading_bill_id="'.$lading_bill_id.'" vessel_date_id="'.$vessel_date_id.'" style="background-color:'.$color.';line-height:3em;" class="tb_rec ship_clicked">';
				$col_vessel  = '<td id="tb_col_a" class="tb_col" style="width:25%;line-height:3em;"> <div class="tb_row">'.$vessel_name.'</div></td>';
				$col_arrival  = '<td id="tb_col_b" class="tb_col" style="width:25%;line-height:3em;text-align:left;"><div class="tb_row">'.$arrival_act.'</div></td>';
				$col_bol = '<td id="tb_col_c" class="tb_col"style="width:20%;text-align:center;line-height:3em;"><div id="vessel_bol_count--'.$id.'" class="tb_row">'.$bol.'</div></td>';
				$col_weight = '<td id="tb_col_c" class="tb_col"style="width:15%;text-align:right;line-height:3em;"><div id="vessel_bol_weight--'.$id.'" class="tb_row">'.$weight.'</div></td>';	
				$body = $rec.$col_vessel.$col_arrival.$col_bol.$col_weight.'<div id="row--'.$id.'" class="spinner"></div></tr>';	
				$message = $message.$body;
			
			
		}
		return $message;
	}
public function materials($input)
	{
		$vessel_date_id = $input['vessel_date_id'];
		require_once "database.php";
		$result = mysql_query("SELECT * FROM vessel_dates
		INNER JOIN vessels ON vessels.id = vessel_dates.vessel_id
		INNER JOIN lading_bills ON lading_bills.vessel_date_id = vessel_dates.id
		INNER JOIN lading_bills_materials ON lading_bills_materials.lading_bill_id = lading_bills.id
		INNER JOIN materials ON materials.id = lading_bills_materials.material_id
		WHERE vessel_dates.id = '$vessel_date_id'
		");
		
		$message = '<table id="materials_tb" class="tb" style="width:96%;postion:relative;padding-left:3px;">';
		$head_bol = '<tr id="bol" class="tb_rec_header">
						<td id="tb_col_a" class="tb_col" >
						<div class="tb_col_header"style="font-size:10px;">BOL</div></td>';
							
		$head_desc = '<td id="tb_col_d" class="tb_col style="text-align:center;"> 
						<div class="tb_col_header" style="font-size:10px;">Desc.</div></td>';				
							
		$head_pieces = '<td id="tb_col_d" class="tb_col" style=""> 
						<div class="tb_col_header"style="font-size:10px;text-align:center;">Pcs/Jts</div></td>';
						
		$head_weight = '<td id="tb_col_e" style="text-align:right;position:relative;left:4%;" class="tb_col"> 
						<div class="tb_col_header"style="font-size:10px;">Wt.</div></td></tr>';
		
		$header = $head_bol . $head_desc . $head_pieces . $head_weight;
	
		$message = $message.$header;
		
		$dark_row = false;
		while($row = mysql_fetch_array($result))
		{
			if ($dark_row == true)
			{
				$color = 'transparent;';
				$dark_row = false;
			}	
			else {
				$color = 'transparent;';
				$dark_row = true;
			}				
				$id = $row['id'];
				$vessel_id = $row['vessel_id'];
				$vessel_date_id = $row['vessel_date_id'];
				$arrival_est = $row['arrival_est'];
				$arrival_act = $row['arrival_act'];
				$vessel_name = $row['name'];
				$bol = $row['quantity'];
				$weight = $row['weight'];
				$name = $row['name'];
				$bol_id = $row['lading_bill_id'];
				$description = $row['description'];
				$parent_id = $row['material_id'];
			
				$rec = '<tr  id="rec_id--'.$bol_id.'" weight_per="'.$weight / $bol.'" lading_bill_id="'.$bol_id.'" parent_id="'.$parent_id.'" rec_id="'.$id.'" bol_org="'.$bol.'" bol_num="'.$bol.'" bol_id="'.$bol_id.$parent_id.'" vessel_date_id="'.$vessel_date_id.'" class="tb_rec" style="background-color:'.$color.'">';
				$col_bol  = '<td id="tb_col_a" class="tb_col" style="width:15%;"> <div class="tb_row">'.$bol_id.'</div></td>';
				$col_desc  = '<td id="tb_col_b" class="tb_col" style="width:100%;text-align:left;"> <div id="desc--'.$bol_id.$parent_id.'" class="tb_row">'.$description.'</div></td>';
				$col_pieces = '<td id="tb_col_c" class="tb_col"style="width:25%;text-align:center;"> <div id="pieces--'.$bol_id.$parent_id.'" class="tb_row">'.$bol.'</div></td>';
				$col_weight = '<td id="tb_col_c" class="tb_col"style="width:15%;text-align:right;"> <div id="ship_weight--'.$bol_id.$parent_id.'" class="tb_row">'.$weight.'</div></td>';	
				$body = $rec.$col_bol.$col_desc.$col_pieces.$col_weight.'<div id="row--'.$id.'" class="spinner"></div></tr>';	
				$message = $message.$body;			
		}
		return $message;
	}
public function trucks($input)
	{
		$lading_bill_id = $input['lading_bill_id'];
		require_once "database.php";

		$result = mysql_query("SELECT *, materials.weight AS materials_weight FROM materials
		INNER JOIN freight_bills_materials ON freight_bills_materials.material_id = materials.id
		INNER JOIN freight_bills ON freight_bills.id = freight_bills_materials.freight_bill_id
		INNER JOIN trucks ON trucks.id = freight_bills.truck_id
		INNER JOIN lading_bills_freight_bills ON lading_bills_freight_bills.freight_bill_id = freight_bills.id
		INNER JOIN carriers ON carriers.id = trucks.carrier_id
		WHERE lading_bills_freight_bills.lading_bill_id = '$lading_bill_id'


		
		");
		
		$message = '<table id="trucks_tb" class="tb" style="width:96.8%;postion:relative;padding-left:3px;">';
		
		$head_bol = '<tr id="bol" class="tb_rec_header">
				<td id="tb_col_a" class="tb_col" >
				<div class="tb_col_header"style="font-size:10px;text-align:left;padding-right:5px;">BOL</div></td>';
				
		$head_description = '<td id="tb_col_b" class="tb_col" >
			<div class="tb_col_header"style="font-size:10px;text-align:left;">Desc.</div></td>';
				
											
		$head_carrier = '<td id="tb_col_b" class="tb_col" >
						<div class="tb_col_header"style="font-size:10px;text-align:center;">Carrier</div></td>';
							
		$head_truck_number = '<td id="tb_col_c" class="tb_col style="text-align:right;"> 
						<div class="tb_col_header" style="font-size:10px;text-align:right;">Truck</div></td>';				
							
		$head_pieces = '<td id="tb_col_d" class="tb_col" style="text-align:center;position:relative;"> 
						<div class="tb_col_header"style="font-size:10px;text-align:center;">Pcs/Jts</div></td>';
						
		$head_weight = '<td id="tb_col_e" style="text-align:right;position:relative;" class="tb_col"> 
						<div class="tb_col_header"style="font-size:10px;text-align:right;">Wt.</div></td></tr>';
		
		$header = $head_bol . $head_description. $head_carrier . $head_truck_number . $head_pieces . $head_weight;
	
		$message = $message.$header;
		
		while($row = mysql_fetch_array($result))
		{
		$quantity=$row['quantity'];
		$weight=$row['materials_weight'];
		$parent_id=$row['parent_id'];
		$lading_bill_id=$row['lading_bill_id'];
		$material_id=$row['material_id'];
		$description=$row['description'];
		$freight_bill_id=$row['freight_bill_id'];
		$truck_id=$row['truck_id'];
		$truck_name=$row['unitnumber'];
		$carrier_id=$row['carrier_id'];
		$carrier_name=$row['name'];
		$weight_per = $weight / $quantity;
		
		$material_result = mysql_query("SELECT * FROM materials WHERE id = '$parent_id'");
		$parent_description = '';
		while($row2 = mysql_fetch_array($material_result)){
			$parent_description = $row2['description'];
		}
		
		$body = "<tr bol_id=\"$lading_bill_id$parent_id\" truck_id=\"$truck_id\" lading_bill_id=\"$lading_bill_id\" class=\"trucks_tr\" id=\"$lading_bill_id--$material_id\"
			carrier_id=\"$carrier_id\" weight_per=\"$weight_per\" parent_id=\"$parent_id\" row_id=\"$lading_bill_id$parent_id$carrier_id$truck_id\">
			<td id=\"tb_col_a\" class=\"tb_col\" style=\"width:5%;padding-right:5px;text-align:left;\"> <div id=\"bol_id--$lading_bill_id$parent_id$carrier_id$truck_id\" class=\"tb_row\">$lading_bill_id</div></td>
			<td id=\"tb_col_d\" class=\"tb_col\" style=\"width:100%;\"> <div id=\"truck_description--$lading_bill_id$parent_id$carrier_id$truck_id\" class=\"tb_row\">$parent_description</div></td>
			<td id=\"tb_col_b\" class=\"tb_col\" style=\"width:50%;text-align:center;\"> <div id=\"carrier--$lading_bill_id$parent_id$carrier_id$truck_id\" class=\"tb_row\">$carrier_name</div></td>
			<td id=\"tb_col_c\" class=\"tb_col\" style=\"width:20%;text-align:right;\"> <div id=\"truck--$lading_bill_id$parent_id$carrier_id$truck_id\" class=\"tb_row\">$truck_name</div></td>
			<td id=\"tb_col_d\" class=\"tb_col\" style=\"width:15%;text-align:center;\"> <div id=\"quantity--$lading_bill_id$parent_id$carrier_id$truck_id\" class=\"tb_row\">$quantity</div></td>
			<td id=\"tb_col_e\" class=\"tb_col\" style=\"width:15%;text-align:right;\"> <div id=\"weight--$lading_bill_id$parent_id$carrier_id$truck_id\" class=\"tb_row\">$weight</div></td>
			</tr>";
			$message = $message.$body;
}	
		
		return $message;
	}
public function freight_bills_material_count($input)
	{
		$lading_bill_id = $input['lading_bill_id'];
		require_once "database.php";

		$result = mysql_query("SELECT *, materials.weight AS materials_weight FROM materials
		INNER JOIN freight_bills_materials ON freight_bills_materials.material_id = materials.id
		INNER JOIN freight_bills ON freight_bills.id = freight_bills_materials.freight_bill_id
		INNER JOIN trucks ON trucks.id = freight_bills.truck_id
		INNER JOIN lading_bills_freight_bills ON lading_bills_freight_bills.freight_bill_id = freight_bills.id
		INNER JOIN carriers ON carriers.id = trucks.carrier_id
		WHERE lading_bills_freight_bills.lading_bill_id = '$lading_bill_id'
		ORDER BY freight_bills_materials.material_id DESC
		");

		$freight_bills_material_count = 0;
	
		while($row = mysql_fetch_array($result))
		{
			$material_id = '';
			$trucks_material_count = 0;
			$quantity=$row['quantity'];
			$weight=$row['materials_weight'];
			$parent_id=$row['parent_id'];
			$new_lading_bill_id=$row['lading_bill_id'];
			$material_id=$row['material_id'];
			$description=$row['description'];
			$freight_bill_id=$row['freight_bill_id'];
			$truck_id=$row['truck_id'];
			$truck_name=$row['unitnumber'];
			$carrier_id=$row['carrier_id'];
			$carrier_name=$row['name'];
			$weight_per = $weight / $quantity;
		
			if ($new_lading_bill_id == $lading_bill_id ){
					$freight_bills_material_count = $freight_bills_material_count + $quantity;
				}
			
		}	
		
		return $freight_bills_material_count;
	}
public function clear_shipment($input){
		$parent_id=$input['parent_id'];
		$carrier_id=$input['carrier_id'];
		$truck_id=$input['truck_id'];
		$quantity=$input['quantity'];
		$weight=$input['weight'];
		$lading_bill_id=$input['bol_id'];
		
		require_once "database.php";
		
		$result = mysql_query("DELETE FROM freight_bills_materials WHERE material_id='$parent_id'");
		$result = mysql_query("DELETE FROM materials WHERE parent_id='$parent_id'");
		$lading_result = mysql_query("SELECT * FROM lading_bills_freight_bills WHERE lading_bill_id='$lading_bill_id'");
		$result = mysql_query("DELETE FROM lading_bills_freight_bills WHERE lading_bill_id='$lading_bill_id'");
		
		while($row = mysql_fetch_array($lading_result))
		{
			$freight_bill_id = $row['freight_bill_id'];
			$result = mysql_query("DELETE FROM freight_bills WHERE id='$freight_bill_id'");
		}
		
		return $result;
}
public function finish($input){

		$parent_id=$input['parent_id'];
		$carrier_id=$input['carrier_id'];
		$truck_id=$input['truck_id'];
		$quantity=$input['quantity'];
		$weight=$input['weight'];
		$lading_bill_id=$input['bol_id'];
		
		$result = 0;
		require_once "database.php";
				
		
		/*
		$result = mysql_query("SELECT * FROM materials
		INNER JOIN freight_bills_materials ON freight_bills_materials.material_id = materials.parent_id
		INNER JOIN freight_bills ON freight_bills.id = freight_bills_materials.freight_bill_id
		INNER JOIN trucks ON trucks.id = freight_bills.truck_id
		INNER JOIN lading_bills_freight_bills ON  lading_bills_freight_bills.freight_bill_id = freight_bills.id
		INNER JOIN carriers ON carriers.id = trucks.carrier_id
		WHERE lading_bills_freight_bills.lading_bill_id = '$lading_bill_id'
		");
		*/
		
		
		//$result = mysql_query("INSERT INTO trucks (id,truck_id,carrier_id)values(NULL,'$truck_id','$carrier_id')");
		//$trucks_id = mysql_insert_id();
		if ($quantity >= 1 && $quantity != '0'){
			$result = mysql_query("INSERT INTO freight_bills (id,truck_id)values(NULL,'$truck_id')");
			$freight_bill_id = mysql_insert_id();
			$result = mysql_query("INSERT INTO lading_bills_freight_bills (id,freight_bill_id,lading_bill_id)values(NULL,'$freight_bill_id','$lading_bill_id')");
			$lading_bills_freight_bill_id = mysql_insert_id();
			$result = mysql_query("INSERT INTO materials(id,quantity,parent_id,weight)values(NULL,'$quantity','$parent_id','$weight')");
			$material_id = mysql_insert_id();
			$result = mysql_query("INSERT INTO freight_bills_materials(id,freight_bill_id,material_id)values(NULL,'$freight_bill_id','$material_id')");
			$freight_bills_materials_id = mysql_insert_id();
		}
		return $result;
} 
public function carrier_list($input)
	{
			
		$carrier_type=$input['carrier_type'];
		
		require_once "database.php";
	
		$message = '';
		
	
			$result = mysql_query("SELECT * FROM carriers
			INNER JOIN trucks ON trucks.carrier_id = carriers.id
			GROUP BY trucks.id
			");	

			while($row = mysql_fetch_array($result))

		{
			$name = $row['name'];
			$id = $row['carrier_id'];

			$message = $message. '<option value="'.$id.'">'.$name.'</option>';
		}
		
		return $message;
	}
public function truck_list($input)
	{
		$carrier_id=$input['carrier_id'];

		require_once "database.php";
	
		$message = '';
	
			$result = mysql_query("SELECT * FROM trucks WHERE carrier_id = '$carrier_id'");	
			//if (mysql_num_rows($result) == 0){$message = $message. '<option value="'.$id.'">'.$name.'</option>';}
			while($row = mysql_fetch_array($result))

			{
				$name = $row['unitnumber'];
				$id = $row['id'];
				$message = $message. '<option value="'.$id.'">'.$name.'</option>';
			}

		return $message;
	}

public function lading_bill_is_locked($input)
	{
		$lading_bill_id=$input['lading_bill_id'];

		require_once "database.php";
	
		$message = '';
	
			$result = mysql_query("SELECT * FROM lading_bills WHERE id = '$lading_bill_id'");	
			while($row = mysql_fetch_array($result))

			{
				$message = $row['is_locked'];
			}

		return $message;
	}

public function lading_bill_lock($input)
	{
		$lading_bill_id=$input['lading_bill_id'];
		$lock_status=$input['lock_status'];
		require_once "database.php";
	
		$message = '';
	
			$result = mysql_query("UPDATE lading_bills SET is_locked = '$lock_status' WHERE id = '$lading_bill_id'");	

		return $result;
	}
}


class Error
{
    // CATCHABLE ERRORS
    public static function captureNormal( $number, $message, $file, $line )
    {
       	$error = array();
	   
	    // Insert all in one table
        $error = array( 'type' => $number, 'message' => $message, 'file' => $file, 'line' => $line );
       
	   	$server_response['error_msg'] = $error;
	
		header("Content-type: text/javascript");
		header("access-control-allow-origin: *");	
		print_r ($_GET['callback']. '('. json_encode($server_response) . ')'); 
		die();
    }
    
    // EXTENSIONS
    public static function captureException( $exception )
    {
        
		$server_response['error_msg'] = $exception;
	
		header("Content-type: text/javascript");
		header("access-control-allow-origin: *");	
		print_r ($_GET['callback']. '('. json_encode($server_response) . ')'); 
		die();
    }
    
    // UNCATCHABLE ERRORS
    public static function captureShutdown( )
    {
        $error = error_get_last( );
        if( $error ) {
            ## IF YOU WANT TO CLEAR ALL BUFFER, UNCOMMENT NEXT LINE:
            # ob_end_clean( );
            
            // Display content $error variable
           $server_response['error_msg'] = $error;
	
			header("Content-type: text/javascript");
			header("access-control-allow-origin: *");	
			print_r ($_GET['callback']. '('. json_encode($server_response) . ')'); 
			die();
		
        } else { return ''; }
    }
}

?>