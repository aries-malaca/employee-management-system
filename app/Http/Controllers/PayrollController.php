<?php
namespace ExactivEM\Http\Controllers;
use ExactivEM\Branch;
use Illuminate\Http\Request;
use ExactivEM\Http\Requests;
use ExactivEM\Libraries\Payroll_Class;
use ExactivEM\Libraries\Pdf_Class;
use ExactivEM\Libraries\Attendance_Class;
use ExactivEM\Libraries\ThirteenthMonth_Class;
use ExactivEM\Libraries\PayrollReport_Class;
use ExactivEM\User;
use ExactivEM\Company;
use ExactivEM\Batch;
use ExactivEM\Contribution;
use ExactivEM\Payslip;
use ExactivEM\PayslipSet;
use Illuminate\Support\Facades\Auth;
use Excel;
use File;
use Response;

class PayrollController extends Controller{
    public function __construct(){
        $this->middleware('auth');
        parent::__construct();
    }

    function index(){
        //check user's permission
        if(!$this->checkRestriction())
            return view('errors.no_permission', $this->data);
        $this->clearSingles();
        //return view
        return view('payroll', $this->data);
    }

    function getPayrolls(){
        return response()->json(PayslipSet::get());
    }

    function getPayrollPeriods(Request $request){
        $data = Payslip::where('employee_id', $request->segment(3))
            ->select('date_start','date_end','payslip_data')
            ->get()->toArray();

        foreach($data as $key=>$value){
            $data[$key]['name'] = date('m/d/Y', strtotime($value['date_start'])) .'-'.date('m/d/Y', strtotime($value['date_end']));
            $data[$key]['start']= date('Y-m-d', strtotime($value['date_start']));
            $data[$key]['end']= date('Y-m-d', strtotime($value['date_end']));
            $data[$key]['payslip_data']= json_decode($value['payslip_data']);
        }

        return response()->json($data);
    }

    function getPayslips(Request $request){
        return response()->json(Payslip::leftJoin('users','payslips.generated_by_id','=','users.id')
            ->where('status','published')
            ->where('employee_id', $request->segment(3))
            ->select('payslips.id','date_start','date_end','generated_by_id','payslips.created_at','payslips.updated_at','name')
            ->get());
    }

    function previewSingle(Request $request){
        //new PDF Object
        $get = Payslip::leftJoin('users', 'payslips.employee_id','=','users.id')
            ->leftJoin('positions', 'users.position_id','=','positions.id')
            ->leftJoin('departments', 'users.department_id','=','departments.id')
            ->where("payslips.id", $request->segment(3))
            ->get()->first();

        if(!isset($get['name'])){
            return view('errors.404');
        }

        //generate a preview for single pdf
        new Pdf_Class('Single', $get);
    }

    function previewMultiple(Request $request){
        //new PDF Object
        $data = array();
        $get = PayslipSet::find($request->segment(4));

        $list = Payslip::leftJoin('users', 'payslips.employee_id','=','users.id')
            ->leftJoin('positions', 'users.position_id','=','positions.id')
            ->leftJoin('departments', 'users.department_id','=','departments.id')
            ->leftJoin('companies', 'users.company_id','=','companies.id')
            ->leftJoin('tax_exemptions', 'users.tax_exemption_id','=','tax_exemptions.id')
            ->where('payslip_set_id', $request->segment(4))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()->all();

        foreach($list as $key=>$value){
            $data[] = $value;
        }

        if(empty($list)){
            return view('errors.404');
        }

        if($request->segment(5) == 'Summary'){
            return $this->createExcel($list);
        }

        //generate multiple payslip
        return new Pdf_Class($request->segment(5), $data, $get->category_set);
    }

