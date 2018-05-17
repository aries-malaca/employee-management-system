<?php
namespace ExactivEM\Libraries;
use ExactivEM\Libraries\PDF\FPDF;
use ExactivEM\EmployeeRequest;
use ExactivEM\User;
use ExactivEM\Leave_type;

class Request_Class extends \ExactivEM\Http\Controllers\Controller{
    function printRequest($id){
        $request = EmployeeRequest::find($id);

        $pdf = new FPDF('P','mm','letter');
        $pdf->AddPage();
        $pdf->SetFont('arial','B',12);
        $pdf->Image(public_path('images/app/2017-logo.png'), 10,5,30,30);
        $pdf->SetY(15);
        $pdf->SetX(60);
        $pdf->Cell(100,10,$this->getTitle($request->request_type),1,1,'C');
        $pdf->Ln();

        $this->printHeading($pdf, $request);
        $pdf->Ln();

        $this->printBody($pdf, $request);
        $pdf->Ln();
        $pdf->Ln();

        $this->printSignatory($pdf, $request);
        if($request->request_type == 'travel'){
            $this->printTravelGuidelines($pdf);
        }
        return $pdf->Output('Request '. 1 .'.pdf','I');
    }

    function getTitle($request_type){
        switch($request_type){
            case 'leave':
                $title = 'Leave of Absence Form';
                break;
            case 'travel':
                $title = 'Official Business Trip Form';
                break;
            case 'adjustment':
                $title = 'Time Adjustment Form';
                break;
            case 'overtime':
                $title = 'Overtime Form';
                break;
            case 'offset':
                $title = 'Offset Form';
                break;
        }
        return $title;
    }

    function printHeading($pdf, $request){
        $user_info = User::leftJoin('departments', 'users.department_id', '=', 'departments.id')
                            ->leftJoin('positions', 'users.position_id', '=', 'positions.id')
                            ->select('users.id as user_id', 'users.*', 'departments.*', 'positions.*')
                            ->where('users.id', $request->employee_id)
                            ->get()->first();
        $request_data = json_decode($request->request_data,true);
        $pdf->SetFont('arial','',8);
        $pdf->Cell(83,10,"Employee Name: " . $user_info['name'],1,0,'L');
        $pdf->Cell(53,10,"Employee ID #: " . $user_info['employee_no'],1,0,'L');
        $pdf->Cell(53,10,"Date Filed: " . date('m/d/Y',strtotime($request->created_at)),1,1,'L');
        $pdf->Cell(83,10,"Position: " . $user_info['position_name'],1,0,'L');
        $pdf->Cell(53,10,"Department: " . $user_info['department_name'],1,0,'L');
        $pdf->Cell(53,10,"Branch: " . $this->getCurrentBranch($request->employee_id, (isset($request_data['date_start'])?$request_data['date_start']:$request_data['date'] ))->branch_name ,1,1,'L');
    }

    function printBody($pdf, $request){
        $request_data = json_decode($request->request_data,true);
        $pdf->SetFont('arial','',10);
        if($request->request_type == 'leave'){
            $date = date('m/d/Y',strtotime($request_data['date_start'])) . ($request_data['date_start'] != $request_data['date_end']? '-'.date('m/d/Y',strtotime($request_data['date_end'])) :'');
            $pdf->Cell(80,10,"Type of Leave: " . Leave_type::find($request_data['leave_type'])->leave_type_name ,0,0,'L');
            $pdf->Cell(60,10,"Date of Leave: " . $date ,0,0,'L');
            $pdf->Cell(20,10,"Days: " . $request_data['days'] ,0,0,'L');
            $pdf->Cell(20,10,"(" . $request_data['mode'] . ")" ,0,1,'L');
        }

        if($request->request_type == 'travel'){
            $pdf->Cell(60,10,"Date of Travel/OB: " . date('m/d/Y',strtotime($request_data['date_start'])),0,0,'L');
            $pdf->Cell(40,10,"Arrival: " . date('h:i A',strtotime('2017-01-01 '.$request_data['time_start'])),0,0,'L');
            $pdf->Cell(40,10,"Departure: " . date('h:i A',strtotime('2017-01-01 '.$request_data['time_end'])),0,1,'L');
        }

        if($request->request_type == 'offset'){
            $date = date('m/d/Y',strtotime($request_data['date_start'])) . ($request_data['date_start'] != $request_data['date_end']? '-'.date('m/d/Y',strtotime($request_data['date_end'])) :'');
            $pdf->Cell(80,10,"Date of Offset: " . $date ,0,0,'L');
            $pdf->Cell(80,10,"Time: " . date('h:i A',strtotime('2017-01-01 '.$request_data['time_start'])) .' - '. date('h:i A',strtotime('2017-01-01 '.$request_data['time_end'])) ,0,1,'L');
        }

        if($request->request_type == 'adjustment'){
            $pdf->Cell(80,10,"Date of Adjustment: " . date('m/d/Y',strtotime($request_data['date'])) ,0,0,'L');
            $pdf->Cell(60,10,"Time: " . date('h:i A',strtotime($request_data['date'].' '.$request_data['time'])) ,0,0,'L');
            $pdf->Cell(60,10,"Mode: " . $request_data['mode'],0,1,'L');
        }

        if($request->request_type == 'overtime'){
            $date = date('m/d/Y',strtotime($request_data['date_start']));
            $pdf->Cell(60,5,"Overtime Date: " . date('m/d/Y',strtotime($request_data['date_start'])) ,0,0,'L');
            $pdf->Cell(60,5,"Rendered Time: " . date('m/d/Y h:i A',strtotime($request_data['date_start'] .' '.$request_data['time_start'])) .' - '. date('m/d/Y h:i A',strtotime($request_data['date_end'].' '.$request_data['time_end'])) ,0,1,'L');
            $pdf->Cell(60,10,"Hours: " . ($this->timeDiff($request_data['date_start'] .' '.$request_data['time_start'], $request_data['date_end'].' '.$request_data['time_end'])/60)/60,0,1,'L');
        }

        $pdf->MultiCell(200,5,"Notes: " . $request->request_note ,0,'L',false);
    }

