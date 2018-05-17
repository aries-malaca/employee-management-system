<?php

namespace ExactivEM\Http\Controllers;

use Illuminate\Http\Request;

use ExactivEM\Http\Requests;
use ExactivEM\Holiday;
use ExactivEM\Branch;
use ExactivEM\HolidayType;
use Validator;

class HolidayController extends Controller
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
        //check user's restriction
        if(!$this->checkRestriction()){
            return view('errors.no_permission', $this->data);
        }
        //get companies,holiday types and holidays....
        $this->data['branches'] = json_decode(file_get_contents(url('collect/Branch')));
        $this->data['holiday_types'] = json_decode(file_get_contents(url('collect/HolidayType')));
        
        $this->data['holidays'] = Holiday::leftjoin('holiday_types','holidays.holiday_type_id','=','holiday_types.id')
                                            ->select('holidays.*', 'holidays.id as holiday_id', 'holiday_types.*')
                                            ->get()->all();
        
        //get holidays with company array
        foreach($this->data['holidays'] as $key => $value) {
            $branches = json_decode($value['branch_covered'], true);

            $this->data['holidays'][$key]['branches_name'] = '';

            if (in_array(0, $branches)) {
                $this->data['holidays'][$key]['branches_name'] = 'All';
            }
            else {
                $array = array();
                foreach ($branches as $key1 => $value1) {
                    $get = Branch::find($value1);
                    //fill the company covered of holiday
                    $branch_name = (isset($get->branch_name) ? $get->branch_name : '');
                    $array[] = $branch_name;
                }
                $this->data['holidays'][$key]['branches_name'] = implode(", ", $array);
            }
        }
        
        //return view
        return view('holidays', $this->data);
    }
    
    //add new holiday
   function processAdd(Request $request){
       
       //yearly must be unique!
       if($request->is_yearly == 1){

            $validator = Validator::make($request->all(), [
                        'holiday_name' => 'required|unique:holidays,holiday_name|max:255'
                    ]);

                
            //if there are an error return to view and display errors  
            if ($validator->fails()) 
            {
                return redirect()->back()
                        ->withErrors($validator,'adding_holiday')
                        ->withInput();
            }
       }
       
        //new holiday object
        $holiday = new Holiday;
        $holiday->is_yearly = $request['is_yearly'];
        $holiday->holiday_name = $request['holiday_name'];
        $holiday->holiday_type_id = $request['holiday_type_id'];
        $holiday->holiday_date = date('Y-m-d',strtotime($request['holiday_date']));
        if(in_array(0,  $request['branch'])){
            $branch = array("0");
        }
        else{
            $branch = $request['branch'];
        }
        $holiday->branch_covered = json_encode($branch);
        $holiday->save();

        //writelog
        $details = 'Added Holiday '. $holiday->holiday_name;
        $this->writeLog("Holiday", $details);
        
        //return with success message
        return redirect()->back()->with('adding', 'success');
    }
    
    //deletes the holiday 
    function processDelete(Request $request){
        
        $holiday_info = Holiday::find($request->input('id'));
        
        //writelog
        $details = 'Deleted Holiday '. $holiday_info->holiday_name;
        $this->writeLog("Holiday", $details);
        
        if($holiday = Holiday::destroy($request->input('id')) )
        {
            //return with success message
            return redirect()->back()->with('deleting', 'success');
        }
    }
    
    //deletes holiday type
    function processDeleteType(Request $request){
        $holiday_info = HolidayType::find($request->input('id'));
        
        //writelog
        $details = 'Deleted Holiday Type '. $holiday_info->holiday_type_name;
        $this->writeLog("Holiday Type", $details);
        
        if($holiday = HolidayType::destroy($request->input('id')) )
        {
            //return with success message
            return redirect()->back()->with('deleting_type', 'success');
        }
    }
    
    function processAddType(Request $request){
        //validate inputs
        $validator = Validator::make($request->all(), [
                    'holiday_type_name' => 'required|unique:holiday_types,holiday_type_name|max:255'
                ]);

            
        //if there are an error return to view and display errors  
        if ($validator->fails()) 
        {
            return redirect()->back()
                    ->withErrors($validator,'adding_type')
                    ->withInput();
        }
       
        //new object
        $holiday = new HolidayType;
        $holiday->holiday_type_name = $request['holiday_type_name'];
        //encode to json the holiday type data
        $holiday->holiday_type_data = json_encode(array("present_workday"=>$request['present_workday'], "absent_workday"=>$request['absent_workday'], 
                                                        "present_restday"=>$request['present_restday'], "absent_restday"=>$request['absent_restday'],
                                                        "beyond_workday"=>$request['beyond_workday'], "beyond_restday"=>$request['beyond_restday']
                                                        ));
        //save the holiday type
        $holiday->save();
        
        //writelog
        $details = 'Added Holiday Type '. $holiday->holiday_type_name;
        $this->writeLog("Holiday Type", $details);
        //success
        return redirect()->back()->with('adding_type', 'success');
    }
    
    
     function processEditType(Request $request){
        //validate inputs
        $validator = Validator::make($request->all(), [
                    'holiday_type_name' => 'required|unique:holiday_types,holiday_type_name,'.$request->id.'|max:255'
                ]);

        //if there are an error return to view and display errors  
        if ($validator->fails()) 
        {
            return redirect()->back()
                    ->withErrors($validator,'editing_type')
                    ->withInput();
        }
       
        $holiday =HolidayType::find($request['id']);
        $holiday->holiday_type_name = $request['holiday_type_name'];
        //encode to json the holiday type data
        $holiday->holiday_type_data = json_encode(array("present_workday"=>$request['present_workday'], "absent_workday"=>$request['absent_workday'], 
                                                        "present_restday"=>$request['present_restday'], "absent_restday"=>$request['absent_restday'],
                                                        "beyond_workday"=>$request['beyond_workday'], "beyond_restday"=>$request['beyond_restday']
                                                        ));
        $holiday->save();
        
        //writelog
        $details = 'Updated Holiday Type '. $holiday->holiday_type_name;
        $this->writeLog("Holiday Type", $details);
        
        //success edit.
        return redirect()->back()->with('editing_type', 'success');
    }
}