    function preparePayroll(Request $request){
        if($request->input('generate_by') == 'companies'){
            if($request->input('company_id') == 0)
                return response()->json(["command"=>"preparePayroll","result"=>"failed","errors"=>"Company is required"]);
        }
        if($request->input('generate_by') == 'employees') {
            if ($request->input('employee_id') == 0)
                return response()->json(["command" => "preparePayroll", "result" => "failed", "errors" => "Employee is required"]);
        }
        if($request->input('generate_by') == 'batches') {
            if ($request->input('batch_id') == 0)
                return response()->json(["command" => "preparePayroll", "result" => "failed", "errors" => "Batch is required"]);
        }

        //range selector
        $start_day = ($request->input('cutoff') == 1? 1:16 );
        $end_day = ($request->input('cutoff') == 1? 15:date('t',strtotime($request->input('year') . '-' . $request->input('month') .'-01') ) );
        $date_start = date('Y-m-d',strtotime($request->input('year') . '-' . $request->input('month') . '-' . $start_day));
        $date_end = date('Y-m-d',strtotime($request->input('year') . '-' . $request->input('month') . '-' . $end_day));
        //end range selector


        $employees = [];
        if($request->input('generate_by') == 'companies'){
            $category_value = $request->input('company_id');
            $employees = User::where('active_status',1)->orderBy('last_name')->where('company_id', $request->input('company_id'))->get()->toArray();
        }
        elseif($request->input('generate_by') == 'batches'){
            $category_value = $request->input('batch_id');
            $employees = User::where('active_status',1)->orderBy('last_name')->where('batch_id', $request->input('batch_id'))->get()->toArray();
        }
        elseif($request->input('generate_by') == 'employees'){
            $category_value = $request->input('employee_id');
            $employees = User::where('active_status',1)->orderBy('last_name')->where('id', $request->input('employee_id'))->get()->toArray();
        }
        elseif($request->input('generate_by') == 'branches'){
            $category_value = $request->input('branch_id');
            $employees = $this->getBranchEmployees($request->input('branch_id'), $date_end ,'last_name');
        }

        //create of a payslip set
        if($request->input('generate_by') != 'employees') {
            PayslipSet::where('category_set', $request->input('generate_by'))
                ->where('category_value', $category_value)
                ->where('date_start','like',$date_start.'%')
                ->where('date_end','like',$date_end.'%')
                ->where('status','draft')
                ->delete();

            $set = new PayslipSet;
            $set->category_set = $request->input('generate_by');
            $set->category_value =$category_value;
            $set->generated_by_id = Auth::user()->id;
            $set->date_start = $date_start;
            $set->date_end = $date_end;
            $set->status = 'draft';
            $set->save();
        }
        //end create of payslip set
        $filtered = array();
        foreach($employees as $key=>$value){
            $employees[$key]['success'] = null;
            $employees[$key]['payslip_id'] = 0;

            if(strtotime($date_end) >= strtotime($value['hired_date'])){
                $filtered[] = $employees[$key];
            }
        }

        $result = array("payslip_id"=>$set->id,
            "generate_by"=>$request->input('generate_by'),
            "date_start"=>$date_start,
            "date_end"=>$date_end,
            "employees"=> $filtered,
            "command" => "preparePayroll",
            "result" => "success",
            "id"=>$category_value);
        return response()->json($result);
    }

    //generate payroll using Payroll Class Library
    function generatePayroll(Request $request){
        if ($request->input('employee_id') == 0)
            return response()->json(["command" => "generatePayroll", "result" => "failed", "errors" => "Employee is required"]);

        //range selector
        $start_day = ($request->input('cutoff') == 1? 1:16 );
        $end_day = ($request->input('cutoff') == 1? 15:date('t',strtotime($request->input('year') . '-' . $request->input('month') .'-01') ) );
        $date_start = date('Y-m-d',strtotime($request->input('year') . '-' . $request->input('month') . '-' . $start_day));
        $date_end = date('Y-m-d',strtotime($request->input('year') . '-' . $request->input('month') . '-' . $end_day));
        //end range selector

        $category_value = $request->input('employee_id');
        $employees = User::where('active_status',1)->orderBy('last_name')->where('id', $request->input('employee_id'))->get()->toArray();

        foreach($employees as $employee){
            $payroll = new Payroll_Class($employee['id'], $date_start, $date_end);
            //compute payroll
            $data = $payroll->computePayroll();
            Payslip::where('employee_id', $employee['id'])
                ->where('status','draft')
                ->where('date_start','like',$date_start.'%')
                ->where('date_end','like',$date_end.'%')
                ->delete();

            $payslip = new Payslip;
            $payslip->employee_id = $employee['id'];
            $payslip->company_id = $payroll->employee->company_id;
            $payslip->batch_id = $payroll->employee->batch_id;
            $payslip->date_start = $date_start;
            $payslip->date_end = $date_end;
            $payslip->generated_by_id = Auth::user()->id;
            $payslip->payslip_data = json_encode($data);
            $payslip->payslip_set_id = $request->input('payslip_id');
            $payslip->status = 'draft';
            //save to database
            $payslip->save();

            if($request->input('generate_by') == 'employees'){
                $result = array("id"=>  $employee['id'],
                    "date_start"=> date("m/d/Y",strtotime($date_start)),
                    "date_end"=> date("m/d/Y",strtotime($date_end)),
                    "type"=> "single",
                    "payslip_id"=>$payslip->id,
                    "generate_by"=> $request->input('generate_by'),
                    "generation_warnings"=>array()
                );

                $this->finalizeSchedule($employee['id'], $date_start, $date_end);

                return response()->json($result);
            }
        }
    }

    function getGenerationWarnings($payslip_id){
        $errors = array();
        $payslips = Payslip::leftJoin('users','payslips.employee_id','=','users.id')
            ->where('payslip_set_id', $payslip_id)->get()->all();

        foreach($payslips as $key=>$value){
            $data = json_decode($value['payslip_data'], true);
            if($data['no_timeouts']>0 OR $data['absents']>0){
                $errors[] = array("no_timeouts"=>$data['no_timeouts_list'],
                    "absents"=>$data['absents'],
                    "last_name"=>$value['last_name'],
                    "first_name"=>$value['first_name'],
                    "id"=>$value['employee_id'],
                    "employee_no"=>$value['employee_no']
                );
            }
        }

        return $errors;
    }

