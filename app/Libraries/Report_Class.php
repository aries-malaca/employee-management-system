<?php
namespace ExactivEM\Libraries;
use ExactivEM\Libraries\PDF\FPDF;
use ExactivEM\Libraries\Attendance_Class;
use ExactivEM\Company;
use ExactivEM\Batch;
use ExactivEM\User;
use ExactivEM\Department;
use ExactivEM\Position;
use ExactivEM\Config;
use Excel;
use Storage;
use ExactivEM\Branch;
use ExactivEM\AttendanceLog;
use Illuminate\Support\Facades\Auth;
class Report_Class extends \ExactivEM\Http\Controllers\Controller{
    function __construct($report_type, $data, $set ='single', $format){
        $this->data = $data;
        $this->set = $set;
        $this->report_type = $report_type;
        $this->format = $format;
        $this->plantilla_headers = array('Employee No.',
                                        'Branch',
                                        'First Name',
                                        'Middle Name',
                                        'Last Name',
                                        'Home Address',
                                        'Email Address',
                                        'Mobile',
                                        'Birth Date',
                                        'Civil Status',
                                        'Gender',
                                        'Department',
                                        'Position',
                                        'Tax Exemption',
                                        'Date Hired',
                                        'Bank',
                                        'Account No.',
                                        'Salary',
                                        'SSS No',
                                        'TIN',
                                        'Philhealth No.',
                                        'Pagibig No.'
                                    );
        //check config 
        $this->deduction_conversion = Config::find(25)->value;

        if($this->set == 'batches')
            $title = Batch::find($this->data[0]['batch_id'])->batch_name;
        elseif($this->set == 'companies'){
            if(isset($this->data[0]['company_id']))
                $title = Company::find($this->data[0]['company_id'])->company_name;
            else
                $title = 'TRAINEE';
        }
        elseif($this->set == 'branches')
            $title = Company::find($this->data[0]['company_id'])->company_name;
        else
            $title = Company::find(User::find($this->data['employee'])->company_id)->company_name;
        if($this->report_type == 'plantilla'){
            if($this->format == 'PDF')
                $this->createPlantillaPDF($title);
            else{
                $this->createPlantillaEXCEL($title);
            }
        }
        elseif($this->report_type == 'timelogs'){
            $this->createTimeKeeping($title);
        }
        else{
            $this->create($title);
        }
    }
    
    function create($title){
        if($this->set != 'single')
			$this->createMultipleReport($this->data, $title);
        else{
            $title = Company::find(User::find($this->data['employee'])->company_id)->company_name;
            $this->createSingleReport($this->data, $title);
        }
    }
    
    function createMultipleReport($data, $title = 'Report All'){
        $pdf = new FPDF('P','mm','letter');
        $pdf->AddPage();
        $diff = strtotime($data[0]['date_end']) - strtotime($data[0]['date_start']);
        $days = $diff/3600/24;

        foreach($data as $key=> $value){
            $pdf->SetFont('arial','B',11);
            $this->createReport($pdf, $value, $title);

			if($days>16)
				$pdf->AddPage();
			else{
                if($days<= 5){
                    if(($key+1)%3 == 0)
                        $pdf->AddPage();
                    else{
                        $pdf->Ln();
                        $pdf->Ln();
                        $pdf->Ln();
                    }
                }
                else{
                    if(($key+1)%2 == 0)
                        $pdf->AddPage();
                    else{
                        $pdf->Ln();
                        $pdf->Ln();
                        $pdf->Ln();
                    }
                }
			}
        }
        $file = strtoupper($this->report_type)  .' '. $title.' ' .$data[0]['date_start'] .' to '.$data[0]['date_end'].'.pdf';
        $pdf->Output('report/'.$file, 'F');
        $this->filename = $file ;
    }

    function createSingleReport($data, $title='Report'){
        $pdf = new FPDF('P','mm','letter');
        $pdf->AddPage();
        $pdf->SetFont('arial','B',8);
        $this->createReport($pdf, $data, $title);

		$file = strtoupper($this->report_type) .' '. User::find($data['employee'])->name .' ' .$data['date_start'] .' to '. $data['date_end'] .'.pdf';
		$pdf->Output('report/'.$file, 'F');
		$this->filename = $file ;
    }

    function createReport($pdf, $data, $title){
        $pdf->SetMargins(10,5,5);
        $pdf->SetFont('arial','',8);
        $pdf->Cell(20,5, strtoupper($title) ,0,1,'L');
        $pdf->Cell(20,5, strtoupper($this->report_type) ,0,1,'L');
        $pdf->Cell(20,5, 'Period Covered: '. date('F d',strtotime($data['date_start'])) .' - ' . date('d, Y',strtotime($data['date_end'])) ,0,1,'L');

        switch($this->report_type){
			case 'timesheet':
				$this->createTimeSheet($pdf,$data);
			    break;
            case 'timelogs':
                $this->createTimeKeeping($pdf,$data);
                break;
		}
    }

