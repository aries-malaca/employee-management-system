<?php
namespace ExactivEM\Libraries;
use ExactivEM\Libraries\PDF\FPDF;
use ExactivEM\TransactionCode;
use ExactivEM\Contribution;
use ExactivEM\Company;
use ExactivEM\Batch;
use ExactivEM\User;
use ExactivEM\Department;
use ExactivEM\Position;
use ExactivEM\Branch;

class Pdf_Class{
    function __construct($type, $data, $set ='single'){
        $this->data = $data;
        $this->set = $set;
        $this->additionCodes = TransactionCode::where('transaction_type', 'addition')
            ->where('is_regular_transaction', 0)
            ->get()->all();

        $this->deductionCodes = TransactionCode::where('transaction_type', 'deduction')
            ->where('is_regular_transaction', 0)
            ->get()->all();

        $this->contributionCodes = Contribution::get()->all();

        //create now
        $this->create($type, $set);
    }

    function create($type, $set){
        if($set == 'single')
            $title = Company::find($this->data['company_id'])->company_name;
        elseif($set == 'companies')
            $title = Company::find($this->data[0]['company_id'])->company_name;
        elseif($set == 'batches')
            $title = Batch::find($this->data[0]['batch_id'])->batch_name;
        elseif($set == 'branches')
            $title = Company::find($this->data[0]['company_id'])->company_name;

        if($type == 'Summary')
            $this->createSummary($this->data, $title);
        elseif($type == 'Multiple')
            $this->createMultiplePayslip($this->data, $title);
        else
            $this->createSinglePayslip($this->data, $title);
    }

    function createMultiplePayslip($data, $title = 'Payslip All'){
        $pdf = new FPDF('P','mm','letter');
        $pdf->AddPage();
        $pdf->SetFont('arial','B',7);
        foreach($data as $key=> $value){
            $this->createPayslip($pdf, $value, $title);
            if($key!=0)
                if(($key+1)%3==0){
                    $pdf->AddPage();
                }
        }

        $pdf->AddPage();
        $pdf->Output('I','Payslips');
    }


    function createSinglePayslip($data, $title='Payslip'){
        $pdf = new FPDF('P','mm','letter');
        $pdf->AddPage();
        $this->createPayslip($pdf, $data, $title);
        $pdf->Output($data['name'].' '. dateNormal($data['date_start']). " - ". dateNormal($data['date_end']) .".pdf", 'D');
    }