    //function to delete the payrollset
    function deletePayroll(Request $request){
        Payslip::where('payslip_set_id', $request->input('id'))->delete();
        PayslipSet::destroy($request->input('id'));
        
        //return success message
        return response()->json(['command' => 'deletePayroll', 'result' => 'success']);
    }
    //publish the payroll set
    function publishPayroll(Request $request){
        $get = PayslipSet::find($request->input('id'));

        $compare = PayslipSet::where('category_set', $get->category_set)
            ->where('category_value', $get->category_value)
            ->where('date_start', $get->date_start)
            ->where('date_end', $get->date_end)
            ->where('status','published')
            ->get()->all();
        if(!empty($compare)){
            return response()->json(['command'=>'deletePayroll','result'=>'failed','errors'=>'Failed to publish.']);
        }
        //mark as published and save
        $get->status = 'published';
        $get->save();

        Payslip::where('payslip_set_id', $request->input('id'))->update(array('status'=>'published'));
        //return with success message
        return response()->json(['command'=>'publishPayroll','result'=>'success']);
    }

    function generateOT(Request $request){
        $sheets = [["name"=>"LBFC", "companies"=>[1]], ["name"=>"LBSDC", "companies"=>[2]],["name"=>"LBWPI","companies"=>[3]]];
        $start = $request->input('year').'-'.$request->input('month').'-'.($request->input('cutoff')==1?1:16);
        $start = date('Y-m-d', strtotime($start));

        $sample = Payslip::where('date_start','LIKE', $start .'%')
            ->get()->first();

        if(isset($sample['id'])){
            Excel::load(public_path('csv/OT.xlsx'), function($excel) use ($sheets, $sample, $start ){
                foreach($sheets as $s){
                    $excel->sheet($s['name'], function($sheet) use ($s, $sample, $start ) {
                        $companies = Company::whereIn('id', $s['companies'])->pluck('company_name')->toArray();
                        $sheet->cell('A1', implode(",",$companies));
                        $date_string = date('F d', strtotime($sample['date_start'])).'-'.date('d, Y', strtotime($sample['date_end']));
                        $sheet->cell('A2', "Overtime Report");
                        $sheet->cell('A4', $date_string);

                        $payslips = Payslip::leftJoin('users','payslips.employee_id','=', 'users.id')
                                        ->where('date_start','LIKE', $start .'%')
                                        ->whereIn('payslips.company_id', $s['companies'])
                                        ->select('payslips.*','last_name','name','first_name','middle_name')
                                        ->get()->toArray();

                        foreach($payslips as $key=>$p){
                            $payslip = json_decode($p['payslip_data']);
                            $sheet->row(7+$key, array(
                                    $key+1,
                                    $p['last_name'] .', '. $p['first_name'],
                                    $payslip->salary_rates->daily,
                                    $payslip->overtimes->regular_overtime->minutes/60,               // p overtime hours
                                    '=D'.(7+$key).'*('.$payslip->overtimes->regular_overtime->rate.')*(C'.(7+$key).'/8)',             // q

                                    $payslip->overtimes->restday_overtime->minutes/60,                                            // r
                                    '=F'.(7+$key).'*('.$payslip->overtimes->restday_overtime->rate.')*(C'.(7+$key).'/8)',             // s

                                    (($payslip->overtimes->regular_nightdiff->minutes/60) + ($payslip->overtimes->restday_nightdiff->minutes/60)),                                          // t
                                    '=H'.(7+$key).'*('.$payslip->overtimes->regular_nightdiff->rate.')*(C'.(7+$key).'/8)',           // u

                                    $payslip->overtimes->restday_beyond_overtime->minutes/60,                                          // v
                                    '=J'.(7+$key).'*('.$payslip->overtimes->restday_beyond_overtime->rate.')*(C'.(7+$key).'/8)',
                                    '=( E'.(7+$key) . '+G'.(7+$key) . '+I'.(7+$key) . '+K'.(7+$key) . ')'
                            ));
                        }

                    });
                    $excel->calculate();
                }
            })->store('xlsx', public_path('report'));

            $file = public_path('report/OT.xlsx');

            if (File::isFile($file))
                return response()->json(['result'=>'success',"file"=>'OT.xlsx']);

            return response()->json(['result'=>'failed']);
        }
    }

