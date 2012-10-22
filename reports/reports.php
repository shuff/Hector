
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

$reporting = new reporting;
$doit = '';

if (isset($_GET['function_name']))
{	
	$client_input = array();
	$server_response = array();
	foreach ($_GET as $key => $value)  
	{
		$function_name = mysql_real_escape_string($_GET['function_name']); 
		if ($key != 'function_name')
		{
				
			$value = mysql_real_escape_string($value);  
			$client_input[$key] = $value;
		}
	}
	if ($function_name == 'weekly_dispatches_report'){
		$doit = weekly_dispatches_report($client_input);
	}
	if (method_exists($reporting, $function_name))
	{
		$doit = $reporting->$function_name($client_input);
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

class reporting
{
	public function weekly_reports($input)
	{
	
/* COMMON TO ALL REPORTS */
		$start_date = $input['start_date'];
		$end_date = $input['end_date'];
		$date_range = "Sunday $start_date to Saturday $end_date";
				
		$report_gross_path = "reports/$start_date--$end_date--gross_report.xls";
		$report_earnings_path = "reports/$start_date--$end_date--earnings_report.xlsx";
		$report_driver_path = "reports/$start_date--$end_date--driver_report.xlsx";
		$report_outside_path = "reports/$start_date--$end_date--outside_report.xlsx";
		
		$report_combined_path = "reports/$start_date--$end_date--combined_report.xlsx";

		
		
		$reports = array();
		$reports['report_gross_path']="$report_gross_path";
		$reports['report_gross_name']="Weekly Gross - $date_range";
		
		$reports['report_earnings_path']="$report_earnings_path";
		$reports['report_earnings_name']="Earnings & Payouts - $date_range";
		
		$reports['report_driver_path']="$report_driver_path";
		$reports['report_driver_name']="Driver Pay - $date_range";
		
		$reports['report_outside_path']="$report_outside_path";
		$reports['report_outside_name']="Outside Carrier Gross - $date_range";
		
		$reports['report_combined_path']="$report_combined_path";
		$reports['report_combined']="Combined Report - $date_range";

		require_once "database.php";
		$reporting = new reporting;

		$parent_company = array();
		$parent_company['name']='Company Name';	
		$parent_company['address']='123 Address';
		$parent_company['address2']='Houston, TX 77002';
		$parent_company['fax']='Fax. (713) 123-4567';
		$parent_company['phone']='Tele. (713) 123-4567';

		
		//Error reporting
		error_reporting(E_ALL);
		date_default_timezone_set('America/Chicago');	
		require_once 'libs/PHPExcel.php';
		
		// Address Box Style
		$address_style = array(
		'font' => array(
			'bold' => true,
			'size'=> 14,
		),
		'alignment' => array(
			'horizontal' => 'left'
		)
		);
		
		// Header Style
		$header_style = array(
		'font' => array(
			'bold' => true,
			'size'=> 12,
		),
		'alignment' => array(
			'horizontal' => 'center'
		)
		);
		// Header Left Justified
		$header_left_style = array(
		'font' => array(
			'bold' => true,
			'size'=> 12,
		),
		'alignment' => array(
			'horizontal' => 'left'
		)
		);
		// Center
		$center_style = array(
		'alignment' => array(
			'horizontal' => 'center'
		)
		);
		// Left
		$left_style = array(
		'alignment' => array(
			'horizontal' => 'left'
		)
		);							
		// Right
		$right_style = array(
		'alignment' => array(
			'horizontal' => 'right'
		)
		);	
		// Banner
		$banner_style = array(
		'alignment' => array(
			'horizontal' => 'right'
		),
		'font' => array(
			'bold' => true,
			'size'=> 24,
		)
		);	
		$border_thin = array(
		'borders' => array(
		'outline' => array(
			'style' => PHPExcel_Style_Border::BORDER_THIN,
			'color' => array('argb' => '00000000'),
		),
	),
);



/* COMBINED GROSS SALES REPORT */
		
		$parent_company['report']='WEEKLY GROSS';	

	// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
		$objPHPExcel->getDefaultStyle()->getFont()->setSize(10); 
		$objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);					
		
											
		// Set document properties
		$objPHPExcel->getProperties()->setCreator("Billing Dept")
		 ->setTitle("Combined Weekly Gross Report")
		 ->setSubject($reports['report_gross_name'])
		 ->setDescription("Combined weekly gross income report")
		 ->setKeywords("combined weekly gross income report")
		 ->setCategory("Accounting");	
				
				
		$result = mysql_query("SELECT * FROM dispatches
		INNER JOIN dispatches_freight_bills ON dispatches_freight_bills.dispatch_id = dispatches.id
		INNER JOIN customers ON customers.id = dispatches.customer_id
		INNER JOIN addresses ON addresses.id = customers.address_id
		WHERE dispatches.status = '3' 
		AND DATE(dispatches.delivery_date) <= '$end_date' 
		AND DATE(dispatches.delivery_date) >= '$start_date' 
		ORDER BY dispatches.customer_id DESC");		
		
		$message = '';
		
		$row_i = 17;
		$i = 0;
		$truck_count = 0;
		$last_customer_id = '';
		$sheet_count = 9;
	
		while ($row = mysql_fetch_array($result))
		{
			
			$customer_id = $row['customer_id'];
			$from_address = $row['from_address'];
			$to_address = $row['to_address'];
			$num_trucks = $row['truck_count'];
			$pickup_date = $row['pickup_date'];
			$delivery_date = $row['delivery_date'];
			$dispatch_id = $row['dispatch_id'];
			$freight_bill_id = $row['freight_bill_id'];		
			$name = $row['name'];
		
			$street1 = $row['street1'];
			$street2 = $row['street2'];
			$city = $row['city'];
			$district = $row['district'];
			$region = $row['region'];
			$postcode = $row['postcode'];
			$country = $row['country'];	
			$phone = $row['phone'];
			$invoice = $row['invoice'];	
			
			$output = array();
			$output['table']='addresses';
			$output['field']='region';
			$output['pkey_field']='id';
			$output['pkey_value']=$from_address;
			$origin = $reporting->get_field_value($output);
			
			$output = array();
			$output['table']='addresses';
			$output['field']='region';
			$output['pkey_field']='id';
			$output['pkey_value']=$to_address;
			$destination = $reporting->get_field_value($output);
			
			$companys = mysql_query("SELECT * FROM dispatches
			INNER JOIN dispatches_freight_bills ON dispatches_freight_bills.dispatch_id = dispatches.id
			INNER JOIN customers ON customers.id = dispatches.customer_id
			INNER JOIN addresses ON addresses.id = customers.address_id
			WHERE dispatches.status = '3' 
			AND DATE(dispatches.delivery_date) <= '$end_date' 
			AND DATE(dispatches.delivery_date) >= '$start_date' 
			AND dispatches.customer_id = '$customer_id'");	
			
			$companys_count = mysql_num_rows($companys);
			
			
			//THIS IS THE FIRST COMPANY
			if ($i == -1){
				$objPHPExcel->getActiveSheet()->setTitle(strtoupper($name));
				$i++;
			}
			
			if($i == 0)	// Make first worksheet which is the combined report
				{
				//hide gridlines	
				
				$objWorksheet1 = $objPHPExcel->createSheet(0);
				$objWorksheet1->setTitle(strtoupper('Combined'));
					
				$objPHPExcel->setActiveSheetIndex(0);
				$objPHPExcel->getActiveSheet()->setShowGridlines(false);
					
				$objPHPExcel->setActiveSheetIndex(0)
		        ->setCellValue('A2', $parent_company['name'])
		        ->setCellValue('A3', $parent_company['address'])
				->setCellValue('A4', $parent_company['address2'])
		        ->setCellValue('A5', $parent_company['phone'])
				->setCellValue('A6', $parent_company['fax'])

				->setCellValue('C1', 'INVOICE')
				->setCellValue('C2', 'Week Ending:')
				->setCellValue('C3', $end_date);
				
				//Styling
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A2')->applyFromArray($address_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A3')->applyFromArray($address_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A4')->applyFromArray($address_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A5')->applyFromArray($address_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A6')->applyFromArray($address_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A9')->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('F16')->applyFromArray($header_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('D1')->applyFromArray($header_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('B16')->applyFromArray($header_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('C16')->applyFromArray($header_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('D16')->applyFromArray($header_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A7')->applyFromArray($header_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('C1')->applyFromArray($banner_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('C2')->applyFromArray($header_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('E2')->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('E3')->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A10:A200')->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('B10:B200')->applyFromArray($header_left_style);
				$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
				
					
		
			
	
					
					$i++;
				
				
				}	
			
			
						
			//$row_i is tracking which is the active row.
			if ($customer_id != $last_customer_id) //a new customer, create header info.
			{
			
				$row_i = 17;	
				$companys_countdown = 1;
				$cloned = $objPHPExcel->getActiveSheet()->copy();
				

				
				
				
				
				if($i > 0)	// Need to make a new worksheet
				{
					
					$objWorksheet1 = $objPHPExcel->createSheet($i);
					$objPHPExcel->setActiveSheetIndex($i);
					$objWorksheet1->setTitle(strtoupper($name));	
						
				}
				
								
				// Add Header
				$objPHPExcel->setActiveSheetIndex($i)
		        ->setCellValue('A2', $parent_company['name'])
		        ->setCellValue('A3', $parent_company['address'])
				->setCellValue('A4', $parent_company['address2'])
		        ->setCellValue('A5', $parent_company['phone'])
				->setCellValue('A6', $parent_company['fax'])

				->setCellValue('A9', 'Bill To')
				->setCellValue('A10', $name)	
				->setCellValue('A11', "$street1 $street2")
				->setCellValue('A12', "$city, $district  $region $postcode $country")
				->setCellValue('A13', $phone)
				->setCellValue('A16', 'Invoice')
				//->setCellValue('A7', $end_date)
				->setCellValue('B16', 'Origin')
				->setCellValue('C16', 'Destination')
				->setCellValue('D16', 'Amount')
				->setCellValue('B17', $origin)
				->setCellValue('C17', $destination)
				->setCellValue('C1', 'INVOICE')
				->setCellValue('C2', 'Week Ending:')
				->setCellValue('C3', $end_date)
				
				->setCellValue("D$row_i", $invoice)
				->setCellValue("A$row_i", $freight_bill_id);
				
				//Styling
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A2')->applyFromArray($address_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A3')->applyFromArray($address_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A4')->applyFromArray($address_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A5')->applyFromArray($address_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A6')->applyFromArray($address_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A9')->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A16')->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('F16')->applyFromArray($header_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('D1')->applyFromArray($header_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('B16')->applyFromArray($header_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('C16')->applyFromArray($header_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('D16')->applyFromArray($header_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A7')->applyFromArray($header_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('C1')->applyFromArray($banner_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('C2')->applyFromArray($header_style);
				
				
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('E2')->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('E3')->applyFromArray($header_left_style);

				

		


}
			
			
			
			else
			{ // This is the same company
			

				$objPHPExcel->setActiveSheetIndex($i)
				->setCellValue("D$row_i", $invoice)
				->setCellValue("A$row_i", $freight_bill_id);
				
			}		
			if( $companys_countdown == $companys_count){
				$final_row = $row_i;
				$row_i = $row_i+4;
				
				$sub_total_col = 'C'.($row_i+1);
				$other_col = 'C'.($row_i+2);
				$total_col = 'C'.($row_i+3);
				
				$sub_total_col_val = 'D'.($row_i+1);
				$other_col_val = 'D'.($row_i+2);
				$total_col_val = 'D'.($row_i+3);
			
			
				
				$objPHPExcel->setActiveSheetIndex($i)
		
				->setCellValue($sub_total_col,'Sub Total:')	
				->setCellValue($sub_total_col_val,"=SUM(D17:D$final_row)")
				->setCellValue($other_col,'Other:')			
				->setCellValue($other_col_val,"0")
				->setCellValue($total_col,'Total:')	
				->setCellValue($total_col_val,"=$sub_total_col_val+$other_col_val");	
		
				
		//styling		
		
		
		$objPHPExcel->setActiveSheetIndex($i)->getStyle($total_col)->applyFromArray($header_style);
		$objPHPExcel->setActiveSheetIndex($i)->getStyle('A10:A'.$row_i)->applyFromArray($left_style);
		$objPHPExcel->setActiveSheetIndex($i)->getStyle('A17:D'.$row_i)->applyFromArray($border_thin);			
		$objPHPExcel->setActiveSheetIndex($i)->getStyle("$sub_total_col:$total_col_val")->applyFromArray($border_thin);			
		$objPHPExcel->setActiveSheetIndex($i)->getStyle("A16:D16")->applyFromArray($border_thin);			
		$objPHPExcel->getActiveSheet()->getStyle("D17:".$total_col_val)->getNumberFormat()->setFormatCode('$#,##0.00');
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
		
	

			$sheet_count++;
			$company_c = 'A'.$sheet_count;
			$company_t = 'B'.$sheet_count;
					
				$thetotal = $objPHPExcel->setActiveSheetIndex($i)
				->getCell($total_col_val)->getCalculatedValue();
				
				$mytotal = $objPHPExcel->setActiveSheetIndex(0)
		        ->setCellValue($company_c, $name)
				->setCellValue($company_t,$thetotal);		
			}	
			$last_customer_id = $customer_id;
			$row_i++;
			$companys_countdown++;	
		}	

		


		
		
		$sheet_count++;
		$company_c = 'A'.$sheet_count;
		$company_t = 'B'.$sheet_count;
		$last_row = $sheet_count -1;
		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue($company_c, 'Total')
		->setCellValue($company_t,"=SUM(B10:B$last_row)");	
		
		$objPHPExcel->setActiveSheetIndex(0)->getStyle($company_c)->applyFromArray($header_left_style);					
		$objPHPExcel->getActiveSheet()->getStyle('B10:B200')->getNumberFormat()->setFormatCode('$#,##0.00');
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
		
		
		// Save Excel 2007 file
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save(str_replace('.php', '.xls', $report_gross_path));

/* GROSS SALES REPORT 
		
		$parent_company['report']='WEEKLY GROSS';	

	// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
		$objPHPExcel->getDefaultStyle()->getFont()->setSize(10); 
		$objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);					
		
											
		// Set document properties
		$objPHPExcel->getProperties()->setCreator("Billing Dept")
		 ->setTitle("Weekly Gross Report")
		 ->setSubject($reports['report_gross_name'])
		 ->setDescription("Weekly gross income report")
		 ->setKeywords("weekly gross income report")
		 ->setCategory("Accounting");	
				
				
		$result = mysql_query("SELECT * FROM dispatches
		INNER JOIN dispatches_freight_bills ON dispatches_freight_bills.dispatch_id = dispatches.id
		INNER JOIN customers ON customers.id = dispatches.customer_id
		INNER JOIN addresses ON addresses.id = customers.address_id
		WHERE dispatches.status = '3' 
		AND DATE(dispatches.delivery_date) <= '$end_date' 
		AND DATE(dispatches.delivery_date) >= '$start_date' 
		ORDER BY dispatches.customer_id DESC");		
		
		$message = '';
		
		$row_i = 17;
		$i = -1;
		$truck_count = 0;
		$last_customer_id = '';

	
		while ($row = mysql_fetch_array($result))
		{
			
			$customer_id = $row['customer_id'];
			$from_address = $row['from_address'];
			$to_address = $row['to_address'];
			$num_trucks = $row['truck_count'];
			$pickup_date = $row['pickup_date'];
			$delivery_date = $row['delivery_date'];
			$dispatch_id = $row['dispatch_id'];
			$freight_bill_id = $row['freight_bill_id'];		
			$name = $row['name'];
		
			$street1 = $row['street1'];
			$street2 = $row['street2'];
			$city = $row['city'];
			$district = $row['district'];
			$region = $row['region'];
			$postcode = $row['postcode'];
			$country = $row['country'];	
			$phone = $row['phone'];
			$invoice = $row['invoice'];	
			
			$output = array();
			$output['table']='addresses';
			$output['field']='region';
			$output['pkey_field']='id';
			$output['pkey_value']=$from_address;
			$origin = $reporting->get_field_value($output);
			
			$output = array();
			$output['table']='addresses';
			$output['field']='region';
			$output['pkey_field']='id';
			$output['pkey_value']=$to_address;
			$destination = $reporting->get_field_value($output);
			
			$companys = mysql_query("SELECT * FROM dispatches
			INNER JOIN dispatches_freight_bills ON dispatches_freight_bills.dispatch_id = dispatches.id
			INNER JOIN customers ON customers.id = dispatches.customer_id
			INNER JOIN addresses ON addresses.id = customers.address_id
			WHERE dispatches.status = '3' 
			AND DATE(dispatches.delivery_date) <= '$end_date' 
			AND DATE(dispatches.delivery_date) >= '$start_date' 
			AND dispatches.customer_id = '$customer_id'");	
			
			$companys_count = mysql_num_rows($companys);
			
			
			//THIS IS THE FIRST COMPANY
			if ($i == -1){
				$objPHPExcel->getActiveSheet()->setTitle(strtoupper($name));
			}
						
			//$row_i is tracking which is the active row.
			if ($customer_id != $last_customer_id) //a new customer, create header info.
			{
				$i++;
				$row_i = 17;	
				$companys_countdown = 1;
				
				//hide gridlines	
				$objPHPExcel->getActiveSheet()->setShowGridlines(false);
				
				if($i > 0)	// Need to make a new worksheet
				{
					
					$objWorksheet1 = $objPHPExcel->createSheet();
					$objWorksheet1->setTitle(strtoupper($name));					
				}	
								
				// Add Header
				$objPHPExcel->setActiveSheetIndex($i)
		        ->setCellValue('A2', $parent_company['name'])
		        ->setCellValue('A3', $parent_company['address'])
				->setCellValue('A4', $parent_company['address2'])
		        ->setCellValue('A5', $parent_company['phone'])
				->setCellValue('A6', $parent_company['fax'])

				->setCellValue('A9', 'Bill To')
				->setCellValue('A10', $name)	
				->setCellValue('A11', "$street1 $street2")
				->setCellValue('A12', "$city, $district  $region $postcode $country")
				->setCellValue('A13', $phone)
				->setCellValue('A16', 'Invoice')
				//->setCellValue('A7', $end_date)
				->setCellValue('B16', 'Origin')
				->setCellValue('C16', 'Destination')
				->setCellValue('D16', 'Amount')
				->setCellValue('B17', $origin)
				->setCellValue('C17', $destination)
				->setCellValue('C1', 'INVOICE')
				->setCellValue('C2', 'Week Ending:')
				->setCellValue('C3', $end_date)
				
				->setCellValue("D$row_i", $invoice)
				->setCellValue("A$row_i", $freight_bill_id);
				
				//Styling
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A2')->applyFromArray($address_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A3')->applyFromArray($address_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A4')->applyFromArray($address_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A5')->applyFromArray($address_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A6')->applyFromArray($address_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A9')->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A16')->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('F16')->applyFromArray($header_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('D1')->applyFromArray($header_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('B16')->applyFromArray($header_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('C16')->applyFromArray($header_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('D16')->applyFromArray($header_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A7')->applyFromArray($header_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('C1')->applyFromArray($banner_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('C2')->applyFromArray($header_style);
				
				
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('E2')->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('E3')->applyFromArray($header_left_style);

				$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);

	
			}
			else
			{ // This is the same company
			

				$objPHPExcel->setActiveSheetIndex($i)
				->setCellValue("D$row_i", $invoice)
				->setCellValue("A$row_i", $freight_bill_id);
				
			}		
			if( $companys_countdown == $companys_count){
				$final_row = $row_i;
				$row_i = $row_i+4;
				
				$sub_total_col = 'C'.($row_i+1);
				$other_col = 'C'.($row_i+2);
				$total_col = 'C'.($row_i+3);
				
				$sub_total_col_val = 'D'.($row_i+1);
				$other_col_val = 'D'.($row_i+2);
				$total_col_val = 'D'.($row_i+3);
			
				$objPHPExcel->setActiveSheetIndex($i)
	
				->setCellValue($sub_total_col,'Sub Total:')	
				->setCellValue($sub_total_col_val,"=SUM(D17:D$final_row)")
				->setCellValue($other_col,'Other:')			
				->setCellValue($other_col_val,"0")
				->setCellValue($total_col,'Total:')	
				->setCellValue($total_col_val,"=$sub_total_col_val+$other_col_val");	
		
		//styling		
		$objPHPExcel->setActiveSheetIndex($i)->getStyle($total_col)->applyFromArray($header_style);
		$objPHPExcel->setActiveSheetIndex($i)->getStyle('A10:A'.$row_i)->applyFromArray($left_style);
		$objPHPExcel->setActiveSheetIndex($i)->getStyle('A17:D'.$row_i)->applyFromArray($border_thin);			
		$objPHPExcel->setActiveSheetIndex($i)->getStyle("$sub_total_col:$total_col_val")->applyFromArray($border_thin);			
		$objPHPExcel->setActiveSheetIndex($i)->getStyle("A16:D16")->applyFromArray($border_thin);			
		$objPHPExcel->getActiveSheet()->getStyle("D17:".$total_col_val)->getNumberFormat()->setFormatCode('$#,##0.00');
				
			}	
			$last_customer_id = $customer_id;
			$row_i++;
			$companys_countdown++;		
		}	

		


		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
		
		
		// Save Excel 2007 file
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save(str_replace('.php', '.xls', $report_gross_path));
	*/	
		
/* WEEKLY EARNINGS & PAYOUT */
		
		$parent_company['report']='Weekly Earnings & Payout';				
	
		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
		$objPHPExcel->getDefaultStyle()->getFont()->setSize(10); 
		$objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);					
		
		// Set document properties
		$objPHPExcel->getProperties()->setCreator("Billing Dept")
		 ->setTitle("Weekly earnings and payouts")
		 ->setSubject($reports['report_earnings_name'])
		 ->setDescription("Weekly earnings and payouts")
		 ->setKeywords("Weekly earnings and payouts")
		 ->setCategory("Accounting");	
		 
		 
		 $result3 = mysql_query("SELECT SUM(invoice) as invoices FROM dispatches
		WHERE dispatches.status = '3' 
		AND DATE(dispatches.delivery_date) <= '$end_date' 
		AND DATE(dispatches.delivery_date) >= '$start_date'");
		while ($row3 = mysql_fetch_array($result3))
		{
			$invoices = $row3['invoices'];
		}
		$result = mysql_query("SELECT * FROM dispatches
		INNER JOIN dispatches_freight_bills ON dispatches_freight_bills.dispatch_id = dispatches.id
		INNER JOIN freight_bills ON freight_bills.id = dispatches_freight_bills.freight_bill_id
		INNER JOIN drivers ON drivers.id = freight_bills.driver_id
		WHERE dispatches.status = '3' 
		AND DATE(dispatches.delivery_date) <= '$end_date' 
		AND DATE(dispatches.delivery_date) >= '$start_date' 
		GROUP BY freight_bills.driver_id DESC");		

		$message = '';
			
		$i = 0;
		$gross_count = 0;
		$last_driver_id = '';
		$row_i = 12;
		$total=0;
		while ($row = mysql_fetch_array($result))
		{
				
			$driver_id = $row['driver_id'];
			$from_address = $row['from_address'];
			$to_address = $row['to_address'];
			$truck_id = $row['truck_id'];
			$trailer_id = $row['trailer_id'];	
			$pickup_date = $row['pickup_date'];
			$delivery_date = $row['delivery_date'];
			$dispatch_id = $row['dispatch_id'];
			$freight_bill_id = $row['freight_bill_id'];		
			$name = $row['name'];
			$number = $row['number'];
			$driver_pay = $row['driver_pay'];
			$truck_pay = $row['truck_pay'];		
				
			
			$output = array();
			$output['table']='addresses';
			$output['field']='region';
			$output['pkey_field']='id';
			$output['pkey_value']=$from_address;
			$origin = $reporting->get_field_value($output);
			
			$output = array();
			$output['table']='addresses';
			$output['field']='region';
			$output['pkey_field']='id';
			$output['pkey_value']=$to_address;
			$destination = $reporting->get_field_value($output);
			
			$output = array();
			$output['table']='freight_bills';
			$output['field']='status';
			$output['pkey_field']='id';
			$output['pkey_value']=$freight_bill_id;
			$freight_status = $reporting->get_field_value($output);
			
			$output = array();
			$output['table']='trucks';
			$output['field']='carrier_id';
			$output['pkey_field']='id';
			$output['pkey_value']=$truck_id;
			$carrier_id = $reporting->get_field_value($output);
			
			$output = array();
			$output['table']='carriers';
			$output['field']='name';
			$output['pkey_field']='id';
			$output['pkey_value']=$carrier_id;
			$carrier_name = $reporting->get_field_value($output);
			
			//$invoices= $row['invoices'];
			
			$total = $total+$invoices;
			if($last_driver_id != $driver_id) // THIS IS A NEW DRIVER
			{
				$result2 = mysql_query("SELECT SUM(truck_pay) AS carrier_pay FROM freight_bills 
				INNER JOIN dispatches_freight_bills ON dispatches_freight_bills.freight_bill_id = freight_bills.id
				INNER JOIN dispatches ON dispatches.id = dispatches_freight_bills.dispatch_id
				WHERE driver_id='$driver_id'
				AND DATE(dispatches.delivery_date) <= '$end_date' 
				AND DATE(dispatches.delivery_date) >= '$start_date' 
				ORDER BY dispatches.id
				");
				
				while($row2 = mysql_fetch_array($result2))
				{
					$sum = $row2['carrier_pay'];
				}	
				$objPHPExcel->setActiveSheetIndex(0)
	            ->setCellValue("A$row_i", $carrier_name)
				->setCellValue("C$row_i", $sum);
	            
			}

			if( $i+1 >= mysql_num_rows($result)){
				$row_i = $row_i+2;
				$total_payout_col = 'B'.($row_i+1);
				$total_payout_col_value = 'C'.($row_i+1);
			
				$broker_col = 'B'.($row_i+2);
				$broker_col_value = 'C'.($row_i+2);
				
				$profit_col = 'B'.($row_i+3);
				$profit_col_value = 'C'.($row_i+3);				
			
				$sum_end = 'C'.$row_i;
				$objPHPExcel->setActiveSheetIndex(0)
	
				->setCellValue($total_payout_col,'Total Payout:')			
				->setCellValue($total_payout_col_value,"=SUM(C11:$sum_end)")
				->setCellValue($broker_col,'Broker:')			
				->setCellValue($broker_col_value,"=D5*.04")
				->setCellValue($profit_col,'Total Profit:')			
				->setCellValue($profit_col_value,"=D5-$broker_col_value-$total_payout_col_value");;			
			}				
			$row_i++;
			$last_driver_id = $driver_id;
			$i++;	
			}

			// Add some data
			$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', $parent_company['name'])
            ->setCellValue('A2', $parent_company['address'])
			->setCellValue('A3', $parent_company['address2'])
            ->setCellValue('A4', $parent_company['phone'])
            ->setCellValue('D1', $parent_company['report'])
			->setCellValue("B10", 'Check#')
			->setCellValue("C10", 'Actual Pay')
			->setCellValue("D4", 'Gross:')
			->setCellValue("D5", $total)
			->setCellValue('D2', 'Week End Date:')
			->setCellValue('D3', $end_date)
			->setCellValue('A10', 'Name');			
			
			// Styling 	
			$objPHPExcel->setActiveSheetIndex(0)->getStyle($total_payout_col_value)->applyFromArray($right_style);
			$objPHPExcel->getActiveSheet()->getStyle($total_payout_col_value)->getNumberFormat()->setFormatCode('$#,##0.00');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle($total_payout_col)->applyFromArray($header_left_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle($broker_col_value)->applyFromArray($right_style);
			$objPHPExcel->getActiveSheet()->getStyle($broker_col_value)->getNumberFormat()->setFormatCode('$#,##0.00');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle($broker_col)->applyFromArray($header_left_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle($profit_col)->applyFromArray($header_left_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle($profit_col_value)->applyFromArray($right_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('B10')->applyFromArray($header_style);		
			$objPHPExcel->getActiveSheet()->getStyle($profit_col_value)->getNumberFormat()->setFormatCode('$#,##0.00');		
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('A10')->applyFromArray($header_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('D6')->applyFromArray($header_style);
			$objPHPExcel->getActiveSheet()->getStyle("E5")->getNumberFormat()->setFormatCode('$#,##0.00');
			$objPHPExcel->getActiveSheet()->getStyle("C")->getNumberFormat()->setFormatCode('$#,##0.00');
			$objPHPExcel->getActiveSheet()->getStyle("D5")->getNumberFormat()->setFormatCode('$#,##0.00');
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('E6')->applyFromArray($right_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('C10')->applyFromArray($header_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('D1')->applyFromArray($banner_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('D2')->applyFromArray($header_left_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('D4')->applyFromArray($header_left_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('D10')->applyFromArray($header_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('G6')->applyFromArray($header_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('E2:E5')->applyFromArray($left_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle("A7:A$row_i")->applyFromArray($left_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('A1:A6')->applyFromArray($address_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('D')->applyFromArray($left_style);
			$objPHPExcel->setActiveSheetIndex(0)->getStyle('D5')->applyFromArray($left_style);
				
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
			//$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);	
			//$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);		
		//	$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
		//	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
				
			$objPHPExcel->getActiveSheet()->getStyle("D12:D$row_i")->getNumberFormat()->setFormatCode('$#,##0.00');

			// Rename worksheet
			$objPHPExcel->getActiveSheet()->setTitle('WEEKLY EARNINGS & PAYOUTS');
			$objPHPExcel->setActiveSheetIndex(0);
			// Save Excel 2007 file
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save(str_replace('.php', '.xlsx', $report_earnings_path));
			
/* DRIVER PAY REPORT */
	
		$parent_company['report']='DRIVER PAY';				
			
		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
		$objPHPExcel->getDefaultStyle()->getFont()->setSize(10); 
		$objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					
		// Set document properties
		$objPHPExcel->getProperties()->setCreator("Billing Dept")
		 ->setTitle("Weekly Driver Pay Report")
		 ->setSubject($reports['report_driver_name'])
		 ->setDescription("Weekly driver pay report")
		 ->setKeywords("weekly driver pay report")
		 ->setCategory("Accounting");	
		
		$result = mysql_query("SELECT * FROM dispatches
		INNER JOIN dispatches_freight_bills ON dispatches_freight_bills.dispatch_id = dispatches.id
		INNER JOIN freight_bills ON freight_bills.id = dispatches_freight_bills.freight_bill_id
		INNER JOIN drivers ON drivers.id = freight_bills.driver_id
		WHERE dispatches.status = '3' 
		AND DATE(dispatches.delivery_date) <= '$end_date' 
		AND DATE(dispatches.delivery_date) >= '$start_date' 
		ORDER BY freight_bills.driver_id DESC");		
		
		$message = '';
		
	
		$i = -1;
		$last_driver_id = '';
		$drivers_total =0;
		$drivers_countdown=0;
		while ($row = mysql_fetch_array($result))
		{
		
			$driver_id = $row['driver_id'];
			$from_address = $row['from_address'];
			$to_address = $row['to_address'];
			$truck_id = $row['truck_id'];
			$trailer_id = $row['trailer_id'];	
			$pickup_date = $row['pickup_date'];
			$delivery_date = $row['delivery_date'];
			$dispatch_id = $row['dispatch_id'];
			$freight_bill_id = $row['freight_bill_id'];		
			$name = $row['name'];
			$number = $row['number'];
			$driver_pay = $row['driver_pay'];
			$truck_pay = $row['truck_pay'];
			$invoice = $row['invoice'];	

			$output = array();
			$output['table']='addresses';
			$output['field']='region';
			$output['pkey_field']='id';
			$output['pkey_value']=$from_address;
			$origin = $reporting->get_field_value($output);
			
			$output = array();
			$output['table']='addresses';
			$output['field']='region';
			$output['pkey_field']='id';
			$output['pkey_value']=$to_address;
			$destination = $reporting->get_field_value($output);
			
			$output = array();
			$output['table']='freight_bills';
			$output['field']='status';
			$output['pkey_field']='id';
			$output['pkey_value']=$freight_bill_id;
			$freight_status = $reporting->get_field_value($output);
			
			//THIS IS THE FIRST DRIVER
			if ($i == -1){
				$objPHPExcel->getActiveSheet()->setTitle(strtoupper($name));
			}
			
			if($last_driver_id != $driver_id) // THIS IS A NEW DRIVER
			{
				$i++;	
				$drivers_total = 0;	
				$drivers_countdown = 1;
				
				if($i > 0)	// Need to make a new worksheet
				{
					$objWorksheet1 = $objPHPExcel->createSheet();
					$objWorksheet1->setTitle(strtoupper($name));					
				}	
				
				$drivers_total_result = mysql_query("SELECT * FROM freight_bills
				INNER JOIN dispatches_freight_bills ON dispatches_freight_bills.freight_bill_id = freight_bills.id
				INNER JOIN dispatches ON dispatches.id = dispatches_freight_bills.dispatch_id
				WHERE dispatches.status = '3' 
				AND DATE(dispatches.delivery_date) <= '$end_date' 
				AND DATE(dispatches.delivery_date) >= '$start_date' 
				AND freight_bills.driver_id = '$driver_id'
				ORDER BY freight_bills.driver_id DESC");
				
				$drivers_total = mysql_num_rows($drivers_total_result);
		
				$row_i = 13;
					
				// Add some data
				$objPHPExcel->setActiveSheetIndex($i)
	            ->setCellValue('A1', $parent_company['name'])
	            ->setCellValue('A2', $parent_company['address'])
				->setCellValue('A3', $parent_company['address2'])
	            ->setCellValue('A4', $parent_company['phone'])
	            ->setCellValue('A6', $parent_company['report'])
				->setCellValue('A8', 'Pay Ending:')
				->setCellValue('B8', $end_date)
				->setCellValue('A10', 'Driver Name:')
				->setCellValue('B10', $name)
				->setCellValue('A12', 'Date')
				->setCellValue('B12', 'From')
				->setCellValue('C12', 'Destination')
				->setCellValue('D12', 'Invoice#')
				->setCellValue('E12', 'Amount')
				->setCellValue('F12', 'Driver Pay');
			}
	
				$objPHPExcel->setActiveSheetIndex($i)
				->setCellValue("A$row_i", $delivery_date)
				->setCellValue("B$row_i", $origin)
				->setCellValue("C$row_i", $destination)
				->setCellValue("D$row_i", $freight_bill_id)
				->setCellValue("E$row_i", $truck_pay)
				->setCellValue("F$row_i", $driver_pay);
	
			if( $drivers_total == $drivers_countdown){ // That's the last rec for this driver, make the totals.
				//$row_i = $row_i+1;
				//$sum_end = "E".$row_i+1;
				
				$gross_col = 'D'.($row_i+2);
				$company_col = 'D'.($row_i+3);
				$sub_total_col = 'D'.($row_i+4);
				$advance_col = 'D'.($row_i+5);
				$status_col_header = 'C'.($row_i+5);
				$insurance_col = 'D'.($row_i+6);
				$status_col_value = 'C'.($row_i+6);
				$diesel_col = 'D'.($row_i+7);
				$other_col = 'D'.($row_i+8);
				$occ_col = 'D'.($row_i+9);
				
				$total_1_pay_col = 'D'.($row_i+11);
				
				$escrow_bal_col = 'D'.($row_i+13);
				$escrow_col_header = 'A'.($row_i+13);
				$damage_col = 'D'.($row_i+14);
				$total_1_col = 'D'.($row_i+15);			
				$adv_escrow_col = 'D'.($row_i+16);
				$escrow_driver_col_header = 'A'.($row_i+16);
				$total_2_pay_col = 'D'.($row_i+17);
				
				$escrow_2_col  = 'D'.($row_i+19);
				$withdraw_col = 'D'.($row_i+20);
				$total_2_col = 'D'.($row_i+21);
				$other_2_col = 'D'.($row_i+22);
				$final_pay_col = 'D'.($row_i+23);
				
				$gross_col_value = 'E'.($row_i+2);
				$company_col_value = 'E'.($row_i+3);
				$sub_total_col_value = 'E'.($row_i+4);
				$advance_col_value = 'E'.($row_i+5);
				$insurance_col_value = 'E'.($row_i+6);
				$diesel_col_value = 'E'.($row_i+7);
				$other_col_value = 'E'.($row_i+8);
				$occ_col_value = 'E'.($row_i+9);
				
				$total_pay_col_value = 'E'.($row_i+11);
				
				$escrow_bal_col_value = 'E'.($row_i+13);
				$damage_col_value = 'E'.($row_i+14);
				$total_col_value = 'E'.($row_i+15);			
				$adv_escrow_col_value = 'E'.($row_i+16);
				$total_2_pay_col_value = 'E'.($row_i+17);
				
				$escrow_2_bal_col_value  = 'E'.($row_i+19);
				$withdraw_col_value = 'E'.($row_i+20);
				$total_2_col_value = 'E'.($row_i+21);
				$other_2_col_value = 'E'.($row_i+22);
				$final_pay_col_value = 'E'.($row_i+23);
				
				$last_row = $row_i+23;
						
				$sum_end = "E$row_i";
				$objPHPExcel->setActiveSheetIndex($i)
		
				->setCellValue($gross_col,'Gross')
				->setCellValue($company_col,'Company')
				->setCellValue($sub_total_col,'Sub Total')
				->setCellValue($advance_col,'Advance')
				->setCellValue($insurance_col,'Insurance')
				->setCellValue($diesel_col,'Diesel')
				->setCellValue($other_col,'Other')
				->setCellValue($occ_col,'OccAcc Ins.')
				
				->setCellValue($total_1_pay_col,'Total Pay')
				->setCellValue($escrow_bal_col,'Escrow Bal.')
				->setCellValue($damage_col,'Phys. Dam.')
				->setCellValue($total_1_col,'Total')
				->setCellValue($adv_escrow_col,'Advance Escrow')
				->setCellValue($total_2_pay_col,'Total Pay')
				
				->setCellValue($escrow_2_col,'Escrow Bal.')
				->setCellValue($withdraw_col,'Esc. Withdraw')
				->setCellValue($total_2_col,'Total')
				->setCellValue($other_2_col,'Other')
				->setCellValue($final_pay_col,'Total Pay')
				
				->setCellValue($gross_col_value,"=SUM(E13:$sum_end)")
				
				->setCellValue($company_col_value,"=$gross_col_value*.2")
				
				->setCellValue($sub_total_col_value,"=$gross_col_value-$company_col_value")
				
				->setCellValue($advance_col_value,'0')
				->setCellValue($insurance_col_value,'215')
				->setCellValue($diesel_col_value,'0')
				->setCellValue($other_col_value,'0')
				->setCellValue($occ_col_value,'0')
				
				->setCellValue($total_pay_col_value,"=$sub_total_col_value-$advance_col_value-$insurance_col_value-$diesel_col_value-$other_col_value-$occ_col_value")
				
				->setCellValue($escrow_bal_col_value,'0')
				->setCellValue($damage_col_value,'0')
				->setCellValue($total_2_col_value,"=$escrow_bal_col_value-$damage_col_value")
				->setCellValue($adv_escrow_col_value,'0')
				->setCellValue($total_2_pay_col_value,"=$total_2_col_value-$adv_escrow_col_value")
				
				->setCellValue($escrow_2_bal_col_value,'0')
				->setCellValue($withdraw_col_value,'0')
				->setCellValue($total_2_col_value,"=$escrow_2_bal_col_value-$withdraw_col_value")
				->setCellValue($other_2_col_value,'0')
				->setCellValue($final_pay_col_value,"=$total_2_col_value-$other_2_col_value")
				->setCellValue($status_col_header,'Status:')
				->setCellValue($status_col_value,$freight_status)
				->setCellValue($escrow_col_header,'Our Escrow $1800')
				->setCellValue($escrow_driver_col_header,'Driver Escrow $500');
				
				
				//Styling
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($gross_col)->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($company_col)->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($sub_total_col)->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($advance_col)->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($insurance_col)->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($diesel_col)->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($other_col)->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($occ_col)->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($total_1_pay_col)->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($escrow_bal_col)->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($damage_col)->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($total_1_col)->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($adv_escrow_col)->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($total_2_pay_col)->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($escrow_2_col)->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($withdraw_col)->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($total_2_col)->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($damage_col)->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($total_1_col)->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($other_2_col)->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($final_pay_col)->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle("F13:$final_pay_col_value")->applyFromArray($right_style);
				
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A5:A10')->applyFromArray($header_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A12:F12')->applyFromArray($header_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A9')->applyFromArray($header_style);	
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A1:A4')->applyFromArray($address_style);
				
				$objPHPExcel->getActiveSheet()->getStyle("E13:E$last_row")->getNumberFormat()->setFormatCode('$#,##0.00');
				$objPHPExcel->getActiveSheet()->getStyle("F13:F$last_row")->getNumberFormat()->setFormatCode('$#,##0.00');
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);								
					
				
			}

			$row_i++;
			$drivers_countdown++;
			$last_driver_id = $driver_id;
			
		}	
		
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
		// Save Excel 2007 file
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save(str_replace('.php', '.xlsx', $report_driver_path));

/* OUTSIDE CARRIER GROSS REPORT */
			
		$parent_company['report']='OUTSIDE CARRIER GROSS';				
			
		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
		$objPHPExcel->getDefaultStyle()->getFont()->setSize(10); 
		$objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					
		// Set document properties
		$objPHPExcel->getProperties()->setCreator("Billing Dept")
		 ->setTitle("Gross Outside Carrier Report")
		 ->setSubject($reports['report_outside_name'])
		 ->setDescription("Weekly Gross Outside Carrier Report")
		 ->setKeywords("weekly Gross Outside Carrier Report")
		 ->setCategory("Accounting");	
		
		$result = mysql_query("SELECT * FROM dispatches
		INNER JOIN dispatches_freight_bills ON dispatches_freight_bills.dispatch_id = dispatches.id
		INNER JOIN freight_bills ON freight_bills.id = dispatches_freight_bills.freight_bill_id
		INNER JOIN drivers ON drivers.id = freight_bills.driver_id
		WHERE dispatches.status = '3' 
		AND DATE(dispatches.delivery_date) <= '$end_date' 
		AND DATE(dispatches.delivery_date) >= '$start_date' 
		ORDER BY freight_bills.driver_id DESC");		
		
		$message = '';

		$i = -1;
		$last_driver_id = '';
		$drivers_total =0;
		$drivers_countdown=0;
		while ($row = mysql_fetch_array($result))
		{
				
			$driver_id = $row['driver_id'];
			$from_address = $row['from_address'];
			$to_address = $row['to_address'];
			$truck_id = $row['truck_id'];
			$trailer_id = $row['trailer_id'];	
			$pickup_date = $row['pickup_date'];
			$delivery_date = $row['delivery_date'];
			$dispatch_id = $row['dispatch_id'];
			$freight_bill_id = $row['freight_bill_id'];		
			$name = $row['name'];
			$number = $row['number'];
			$driver_pay = $row['driver_pay'];
			$truck_pay = $row['truck_pay'];
			
			$invoice = $row['invoice'];
			$truck_pay = $row['truck_pay'];		
			$driver_pay = $row['driver_pay'];	

			$output = array();
			$output['table']='addresses';
			$output['field']='region';
			$output['pkey_field']='id';
			$output['pkey_value']=$from_address;
			$origin = $reporting->get_field_value($output);
			
			$output = array();
			$output['table']='addresses';
			$output['field']='region';
			$output['pkey_field']='id';
			$output['pkey_value']=$to_address;
			$destination = $reporting->get_field_value($output);
			
			$output = array();
			$output['table']='freight_bills';
			$output['field']='status';
			$output['pkey_field']='id';
			$output['pkey_value']=$freight_bill_id;
			$freight_status = $reporting->get_field_value($output);
			
			$output = array();
			$output['table']='trucks';
			$output['field']='carrier_id';
			$output['pkey_field']='id';
			$output['pkey_value']=$truck_id;
			$carrier_id = $reporting->get_field_value($output);
			
			$output = array();
			$output['table']='carriers';
			$output['field']='name';
			$output['pkey_field']='id';
			$output['pkey_value']=$carrier_id;
			$carrier_name = $reporting->get_field_value($output);
			
			
			//THIS IS THE FIRST DRIVER
			if ($i == -1){
				$objPHPExcel->getActiveSheet()->setTitle(strtoupper($name));
			}
			
			if($last_driver_id != $driver_id) // THIS IS A NEW DRIVER
			{
				$i++;	
				$drivers_total = 0;	
				$drivers_countdown = 1;
				
				$drivers_total_result = mysql_query("SELECT * FROM freight_bills
				INNER JOIN dispatches_freight_bills ON dispatches_freight_bills.freight_bill_id = freight_bills.id
				INNER JOIN dispatches ON dispatches.id = dispatches_freight_bills.dispatch_id
				WHERE dispatches.status = '3' 
				AND DATE(dispatches.delivery_date) <= '$end_date' 
				AND DATE(dispatches.delivery_date) >= '$start_date' 
				AND freight_bills.driver_id = '$driver_id'
				ORDER BY freight_bills.driver_id DESC");
				
				$drivers_total = mysql_num_rows($drivers_total_result);
				
				if ($i > 0)	
				{
					$objWorksheet1 = $objPHPExcel->createSheet(); // CREATE NEW WORKSHEET 
					$objWorksheet1->setTitle(strtoupper($name));
				}	
				
				$row_i = 13;
					
				// Add some data
				$objPHPExcel->setActiveSheetIndex($i)
	            ->setCellValue('A1', $parent_company['name'])
	            ->setCellValue('A2', $parent_company['address'])
				->setCellValue('A3', $parent_company['address2'])
	            ->setCellValue('A4', $parent_company['phone'])
	            ->setCellValue('F1', $parent_company['report'])
				->setCellValue('B2', 'Pay Ending:')
				->setCellValue('B3', $end_date)
				->setCellValue('A9', 'Driver Name:')
				->setCellValue('B9', $name)
				->setCellValue('B10', $carrier_name)
				->setCellValue('A12', 'Date')
				->setCellValue('B12', 'From')
				->setCellValue('C12', 'Destination')
				->setCellValue('D12', 'Invoice#')
				->setCellValue('E12', 'Amount');
			}
	
			$objPHPExcel->setActiveSheetIndex($i)
			->setCellValue("A$row_i", $delivery_date)
			->setCellValue("B$row_i", $origin)
			->setCellValue("C$row_i", $destination)
			->setCellValue("D$row_i", $freight_bill_id)
			->setCellValue("E$row_i", $truck_pay);
	
	
			if( $drivers_total == $drivers_countdown){ // That's the last rec for this driver, make the totals.
				$row_i = $row_i+5;
				//$sum_end = "E".$row_i+1;
				
				$gross_col = 'D'.($row_i+1);
				$gross_col_value = 'E'.($row_i+1);
				
			
			
				$sum_end = "E$row_i";
				$objPHPExcel->setActiveSheetIndex($i)
	
				->setCellValue($gross_col,'Gross:')
				
				->setCellValue($gross_col_value,"=SUM(E13:$sum_end)");
				
				// Styling 
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($gross_col)->applyFromArray($header_left_style);
				$objPHPExcel->getActiveSheet()->getStyle("E13:$gross_col_value")->getNumberFormat()->setFormatCode('$#,##0.00');	
			
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A6:A7')->applyFromArray($header_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A12:F12')->applyFromArray($header_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A9')->applyFromArray($header_style);
		
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A1:A4')->applyFromArray($address_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle("A7:A$row_i")->applyFromArray($left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('F1')->applyFromArray($banner_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('B2')->applyFromArray($header_left_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('B3')->applyFromArray($left_style);
			
				$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);	
				$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);	
			}									
			
			$row_i++;
			$drivers_countdown++;
			$last_driver_id = $driver_id;
							
		}	
	
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
		
		// Save Excel 2007 file
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save(str_replace('.php', '.xlsx', $report_outside_path));		
		
		return $reports;
					
	}
		
	public function weekly_report_list() // build a dynamic list of date ranges (Sun-Sun) based on data in the db.
	{
	
		$last_delivery_date = '';
		$next_sunday = 0;
		$last_sunday = 0;
		$previous_next_sunday = 1;
		$previous_last_sunday = 1;
		$options = '<option value=""></option>';

		date_default_timezone_set('America/Chicago');	
		require_once "database.php";
		$result = mysql_query("SELECT * FROM dispatches ORDER BY delivery_date DESC");
		while ($row = mysql_fetch_array($result)){
			
			$delivery_date = $row['delivery_date'];
			$next_sunday = date('m.d.Y', strtotime('next Saturday', strtotime($delivery_date)));
			$last_sunday = date('m.d.Y', strtotime('last Sunday', strtotime($delivery_date)));			
				
			if ($delivery_date != $last_delivery_date) // not a duplicate
			{
					if ($last_sunday != $previous_last_sunday && $next_sunday != $previous_next_sunday )	// have now included this week range in the list
					{
						$options = $options.'<option value="'.$last_sunday.'_'.$next_sunday.'">Sun. '.$last_sunday.'&nbsp;&nbsp; - &nbsp;&nbsp;Sat. '.$next_sunday.'</option>';
					}
			}

			$last_delivery_date = $delivery_date;
			$previous_next_sunday = $next_sunday;
			$previous_last_sunday = $last_sunday;
					
		}
		
				return $options;
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