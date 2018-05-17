<?php

namespace ExactivEM\Http\Controllers;

use Illuminate\Http\Request;

use ExactivEM\Http\Requests;
use ExactivEM\Emergency;
use ExactivEM\Branch;
use Validator;

class EmergencyController extends Controller{
    public function __construct(){
        $this->middleware('auth');
        parent::__construct();
        
        //default title and url
        $this->data['page']['parent'] = 'Payroll Setup';
        $this->data['page']['parent_url'] = '#';
    }
    
    function index(){
        //check user's permission to view the page
        if(!$this->checkRestriction()){
            return view('errors.no_permission', $this->data);
        }
        return view('emergencies', $this->data);
    }

    function getEmergencies(){
        return response()->json(Emergency::get());
    }

    function addEmergency(Request $request){
        //validate inputs
        $validator = Validator::make($request->all(), [
            'emergency_name' => 'required|max:255',
        ]);

        //if there are an error return to view and display errors
        if ($validator->fails()) {
            return response()->json(["command"=>"addEmergency", "result"=>"failed", "errors"=> $validator->errors()->all()]);
        }

        $emergency = new Emergency;
        $emergency->emergency_name = $request->input('emergency_name');
        $emergency->date_start = date('Y-m-d',strtotime($request->input('date_start')));
        $emergency->date_end = date('Y-m-d',strtotime($request->input('date_end')));
        $emergency->notes = $request->input('notes');
        $emergency->branch_covered = $request->input('branch_covered')!==null?json_encode($request->input('branch_covered')):'[]';
        $emergency->exempted_employees = $request->input('exempted_employees')!==null?json_encode($request->input('exempted_employees')):'[]';
        $emergency->save();

        //writelog
        $details = 'Added Emergency '. $emergency->emergency_name;
        $this->writeLog("Emergency", $details);

        return response()->json(["command"=>"addEmergency", "result"=>"success"]);
    }

    function updateEmergency(Request $request){
        //validate inputs
        $validator = Validator::make($request->all(), [
            'emergency_name' => 'required|max:255',
        ]);

        //if there are an error return to view and display errors
        if ($validator->fails()) {
            return response()->json(["command"=>"addEmergency", "result"=>"failed", "errors"=> $validator->errors()->all()]);
        }

        $emergency = Emergency::find($request->input('id'));
        $emergency->emergency_name = $request->input('emergency_name');
        $emergency->date_start = date('Y-m-d',strtotime($request->input('date_start')));
        $emergency->date_end = date('Y-m-d',strtotime($request->input('date_end')));
        $emergency->notes = $request->input('notes');
        $emergency->branch_covered = $request->input('branch_covered')!==null?json_encode($request->input('branch_covered')):'[]';
        $emergency->exempted_employees = $request->input('exempted_employees')!==null?json_encode($request->input('exempted_employees')):'[]';
        $emergency->save();

        //writelog
        $details = 'Updated Emergency '. $emergency->emergency_name;
        $this->writeLog("Emergency", $details);

        return response()->json(["command"=>"updateEmergency", "result"=>"success"]);
    }

    function deleteEmergency(Request $request){
        $emergency = Emergency::find($request->input('id'));

        Emergency::destroy($request->input('id'));

        //writelog
        $details = 'Deleted Emergency '. $emergency->emergency_name;
        $this->writeLog("Emergency", $details);

        return response()->json(["command"=>"deleteEmergency", "result"=>"success"]);
    }
}