    function createExcel($data){
        Excel::load(public_path('csv/PAYROLL REPORT.xlsx'), function($excel) use ($data){
            $excel->sheet('Detailed', function($sheet) use ($data) {
                $start = 7;
                $date_string = date('F d', strtotime($data[0]['date_start'])).'-'.date('d, Y', strtotime($data[0]['date_end']));
                $sheet->cell('A1', $data[0]['company_name']);
                $sheet->cell('A3', $date_string);

                foreach($data as $key=>$value){
                    $payslip = json_decode($value['payslip_data']);
                    $row = $key+$start;

                    $branch = Branch::find($payslip->branch_id);
                    if($payslip->is_daily){
                        $columnD = isset($branch->id)?$branch->branch_name:'N/A';
                        $columnE = $payslip->days_worked;
                        $columnF = $payslip->paid_leaves;
                        $columnG = $payslip->leave_credit;
                        $columnH = $payslip->salary_rates->daily;
                        $formulaP = '((E'.$row.'*H'.$row.')+G'.$row.'+I'.$row.')';

                        if($key==0){
                            $sheet->cell('D5', 'Branch');
                            $sheet->cell('E5', 'Days Worked');
                        }

                        $payslip->absents=0;
                    }
                    else{
                        if($key==0){
                            $sheet->cell('D5', 'Monthly Rate');
                            $sheet->cell('E5', 'This Period');
                        }

                        $columnD =$payslip->salary_rates->monthly;
                        $columnE = '=D'.$row.'/2';
                        $columnF = 0;
                        $columnH = '=SUM((D'.$row.'/'.($payslip->working_days>11?'313':'261').')*12)';
                        $formulaP = 'E'.$row .'-O'.$row . '+I'.$row;
                        $columnG = 0;
                    }

                    $sheet->row($start+$key, array(
                        $key+1,                                          // A No
                        $value['last_name'].', '. $value['first_name'] , // B name 
                        $value['bank_number'],                           // C bank
                        $columnD,                                       // D monthly rate
                        $columnE,                                       // E this period
                        $columnF,                                       // E this period
                        $columnG,                                       // E this period
                        $columnH,                                       // F daily rate
                        $payslip->ecola,                                  //g COLA
                        $payslip->late_minutes/60,                          // H late hours
                        '=(H'.$row.'/8)*J'.$row,                         // i late amount
                        $payslip->under_time_minutes/60,                     // j undertime hours
                        '=(H'.$row.'/8)*L'.$row,                        // k undertime amount
                        $payslip->absents,                               // l absents
                        '=H'.$row.'*N'.$row,                             // o absent amount

                        $payslip->overtimes->regular_overtime->minutes/60,               // p overtime hours
                        '=P'.$row.'*('.$payslip->overtimes->regular_overtime->rate.')*(H'.$row.'/8)',             // q

                        $payslip->overtimes->restday_overtime->minutes/60,                                            // r
                        '=R'.$row.'*('.$payslip->overtimes->restday_overtime->rate.')*(H'.$row.'/8)',             // s

                        (($payslip->overtimes->regular_nightdiff->minutes/60) + ($payslip->overtimes->restday_nightdiff->minutes/60)),                                          // t
                        '=T'.$row.'*('.$payslip->overtimes->regular_nightdiff->rate.')*(H'.$row.'/8)',           // u

                        $payslip->overtimes->restday_beyond_overtime->minutes/60,                                          // v
                        '=V'.$row.'*('.$payslip->overtimes->restday_beyond_overtime->rate.')*(H'.$row.'/8)',           // R

                        $payslip->regular_holiday->minutes/60 + ($payslip->is_daily?$this->getHolidayHours($payslip->holidays,1,'absent_restday'):0) + ($payslip->is_daily?$this->getHolidayHours($payslip->holidays,1,'absent_workday'):0),                                          // Q
                        '=X'.$row.'*('.$payslip->regular_holiday->rate.')*(H'.$row.'/8)',

                        $payslip->regular_holiday_ot->minutes/60,                                          // Q
                        '=Z'.$row.'*('.$payslip->regular_holiday_ot->rate.')*(H'.$row.'/8)',

                        $payslip->regular_holiday_restday->minutes/60,                                          // Q
                        '=AB'.$row.'*('.($payslip->regular_holiday_restday->rate + 1).')*(H'.$row.'/8)',

                        $payslip->regular_holiday_restday_beyond->minutes/60,                                          // Q
                        '=AD'.$row.'*('.$payslip->regular_holiday_restday_beyond->rate.')*(H'.$row.'/8)',

                        $payslip->regular_holiday_nightdiff->minutes/60,                                          // Q
                        '=AF'.$row.'*('.$payslip->regular_holiday_nightdiff->rate.')*(H'.$row.'/8)',

                        $payslip->special_holiday->minutes/60,                                          // Q
                        '=AH'.$row.'*('.$payslip->special_holiday->rate.')*(H'.$row.'/8)',

                        $payslip->special_holiday_ot->minutes/60,                                          // Q
                        '=AJ'.$row.'*('.$payslip->special_holiday_ot->rate.')*(H'.$row.'/8)',

                        $payslip->special_holiday_restday->minutes/60,                                          // Q
                        '=AL'.$row.'*('. (($payslip->is_daily? 0:1) + $payslip->special_holiday_restday->rate) .')*(H'.$row.'/8)',

                        $payslip->special_holiday_restday_beyond->minutes/60,                                          // Q
                        '=AN'.$row.'*('.$payslip->special_holiday_restday_beyond->rate.')*(H'.$row.'/8)',

                        $payslip->special_holiday_nightdiff->minutes/60,                                          // Q
                        '=AP'.$row.'*('.$payslip->special_holiday_nightdiff->rate.')*(H'.$row.'/8)',            //pq

                        '=('. $formulaP .'-SUM(O'.$row.',K'.$row.',M'.$row.')) + O'.$row.'+Q'.$row.'+S'.$row.'+U'.$row.'+W'.$row.'+Y'.$row.'+AA'.
                        $row .'+AC'. $row .'+AE'. $row .'+AG'. $row .'+AI'. $row .'+AK'. $row .'+AM'. $row .'+AO'.$row .'+AQ'.$row ,                        // r

                        ($payslip->additions->total) - $this->findTransaction($payslip->additions->transactions, 'Special Adjustment') - $this->findTransaction($payslip->additions->transactions, 'Salary Adjustment') - $this->findTransaction($payslip->additions->transactions, '13th Month Pay Adjustment'),                             // B
                        $this->findTransaction($payslip->additions->transactions, 'Special Adjustment') + $this->findTransaction($payslip->additions->transactions, 'Salary Adjustment'),                             // B
                        $this->findTransaction($payslip->additions->transactions, '13th Month Pay Adjustment'),                             // B
                        '=AR'.$row.'+AS'.$row.'+AT'.$row.'+AU'.$row,                                              // C
                        $this->findTransaction($payslip->contributions, 'SSS'),              // D
                        $this->findTransaction($payslip->contributions, 'Pagibig'),          // e
                        $this->findTransaction($payslip->contributions, 'Philhealth'),        // f
                        $payslip->tax,                                                      // g
                        $this->findTransaction($payslip->deductions->transactions, 'SSS Loan'),         // h
                        $this->findTransaction($payslip->deductions->transactions, 'Pagibig Loan'),     // i
                        $this->findTransaction($payslip->deductions->transactions, 'EO'),               // j
                        $this->findTransaction($payslip->deductions->transactions, 'Supplies'),         // k
                        $this->findTransaction($payslip->deductions->transactions, 'LB Salary Loan'),   // l
                        $this->findTransaction($payslip->deductions->transactions, 'TShirt/Uniform'),   // m
                        $this->findTransaction($payslip->deductions->transactions, 'Other Deductions'),   // m
                        $this->findTransaction($payslip->deductions->transactions, 'Receivables From Employee'),   // m
                        $this->findTransaction($payslip->deductions->transactions, 'Penalty Charges'),   // m
                        $this->findTransaction($payslip->deductions->transactions, 'Deposit Shortage/ Overage'),   // m
                        '=AV'.$row.'-SUM(AW'.$row.':BJ'.$row.')',                         // n
                        '',
                        $value['tin_no'],
                        '',
                        '',
                    ));
                }

                $sheet->cell('BK' . ($row+1), '=SUM(BK7:BK'. $row .')');
            });
            $excel->calculate();
        })->store('xlsx', public_path('report'));

        $file = public_path('report/PAYROLL REPORT.xlsx');

        if (File::isFile($file))
            return Response::download($file);
    }

