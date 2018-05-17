<?php
namespace ExactivEM\Http\Controllers;
use Illuminate\Http\Request;
use ExactivEM\Http\Requests;
use ExactivEM\Department;
use ExactivEM\User;
use Validator;

class DepartmentController extends Controller{
    public function __construct(){
        $this->middleware('auth');
        parent::__construct();
        
        //default title and url
        $this->data['page']['parent'] = 'Management';
        $this->data['page']['parent_url'] = '#';
    }
    
    function index(){
        //check users permission to access the page
        if(!$this->checkRestriction()){
            return view('errors.no_permission', $this->data);
        }
        return view('departments', $this->data);
    }

    function getDepartments(){
        return response()->json(Department::get());
    }

    //adding a department method
    function addDepartment(Request $request){
        //validate inputs
        $validator = Validator::make($request->all(), [
                    'department_name' => 'required|unique:departments,department_name|max:255'
                ]);
        //if there are an error return to view and display errors  
        if ($validator->fails()){
            return response()->json(["command"=>"addDepartment", "result"=>"failed", "errors"=> $validator->errors()->all()]);
        }
        
        $department = new Department;
        $department->department_name = $request['department_name'];
        $department->department_desc = $request['department_desc'];
        $department->department_active = 1;
        $department->save();
        
        //writelog
        $details = 'Added Department '. $department->department_name;
        $this->writeLog("Department", $details);
        
        //return with success message
        return response()->json(["command"=>"addDepartment", "result"=>"success"]);
    }

    function updateDepartment(Request $request){

        //validate inputs
        $validator = Validator::make($request->all(), [
                    'department_name' => 'required|unique:departments,department_name,'.$request->input('id').'|max:255']);

        //if there are an error return to view and display errors
        if ($validator->fails()) {
            return response()->json(["command"=>"updateDepartment", "result"=>"failed", "errors"=> $validator->errors()->all()]);
        }

        $department = Department::find($request->input('id'));
        $department->department_name = $request['department_name'];
        $department->department_desc = $request['department_desc'];
        $department->save();

        //writelog
        $details = 'Updated Department '. $department->department_name;
        $this->writeLog("Department", $details);

        //return with success message
        return response()->json(["command"=>"updateDepartment", "result"=>"success"]);
    }

    function activateDepartment(Request $request){
        $department = Department::find($request->input('id'));
        $department->department_active = 1;
        $department->save();
        return response()->json(["command"=>"activateDepartment", "result"=>"success"]);
    }

    function deactivateDepartment(Request $request){
        $department = Department::find($request->input('id'));
        $department->department_active = 0;
        $department->save();
        return response()->json(["command"=>"deactivateDepartment", "result"=>"success"]);
    }
}