<?php
namespace ExactivEM\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use ExactivEM\Http\Requests;
use ExactivEM\EmployeeRequest;
use ExactivEM\Attendance;
use ExactivEM\Leave_type;
use ExactivEM\Libraries\Attendance_Class;
use ExactivEM\User;
use ExactivEM\Position;
class FormController extends Controller{
    public function __construct(){
        $this->middleware('auth');
        parent::__construct();
        //default title and url
        $this->data['page']['title'] = 'Employee Forms';
    }
    
    function index(){
        return view('forms', $this->data);
    }

    //add adjustment function
    function addAdjustment(Request $request){
        foreach($request->input('data') as $key=>$value){
            //check if the adjustment already existed
            $error = '';
            $att = new Attendance_Class(Auth::user()->id, date('Y-m-d',strtotime($value["date"])));

            if($this->isRequestExists('adjustment', $value, Auth::user()->id))
                $error="Item " . ($key+1) . " already have existing adjustment in date: ". date('m/d/Y',strtotime($value['date']));

            if($att->hasSchedule() === false)
                $error = "Item " . ($key+1) . " no schedule on date:". date('m/d/Y',strtotime($value['date'])) . " .";

            if($error != '')
                return response()->json(["command"=>"addAdjustment","result"=>"failed","errors"=> $error]);
        }

        foreach($request->input('data') as $key=>$value){
            $req = new EmployeeRequest;
            $req->request_type = 'adjustment';
            $req->employee_id = Auth::user()->id;
            $req->request_note = $value['notes'];
            //get the original timestamp
            $get = Attendance::where('employee_id', $req->employee_id)
                                ->where('in_out', $value['mode'] )
                                ->where('attendance_stamp','like', date('Y-m-d',strtotime($value['date'])).'%' )
                                ->whereIn('stamp_type', array('REGULAR','ADJUSTMENT') )
                                ->get();
            //placeholder value
            $orig_data['time'] = '';
            $orig_data['id'] =0;

            if(!empty($get[0])){
                $orig_data['time'] =  date('h:i A',strtotime($get[0]['attendance_stamp']));
                $orig_data['id'] =  $get[0]['id'];
            }

            //create the data
            $data = array("date" => date('Y-m-d',strtotime($value['date'])),
                            "time" =>  date('H:i',strtotime($value['time'])),
                            "mode" => $value['mode'],
                            "original" => $orig_data['time'],
                            "original_id" => $orig_data['id'],
                            "status" => 'pending');

            $req->request_data = json_encode($data);
            $generatedArray = $this->getActionData(Auth::user()->position_id,Auth::user()->id);
            $req->action_data = json_encode(array("approved_by"=> !empty($generatedArray)? $generatedArray: array() ) );
            $req->save();
            $this->notifyApprovingOfficer($generatedArray, $req);

            
            //write log
            $details = 'Requested Adjustment '.$value['date'] .' ' . $value['time'];
            $this->writeLog("Employee Request", $details);
        }
        return response()->json(["command"=>"addAdjustment","result"=>"success"]);
    }


    //add adjustment function
    function addSalaryAdjustment(Request $request){
        foreach($request->input('data') as $key=>$value) {
            $find = EmployeeRequest::where('employee_id', Auth::user()->id)
                ->where('request_type', 'salary_adjustment')
                ->where('request_data', 'LIKE', '%"period":"' . $value['period'] . '"%')
                ->where('request_data', 'LIKE', '%"discrepancy":"'.$value['discrepancy'].'"%')
                ->count();
            if($find>0)
            return response()->json(["command"=>"addSalaryAdjustment","result"=>"failed",
                                    "errors"=> "You not able file salary adjustment for this period and discrepancy type."]);
        }


        foreach($request->input('data') as $key=>$value){
            $req = new EmployeeRequest;
            $req->request_type = 'salary_adjustment';
            $req->employee_id = Auth::user()->id;
            $req->request_note = $value['notes'];
            //get the original timestamp

            $req->request_data = json_encode(array(
                                            "discrepancy" =>  $value['discrepancy'],
                                            "status" => 'pending',
                                            "period" => $value['period'],
                                            "amount"=>0));
            $req->action_data = json_encode(array(
                                            "feedback" => '',
                                            "author"=> ''));
            $req->save();

            //write log
            $details = 'Requested Salary Adjustment '.$value['notes'];
            $this->writeLog("Employee Request", $details);
        }
        return response()->json(["command"=>"addAdjustment","result"=>"success"]);
    }


    function hasLeaveConflict($data, $employee_id){
        $get_all = EmployeeRequest::where('employee_id', $employee_id)
                                    ->where('request_type', 'leave')
                                    ->get()->toArray();
        foreach($get_all as $key=>$value){
            $leave_data = json_decode($value['request_data'],true);
            if(($data['date_start'] <= $leave_data['date_end']) and ($data['date_end'] >= $leave_data['date_start']) AND $leave_data['status'] != 'denied' )
                return true;
        }

        return false;
    }