    function createPayslip($pdf, $payslip, $title)
    {
        $pdf->SetFont('arial', 'B', 7);
        $pdf->SetX(10);
        $pdf->Cell(20, 3, strtoupper($title), 0, 1, 'L');
        $pdf->SetFont('arial', '', 7);
        $pdf->Cell(20, 3, 'Payroll Period: ' . date('F d', strtotime($payslip['date_start'])) . ' - ' . date('d, Y', strtotime($payslip['date_end'])), 0, 1, 'L');
        $pdf->SetMargins(10, 5, 5);
        $pdf->SetX(15);
        $pdf->Ln();

        $add = array();
        $ded = array();

        foreach ($this->additionCodes as $value)
            $add[] = array("name" => $value['transaction_name'], "width" => 14);

        foreach ($this->deductionCodes as $value)
            $ded[] = array("name" => $value['transaction_name'], "width" => 14);

        foreach ($this->contributionCodes as $value)
            $ded[] = array("name" => $value['contribution_name'], "width" => 14);

        $payslip_data = json_decode($payslip['payslip_data'], true);

        //single header
        $pdf->SetX(15);
        $pdf->Cell(20, 3, "Employee: ", 0, 0, 'L');
        $pdf->SetX(45);
        $pdf->Cell(20, 3, "(" . $payslip->employee_no . ") " . utf8_decode($payslip->first_name . ' ' . $payslip->last_name) , 0, 1, 'L');
        $pdf->SetX(15);
        $pdf->Cell(20, 3, "Department/Position: ", 0, 0, 'L');
        $pdf->SetX(45);
        $pdf->Cell(20, 3, $payslip->department_name . "/ " . $payslip->position_name, 0, 1, 'L');
        $pdf->Ln();
        //end single header

        //start left column
        $y = $pdf->GetY();
        $pdf->SetX(15);
        $pdf->Cell(20, 3, "Basic Pay Rate: ", 0, 0, 'L');
        $pdf->SetX(50);
        $pdf->Cell(20, 3, number_format((!$payslip_data['is_daily']?$payslip_data['salary_rates']['monthly']:$payslip_data['salary_rates']['daily']), 2), 0, 1, 'R');

        $pdf->SetX(15);
        $pdf->Cell(20, 3, "No. of Days: ", 0, 0, 'L');
        $pdf->SetX(50);
        $pdf->Cell(20, 3, (!$payslip_data['is_daily']?'-':$payslip_data['days_worked']), 0, 1, 'R');

        if($payslip_data['paid_leaves']>0 && $payslip_data['is_daily']){
            $pdf->SetX(15);
            $pdf->Cell(20, 3, "No. of Paid Leaves: ", 0, 0, 'L');
            $pdf->SetX(50);
            $pdf->Cell(20, 3, $payslip_data['paid_leaves'], 0, 1, 'R');
        }

        if($payslip_data['paid_leaves']>0 && $payslip_data['is_daily']){
            $pdf->SetX(15);
            $pdf->Cell(20, 3, "Leave Credit: ", 0, 0, 'L');
            $pdf->SetX(50);
            $pdf->Cell(20, 3, number_format($payslip_data['leave_credit'],2), 0, 1, 'R');
        }

        $pdf->SetX(15);
        $pdf->Cell(20, 3, "Basic Salary: ", 0, 0, 'L');
        $pdf->SetX(50);
        $pdf->Cell(20, 3, number_format($payslip_data['basic_pay'] , 2), 0, 1, 'R');
        $pdf->Ln();

        $pdf->SetX(15);
        $pdf->Cell(20, 3, "Absences Tardiness: ", 0, 0, 'L');
        $pdf->SetX(50);

        $pdf->Cell(20, 3, '('.number_format( $payslip_data['tardiness'],2) .')', 0, 1, 'R');

        $pdf->SetX(15);
        $pdf->Cell(20, 3, "Overtime Hrs.: ", 0, 0, 'L');
        $pdf->SetX(50);
        $pdf->Cell(20, 3, '-', 0, 1, 'R');

        $pdf->SetX(15);
        $pdf->Cell(20, 3, "Overtime Pay: ", 0, 0, 'L');
        $pdf->SetX(50);
        $pdf->Cell(20, 3, number_format($payslip_data['overtimes']['total'] + $payslip_data['holiday_pay'], 2), 0, 1, 'R');

        $pdf->SetX(15);
        $pdf->Cell(20, 3, "ECOLA: ", 0, 0, 'L');
        $pdf->SetX(50);
        $pdf->Cell(20, 3, number_format($payslip_data['ecola'], 2), 0, 1, 'R');

        $pdf->SetFont('arial', 'B', 7);
        $pdf->SetX(15);
        $pdf->Cell(20, 3, "Gross Pay: ", 0, 0, 'L');
        $pdf->SetX(50);
        $pdf->Cell(20, 3, number_format($payslip_data['gross_pay'], 2), 0, 1, 'R');
        $pdf->Ln();

        foreach($payslip_data['additions']['transactions'] as $key=>$value){

            $pdf->SetFont('arial', '', 7);
            $pdf->SetX(15);
            $pdf->Cell(20, 3, $value['name'], 0, 0, 'L');
            $pdf->SetX(50);
            $pdf->Cell(20, 3, number_format($value['amount'], 2), 0, 1, 'R');
        }

        $pdf->SetFont('arial', 'B', 7);
        $pdf->SetX(15);
        $pdf->Cell(20, 3, "Total Pay: ", 0, 0, 'L');
        $pdf->SetX(50);
        $pdf->Cell(20, 3, number_format($payslip_data['gross_pay'] + $payslip_data['additions']['total'], 2), 0, 1, 'R');
        //end left column

        //start mid column
        $pdf->SetY($y);
        $pdf->SetX(80);
        $pdf->Cell(20, 3, "Deductions: ", 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 7);

        foreach ($this->deductionCodes as $key => $value) {
            if ($this->findTransaction($payslip_data['deductions']['transactions'], $value['transaction_name']) != 0) {
                $pdf->SetX(80);
                $pdf->Cell(20, 3, $value['transaction_name'] . ":", 0, 0, 'L');
                $pdf->SetX(115);
                $pdf->Cell(20, 3, number_format($this->findTransaction($payslip_data['deductions']['transactions'], $value['transaction_name']), 2), 0, 1, 'R');
            }
        }

        foreach ($this->contributionCodes as $key => $value) {
            $pdf->SetX(80);
            $pdf->Cell(20, 3, $value['contribution_name'] . ":", 0, 0, 'L');
            $pdf->SetX(115);
            $pdf->Cell(20, 3, number_format($this->getContributionsValue($payslip_data['contributions'], $value['contribution_name']), 2), 0, 1, 'R');
        }

        $pdf->SetX(80);
        $pdf->Cell(20, 3, "Tax:", 0, 0, 'L');
        $pdf->SetX(115);
        $pdf->Cell(20, 3, number_format($payslip_data['tax'], 2), 0, 1, 'R');

        $pdf->SetX(80);
        $pdf->SetFont('arial', 'B', 7);
        $pdf->Cell(20, 3, "Total Deductions:", 0, 0, 'L');
        $pdf->SetX(115);
        $pdf->SetFont('arial', 'UB', 7);
        $pdf->Cell(20, 3, number_format($payslip_data['deductions_total'], 2), 0, 1, 'R');

        $pdf->SetX(80);
        $pdf->SetFont('arial', 'B', 7);
        $pdf->Cell(20, 3, "Net Pay:", 0, 0, 'L');
        $pdf->SetX(115);
        $pdf->SetFont('arial', 'UB', 7);
        $pdf->Cell(20, 3, number_format($payslip_data['net_pay'], 2), 0, 1, 'R');
        //end mid column

        //start right column
        $breaks = 1;
        $pdf->SetY($y);
        $pdf->SetX(145);
        $pdf->Cell(20, 3, "Overtime: ", 0, 0, 'L');
        $pdf->SetX(185);
        $pdf->Cell(10, 3, "Hrs.", 0, 0, 'L');
        $pdf->SetX(195);
        $pdf->Cell(10, 3, "Amount", 0, 1, 'L');
        $pdf->SetFont('arial', '', 7);

        $pdf->SetX(145);
        $pdf->Cell(20, 3, "Regular:", 0, 0, 'L');
        $pdf->SetX(185);
        $pdf->Cell(10, 3, number_format($payslip_data['overtimes']['regular_overtime']['minutes'] / 60, 2), 0, 0, 'L');
        $pdf->SetX(195);
        $pdf->Cell(10, 3, number_format($payslip_data['overtimes']['regular_overtime']['total'], 2), 0, 1, 'R');

        $pdf->SetX(145);
        $pdf->Cell(20, 3, "Restday OT:", 0, 0, 'L');
        $pdf->SetX(185);
        $pdf->Cell(20, 3, number_format($payslip_data['overtimes']['restday_overtime']['minutes'] / 60, 2), 0, 0, 'L');
        $pdf->SetX(195);
        $pdf->Cell(10, 3, number_format($payslip_data['overtimes']['restday_overtime']['total'] , 2), 0, 1, 'R');


        if ($payslip_data['overtimes']['restday_beyond_overtime']['minutes'] != 0){
            $pdf->SetX(145);
            $pdf->Cell(20, 3, "Restday after 8 hrs:", 0, 0, 'L');
            $pdf->SetX(185);
            $pdf->Cell(20, 3, number_format($payslip_data['overtimes']['restday_beyond_overtime']['minutes'] / 60, 2), 0, 0, 'L');
            $pdf->SetX(195);
            $pdf->Cell(10, 3, number_format($payslip_data['overtimes']['restday_beyond_overtime']['total'] , 2), 0, 1, 'R');
        }
        else{
            $breaks++;
        }

        $pdf->SetX(145);
        $pdf->Cell(20,3, "Night Diff.:",0,0,'L');
        $pdf->SetX(185);
        $pdf->Cell(20,3, number_format(($payslip_data['overtimes']['regular_nightdiff']['minutes'] + $payslip_data['overtimes']['restday_nightdiff']['minutes'] ) / 60,2)  ,0,0,'L');
        $pdf->SetX(195);
        $pdf->Cell(10,3, number_format((( $payslip_data['overtimes']['regular_nightdiff']['total'] + $payslip_data['overtimes']['restday_nightdiff']['total'] ) ) , 2) ,0,1,'R');

        $pdf->SetX(145);
        $pdf->Cell(20,3, "Regular Holiday:",0,0,'L');
        $pdf->SetX(185);
        $pdf->Cell(20,3,'-' ,0,0,'L');
        $pdf->SetX(195);
        $pdf->Cell(10,3, number_format($this->getHolidayTotals($payslip_data['holidays'],1,'absent_workday') + $this->getHolidayTotals($payslip_data['holidays'],1,'present_workday'),2) ,0,1,'R');

        $pdf->SetX(145);
        $pdf->Cell(20, 3, "Regular Holiday on Restday:", 0, 0, 'L');
        $pdf->SetX(185);
        $pdf->Cell(20, 3, '-', 0, 0, 'L');
        $pdf->SetX(195);
        $pdf->Cell(10, 3, number_format($this->getHolidayTotals($payslip_data['holidays'], 1, 'absent_restday') + $this->getHolidayTotals($payslip_data['holidays'], 1, 'present_restday'), 2), 0, 1, 'R');


        if($this->getHolidayTotals($payslip_data['holidays'],1,'beyond_workday') != 0){
            $pdf->SetX(145);
            $pdf->Cell(20,3, "Regular Holiday after 8 hrs:",0,0,'L');
            $pdf->SetX(185);
            $pdf->Cell(20,3,'-' ,0,0,'L');
            $pdf->SetX(195);
            $pdf->Cell(10,3,number_format($this->getHolidayTotals($payslip_data['holidays'],1,'beyond_workday') ,2) ,0,1,'R');
        }
        else{
            $breaks++;
        }

        if($this->getHolidayTotals($payslip_data['holidays'],1,'beyond_restday') !=0){
            $pdf->SetX(145);
            $pdf->Cell(20,3, "Regular Holiday Restday after 8 hrs:",0,0,'L');
            $pdf->SetX(185);
            $pdf->Cell(20,3,'-' ,0,0,'L');
            $pdf->SetX(195);
            $pdf->Cell(10,3, number_format($this->getHolidayTotals($payslip_data['holidays'],1,'beyond_restday') ,2) ,0,1,'R');
        }
        else{
            $breaks++;
        }

        $pdf->SetX(145);
        $pdf->Cell(20,3, "Special Holiday:",0,0,'L');
        $pdf->SetX(185);
        $pdf->Cell(20,3, '-' ,0,0,'L');
        $pdf->SetX(195);
        $pdf->Cell(10,3, number_format($this->getHolidayTotals($payslip_data['holidays'],2,'absent_workday') + $this->getHolidayTotals($payslip_data['holidays'],2,'present_workday'),2) ,0,1,'R');

        $pdf->SetX(145);
        $pdf->Cell(20,3, "Special Holiday on Restday:",0,0,'L');
        $pdf->SetX(185);
        $pdf->Cell(20,3,'-' ,0,0,'L');
        $pdf->SetX(195);
        $pdf->Cell(10,3,number_format($this->getHolidayTotals($payslip_data['holidays'],2,'absent_restday') + $this->getHolidayTotals($payslip_data['holidays'],2,'present_restday'),2) ,0,1,'R');


        if($this->getHolidayTotals($payslip_data['holidays'],2,'beyond_workday') != 0){
            $pdf->SetX(145);
            $pdf->Cell(20,3, "Special Holiday after 8 hrs:",0,0,'L');
            $pdf->SetX(185);
            $pdf->Cell(20,3,'-' ,0,0,'L');
            $pdf->SetX(195);
            $pdf->Cell(10,3,number_format($this->getHolidayTotals($payslip_data['holidays'],2,'beyond_workday') ,2) ,0,1,'R');
        }
        else{
            $breaks++;
        }

        if($this->getHolidayTotals($payslip_data['holidays'],2,'beyond_restday')!=0){
            $pdf->SetX(145);
            $pdf->Cell(20,3, "Special Holiday Restday after 8 hrs:",0,0,'L');
            $pdf->SetX(185);
            $pdf->Cell(20,3,'-' ,0,0,'L');
            $pdf->SetX(195);
            $pdf->Cell(10,3, number_format($this->getHolidayTotals($payslip_data['holidays'],2,'beyond_restday') ,2) ,0,1,'R');
        }
        else{
            $breaks++;
        }

        $all_hnd = $this->getHolidayTotals($payslip_data['holidays'],1,'nightdiff_workday') +
            $this->getHolidayTotals($payslip_data['holidays'],1,'nightdiff_restday') +
            $this->getHolidayTotals($payslip_data['holidays'],2,'nightdiff_workday') +
            $this->getHolidayTotals($payslip_data['holidays'],2,'nightdiff_restday');

        $pdf->SetX(145);
        $pdf->Cell(20,3, "Holidays Night Diff.:",0,0,'L');
        $pdf->SetX(185);
        $pdf->Cell(20,3,'-' ,0,0,'L');
        $pdf->SetX(195);
        $pdf->Cell(10,3,number_format($all_hnd ,2) ,0,1,'R');

        $pdf->SetX(185);
        $pdf->SetFont('arial','UB',7);
        $pdf->Cell(20,3, "-",0,0,'L');
        $pdf->SetX(195);
        $pdf->SetFont('arial','UB',7);
        $pdf->Cell(10,3, number_format( ($payslip_data['overtimes']['total'] +  $payslip_data['holiday_pay']),2) ,0,1,'R');
        //end right column

        //start footer
        $pdf->Ln();
        $pdf->Ln();
        $pdf->SetX(80);
        $pdf->Cell(20,3, '_____________________' ,0,0,'L');
        $pdf->SetX(115);
        $pdf->Cell(20,3, '_________' ,0,1,'L');

        $pdf->SetX(80);
        $pdf->Cell(20,3, 'Received by:' ,0,0,'C');
        $pdf->SetX(115);
        $pdf->Cell(20,3, 'Date' ,0,1,'C');
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln($breaks);
        //end footer
    }