    function createPlantillaPDF($title){
        $pdf = new FPDF('L','mm','legal');
        $pdf->addPage();
        $pdf->SetMargins(7,5,5);
        $pdf->SetFont('arial','B',10);
        $pdf->Cell(330,5, strtoupper($title) ,0,1,'C');
        $pdf->Cell(330,5, 'Plantilla' ,0,1,'C');
        $pdf->SetFont('arial','',10);
        $pdf->Cell(330,5, 'As of: ' . date('Y-m-d'),0,1,'C');
        $pdf->Ln();

        $pdf->SetFont('arial','',7);
        $pdf->Cell(15,5, 'Employee No.',1,0,'C');
        $pdf->Cell(25,5, 'First Name',1,0,'C');
        $pdf->Cell(20,5, 'Middle Name',1,0,'C');
        $pdf->Cell(20,5, 'Last Name',1,0,'C');
        $pdf->Cell(17,5, 'Birth Date',1,0,'C');
        $pdf->Cell(32,5, 'Department',1,0,'C');
        $pdf->Cell(42,5, 'Position',1,0,'C');
        $pdf->Cell(12,5, 'Tax Exmt.',1,0,'C');
        $pdf->Cell(17,5, 'Date Hired',1,0,'C');
        $pdf->Cell(17,5, 'Employment',1,0,'C');
        $pdf->Cell(22,5, 'Account No.',1,0,'C');
        $pdf->Cell(14,5, 'Salary',1,0,'C');
        $pdf->Cell(20,5, 'SSS',1,0,'C');
        $pdf->Cell(20,5, 'TIN',1,0,'C');
        $pdf->Cell(20,5, 'Philhealth',1,0,'C');
        $pdf->Cell(20,5, 'Pagigbig',1,1,'C');

        foreach($this->data as $key=>$value){
            $employee = $this->getEmployeeInfo($value['employee']);
            $pdf->Cell(15,5, $employee['employee_no'],1,0,'L');
            $pdf->Cell(25,5, $employee['first_name'],1,0,'L');
            $pdf->Cell(20,5, $employee['middle_name'],1,0,'L');
            $pdf->Cell(20,5, $employee['last_name'],1,0,'L');
            $pdf->Cell(17,5, date('m/d/Y',strtotime($employee['birth_date'])),1,0,'L');
            $pdf->Cell(32,5, $employee['department_name'],1,0,'L');
            $pdf->Cell(42,5, $employee['position_name'],1,0,'L');
            $pdf->Cell(12,5, $employee['tax_exemption_shortname'],1,0,'L');
            $pdf->Cell(17,5, date('m/d/Y',strtotime($employee['hired_date'])),1,0,'L');
            $pdf->Cell(17,5, $employee['employment_status_name'],1,0,'L');
            $pdf->Cell(22,5, $employee['bank_number'],1,0,'L');
            $pdf->Cell(14,5, (Auth::user()->level == 1 || Auth::user()->level == 5 ? $this->getCurrentSalary($employee['id']):"0"),1,0,'R');
            $pdf->Cell(20,5, $employee['sss_no'],1,0,'L');
            $pdf->Cell(20,5, $employee['tin_no'],1,0,'L');
            $pdf->Cell(20,5, $employee['philhealth_no'],1,0,'L');
            $pdf->Cell(20,5, $employee['pagibig_no'],1,1,'L');
        }

        $file = strtoupper($this->report_type) .' '. $title . ' as of '. date('Y-m-d') .'.pdf';
        $pdf->Output('report/'.$file, 'F');
        $this->filename = $file ;
    }