    function generateReport(Request $request){
        $sheets = [["name"=>"LBFC", "companies"=>[1]], ["name"=>"LBSDC", "companies"=>[2]],["name"=>"LBWPI","companies"=>[3]]];
        $start = $request->input('year').'-'.$request->input('month').'-'.($request->input('cutoff')==1?1:16);
        $start = date('Y-m-d', strtotime($start));

        $sample = Payslip::where('date_start','LIKE', $start .'%')
                            ->get()->first();

        if(isset($sample['id'])){
            Excel::load(public_path('csv/Consolidated.xlsx'), function($excel) use ($sheets, $sample){
                foreach($sheets as $s){
                    $excel->sheet($s['name'], function($sheet) use ($s, $sample) {
                        $companies = Company::whereIn('id', $s['companies'])->pluck('company_name')->toArray();
                        $sheet->cell('A1', implode(",",$companies));
                        $columns = ["B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X",
                            "Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ","BA","BB","BC","BD","BE","BF"];

                        $date_string = date('F d', strtotime($sample['date_start'])).'-'.date('d, Y', strtotime($sample['date_end']));
                        $sheet->cell('A2', "Consolidated Payroll Report for the year " . date('Y', strtotime($sample['date_start'])));
                        $sheet->cell('A4', $date_string);
                        $branches = $this->getConsolidatedBranches(date('Y-m-d',strtotime($sample['date_start'])), $s['companies']);

                        $index = 0;
                        foreach($branches as $key=>$value){
                            $sheet->cell($columns[$index].'4', $value['branch_name']);
                            $sheet->cell($columns[$index].'5', $this->getFieldSum($value['payslips'], 'gross_pay'));
                            $sheet->cell($columns[$index].'6', $this->getFieldSum($value['payslips'], 'additions','total'));
                            $sheet->cell($columns[$index].'7', $this->getFieldSum($value['payslips'], 'tardiness'));
                            $sheet->cell($columns[$index].'9', $this->getTransactionSum('contributions','blank', $value['payslips'], 'SSS'));
                            $sheet->cell($columns[$index].'10', $this->getTransactionSum('deductions','transactions', $value['payslips'], 'SSS Loan'));
                            $sheet->cell($columns[$index].'11', $this->getTransactionSum('contributions','blank', $value['payslips'], 'Philhealth'));
                            $sheet->cell($columns[$index].'12', $this->getTransactionSum('contributions','blank', $value['payslips'], 'Pagibig'));
                            $sheet->cell($columns[$index].'13', $this->getTransactionSum('deductions','transactions', $value['payslips'], 'Pagibig Loan'));
                            $sheet->cell($columns[$index].'14', $this->getFieldSum($value['payslips'], 'tax'));
                            $sheet->cell($columns[$index].'18', $this->getTransactionSum('deductions','transactions', $value['payslips'], 'Supplies'));
                            $sheet->cell($columns[$index].'19', $this->getTransactionSum('deductions','transactions', $value['payslips'], 'EB Make Up'));
                            $sheet->cell($columns[$index].'20', $this->getTransactionSum('deductions','transactions', $value['payslips'], 'Other Deductions'));
                            $sheet->cell($columns[$index].'21', $this->getTransactionSum('deductions','transactions', $value['payslips'], 'Penalty Charges'));
                            $sheet->cell($columns[$index].'22', $this->getTransactionSum('deductions','transactions', $value['payslips'], 'Deposit Shortage/ Overage'));
                            $sheet->cell($columns[$index].'23', $this->getTransactionSum('deductions','transactions', $value['payslips'], 'Receivables From Employee'));
                            $sheet->cell($columns[$index].'24', $this->getTransactionSum('deductions','transactions', $value['payslips'], 'LB Salary Loan'));
                            $sheet->cell($columns[$index].'25', $this->getTransactionSum('deductions','transactions', $value['payslips'], 'TShirt/Uniform'));
                            $sheet->cell($columns[$index].'26', $this->getTransactionSum('deductions','transactions', $value['payslips'], 'EO'));
                            $sheet->cell($columns[$index].'27', $this->getFieldSum($value['payslips'], 'net_pay'));
                            $index++;
                        }

                        $sheet->cell($columns[$index+1].'4', 'Total');
                        $sheet->cell($columns[$index+1].'5', '=SUM('.$columns[0].'5:'.$columns[$index].'5)');
                        $sheet->cell($columns[$index+1].'6', '=SUM('.$columns[0].'6:'.$columns[$index].'6)');
                        $sheet->cell($columns[$index+1].'7', '=SUM('.$columns[0].'7:'.$columns[$index].'7)');
                        $sheet->cell($columns[$index+1].'9', '=SUM('.$columns[0].'9:'.$columns[$index].'9)');
                        $sheet->cell($columns[$index+1].'10', '=SUM('.$columns[0].'10:'.$columns[$index].'10)');
                        $sheet->cell($columns[$index+1].'11', '=SUM('.$columns[0].'11:'.$columns[$index].'11)');
                        $sheet->cell($columns[$index+1].'12', '=SUM('.$columns[0].'12:'.$columns[$index].'12)');
                        $sheet->cell($columns[$index+1].'13', '=SUM('.$columns[0].'13:'.$columns[$index].'13)');
                        $sheet->cell($columns[$index+1].'14', '=SUM('.$columns[0].'14:'.$columns[$index].'14)');
                        $sheet->cell($columns[$index+1].'18', '=SUM('.$columns[0].'18:'.$columns[$index].'18)');
                        $sheet->cell($columns[$index+1].'19', '=SUM('.$columns[0].'19:'.$columns[$index].'19)');
                        $sheet->cell($columns[$index+1].'20', '=SUM('.$columns[0].'20:'.$columns[$index].'20)');
                        $sheet->cell($columns[$index+1].'21', '=SUM('.$columns[0].'21:'.$columns[$index].'21)');
                        $sheet->cell($columns[$index+1].'22', '=SUM('.$columns[0].'22:'.$columns[$index].'22)');
                        $sheet->cell($columns[$index+1].'23', '=SUM('.$columns[0].'23:'.$columns[$index].'23)');
                        $sheet->cell($columns[$index+1].'24', '=SUM('.$columns[0].'24:'.$columns[$index].'24)');
                        $sheet->cell($columns[$index+1].'25', '=SUM('.$columns[0].'25:'.$columns[$index].'25)');
                        $sheet->cell($columns[$index+1].'26', '=SUM('.$columns[0].'26:'.$columns[$index].'26)');
                        $sheet->cell($columns[$index+1].'27', '=SUM('.$columns[0].'27:'.$columns[$index].'27)');

                    });
                    $excel->calculate();
                }
            })->store('xlsx', public_path('report'));

            $file = public_path('report/Consolidated.xlsx');

            if (File::isFile($file))
                return response()->json(['result'=>'success',"file"=>'Consolidated.xlsx']);

            return response()->json(['result'=>'failed']);
        }
    }


