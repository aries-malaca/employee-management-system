<?php
namespace ExactivEM\Http\Controllers;
use Illuminate\Http\Request;

use ExactivEM\Http\Requests;
use ExactivEM\User;
use ExactivEM\Company;
use ExactivEM\Batch;
use ExactivEM\Trainee;
use ExactivEM\ReportSet;
use ExactivEM\Libraries\Report_Class;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller{
    public function __construct(){
        $this->middleware('auth');
        parent::__construct();
    }
    
    function index(){
        if(!$this->checkRestriction()){
            return view('errors.no_permission', $this->data);
        }
        return view('reports', $this->data);
    }

    function getReports(){
        return response()->json(ReportSet::get());
    }
    
    function generateReport(Request $request){
        if( (strtotime($request->input('date_end')) < strtotime($request->input('date_start')))
                OR $request->input('date_start')=='' OR $request->input('date_end') ===''){
            return response()->json(['command'=>'generateReport','result'=>'failed','errors'=>'Invalid date inputs.']);
        }
        $report_id = 0;
        if($request->input('generate_by') != 'employees'){
            if($request->input('generate_by') == 'companies'){
                //generate by company
                $set = array("type" => 'companies',"id"=> $request->input('company_id'));
                             
                $employees = User::orderBy('last_name')->orderBy('first_name')
                    ->where('company_id', $request->input('company_id'))
                    ->where('active_status',1)
                    ->get()->toArray();

                if($request->input('company_id') == 5){
                    $employees = Trainee::where('status','active')
                                        ->where('assigned_id',0)
                                        ->get()->toArray();
                } //trainee id number 5

            }
            elseif($request->input('generate_by') == 'batches'){
                //generate by batch
                $set = array("type" => 'batches',"id"=> $request->input('batch_id'));
                             
                $employees = User::orderBy('last_name')
                                    ->where('batch_id', $request->input('batch_id'))
                                    ->where('active_status',1)
                                    ->get()->toArray();
            }
            elseif($request->input('generate_by') == 'branches'){
                //generate by batch
                $set = array("type" => 'branches',"id"=> $request->input('branch_id'));
                $employees = $this->getBranchEmployees($request->input('branch_id'), $request->input('date_end'), 'last_name');
            }
            
            $data = array();
            foreach($employees as $employee){
                $employee["employee"] = $employee['id'];
                $employee["name"] =isset($employee['name'])?$employee['name']:$employee['first_name'].' '.$employee['last_name'] .'('.$employee['biometric_no'].')';
                $employee["date_start"] = $request->input('date_start');
                $employee["date_end"] = $request->input('date_end');
                if($request->input('generate_by') != 'companies')
                    $this->finalizeSchedule($employee['id'], $request->input('date_start'), $request->input('date_end'));

                $data[] = $employee;
            }
            $report = new Report_Class($request->input('report_type'), $data, $set['type'], $request->input('format'));
        }
        else{
            $data = array("employee"=>$request->input('employee_id'),
                          "date_start"=>$request->input('date_start'),
                          "date_end"=>$request->input('date_end'));

            $report = new Report_Class($request->input('report_type'), $data, 'single', $request->input('format'));

            $set = array("type" => 'employees',"id"=> $request->input('employee_id'));
        }
        
        return response()->json(['filename'=>$report->filename,
                                 'generate_by'=>$request->input('generate_by'),
                                 "date_start"=>$request->input('date_start'),
                                 "date_end"=>$request->input('date_end'),
                                 "id"=> $set['id'],
                                 "report_id"=>$report_id,
                                 "report_type"=>$request->input('report_type')]
                                );
    }
    
    function deleteReport(Request $request){
        $file = ReportSet::find($request->input('id'));
        if(file_exists(public_path('report/'.$file->report_url)))
            unlink(public_path('report/'.$file->report_url));

        ReportSet::destroy($request->input('id'));
        return response()->json(['command'=>'deleteReport','result'=>'success']);
    }
}