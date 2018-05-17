<?php
namespace ExactivEM\Http\Controllers;
use Illuminate\Http\Request;
use ExactivEM\Position;
use ExactivEM\Http\Requests;
use ExactivEM\User;
use ExactivEM\EmployeeRequest;

use Validator;

class PositionController extends Controller{
    public function __construct(){
        $this->middleware('auth');
        parent::__construct();
        
        //default title and url
        $this->data['page']['parent'] = 'Management';
        $this->data['page']['parent_url'] = '#';
    }
    
    function index(){
        if(!$this->checkRestriction()){
            return view('errors.no_permission', $this->data);
        }
        return view('positions', $this->data);
    }

    function getPositions(){
        return response()->json(Position::leftJoin('departments','positions.department_id','=','departments.id')
                                            ->select('positions.*','departments.department_name')
                                            ->orderBy('position_name')
                                            ->get());
    }

    //add new Position to database
    function addPosition(Request $request){
        //validate inputs
        $validator = Validator::make($request->all(), [
                    'position_name' => 'required|unique:positions,position_name|max:255',
                    'department_id' => 'required|not_in:0'
                ]);
        //if there are an error return to view and display errors  
        if ($validator->fails()) {
            return response()->json(["command"=>"addPosition", "result"=>"failed", "errors"=> $validator->errors()->all()]);
        }
        
        // new Position object
        $position = new Position;
        $position->position_name = $request['position_name'];
        $position->position_desc = $request['position_desc'];
        $position->department_id = $request['department_id'];
        $position->is_department_head = $request['is_department_head'];
        $position->position_active = 1 ;

        $position_data = array("reporting_lines"=>$request->input('reporting_lines'),
                               "audience_data"=>$request->input('audience_data'),
                               "leave_data"=>$request->input('leave_data'),
                               "salary_frequency"=>$request->input('position_data')['salary_frequency'],
                               "grace_period_minutes"=>$request->input('position_data')['grace_period_minutes'],
                               "grace_period_per_month"=>$request->input('position_data')['grace_period_per_month'],
                               "standard_days"=>$request->input('position_data')['standard_days'],
                               "branch_aware"=>$request->input('position_data')['branch_aware'],
                            );

        $position->position_data = json_encode($position_data);
        if($position->save()){
            if($request['is_department_head'] == 1){
                //call local function to deactivate head
                $this->deactivateHead($request['department_id'], 0);
            } 
        }
        
        //writelog
        $details = 'Added Position '. $position->position_name;
        $this->writeLog("Position", $details);
            
        //return with success message
        return response()->json(["command"=>"addPosition", "result"=>"success"]);
    }
    
    //function to edit position data
    function updatePosition(Request $request){
        $position = Position::find($request->input('id'));
        //validate inputs
        $validator = Validator::make($request->all(), [
                    'position_name' => 'required|unique:positions,position_name,'.$request->id.'|max:255',
                    'department_id' => 'required|not_in:0'
                ]);
        //if there are an error return to view and display errors
        if ($validator->fails()) {
            return response()->json(["command"=>"updatePosition", "result"=>"failed", "errors"=> $validator->errors()->all()]);
        }

        $position->position_name = $request['position_name'];
        $position->position_desc = $request['position_desc'];
        $position->is_department_head = $request['is_department_head'];
        $position->department_id = $request['department_id'];


        $position_data = array("reporting_lines"=>$request->input('reporting_lines'),
            "leave_data"=>$request->input('leave_data'),
            "audience_data"=>$request->input('audience_data'),
            "salary_frequency"=>$request->input('position_data')['salary_frequency'],
            "grace_period_minutes"=>$request->input('position_data')['grace_period_minutes'],
            "grace_period_per_month"=>$request->input('position_data')['grace_period_per_month'],
            "standard_days"=>$request->input('position_data')['standard_days'],
            "branch_aware"=>$request->input('position_data')['branch_aware'],
        );
        $old_reporting_lines = json_decode($position->position_data,true)['reporting_lines'];
        $position->position_data = json_encode($position_data);
        if($position->save()){
            if($request['is_department_head'] == 1){
                $this->deactivateHead($request['department_id'], $request->input('id'));
            }
        }

        if($this->isModified($old_reporting_lines, $position_data['reporting_lines'])){
            $this->revokeRequests($position->id);
        }

        //writelog
        $details = 'Updated Position '. $position->position_name;
        $this->writeLog("Position", $details);

        //return with success message
        return response()->json(["command"=>"updatePosition", "result"=>"success"]);
    }

    //@params old, new
    function isModified($old, $new){
        if(sizeof($old) != sizeof($new)){
            return true;
        }
        foreach($old as $key=>$value){
            if($value['position_id'] != $new[$key]['position_id']){
                return true;
            }
        }
        return false;
    }

    function revokeRequests($position_id){
        $users = User::where('position_id', $position_id)->get()->toArray();
        foreach($users as $key=>$value){
            $requests = EmployeeRequest::where('employee_id', $value['id'])->get()->toArray();
            foreach($requests as $k=>$val){
                $request_data = json_decode($val['request_data'],true);
                if($request_data['status'] == 'pending'){
                    $new = array("approved_by"=> $this->getActionData($position_id, $val['employee_id']));
                    EmployeeRequest::where('id', $val['id'])->update(["action_data" => json_encode($new)]);
                }
            }
        }
    }

    //updates the department heads
    function deactivateHead($id, $exempt){
        Position::where('department_id', $id)->where('id', '<>', $exempt)->update(['is_department_head' => 0]);
    }

    function deactivatePosition(Request $request){
        $position = Position::find($request->input('id'));
        $position->position_active = 0;
        $position->save();
        //return with success message
        return response()->json(["command"=>"deactivatePosition", "result"=>"success"]);
    }

    function activatePosition(Request $request){
        $position = Position::find($request->input('id'));
        $position->position_active = 1;
        $position->save();
        //return with success message
        return response()->json(["command"=>"activatePosition", "result"=>"success"]);
    }

    function getOrgChart(){
        $array = array();
        $head = Position::find($this->data['config']['head_position_id']);
        $positions = Position::where('id','<>',$this->data['config']['head_position_id'])->get()->toArray();

        $array[] = array($head->position_name, "", "Executive Head");
        foreach($positions as $key=>$position){
            $position_data = json_decode($position['position_data'],true);
            $manager = '';
            if(isset($position_data['reporting_lines'])){
                if($position_data['reporting_lines'] !== null)
                    $manager = Position::find($position_data['reporting_lines'][0]['position_id'])->position_name;
            }
            $array[] = array($position['position_name'], $manager, $position['position_name'] );
        }

        return response()->json($array);
    }


}