    function getFieldSum($payslips, $field, $other_field='blank'){
        $sum = 0;
        foreach($payslips as $key=>$value){
            if($other_field == 'blank' )
                $sum += $value['payslip_data']->$field;
            else
                $sum += $value['payslip_data']->$field->$other_field;
        }

        return $sum;
    }

    function getConsolidatedBranches($start, $companies){
        $branches = Branch::get()->toArray();
        $payslips = Payslip::where('date_start','LIKE', $start .'%')
            ->whereIn('company_id', $companies)->get()->toArray();

        foreach($branches as $key=>$value){
            $branches[$key]['payslips'] = array();
        }

        foreach($branches as $key=>$value){
            foreach($payslips as $k=>$v){
                $v['payslip_data'] = json_decode($v['payslip_data']);

                if($v['payslip_data']->branch_id == $value['id'])
                    $branches[$key]['payslips'][] = $v;
            }
        }

        foreach($branches as $key=>$value){
            if(empty($value['payslips']))
                unset($branches[$key]);
        }

        return $branches;
    }


    function findTransaction($data, $name){
        $val = 0;
        foreach($data as $key=>$value){
            if($value->name == $name)
                $val += $value->amount;
        }

        return $val;
    }


    function getTransactionSum($what, $field='blank', $payslips, $name){
        $sum = 0;
        foreach($payslips as $key=>$value){
            if($field == 'blank'){
                foreach($value['payslip_data']->$what as $k=>$v){
                    if($v->name == $name)
                        $sum += $v->amount;
                }
            }
            else{
                foreach($value['payslip_data']->$what->$field as $k=>$v){
                    if($v->name == $name)
                        $sum += $v->amount;
                }
            }

        }

        return $sum;
    }