    function addLeave(Request $request){
        foreach($request->input('data') as $key=>$value){
            $leave_data = json_decode(Leave_type::find($value['leave_type_id'])->leave_type_data,true);
            $error = '';

            if( $this->hasLeaveConflict($value, Auth::user()->id))
                $error = 'Leave conflict on other pending/approved leave for item # ' . ($key+1);

            $days = $this->getLeaveDays($value, Auth::user()->id);

            $usage = $this->getLeaveUsage($value['leave_type_id'], Auth::user()->id);

            if($days > $usage['credits'])
                $error = 'Not enough leave credits for item # ' . ($key+1);

            if($leave_data['within_condition'] != ''){
                if(date('m',strtotime($value['date_start'])) != date('m',strtotime(Auth::user()->birth_date))){
                    $error ='Birthday leave must be within birthday month.';
                }
            }

            if($days == 0)
                $error = 'Leave does not covered any working day in item # ' . ($key+1);

            if($leave_data['is_staggered'] == 'false'){
                if( $this->getLeaveDays($value, Auth::user()->id, 'all') != ($usage['credits'] + $usage['used']) )
                    $error = 'Staggered not allowed in item # ' . ($key+1) . $this->getLeaveDays($value, Auth::user()->id, 'all');
            }

            if($error != '')
                return response()->json(["command"=>"addLeave","result"=>"failed","errors"=> $error]);
        }

        foreach($request->input('data') as $key=>$value){
            $leave_data = json_decode(Leave_type::find($value['leave_type_id'])->leave_type_data,true);
            $req = new EmployeeRequest;
            $req->request_type = 'leave';
            $req->request_note = $value['notes'];
            $req->employee_id = Auth::user()->id;
            $data = array("date_start" =>  date('Y-m-d',strtotime($value['date_start'])),
                "date_end" => date('Y-m-d',strtotime($value['date_end'] ."+23 hours")),
                "status" => 'pending',
                "leave_type"=>$value['leave_type_id'],
                "paid"=> ($leave_data['paid']=='true'?1:0),
                "mode"=>$value['mode'],
                "days"=>$this->getLeaveDays($value, Auth::user()->id, ($leave_data['is_staggered']=='true'?'scheduled':'all') ));

            $req->request_data = json_encode($data);
            $generatedArray = $this->getActionData(Auth::user()->position_id,Auth::user()->id);
            $req->action_data = json_encode(array("approved_by"=> !empty($generatedArray)? $generatedArray: array() ) );

            $req->save();
            $this->notifyApprovingOfficer($generatedArray, $req);
            $details = 'Requested Leave '. $request->input("start") .' - '. $request->input("end");
            $this->writeLog("Employee Request", $details);
        }
        return response()->json(["command"=>"addLeave","result"=>"success"]);
    }
    
    function addOvertime(Request $request){
        //check if the overtime already exists
        foreach($request->input('data') as $key=>$value){
            //check if the adjustment already existed
            $error = '';
            $att = new Attendance_Class(Auth::user()->id, date('Y-m-d',strtotime($value["date_start"])));
            //check the schedule and holiday existence
            if($att->hasSchedule() AND $att->getHoliday() === false){
                //if($att->withinTime($value["time_start"], $value["time_end"]) )
                   // $error = 'Within the scheduled time.';
            }
            if($this->isRequestExists('overtime', $value, Auth::user()->id))
                $error="Item " . ($key+1) . " already have existing overtime in date: ". date('m/d/Y',strtotime($value['date_start']));

            if($error != '')
                return response()->json(["command"=>"addOvertime","result"=>"failed","errors"=> $error]);
        }

        foreach($request->input('data') as $key=>$value){
            $req = new EmployeeRequest;
            $req->request_type = 'overtime';
            $req->employee_id = Auth::user()->id;
            $req->request_note = $value["notes"];

            //create the data
            $data = array("date_start" => date('Y-m-d',strtotime($value["date_start"])),
                            "date_end" => date('Y-m-d',strtotime($value["date_end"])),
                            "time_start" => date('H:i',strtotime($value["time_start"])),
                            "time_end" => date('H:i',strtotime($value["time_end"])),
                            "status" => 'pending');

            $req->request_data = json_encode($data);
            $generatedArray = $this->getActionData(Auth::user()->position_id,Auth::user()->id);
            $req->action_data = json_encode(array("approved_by"=> !empty($generatedArray)? $generatedArray: array() ) );

            //write log
            $details = 'Requested Overtime '. $value["date_start"] .' '.$value["time_start"].' - '. $value["date_end"] . ' ' .$value["time_end"];
            $this->writeLog("Employee Request", $details);

            $req->save();
            $this->notifyApprovingOfficer($generatedArray, $req);
        }
        return response()->json(["command"=>"addOvertime","result"=>"success"]);
    }