    function createPlantillaEXCEL($title){
        $data = $this->data;
        $title = strtoupper($this->report_type) . ' ' . $title . ' as of '. date('Y-m-d');
        Excel::create($title, function($excel) use ($data){
            $excel->sheet('Sheet 1', function($sheet) use ($data) {
                $sheet->setFreeze('E2');
                //make table header
                $sheet->setFontBold(true);
                $sheet->row(1,$this->plantilla_headers);
                $sheet->setFontBold(false);
                foreach($data as $key=>$value){
                    $employee = $this->getEmployeeInfo($value['employee']);
                    $branch = $this->getCurrentBranch($value['employee'],date('Y-m-d'));
                    if($branch !== false){
                        $branch = $branch['branch_name'];
                    }
                    else{
                        $branch = 'N/A';
                    }

                    $sheet->row($key+2,
                                array($employee['employee_no'],
                                    $branch,
                                    utf8_decode(utf8_encode($employee['first_name'])),
                                    utf8_decode(utf8_encode($employee['middle_name'])),
                                    utf8_decode(utf8_encode($employee['last_name'])),
                                    $employee['address'],
                                    $employee['email'],
                                    $employee['mobile'],
                                    date('m/d/Y',strtotime($employee['birth_date'])),
                                    $employee['civil_status'],
                                    $employee['gender'],
                                    $employee['department_name'],
                                    $employee['position_name'],
                                    $employee['tax_exemption_shortname'],
                                    date('m/d/Y',strtotime($employee['hired_date'])),
                                    $employee['bank_code'],
                                    $employee['bank_number'],
                                    (Auth::user()->level == 1 || Auth::user()->level == 5 ? $this->getCurrentSalary($employee['id']):"0"),
                                    $employee['sss_no'],
                                    $employee['tin_no'],
                                    $employee['philhealth_no'],
                                    $employee['pagibig_no']
                                )
                            );
                    if($employee['employment_status_name'] == 'Probationary')
                    $sheet->row($key+2, function($row) {
                        // call cell manipulation methods
                        $row->setBackground('#FFFF00');
                    });
                }

            });

        })->store('xlsx',public_path('report'));
        $this->filename = $title.'.xlsx';
    }
    
