<?php


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

/*
ini_set( 'display_errors', 1 );
error_reporting( -1 );
set_error_handler( array( 'Error', 'captureNormal' ) );
set_exception_handler( array( 'Error', 'captureException' ) );
register_shutdown_function( array( 'Error', 'captureShutdown' ) );

// PHP set_error_handler TEST
IMAGINE_CONSTANT;

// PHP set_exception_handler TEST
throw new Exception( 'Imagine Exception' );

// PHP register_shutdown_function TEST ( IF YOU WANT TEST THIS, DELETE PREVIOUS LINE )
imagine_function( );
*/

class foundation
{
	public function pcase($input)
	{
		$string = $input['string'];	
		$string = strtolower($string);
		$string = substr_replace($string, strtoupper(substr($string, 0, 1)), 0, 1);
		return $string;
	}
	public function ucase($input)
	{
		$string = $input['string'];		
		return strtoupper($string);
	}
	public function hello_world($input)
	{
		$my_name = $input['first_name'];
		return $my_name;
	}
	public function get_field_value($input)
	{
		$table = $input['table'];
		$field = $input['field'];
		$pkey_field  = $input['pkey_field'];
		$pkey_value = $input['pkey_value'];
		
		require_once "database.php";
		$result = '';
		$row = '';
		$foundit = '';
		$result = mysql_query("SELECT * FROM $table WHERE $pkey_field = '$pkey_value'");
			
		while($row = mysql_fetch_array($result))
		{
			$foundit = $row[$field];
		}		
			return $foundit;
	}
	public function exists($input)
	{
		$table = $input['table'];
		$field = $input['field'];
		$value = $input['value'];
		
		require_once "database.php";		
		$result = '';
		$row = '';
		$foundit = false;
		$result = mysql_query("SELECT * FROM $table WHERE $field = '$value'");	
		while($row = mysql_fetch_array($result))
		{
			if ($value = $row[$field])
			{
				$foundit = true;
			}
		}	
		return $foundit;
	}	
	public function update_field_by_id($input)
	
	{		
		$table=$input['table'];
		$field=$input['field'];
		$myvalue=$input['myvalue'];
		$pkey=$input['pkey'];
		$pkey_field=$input['pkey_field'];
		
		require_once 'database.php';$action = new action;
		$result = '';
		$row = '';

		$result = mysql_query("UPDATE $table SET $field='$myvalue' WHERE $pkey_field='$pkey'");
		
		return $result;
	}
	public function update_field_by_pkey($input)
	{		
		
		$table = $input['table'];
		$field = $input['field'];
		$myvalue = $input['value'];
		$pkey = $input['pkey'];			
			
				require_once 'database.php';$action = new action;
		$result = '';
		$row = '';

		$result = mysql_query("UPDATE $table SET $field='$myvalue' WHERE pkey='$pkey'");
		
		return $result;
	}	

public function send_email($input)
{

$to = $input['to'];
$from = $input['from'];
$subject = $input['subject'];
$message = $input['message'];

	$headers = "From: $from \r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

	$ok = @mail($to,$subject,$message,$headers);
//	$ok = @mail('rtmshannon@gmail.com',$subject,$message,$headers);

	if($ok) {
		$send_status = true;
		return $send_status;
	}
	else
	{
		$send_status = false;
		return $send_status;
	}

} 	
	
}
?>