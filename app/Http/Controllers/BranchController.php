<?php
namespace ExactivEM\Http\Controllers;

use Illuminate\Http\Request;
use ExactivEM\Branch;
use ExactivEM\ScheduleType;
use Validator;
use ExactivEM\Http\Requests;
use ExactivEM\User;
use ExactivEM\Position;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller{
    public function __construct(){
        $this->middleware('auth');
        parent::__construct();
        
        //default title and url
        $this->data['page']['parent'] = 'Management';
        $this->data['page']['parent_url'] = '#';
    }
    
    function index(){
        //check the permission of the user
        if(!$this->checkRestriction()){
            return view('errors.no_permission', $this->data);
        }
        return view('branches', $this->data);
    }
    //add new branch to the database
    function addSchedule(Request $request){
        //check the schedule if already taken
        $check = ScheduleType::where("schedule_name", $request->input("schedule_name"))
                            ->where("branch_id", $request->input("branch_id"))
                            ->get()->all();
        $check2 = ScheduleType::where("schedule_color", $request->input("schedule_color"))
                            ->where("branch_id", $request->input("branch_id"))
                            ->get()->all();

        $validator = Validator::make($request->all(), [
            'schedule_name' => 'required|max:255',
            'schedule_color' => 'required']);
        //if there are an error return to view and display errors
        if ($validator->fails()) {
            return response()->json(["command"=>"addSchedule","result"=>"failed","errors"=>$validator->errors()->all()]);
        }

        //return failed of you want to add an existing branch schedule...
        if(!empty($check) OR !empty($check2)){
            return response()->json(["command"=>"addSchedule","result"=>"failed","errors"=>"Color/Name already been taken."]);
        }

        $schedule = new ScheduleType;
        $schedule->schedule_name = $request->input("schedule_name");
        $schedule->schedule_color = $request->input("schedule_color");
        $schedule->is_default = $request->input("is_default");
        if($request->input("is_default") == 1){
            ScheduleType::where('branch_id',$request->input("branch_id"))->update(["is_default"=>0]);
        }

        $days = array();
        //7 days schedules
        for($x = 0; $x<=6; $x++){
            //if the day is not closed then format it to 00:00 format
            if($request->input("schedule_data")[$x] != 'closed'){
                $days[] = date('H:i', strtotime(date('Y-m-d')." ".$request->input("schedule_data")[$x]));
            }
            else{
                //defenifely below value is "closed"
                $days[] = $request->input("schedule_data")[$x];
            }
        }
        //convert array to string.... Note: not json Format, just comma separated values
        $schedule->schedule_data = implode(",",$days);
        $schedule->branch_id = $request->input("branch_id");
        $schedule->save();

        //return with success message
        return response()->json(["command"=>"addSchedule","result"=>"success"]);
    }


    function updateSchedule(Request $request){
        $check = ScheduleType::where("schedule_name", $request->input("edit_name"))
            ->where("branch_id", $request->input("branch_id"))
            ->where("id","<>",$request->input("schedule_id"))
            ->get()->all();
        $check2 = ScheduleType::where("schedule_color", $request->input("edit_color"))
            ->where("branch_id", $request->input("branch_id"))
            ->where("id","<>",$request->input("schedule_id"))
            ->get()->all();
        //check1 and check2 is to determine if branch info was already been taken
        //this is to prevent duplicated data

        if(!empty($check) OR !empty($check2)){
            //return failed message
            return response()->json(["command"=>"updateSchedule","result"=>"failed","errors"=>"Color/Name already been taken."]);
        }

        $validator = Validator::make($request->all(), [
            'schedule_name' => 'required|max:255',
            'schedule_color' => 'required']);
        //if there are an error return to view and display errors
        if ($validator->fails()) {
            return response()->json(["command"=>"updateSchedule","result"=>"failed","errors"=>$validator->errors()->all()]);
        }

        $schedule = ScheduleType::find($request->input("id"));
        $schedule->schedule_name = $request->input("schedule_name");
        $schedule->schedule_color = $request->input("schedule_color");
        $schedule->is_default = $request->input("is_default");

        if($request->input("is_default") == 1){
            ScheduleType::where('branch_id',$schedule->branch_id)->update(["is_default"=>0]);
        }

        $days = array();

        //7 days loop
        for($x = 0; $x<=6; $x++){
            //formats the time to 00:00 if not equal to closed
            if($request->input("schedule_data")[$x] != 'closed'){
                $days[] = date('H:i', strtotime(date('Y-m-d')." ".$request->input("schedule_data")[$x]));
            }
            else{
                $days[] = $request->input("schedule_data")[$x];
            }
        }

        //convert array to string (not json)
        $schedule->schedule_data = implode(",",$days);
        $schedule->save();

        //return success message
        return response()->json(["command"=>"updateSchedule","result"=>"success"]);
    }


    //delete branch schedule and return success message
    function deleteSchedule(Request $request){
        ScheduleType::destroy($request->input('id'));
        return response()->json(["command"=>"deleteSchedule","result"=>"success"]);
    }
    
    //add new branch to the database
    function addBranch(Request $request){
        //validate inputs
        $validator = Validator::make($request->all(), [
                    'new_id' => 'required|not_in:0|unique:branches,id',
                    'branch_name' => 'required|unique:branches,branch_name|max:255',
                    'branch_head_employee_id' => 'required'
                ]);
        //if there are an error return to view and display errors  
        if ($validator->fails()) {
            return response()->json(["command"=>"addBranch","result"=>"failed","errors"=>$validator->errors()->all()]);
        }

        $branch = new Branch;
        $branch->id = $request['new_id'];
        $branch->branch_name = $request['branch_name'];
        $branch->branch_address = $request['branch_address'];
        $branch->branch_email = $request['branch_email'];
        $branch->branch_phone = $request['branch_phone'];
        $branch->branch_data = json_encode($request->input('branch_data'));
        $branch->branch_head_employee_id = $request['branch_head_employee_id'];
        $branch->sas_id = $request['sas_id'];
        $branch->bs_id = $request['bs_id'];
        $branch->save();
        
        //writelog
        $details = 'Added Branch '. $branch->branch_name;
        $this->writeLog("Branch", $details);
            
        //return success message
        return response()->json(["command"=>"addBranch","result"=>"success"]);
    }
    
    //edit the branch info
    function updateBranch(Request $request){
        if($branch = Branch::find($request->input('id')) ) {
            //validate inputs
            $validator = Validator::make($request->all(), [
                        'branch_name' => 'required|unique:branches,branch_name,'. $branch->id .'|max:255',
                        'id' => 'required|not_in:0|unique:branches,id,'.$branch->id,
                        'branch_head_employee_id' => 'required']);
            //if there are an error return to view and display errors  
            if ($validator->fails()) {
                return response()->json(["command"=>"updateBranch","result"=>"failed","errors"=>$validator->errors()->all()]);
            }
            
            $branch->branch_name = $request['branch_name'];
            $branch->branch_address = $request['branch_address'];
            $branch->branch_email = $request['branch_email'];
            $branch->branch_phone= $request['branch_phone'];
            $branch->branch_head_employee_id = $request['branch_head_employee_id'];
            $branch->branch_data = json_encode($request->input('branch_data'));
            $branch->sas_id = $request['sas_id'];
            $branch->bs_id = $request['bs_id'];
            $branch->save();
            
            //writelog
            $details = 'Updated Branch '. $branch->branch_name;
            $this->writeLog("Branch", $details);
        
            //return with success message
            return response()->json(["command"=>"updateBranch","result"=>"success"]);
        }
    }

    function getAllBranches(){
        $data = Branch::leftJoin('users','branches.branch_head_employee_id', '=', 'users.id')
                                ->select('users.name', 'branches.*')
                                ->orderBy('branch_name')
                                ->get()->toArray();

        foreach($data as $key=>$value){
            $data[$key]['schedules'] = ScheduleType::where('branch_id', $value['id'] )->get()->toArray();
        }

        return response()->json($data);
    }

    function getBranches(){
        $my_position = Position::find(Auth::user()->position_id);
        $branch_aware = (json_decode($my_position->position_data)->branch_aware == 'true');
        $my_current_branch = $this->getCurrentBranch(Auth::user()->id,date('Y-m-d'));
		$my_area_supervisor = Branch::find($my_current_branch['branch_id'])->branch_head_employee_id;

        $data = Branch::leftJoin('users','branches.branch_head_employee_id', '=', 'users.id');

        if($this->data['config']['employee_level_id'] == Auth::user()->level){
        	//if($my_area_supervisor!==null)
        		//$data = $data->where('branch_head_employee_id', $my_area_supervisor);
        }
        else{
            if($branch_aware){
                if( in_array(Auth::user()->position_id, [50,68]) ){
                    $bbb = Branch::where('bs_id', Auth::user()->id)->pluck('id')->toArray();
                    $bbb[] = $my_current_branch['branch_id'];
                    $data = $data->whereIn('branches.id', $bbb);
                }
                elseif(Auth::user()->position_id == 73){
                    $data = $data->whereIn('branch_head_employee_id', [Auth::user()->id, 0]);
                }
                elseif(Auth::user()->position_id == 76){
                    //$data = $data->whereIn('sas_id', [Auth::user()->id, 0]);
                }
                else{
                    $data = $data->whereIn('branch_head_employee_id', [Auth::user()->id, 0]);
                }
            }
        }


        $data = $data->select('users.name', 'branches.*')
                ->orderBy('branch_name')
                ->get()->toArray();

        foreach($data as $key=>$value){
            $data[$key]['schedules'] = ScheduleType::where('branch_id', $value['id'] )->get()->toArray();
            $data[$key]['employees'] = $this->getBranchEmployees($value['id'], date('Y-m-d'));
            $sas = User::find($value['sas_id']);
            if(isset($sas['name'])){
                $data[$key]['sas_name'] = $sas['name'];
            }
            else{
                $data[$key]['sas_name'] = '';
            }

            $bs = User::find($value['bs_id']);
            if(isset($bs['name'])){
                $data[$key]['bs_name'] = $bs['name'];
            }
            else{
                $data[$key]['bs_name'] = '';
            }

        }
        return response()->json($data);
    }
}