    function createSummary($data, $title = 'Payroll Summary'){
        $pdf = new FPDF('L','mm','legal');
        $pdf->AddPage();
        $pdf->SetMargins(10,5,5);

        $pdf->SetFont('arial','',10);
        $pdf->Cell(20,5, strtoupper($title) ,0,1,'L');
        $pdf->Cell(20,5, 'Payroll Period: '. date('F d',strtotime($data[0]['date_start'])) .' - ' . date('d, Y',strtotime($data[0]['date_end'])) ,0,1,'L');
        $pdf->Ln();
        $pdf->SetFont('arial','B',7);

        $headers_start = array(50,30,10,20,15,15,15,15,15,15,15);
        $headers_mid = array();
        $headers_end = array(12,22,22,20);

        $pdf->Cell(50,8, "Name ",1,0,'C');
        $pdf->Cell(30,8, "Bank",1,0,'C');
        $pdf->Cell(10,8, "Status",1,0,'C');
        $pdf->Cell(20,8, "Rate",1,0,'C');
        $pdf->Cell(15,8, "This Period",1,0,'C');
        $pdf->Cell(15,8, "Tardiness",1,0,'C');
        $pdf->Cell(15,8, "Overtime",1,0,'C');
        $pdf->Cell(15,8, "ECOLA",1,0,'C');
        $pdf->Cell(15,8, "Gross Pay",1,0,'C');
        $pdf->Cell(15,8, "Allowance",1,0,'C');
        $pdf->Cell(15,8, "Total Pay",1,0,'C');

        foreach($this->contributionCodes as $k=> $v){
            $pdf->Cell(12,8, $v['contribution_name'],1,0,'C');
            $headers_mid[] = 12;
        }
        $pdf->Cell(12,8, "Tax",1,0,'C');
        $pdf->Cell(22,8, "Loans/C.A./Other",1,0,'C');
        $pdf->Cell(22,8, "Total Deductions",1,0,'C');
        $pdf->Cell(20,8, "Net Pay",1,1,'C');
        $pdf->SetFont('arial','',7);

        $total = array();
        foreach($data as $key=>$value){
            $payslip_data = json_decode($value['payslip_data'],true);
            $pdf->Cell(50,8, utf8_decode($value['last_name']).', '.utf8_decode($value['first_name']),1,0,'L');
            $pdf->Cell(30,8, $value['bank_number'],1,0,'C');
            $pdf->Cell(10,8, $value['tax_exemption_shortname'],1,0,'C');
            $pdf->Cell(20,8, number_format(($payslip_data['is_daily']!=1?$payslip_data['monthly_rate']:$payslip_data['daily_rate']),2),1,0,'R');

            $total[0] = 0;

            $rate = $payslip_data['basic_pay'];
            $pdf->Cell(15,8, number_format($rate,2),1,0,'R');
            $total[1] = (isset($total[1])? $total[1]+ $rate : $rate );

            if($payslip_data['is_daily']==1)
                $tardiness = $payslip_data['undertime_amount'] + $payslip_data['late_amount'];
            else
                $tardiness = $payslip_data['absent_amount'] + $payslip_data['undertime_amount'] + $payslip_data['late_amount'];

            $pdf->Cell(15,8, number_format($tardiness ,2),1,0,'R');
            $total[2] = (isset($total[2])? $total[2] + $tardiness : $tardiness);

            $pdf->Cell(15,8, number_format($payslip_data['overtime_amount'] + $payslip_data['holiday_pay'],2),1,0,'R');
            $total[3] = (isset($total[3])? $total[3] + $payslip_data['overtime_amount'] + $payslip_data['holiday_pay']: $payslip_data['overtime_amount'] + $payslip_data['holiday_pay']);

            $pdf->Cell(15,8, number_format($payslip_data['ecola'] ,2),1,0,'R');
            $total[4] = (isset($total[4])? $total[4] + $payslip_data['ecola'] : $payslip_data['ecola']);

            $pdf->Cell(15,8, number_format($payslip_data['gross_pay'] ,2),1,0,'R');
            $total[5] = (isset($total[5])? $total[5] + $payslip_data['gross_pay'] : $payslip_data['gross_pay']);

            $pdf->Cell(15,8, number_format($this->getTotals($payslip_data['additions']),2), 1,0,'R');
            $total[6] = (isset($total[6])? $total[6] + $this->getTotals($payslip_data['additions']): $this->getTotals($payslip_data['additions']));

            $pdf->Cell(15,8, number_format($payslip_data['gross_pay'] + $this->getTotals($payslip_data['additions']),2),1,0,'R');
            $total[7] = (isset($total[7])? $total[7] + $payslip_data['gross_pay'] + $this->getTotals($payslip_data['additions']) : $payslip_data['gross_pay'] + $this->getTotals($payslip_data['additions']));

            $f = 8;
            foreach($this->contributionCodes as $k=> $v){
                $pdf->Cell(12,8,number_format($this->getContributionsValue($payslip_data['contribution'], $v['contribution_name'] ) ,2),1,0,'C');
                $total[$f] = (isset($total[$f])? $total[$f] + $this->getContributionsValue($payslip_data['contribution'], $v['contribution_name'] ) : $this->getContributionsValue($payslip_data['contribution'], $v['contribution_name'] ));
                $f++;
            }

            $pdf->Cell(12,8, number_format($payslip_data['tax'],2),1,0,'R');
            $total[$f] = (isset($total[$f])? $total[$f] + $payslip_data['tax'] : $payslip_data['tax']);

            $pdf->Cell(22,8, number_format($this->getTotals($payslip_data['deductions']),2), 1,0,'R');
            $total[$f+1] = (isset($total[$f+1])? $total[$f+1] + $this->getTotals($payslip_data['deductions']): $this->getTotals($payslip_data['deductions']));

            $pdf->Cell(22,8, number_format($payslip_data['deduction_amount'],2),1,0,'R');
            $total[$f+2] = (isset($total[$f+2])? $total[$f+2] + $payslip_data['deduction_amount'] : $payslip_data['deduction_amount']);

            $pdf->Cell(20,8, number_format($payslip_data['net_pay'],2),1,1,'R');
            $total[$f+3] = (isset($total[$f+3])? $total[$f+3] + $payslip_data['net_pay'] : $payslip_data['net_pay']);
        }
        $pdf->SetFont('arial','B',7);
        for($x = 0; $x<sizeof($headers_start);$x++){
            if($x>2)
                $pdf->Cell($headers_start[$x],8, number_format($total[$x-3],2),1,0,'R');
            else
                $pdf->Cell($headers_start[$x],8, '',1,0,'R');
        }

        for($x = 0; $x<sizeof($headers_mid);$x++){
            $pdf->Cell($headers_mid[$x],8, number_format($total[$x+8],2),1,0,'R');
        }

        for($x = 0; $x<sizeof($headers_end);$x++){
            $pdf->Cell($headers_end[$x],8, number_format($total[$x+8+sizeof($headers_mid)],2),1,0,'R');
        }

        $pdf->SetFont('arial','',9);
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();
        $pdf->SetX(20);
        $pdf->Cell(53,5,"__________________________",0,0,'C');

        $pdf->SetX(100);
        $pdf->Cell(53,5,"__________________________",0,0,'C');

        $pdf->SetX(180);
        $pdf->Cell(53,5,"__________________________",0,0,'C');

        $pdf->SetX(260);
        $pdf->Cell(53,5,"__________________________",0,1,'C');

        $pdf->SetX(20);
        $pdf->Cell(53,5,"Prepared by:",0,0,'C');

        $pdf->SetX(100);
        $pdf->Cell(53,5,"Checked by:",0,0,'C');

        $pdf->SetX(180);
        $pdf->Cell(53,5,"Noted by: ",0,0,'C');

        $pdf->SetX(260);
        $pdf->Cell(53,5,"Approved by: ",0,1,'C');

        $pdf->Output('I');die;
    }

    function findTransaction($data, $name){

        $val = 0;
        foreach($data as $key=>$value){
            if($value['name'] == $name)
                $val += $value['amount'];
        }

        return $val;
    }

    function getContributionsValue($data, $name){
        $val = 0;
        if(!isset($data))
            return $val;

        foreach($data as $key=>$value){
            if($value['name']==$name)
                return $value['amount'];
        }
    }

    function getTotals($data){
        $total = 0;
        foreach($data as $value)
            $total+= $value['amount'];

        return $total;
    }

    function getHolidayTotals($data, $type_id, $field){
        $total = 0;
        foreach($data as $value){
            if($type_id == $value['holiday_type'])
                $total+= $value['totals'][$field];
        }
        return $total;
    }
}