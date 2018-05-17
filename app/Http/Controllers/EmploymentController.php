<?php

namespace ExactivEM\Http\Controllers;

use Illuminate\Http\Request;
use ExactivEM\EmploymentStatus;
use ExactivEM\Http\Requests;
use ExactivEM\Leave_type;
use ExactivEM\HolidayType;
use Validator;

class EmploymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
        
        //default title and url
        $this->data['page']['parent'] = 'Payroll Setup';
        $this->data['page']['parent_url'] = '#';
    }
    
    function index()
    {
        //check user's permission
        if(!$this->checkRestriction()){
            return view('errors.no_permission', $this->data);
        }
        
        $this->data['employments'] = EmploymentStatus::get()->all();
        
        //get hol types
        foreach($this->data['employments'] as $key=> $employment){
            $types = explode(',',$employment['paid_holidays_types']);
            $this->data['employments'][$key]['paid_holiday_type_names'] = HolidayType::whereIn('id', $types)->get()->all();
        }
        
        
        //get leave types
        foreach($this->data['employments'] as $key=> $employment){
            $types = explode(',',$employment['paid_leave_types']);
            $this->data['employments'][$key]['paid_leave_type_names'] = Leave_type::whereIn('id', $types)->get()->all();
        }
        
        
        $this->data['holiday_types'] = json_decode(file_get_contents(url('collect/HolidayType')));
        $this->data['leave_types']= Leave_type::get()->all();
        
        return view('employment', $this->data);
    }
    
    //delete employement status
    function processDelete(Request $request){
        $status = EmploymentStatus::find($request->input('id'));
        
        //writelog
        $details = 'Deleted Employment Status '. $status->employment_status_name;
        $this->writeLog("Employment Status", $details);
        
        if($type = EmploymentStatus::destroy($request->input('id')) )
        {
            //return with success message
            return redirect()->back()->with('deleting', 'success');
        }
    }
    
    //add new employment status
    function processAdd(Request $request){
        //validate inputs
        $validator = Validator::make($request->all(), [
                    'employment_status_name' => 'required|unique:employment_statuses,employment_status_name|max:255'
                ]);
        //if there are an error return to view and display errors  
        if ($validator->fails()) 
        {
            return redirect()->back()
                    ->withErrors($validator,'adding_employment')
                    ->withInput();
        }
        
        //new object
        $status = new EmploymentStatus;
        $status->employment_status_name = $request['employment_status_name'];
        $status->employment_status_active = 1;
        $status->cola_frequency = $request['cola_frequency'];
        $status->salary_frequency = $request['salary_frequency'];
        $status->evaluation_months = $request['evaluation_months'];
        
        //paid leaves
        if(isset($request['leaves']))
            $status->paid_leave_types = implode(',', $request['leaves']);
        else
            $status->paid_leave_types = '';

        //paid holidays
        if(isset($request['holidays']))
            $status->paid_holidays_types = implode(',', $request['holidays']);
        else
            $status->paid_holidays_types = '';
        
        //finally save to the database
        $status->save();
        
        //writelog
        $details = 'Added Employee Status '.  $status->employment_status_name;
        $this->writeLog("Employee Status", $details);
            
        //return with success message
        return redirect()->back()->with('adding', 'success');
    }
    
    //update the employment status
    function processEdit(Request $request){
        
        if($status = EmploymentStatus::find($request->input('id')) )
        {
            //validate inputs
            $validator = Validator::make($request->all(), [
                        'employment_status_name' => 'required|unique:employment_statuses,employment_status_name,'.$request->id.'|max:255'
                    ]);
            //if there are an error return to view and display errors  
            if ($validator->fails()) 
            {
                return redirect()->back()
                        ->withErrors($validator,'editing_employment')
                        ->withInput();
            }
            
            $status->employment_status_name = $request['employment_status_name'];
            
            //paid leaves
            if(isset($request['leaves']))
                $status->paid_leave_types = implode(',', $request['leaves']);
            else
                $status->paid_leave_types = '';

            //paid holidays
            if(isset($request['holidays']))
                $status->paid_holidays_types = implode(',', $request['holidays']);
            else
                $status->paid_holidays_types = '';
                
            $status->cola_frequency = $request['cola_frequency'];
            $status->salary_frequency = $request['salary_frequency'];
            $status->evaluation_months = $request['evaluation_months'];
            //save to the database
            $status->save();
                    
            //writelog
            $details = 'Updated Employee Status '.  $status->employment_status_name;
            $this->writeLog("Employee Status", $details);
            
            //return with success message
            return redirect()->back()->with('editing', 'success');
        }
    }


    function getEmploymentStatuses(){
        return response()->json(EmploymentStatus::get());
    }
}
