		/* OUTSIDE CARRIER GROSS REPORT */
			
		$parent_company['report']='OUTSIDE CARRIER GROSS';				
			
		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
		$objPHPExcel->getDefaultStyle()->getFont()->setSize(10); 
		$objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					
		// Address Box Style
		$address_style = array(
		'borders' => array(
			'outline' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array('argb' => 'C0C0C0')
			),
		),
		'font' => array(
			'bold' => true,
			'size'=> 15,
		),
		'alignment' => array(
			'horizontal' => 'center'
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
		$header_style_left = array(
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
		GROUP BY drivers.id DESC");		
		
		$message = '';

		$i = 0;
		$last_driver_id = '';
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
			
			if($last_driver_id != $driver_id) // THIS IS A NEW DRIVER
			{
					$row_i = 13;
					
				// Add some data
				$objPHPExcel->setActiveSheetIndex($i)
	            ->setCellValue('A1', $parent_company['name'])
	            ->setCellValue('A2', $parent_company['address'])
				->setCellValue('A3', $parent_company['address2'])
	            ->setCellValue('A4', $parent_company['phone'])
	            ->setCellValue('A6', $parent_company['report'])
				->setCellValue('A7', 'Pay Ending:')
				->setCellValue('B7', $end_date)
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
	
	
			if( $i+1 == mysql_num_rows($result)){
				$row_i = $row_i+5;
				//$sum_end = "E".$row_i+1;
				
				$gross_col = 'D'.($row_i+1);
				$gross_col_value = 'E'.($row_i+1);
				
			
			
				$sum_end = "E$row_i";
				$objPHPExcel->setActiveSheetIndex($i)
	
				->setCellValue($gross_col,'Gross:')
				
				->setCellValue($gross_col_value,"=SUM(E13:$sum_end)");
				
				$objPHPExcel->setActiveSheetIndex(0)->getStyle($gross_col)->applyFromArray($header_style_left);
				$objPHPExcel->getActiveSheet()->getStyle("E13:$gross_col_value")->getNumberFormat()->setFormatCode('$#,##0.00');
				
			}
			
			// Styling 	
			
			$objPHPExcel->setActiveSheetIndex($i)->getStyle('A6:A7')->applyFromArray($header_style);
			$objPHPExcel->setActiveSheetIndex($i)->getStyle('A12:F12')->applyFromArray($header_style);
			$objPHPExcel->setActiveSheetIndex($i)->getStyle('A9')->applyFromArray($header_style);
		
			$objPHPExcel->setActiveSheetIndex($i)->getStyle('A1:A4')->applyFromArray($address_style);
			$objPHPExcel->setActiveSheetIndex($i)->getStyle("A7:A$row_i")->applyFromArray($left_style);
			
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);							
			
			// Rename worksheet
			$objPHPExcel->getActiveSheet()->setTitle($name);
			
			$row_i++;
			$last_driver_id = $driver_id;
			$i++;	
						
		}	
	
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
		
		// Save Excel 2007 file
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save(str_replace('.php', '.xlsx', $report_outside_path));
		
		