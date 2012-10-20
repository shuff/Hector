
<?php

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

$calendar = new calendar;

$doit = '';

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
	if (method_exists($calendar, $function_name))
	{
		$doit = $calendar->$function_name($client_input);
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

class calendar
{
	public function cal()
	{
		require_once "database.php";
		$date_range = array();
		$i =0;			
		$result = mysql_query("SELECT * FROM calendar 
		ORDER BY calendar.date DESC");			
		while ($row = mysql_fetch_array($result)){
					

				$title = stripslashes($row['title']);
				$date = $row['date'];

				
				$cur_date = array(
				'id' => $i,
				'title' => $title,
				'start' => $date,
				'color' => '#000099'
				);
				
				$date_range[$i] = $cur_date;
				
				$i++;
				
			}		
		$result = mysql_query("SELECT * FROM drivers
		INNER JOIN licenses ON licenses.driver_id = drivers.id
		ORDER BY licenses.expiry DESC");	
		while ($row = mysql_fetch_array($result)){
			$title = stripslashes($row['title']);
			$date = $row['expiry'];
			$pkey = $row['id'];	
			$name = $row['name'];
			$title = "$name $title";		
			$cur_date = array(
			'id' => $i,
			'title' => $title,
			'start' => $date,
			'pkey' => $pkey,
			'color' => '#666633'
			);			
			$date_range[$i] = $cur_date;			
			$i++;
		}
		$result = mysql_query("SELECT * FROM trucks");	
		while ($row = mysql_fetch_array($result)){
			$title = stripslashes($row['vin']);
			$date = $row['plateexpiry'];
			$pkey = $row['id'];	
			$title = "$title plate expires";		
			$cur_date = array(
			'id' => $i,
			'title' => $title,
			'start' => $date,
			'pkey' => $pkey,
			'color' => '#FF6600'
			);			
			$date_range[$i] = $cur_date;			
			$i++;
		}		
		return $date_range;
	}	
	public function existing_entries($input)
	{		
		$day = strtotime($input['day']);
		require_once "database.php";
		$date_range = array();
		
		$result = mysql_query("SELECT * FROM calendar WHERE date = '$day'");			
		$i = 0;
		while ($row = mysql_fetch_array($result)){
			$title = stripslashes($row['title']);
			$date = $row['date'];
			$pkey = $row['id'];			
			$cur_date = array(
			'id' => $i,
			'title' => $title,
			'start' => $date,
			'pkey' => $pkey
			);			
			$date_range[$i] = $cur_date;			
			$i++;
		}	 

		return $date_range;
	}
	public function delete_calendar_entry($input)
	{		
		$pkey = $input['pkey'];
		require_once "database.php";		
		$doit = mysql_query("DELETE FROM calendar WHERE id = '$pkey'");	
		return $doit;
	}		
	public function add_calendar_entry($input)
	{		
		$date = $input['date'];
		$title = addslashes($input['title']);
		$date = strtotime($date);
		require_once "database.php";
		$result = mysql_query("DELETE FROM calendar WHERE FROM_UNIXTIME(date) < DATE_SUB(NOW(), INTERVAL 3 MONTH)");
		$doit = mysql_query("INSERT INTO calendar (title,date)values('$title','$date')");	
		return $doit;
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
		print ($_GET['callback']. '('. json_encode($server_response) . ')'); 
		die();
    }
    
    // EXTENSIONS
    public static function captureException( $exception )
    {
        
		$server_response['error_msg'] = $exception;
	
		header("Content-type: text/javascript");
		header("access-control-allow-origin: *");	
		print ($_GET['callback']. '('. json_encode($server_response) . ')'); 
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
			print ($_GET['callback']. '('. json_encode($server_response) . ')'); 
			die();
		
        } else { return ''; }
    }
}

?>