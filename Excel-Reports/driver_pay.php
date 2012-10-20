/* DRIVER PAY REPORT */
		
		
		$parent_company['report']='DRIVER PAY';				
		
		/* CSS */
		
				
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
		GROUP BY drivers.id DESC");		
		
		$message = '';
		
	
		$i = 0;
		$last_driver_id = '';
		while ($row = mysql_fetch_array($result))
		{
			if ($i > 0)	{
				$objWorksheet1 = $objPHPExcel->createSheet();
				$objWorksheet1->setTitle($name);
			}
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
			
			//$birthday = $row['birthdate'];
		//	$hiredate = $row['hiredate'];
			//$type = $row['type'];
		//	$licence_id = $row['licence_id'];
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
	
			if( $i+1 == mysql_num_rows($result)){
				$row_i = $row_i+6;
				//$sum_end = "E".$row_i+1;
				
				$gross_col = 'D'.($row_i+1);
				$company_col = 'D'.($row_i+2);
				$sub_total_col = 'D'.($row_i+3);
				$advance_col = 'D'.($row_i+4);
				$status_col_header = 'C'.($row_i+4);
				$insurance_col = 'D'.($row_i+5);
				$status_col_value = 'C'.($row_i+5);
				$diesel_col = 'D'.($row_i+6);
				$other_col = 'D'.($row_i+7);
				$occ_col = 'D'.($row_i+8);
				
				$total_1_pay_col = 'D'.($row_i+10);
				
				$escrow_bal_col = 'D'.($row_i+12);
				$escrow_col_header = 'A'.($row_i+12);
				$damage_col = 'D'.($row_i+13);
				$total_1_col = 'D'.($row_i+14);			
				$adv_escrow_col = 'D'.($row_i+15);
				$escrow_driver_col_header = 'A'.($row_i+15);
				$total_2_pay_col = 'D'.($row_i+16);
				
				$escrow_2_col  = 'D'.($row_i+18);
				$withdraw_col = 'D'.($row_i+19);
				$total_2_col = 'D'.($row_i+20);
				$other_2_col = 'D'.($row_i+21);
				$final_pay_col = 'D'.($row_i+22);
				
				$gross_col_value = 'E'.($row_i+1);
				$company_col_value = 'E'.($row_i+2);
				$sub_total_col_value = 'E'.($row_i+3);
				$advance_col_value = 'E'.($row_i+4);
				$insurance_col_value = 'E'.($row_i+5);
				$diesel_col_value = 'E'.($row_i+6);
				$other_col_value = 'E'.($row_i+7);
				$occ_col_value = 'E'.($row_i+8);
				
				$total_pay_col_value = 'E'.($row_i+10);
				
				$escrow_bal_col_value = 'E'.($row_i+12);
				$damage_col_value = 'E'.($row_i+13);
				$total_col_value = 'E'.($row_i+14);			
				$adv_escrow_col_value = 'E'.($row_i+15);
				$total_2_pay_col_value = 'E'.($row_i+16);
				
				$escrow_2_bal_col_value  = 'E'.($row_i+18);
				$withdraw_col_value = 'E'.($row_i+19);
				$total_2_col_value = 'E'.($row_i+20);
				$other_2_col_value = 'E'.($row_i+21);
				$final_pay_col_value = 'E'.($row_i+22);
			
				// Styling 	
				
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A5:A10')->applyFromArray($header_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A12:F12')->applyFromArray($header_style);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A9')->applyFromArray($header_style);	
				$objPHPExcel->setActiveSheetIndex($i)->getStyle('A1:A4')->applyFromArray($address_style);
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);								
					
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($gross_col)->applyFromArray($header_style_left);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($company_col)->applyFromArray($header_style_left);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($sub_total_col)->applyFromArray($header_style_left);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($advance_col)->applyFromArray($header_style_left);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($insurance_col)->applyFromArray($header_style_left);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($diesel_col)->applyFromArray($header_style_left);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($other_col)->applyFromArray($header_style_left);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($occ_col)->applyFromArray($header_style_left);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($total_1_pay_col)->applyFromArray($header_style_left);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($escrow_bal_col)->applyFromArray($header_style_left);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($damage_col)->applyFromArray($header_style_left);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($total_1_col)->applyFromArray($header_style_left);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($adv_escrow_col)->applyFromArray($header_style_left);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($total_2_pay_col)->applyFromArray($header_style_left);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($escrow_2_col)->applyFromArray($header_style_left);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($withdraw_col)->applyFromArray($header_style_left);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($total_2_col)->applyFromArray($header_style_left);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($damage_col)->applyFromArray($header_style_left);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($total_1_col)->applyFromArray($header_style_left);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($other_2_col)->applyFromArray($header_style_left);
				$objPHPExcel->setActiveSheetIndex($i)->getStyle($final_pay_col)->applyFromArray($header_style_left);
	
				$objPHPExcel->getActiveSheet()->getStyle("E13:$final_pay_col_value")->getNumberFormat()->setFormatCode('$#,##0.00');
				$objPHPExcel->getActiveSheet()->getStyle("F")->getNumberFormat()->setFormatCode('$#,##0.00');
				$objPHPExcel->setActiveSheetIndex($i)->getStyle("F13:$final_pay_col_value")->applyFromArray($right_style);
							
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
				
				->setCellValue($escrow_bal_col_value,'641.49')
				->setCellValue($damage_col_value,'0')
				->setCellValue($total_2_col_value,"=$escrow_bal_col_value-$damage_col_value")
				->setCellValue($adv_escrow_col_value,'75')
				->setCellValue($total_2_pay_col_value,"=$total_2_col_value-$adv_escrow_col_value")
				
				->setCellValue($escrow_2_bal_col_value,'1150')
				->setCellValue($withdraw_col_value,'25')
				->setCellValue($total_2_col_value,"=$escrow_2_bal_col_value-$withdraw_col_value")
				->setCellValue($other_2_col_value,'0')
				->setCellValue($final_pay_col_value,"=$total_2_col_value-$other_2_col_value")
				
				->setCellValue($status_col_header,'Status:')
				->setCellValue($status_col_value,$freight_status)
				->setCellValue($escrow_col_header,'Our Escrow $1800')
				->setCellValue($escrow_driver_col_header,'Driver Escrow $500');
				
			}
			
			// Rename worksheet
			$objPHPExcel->getActiveSheet()->setTitle($name);
			
			$row_i++;
			$last_driver_id = $driver_id;
			$i++;	
						
		}	
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
		// Save Excel 2007 file
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save(str_replace('.php', '.xlsx', $report_driver_path));

		
		
		