    function getTotals($data){
        $total = 0;
        foreach($data as $value)
            $total+= $value->amount;

        return $total;
    }

    //function that sets payrollset to draft
    function draftPayroll(Request $request){
        Payslip::where('payslip_set_id', $request->input('id'))->update(array('status'=>'draft'));
        PayslipSet::where('id',$request->input('id'))->update(array('status'=>'draft'));
        //set to draft and return with success message
        return response()->json(['command'=>'draftPayroll','result'=>'success']);
    }

    function clearSingles(){
        Payslip::where('payslip_set_id', 0)
            ->where('created_at', 'NOT LIKE', date('Y-m-d').'%')
            ->delete();
    }


    function getHolidayHours($data, $type_id, $field){

        $total = 0;
        foreach($data as $value){
            if($type_id == $value->holiday_type)
                $total+= $value->values->$field;
        }
        if($total>0)
            return $total/60;

        return $total;
    }


    function generateContributions(Request $request){
        $start = $request->input('year').'-'.$request->input('month').'-'.($request->input('cutoff')==1?1:16);
        $start = date('Y-m-d', strtotime($start));

        $companies = Company::where('has_contributions', 1)->get()->toArray();
        foreach($companies as $key=>$value){
            $payslips = Payslip::leftJoin('users', 'payslips.employee_id', '=','users.id')
                            ->where('date_start','LIKE', $start .'%')
                            ->where('payslips.company_id', $value['id'])
                            ->select('users.*', 'payslip_data', 'payslips.company_id', 'date_start', 'payslips.employee_id')
                            ->orderBy('last_name')
                            ->get()->toArray();

            $companies[$key]['sss'] = $this->createSSSExcel($value, $payslips, $start);
            $companies[$key]['pagibig'] = $this->createPagibigExcel($value, $payslips, $start);
            $companies[$key]['philhealth'] = $this->createPhilhealthExcel($value, $payslips, $start);
        }

        return response()->json(['result'=>'success',"files"=>$companies]);
    }

    function createSSSExcel($company, $payslips, $start){
        Excel::load(public_path('csv/SSS.xlsx'), function($excel) use ($company, $payslips, $start){
            $excel->sheet('Sheet1', function($sheet) use ($company, $payslips, $start) {
                foreach($this->uniqueEmployees($payslips) as $key=>$value){
                    $gross =  $this->getGrossForMonth($start, $value['id']);
                    $ec = $gross >=14750 ?30:10; // ec constant
                    $ee = $this->getContForMonth($start, $value['id'],'SSS');
                    $er = $this->getER($gross);
                    $sheet->cell('A'.($key+3), str_replace('-','',$value['sss_no']));
                    $sheet->cell('B'.($key+3), $value['last_name']);
                    $sheet->cell('C'.($key+3), $value['first_name']);
                    $sheet->cell('D'.($key+3), substr($value['middle_name'],0,1));
                    $sheet->cell('E'.($key+3), $gross);
                    $sheet->cell('F'.($key+3), $ee);
                    $sheet->cell('G'.($key+3), $er);
                    $sheet->cell('H'.($key+3), '=SUM(F'.($key+3).'+G'.($key+3).')');
                    $sheet->cell('I'.($key+3), $ee>0?$ec:0);
                    $sheet->cell('J'.($key+3), '=SUM(H'.($key+3).'+I'.($key+3).')');
                    $sheet->cell('K'.($key+3), date('m/d/Y',strtotime($value['birth_date'])));
                }
            });
        })->store('xlsx', public_path('report/'.$company['company_name']));

        return array("filename"=>"SSS.xlsx", "name"=>$company['company_name']);
    }