    function addTravel(Request $request){
        //check if the overtime already exists
        foreach($request->input('data') as $key=>$value){
            //check if the adjustment already existed
            $error = '';
            $att = new Attendance_Class(Auth::user()->id, date('Y-m-d',strtotime($value["date_start"])));
            
            if($error != '')
                return response()->json(["command"=>"addTravel","result"=>"failed","errors"=> $error]);
        }

        foreach($request->input('data') as $key=>$value){
            $req = new EmployeeRequest;
            $req->request_type = 'travel';
            $req->employee_id = Auth::user()->id;
            $req->request_note = $value["notes"];

            //create the data
            $data = array("date_start" => date('Y-m-d',strtotime($value["date_start"])),
                "date_end" => date('Y-m-d',strtotime($value["date_start"])),
                "time_start" => date('H:i',strtotime($value["time_start"])),
                "time_end" => date('H:i',strtotime($value["time_end"])),
                "status" => 'pending');

            $req->request_data = json_encode($data);
            $generatedArray = $this->getActionData(Auth::user()->position_id,Auth::user()->id);
            $req->action_data = json_encode(array("approved_by"=> !empty($generatedArray)? $generatedArray: array() ) );

            //write log
            $details = 'Requested Travel '. $value["date_start"] .' '.$value["time_start"].' - ' .$value["time_end"];
            $this->writeLog("Employee Request", $details);

            $req->save();
            $this->notifyApprovingOfficer($generatedArray, $req);
        }
        return response()->json(["command"=>"addTravel","result"=>"success"]);
    }

    function addSchedule(Request $request){
        $req = new EmployeeRequest;
        $req->request_type = 'schedule';
        $req->employee_id = $request->input('employee_id');
        $req->request_note = $request->input('notes');

        //create the data
        $data = array("date" => date('Y-m-d',strtotime( $request->input("date"))),
                        "time" =>  $request->input("time"),
                        "status" => 'pending',
                        "branch_id"=> $request->input("branch_id"),
                        "is_flexi_time"=> $request->input("is_flexi_time")
        );

        $req->request_data = json_encode($data);
        $generatedArray = $this->getActionData(Auth::user()->position_id, Auth::user()->id);
        $req->action_data = json_encode(array("approved_by"=> !empty($generatedArray)? $generatedArray: array() ) );

        //write log
        $details = 'Requested Schedule '. $request->input("date_start") .' - '.$request->input("date_end");
        $this->writeLog("Employee Request", $details);

        $req->save();
        $this->notifyApprovingOfficer($generatedArray, $req);
        return response()->json(["command"=>"addTravel","result"=>"success"]);
    }

    function addOffset(Request $request){
        //check if the overtime already exists
        foreach($request->input('duties') as $key=>$value){
            //check if the adjustment already existed
            $error = '';

            if($value['notes'] == '')
                $error = "Please provide notes.";

            if($error != '')
                return response()->json(["command"=>"addTravel","result"=>"failed","errors"=> $error]);
        }

        //check if the overtime already exists
        foreach($request->input('offsets') as $key=>$value){
            //check if the adjustment already existed
            $error = '';
            $att = new Attendance_Class(Auth::user()->id, date('Y-m-d',strtotime($value["date_start"])));

            if($att->hasSchedule() === false)
                $error = "Item " . ($key+1) . " no schedule on date:". date('m/d/Y',strtotime($value['date_start'])) . " .";

            if($value['notes'] == '')
                $error = "Please provide notes.";
            if($error != '')
                return response()->json(["command"=>"addTravel","result"=>"failed","errors"=> $error]);
        }

        foreach($request->input('offsets') as $key=>$value){
            $req = new EmployeeRequest;
            $req->request_type = 'offset';
            $req->employee_id = Auth::user()->id;
            $req->request_note = $value["notes"];

            //create the data
            $data = array("date_start" => date('Y-m-d',strtotime($value["date_start"])),
                            "date_end" => date('Y-m-d',strtotime($value["date_start"])),
                            "time_start" => date('H:i',strtotime($value["time_start"])),
                            "time_end" => date('H:i',strtotime($value["time_end"])),
                            "status" => 'pending',
                            "duties" => $request->input('duties'),
                            "duty_sum" => $request->input('duty_sum'),
                            "offset_sum" => $request->input('offset_sum'),
                            "overhead" => $request->input('overhead')
                        );

            $req->request_data = json_encode($data);
            $generatedArray = $this->getActionData(Auth::user()->position_id,Auth::user()->id);
            $req->action_data = json_encode(array("approved_by"=> !empty($generatedArray)? $generatedArray: array() ) );

            //write log
            $details = 'Requested Offset '. $value["date_start"] .' '.$value["time_start"].' - ' . $value["date_end"] .' - '. $value["time_end"];
            $this->writeLog("Employee Request", $details);

            $req->save();
            $this->notifyApprovingOfficer($generatedArray, $req);
        }
        return response()->json(["command"=>"addTravel","result"=>"success"]);
    }
}