    function createTimeSheet($pdf, $data){
    	$employee_data = User::find($data['employee']);

    	$pdf->Cell(20,5, utf8_decode(utf8_encode($employee_data->last_name .', '.$employee_data->first_name)) .' ('.$employee_data->employee_no.')',0,1,'L',false, 'https://'.$_SERVER['HTTP_HOST'] .'/employee/' . $employee_data->id );
        $pdf->Ln();
    	$pdf->SetFont('arial','B',7);

		$iterator = strtotime($data['date_start']);

		$pdf->Cell(24,5, 'Date' ,1,0,'C');
		$pdf->Cell(15,5, 'Time In' ,1,0,'C');
		$pdf->Cell(15,5, 'Time Out' ,1,0,'C');
		$pdf->Cell(25,5, 'Remarks' ,1,0,'C');
		$pdf->Cell(13,5, 'Late' ,1,0,'C');

		$total_late = 0;

		if($this->deduction_conversion == 0)
			$pdf->Cell(13,5, 'UT' ,1,0,'C');
		else
			$pdf->Cell(13,5, 'Ded.' ,1,0,'C');

        $pdf->Cell(13,5, 'OT' ,1,0,'C');
		$pdf->Cell(60,5, 'Notes' ,1,1,'C');
        $total_undertime = 0;
        $total_deductions = 0;
        $total_ot = 0;

		while($iterator<= strtotime($data['date_end'])){
			$pdf->SetFont('arial','',7);
			$att = new Attendance_Class($data['employee'], date('Y-m-d',$iterator));
			$pdf->Cell(24,5, dateNormal($att->date) .' ('.date('D',strtotime($att->date)).')'  ,1,0,'L');
			$remarks = $att->getRemarks();
			
			$in = $att->getLogs('IN');
            unset($in_log);
			if($in !== false){
				foreach($in as $value){
					if($value['stamp_type'] == 'REGULAR' OR $value['stamp_type']=='ADJUSTMENT' OR $value['stamp_type']=='TRAVEL' OR $value['stamp_type']=='OFFSET')
                    {
                        if(isset($in_log)){
                            if(strtotime(date('Y-m-d ').$in_log) > strtotime( date('Y-m-d '). date('h:i A', strtotime($value['attendance_stamp'])) )){
                                $in_log = date('h:i A', strtotime($value['attendance_stamp']));
                            }
                        }
                        else{
                            $in_log = date('h:i A', strtotime($value['attendance_stamp']));
                        }
                    }
				}
			}
			else
				$in_log = '';
			
			$pdf->Cell(15,5, $in_log,1,0,'C');
			
			$out= $att->getLogs('OUT');
			if($out !== false){
				foreach($out as $value){
					if($value['stamp_type'] == 'REGULAR' OR $value['stamp_type']=='ADJUSTMENT' OR $value['stamp_type']=='TRAVEL' OR $value['stamp_type']=='OFFSET')
						$out_log = date('h:i A', strtotime($value['attendance_stamp']));
				}
			}
			else
				$out_log = '';

			$late = $att->getLate();
			$undertime = $att->getUndertime();
			$pdf->Cell(15,5, $out_log,1,0,'C');
			if(in_array('Absent', $remarks))
			    $pdf->SetTextColor(255);
			$pdf->Cell(25,5, ucfirst(implode(', ', $remarks)),1,0,'C');
            $pdf->SetTextColor(0);

			$pdf->Cell(13,5, ( $late !==false? number_format($late,0).' m':'N/A' ),1,0,'C');
			
			$total_late += $late;
			
			if($this->deduction_conversion == 0){
				$pdf->Cell(13,5, ( $undertime !==false? number_format($undertime,0).' m':'N/A' ),1,0,'C');
				$total_undertime += $undertime;
			}
			else{
				$pdf->Cell(13,5, ( $late !==false? $att->toHours($att->toStrictDeduction($late)):'N/A' ),1,0,'C');
				$total_deductions += $att->toStrictDeduction($late);
			}

            $ots =  $att->getRegularOT() + $att->getRestdayOT();
			$total_ot+= $ots;

            $pdf->Cell(13,5, number_format(($ots/60),2) ,1,0,'C');

            $oot = $att->getOvertime();
            $travel = $att->getTravel();
            $offset = $att->getOffset();
            $leave = $att->getLeave();
			if(in_array('overtime',$remarks))
				$remark_others = 'OT:'.date('h:i A',strtotime($oot[0]['attendance_stamp'])). '-'.
									date('h:i A',strtotime($oot[1]['attendance_stamp'])) . ' ' .
                    (isset($oot[2])?', '.date('h:i A',strtotime($oot[2]['attendance_stamp'])). '-'.
                        date('h:i A',strtotime($oot[3]['attendance_stamp'])):'')
                ;
			elseif(in_array('travel',$remarks)){
			    if(sizeof($travel)>1){
                    $remark_others = 'Travel:'.date('h:i A',strtotime($travel[0]['attendance_stamp'])). '-'.
                        date('h:i A',strtotime($travel[1]['attendance_stamp']));
                }
            }
			elseif(in_array('offset',$remarks))
				$remark_others = date('h:i A',strtotime($offset[0]['attendance_stamp'])). '-'.
									date('h:i A',strtotime($offset[1]['attendance_stamp']));
			elseif(in_array('offset',$remarks))
				$remark_others = 'Offset:'.date('h:i A',strtotime($offset[0]['attendance_stamp'])). '-'.
									date('h:i A',strtotime($offset[1]['attendance_stamp']));
			elseif(in_array('leave',$remarks)){
                $remark_others = 'Leave:'.$leave['mode'] .' (' .$leave['leave_type_name']. ')';
            }
			elseif(in_array('holiday',$remarks))
				$remark_others = 'Holiday:'.$att->getHoliday()['name'];
			elseif(in_array('emergency',$remarks))
				$remark_others = 'Blocked Sched.:'.$att->getEmergency()['emergency_name'];
			else
				$remark_others='';

			$pdf->Cell(60,5, $remark_others,1,1,'L');
			$iterator +=86400;
		}

		$pdf->Cell(24,5, '',1,0,'L');
		$pdf->Cell(15,5, '',1,0,'L');
		$pdf->Cell(15,5, '',1,0,'L');
		$pdf->Cell(25,5, '',1,0,'L');
		$pdf->Cell(13,5, $total_late ." m.",1,0,'C');

		if($this->deduction_conversion == 0)
			$pdf->Cell(13,5, $total_undertime ." m.",1,0,'C');
		else
			$pdf->Cell(13,5, $att->toHours($total_deductions),1,0,'C');

        $pdf->Cell(13,5, number_format(($total_ot/60),2) .' h.',1,0,'C');
		$pdf->Cell(60,5, '',1,1,'L');
    }

    function createTimeKeeping($title){
        $data = $this->data;
        Excel::create($title, function($excel) use ($data){
            $excel->sheet('Sheet 1', function($sheet) use ($data) {
                //make table header
                $sheet->setFontBold(true);
                $sheet->setFontBold(false);
                $cursor = 1;
                foreach($data as $key=>$value){
                    $timelogs = AttendanceLog::where('biometric_no', $value['biometric_no'])
                                                ->get()->toArray();

                    $name = $value['name'];
                    $sheet->row($cursor, [$name]);

                    $cursor++;

                    $iterator = strtotime($value['date_start']);
                    while($iterator <= strtotime($value['date_end'])){

                        $new_row = array('',date('m/d/Y',$iterator));

                        foreach($timelogs as $log){
                            if(date('Y-m-d',$iterator) == date('Y-m-d',strtotime($log['datetime'])))
                                $new_row[] = date('H:i',strtotime($log['datetime']));
                        }

                        $sheet->row($cursor, $new_row);
                        $cursor++;
                        $iterator+=86400;
                    }
                }
            });

        })->store('xlsx',public_path('report'));
        $this->filename = $title.'.xlsx';
    }
}