<?php
session_start();
//******************************************************************************
/* place this file outside of the WWW folder, no public access*/
//******************************************************************************

//******************************************************************************
// 									SET VARs & INCLUDES
//******************************************************************************
$login = new login;
//******************************************************************************
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
	if (method_exists($login, $function_name))
	{
		$doit = $login->$function_name($client_input);
	}

	$server_response['message'] = $doit;	
	header("Content-type: text/javascript");
	header("access-control-allow-origin: *");	
	print ($_GET['callback']. '('. json_encode($server_response) . ')'); 
	
}

//******************************************************************************

class login
{
	

	public function exists($input)
	{
		$table = $input['table'];
		$field = $input['field'];
		$value = $input['value'];
		
		$table = trim($table);
		$field = trim($field);
		$value = trim($value);
		
		require_once "database.php";
			$foundit = 'no';
			$result = mysql_query("SELECT * FROM $table WHERE $field = '$value'");
			
			while($row = mysql_fetch_array($result))
			{
				if ($value == $row[$field])
				{
					$foundit = 'yes';
				}
			
			}
			return $foundit;
		
	}
	public function enabled($input)
	{
		$username = $input['username'];
		$username= strtolower($username);
		
		require_once "database.php";
		
			$foundit = 'false';
			$result = mysql_query("SELECT * FROM users WHERE lcase(username) = '$username'");
			
			while($row = mysql_fetch_array($result))
			{
			
				if ($row['enabled'] == '1')
				{
					
					$foundit = 'true';
				}
			
			}
			

		return $foundit;
	}	
	
	public function log_me_in($input)
	{
	
		$login = new login;
		$username = strtolower($input['username']);
		$password = sha1($input['password']);
		
		require_once "database.php";

		$output = array();
		$output['username']=$username;
		$chance = $login->enabled($output);
		
		if ($chance == 'false'){return 'false';} // no good

		$foundit = 'false';
		$result = mysql_query("SELECT * FROM users WHERE lcase(username) = '$username'");
		
		while($row = mysql_fetch_array($result))
		{
			if ($password == $row['password'])
			{				
				$foundit = 'true';
				$_SESSION['client_id'] = $row['id'];
				$_SESSION['uid'] = $row['password'];
				$_SESSION['role'] = $row['role'];
				$_SESSION['username'] = $row['username'];
			}
		
		}
		
		return $foundit;
				
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
//******************************************************************************


	?>