    function createPagibigExcel($company, $payslips, $start){

        Excel::load(public_path('csv/PAGIBIG.xlsx'), function($excel) use ($company, $payslips, $start){
            $excel->sheet('Sheet1', function($sheet) use ($company, $payslips, $start) {

                foreach($this->uniqueEmployees($payslips) as $key=>$value){
                    $sheet->cell('A'.($key+3), str_replace('-','',$value['pagibig_no']));
                    $sheet->cell('B'.($key+3), '');
                    $sheet->cell('C'.($key+3), $value['last_name']);
                    $sheet->cell('D'.($key+3), $value['first_name']);
                    $sheet->cell('E'.($key+3), $value['middle_name']);
                    $sheet->cell('F'.($key+3), $this->getGrossForMonth($start, $value['id']));
                    $sheet->cell('G'.($key+3), $this->getContForMonth($start, $value['id'],'Pagibig'));
                    $sheet->cell('H'.($key+3), $this->getContForMonth($start, $value['id'],'Pagibig'));
                    $sheet->cell('I'.($key+3), $value['tin_no']);
                    $sheet->cell('J'.($key+3), date('m/d/Y',strtotime($value['birth_date'])));
                }
            });
        })->store('xlsx', public_path('report/'.$company['company_name']));

        return array("filename"=>"PAGIBIG.xlsx", "name"=>$company['company_name']);
    }

    function createPhilhealthExcel($company, $payslips, $start){
        Excel::load(public_path('csv/PHILHEALTH.xlsx'), function($excel) use ($company, $payslips, $start){
            $excel->sheet('Sheet1', function($sheet) use ($company, $payslips, $start) {

                foreach($this->uniqueEmployees($payslips) as $key=>$value){
                    $sheet->cell('A'.($key+3), $value['last_name']);
                    $sheet->cell('B'.($key+3), $value['first_name']);
                    $sheet->cell('C'.($key+3), $value['middle_name']);
                    $sheet->cell('D'.($key+3), str_replace('-','',$value['philhealth_no']));
                    $sheet->cell('E'.($key+3), $this->getGrossForMonth($start, $value['id']));
                    $sheet->cell('F'.($key+3), $this->getContForMonth($start, $value['id'], 'Philhealth'));
                    $sheet->cell('G'.($key+3), $this->getContForMonth($start, $value['id'], 'Philhealth'));
                    $sheet->cell('H'.($key+3), '=SUM(F'.($key+3).'+G'.($key+3).')');
                }
            });
        })->store('xlsx', public_path('report/'.$company['company_name']));

        return array("filename"=>'PHILHEALTH.xlsx', "name"=>$company['company_name']);
    }

    function getContForMonth($start, $id, $name){
        $year_month = date('Y-m', strtotime($start));
        $payslips = Payslip::where('employee_id', $id)
                            ->where('payslip_set_id','<>', 0)
                            ->where('date_start', 'LIKE', $year_month.'%')
                            ->get()->toArray();
        $total = 0;
        foreach($payslips as $key=>$value){
            $data = json_decode($value['payslip_data']);
            foreach($data->contributions as $k=>$v){
                if($v->name == $name)
                    $total+=$v->amount;
            }
        }
        return $total>0?$total:'0';
    }

    function getER($grosspay){
        $sss_table = Contribution::where('contribution_name', 'SSS')->get()->first();
        $data = json_decode($sss_table['contribution_data'], true);

        if(isset($data['data'])){
            foreach($data['data'] as $key=>$value){
                if($grosspay >= $value[0]['from'] && $grosspay <= $value[0]['to'] )
                    return $value[0]['employer'] - $value[0]['ec'];
            }
        }

        return 0;
    }

    function getGrossForMonth($start, $id){
        $year_month = date('Y-m', strtotime($start));
        $payslips = Payslip::where('employee_id', $id)
            ->where('payslip_set_id','<>', 0)
            ->where('date_start', 'LIKE', $year_month.'%')
            ->get()->toArray();
        $total = 0;
        foreach($payslips as $key=>$value){
            $data = json_decode($value['payslip_data']);
            $total += $data->gross_pay;
        }
        return $total>0?$total:'0';
    }

    function uniqueEmployees($payslips){
        $unique = [];
        foreach($payslips as $key=>$value){
            if(!$this->employeeExists($value['id'], $unique))
                $unique[] = $value;
        }
        return $unique;
    }

    function employeeExists($id, $array){
        foreach($array as $k=>$v){
            if($v['id'] == $id)
                return true;
        }

        return false;
    }
}