    function timeDiff($firstTime,$lastTime) {
        $firstTime=strtotime($firstTime);
        $lastTime=strtotime($lastTime);
        $timeDiff=$lastTime-$firstTime;
        return $timeDiff;
    }


    function printSignatory($pdf, $request){
        $action = json_decode($request->action_data,true);

        if(isset($action['approved_by'][0]['id']))
            $user = User::find($action['approved_by'][0]['id'])->name;
        else
            $user = '_____________________';

        if(isset($action['approved_by'][sizeof($action['approved_by'])-1]['id']))
            $hr = User::find($action['approved_by'][sizeof($action['approved_by'])-1]['id'])->name;
        else
            $hr = '_____________________';

        $pdf->SetFont('arial','B',8);
        $pdf->SetX(10);
        $pdf->Cell(53,5,"Submitted by:",0,0,'L');

        $pdf->SetX(80);
        $pdf->Cell(53,5,"Noted by: ",0,0,'L');

        $pdf->SetX(150);
        $pdf->Cell(53,5,"Approved by: ",0,1,'L');
        $pdf->Ln();

        $pdf->SetFont('arial','UB',8);
        $pdf->SetX(10);
        $pdf->Cell(53,5, User::find($request->employee_id)->name,0,0,'L');

        $pdf->SetX(80);
        $pdf->Cell(53,5,$user,0,0,'L');

        $pdf->SetX(150);
        $pdf->Cell(53,5,$hr,0,1,'L');

        $pdf->SetFont('arial','B',8);
        $pdf->SetX(10);
        $pdf->Cell(53,5,"Employee's Signature",0,0,'L');

        $pdf->SetX(80);
        $pdf->Cell(53,5,"Immediate Head/ Date",0,0,'L');

        $pdf->SetX(150);
        $pdf->Cell(53,5,"Human Resources/ Date ",0,1,'L');
        $pdf->Ln();
    }

    function printTravelGuidelines($pdf){
        $pdf->Ln();
        $pdf->SetFont('arial','',8);
        $pdf->SetX(10);
        $pdf->Cell(0,5,"Guidelines: ",0,1,'L');
        $pdf->MultiCell(200,5,"1. An employee who is required to go on official business outside the office during office hours must accomplish the OB Form, duly approved b department head at least three days before the actual date of OB. For out of town trips please use the Travel Request Form.",0,'L',false);
        $pdf->MultiCell(200,5,"2. In the event that the official business is submitted late, or not approved by direct superior, the related business transaction will not push through.",0,'L',false);
        $pdf->MultiCell(200,5,"3. OB Form should be filled-up and signed with client conforme after the meeting,seminar, client calls, conversations, or any other related business transactions.",0,'L',false);
        $pdf->MultiCell(200,5,"4. The official business will not be considered valid if the itenerary report is not fully accomplished.",0,'L',false);
        $pdf->MultiCell(200,5,"5. Accomplished form without the signature of official approver and client shall be considered invalidated and thereby OB may be classified as unauthorized.",0,'L',false);
    }
}