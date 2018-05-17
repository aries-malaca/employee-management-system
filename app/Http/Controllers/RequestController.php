<?php
namespace ExactivEM\Http\Controllers;
use Illuminate\Http\Request;
use ExactivEM\Http\Requests;
use ExactivEM\EmployeeRequest;
use ExactivEM\Attendance;
use Illuminate\Support\Facades\Auth;
use ExactivEM\Leave_type;
use ExactivEM\Libraries\Attendance_Class;
use ExactivEM\Libraries\Request_Class;
use ExactivEM\Libraries\Mailer_Class;
use ExactivEM\User;
use ExactivEM\Position;
use ExactivEM\Branch;
use ExactivEM\ScheduleHistory;
use ExactivEM\Notification;
use Validator;

class RequestController extends Controller{
    public function __construct(){
        $this->middleware('auth');
        parent::__construct();
        $this->data['page']['parent'] = 'Employee Requests';
        $this->data['page']['parent_url'] = 'request';
    }

    function index(Request $request){
        if($request->segment(2) === null)
            $this->data['page']['title'] = 'All Requests';
        else
            $this->data['page']['title'] = ucfirst($request->segment(2)).'s';
        return view('request', $this->data);
    }

    function getLastPendingIndex($action_data){

        foreach($action_data['approved_by'] as $key=>$value){
            if($value['status']=='Pending' AND $value['position_id']==Auth::user()->position_id)
                return $key;

            //STATIC logic
            //routing of Miss Karen to miss jen... edit this on future....
            if(Auth::user()->id == 145 && $value['status']=='Pending' AND $value['position_id']==73)
                return $key;

        }
        return false;
    }

    function resetRequestApproval(Request $request){
        $r = EmployeeRequest::find($request->input('id'));
        $position_id = User::find($request->employee_id)->position_id;
        $r->action_data = json_encode(array("approved_by"=>$this->getActionData($position_id, $request->employee_id)));
        $r->save();

        return response()->json(["command"=>"resetRequest","result"=>"success"]);
    }

    function approveRequest(Request $request){
        $req = EmployeeRequest::find($request->input('request')['id']);
        $action_data = json_decode($req->action_data,true);
        $kkk = $this->getLastPendingIndex($action_data);

        if($kkk === false){
            return response()->json(["command"=>"approveRequest","result"=>"failed","errors"=>'This is invalid request.']);
        }

        $action_data['approved_by'][$kkk] = array(
                             'name'=> Auth::user()->name,
                             'id'=>Auth::user()->id,
                             'position_id'=>Auth::user()->position_id,
                             'feedback'=>$request->input('notes'),
                             'date'=> date('Y-m-d H:i'),
                             'status'=>'approved');
        $req->action_data = json_encode($action_data);

        $isFinalApproval = true;
        foreach($action_data['approved_by'] as $key=>$value){
            if($key<$kkk)
                continue;

            if($value['status'] == 'Pending')
                $isFinalApproval = false;
        }

        if($isFinalApproval){
            $request_raw= json_decode($req['request_data']);
            $request_raw->status ='approved';
            $req->request_data = json_encode($request_raw);

            //adjustment only
            if($req->request_type =='adjustment'){
                //get the schedule of the day
                $att = new Attendance_Class($req->employee_id, json_decode($req['request_data'])->date);
                $scheduled_stamp = json_decode($req['request_data'])->date ." ".$att->getSchedule('IN');

                if(json_decode($req['request_data'])->mode =='OUT')
                    $scheduled_stamp =  json_decode($req['request_data'])->date." ".$att->getSchedule('OUT');

                $data =  array('date_credited'=> json_decode($req['request_data'])->date,
                    'employee_id' =>$req['employee_id'],
                    'attendance_stamp'=>date('Y-m-d H:i:s',strtotime(json_decode($req['request_data'])->date .' ' . json_decode($req['request_data'])->time)),
                    'stamp_type'=>'ADJUSTMENT',
                    'via'=>'REQUEST',
                    'in_out'=> json_decode($req['request_data'])->mode ,
                    'scheduled_stamp'=> $scheduled_stamp);

                //remove the original...
                Attendance::destroy(json_decode($req['request_data'])->original_id);
                $this->addAttendance($data);
            }
            //overtime, travel and offset
            elseif($req->request_type !='adjustment' AND $req->request_type !='leave' AND $req->request_type != 'schedule'){
                $att = new Attendance_Class($req->employee_id, json_decode($req['request_data'])->date_start);
                $data[0] =  array('date_credited'=> json_decode($req['request_data'])->date_start,
                    'employee_id' =>$req['employee_id'],
                    'attendance_stamp'=>
                        date('Y-m-d H:i:s',strtotime(json_decode($req['request_data'])->date_start .' ' . json_decode($req['request_data'])->time_start)),
                    'stamp_type'=>strtoupper($req->request_type),
                    'via'=>'REQUEST',
                    'in_out'=> 'IN' ,
                    'scheduled_stamp'=> json_decode($req['request_data'])->date_start." ".$att->getSchedule('IN'));
                $data[1] =  array('date_credited'=> json_decode($req['request_data'])->date_start,
                    'employee_id' =>$req['employee_id'],
                    'attendance_stamp'=>
                        date('Y-m-d H:i:s',strtotime(json_decode($req['request_data'])->date_end .' ' . json_decode($req['request_data'])->time_end)),
                    'stamp_type'=>strtoupper($req->request_type),
                    'via'=>'REQUEST',
                    'in_out'=> 'OUT' ,
                    'scheduled_stamp'=>json_decode($req['request_data'])->date_start." ".$att->getSchedule('OUT') );

                $this->addAttendance($data[0]);
                $this->addAttendance($data[1]);
            }
            elseif($req->request_type == 'schedule'){
                $can_be_updated = true;

                $ee = ScheduleHistory::where('schedule_start','LIKE',$request_raw->date.'%')
                                    ->where('employee_id', $req['employee_id'])
                                    ->where('schedule_type','SINGLE')
                                    ->get()->first();
                if(isset($ee['id'])) {
                    if ($ee['is_read_only'] == 1)
                        $can_be_updated = false;
                }
                
                if($can_be_updated){

                    ScheduleHistory::destroy($ee['id']);

                    $current = new Attendance_Class($req['employee_id'],  $request_raw->date);
                    $t = $current->getSchedule();

                    //insert the schedule
                    $schedule = new ScheduleHistory;
                    $schedule->employee_id = $req['employee_id'];
                    $schedule->schedule_start = date('Y-m-d',strtotime($request_raw->date));
                    $schedule->schedule_end = date('Y-m-d',strtotime($request_raw->date)) .' 23:59:59';
                    $schedule->schedule_data = $request_raw->time;
                    $schedule->schedule_type = 'SINGLE';
                    $schedule->branch_id = $request_raw->branch_id;
                    $schedule->is_flexi_time = $request_raw->is_flexi_time;
                    //save the schedule
                    $schedule->save();

                    $day_index = idate('w',strtotime($request_raw->date));
                    $start_search = strtotime($request_raw->date) - ($day_index*86400);
                    $end_search = $start_search + (86400 * 6);

                    $punched = false;
                    if($request_raw->time == '00:00'){
                        while($start_search <= $end_search){
                            $att = new Attendance_Class($schedule->employee_id, date('Y-m-d', $start_search));
                            if($att->getSchedule() == '00:00'){

                                if($request_raw->date != date('Y-m-d', $start_search) && !$punched){
                                    $schedule = new ScheduleHistory;
                                    $schedule->employee_id = $req['employee_id'];
                                    $schedule->schedule_start = date('Y-m-d', $start_search);
                                    $schedule->schedule_end = date('Y-m-d', $start_search) .' 23:59:59';
                                    $schedule->schedule_data = $t;
                                    $schedule->schedule_type = 'SINGLE';
                                    $schedule->branch_id = $request_raw->branch_id;
                                    $schedule->is_flexi_time = $request_raw->is_flexi_time;
                                    //save the schedule
                                    $schedule->save();
                                    $punched = true;
                                }
                            }
                            $start_search+=86400;
                        }
                    }
                }
            }
        }

        $req->save();

        //write log
        if($req->request_type == 'leave'){
            $details = 'Approved Request, '. $req->request_type . ' ' . date('m/d/Y',strtotime(json_decode($req['request_data'])->date_start)) .
                        ' - ' . date('m/d/Y',strtotime(json_decode($req['request_data'])->date_end)) . ' ' . $this->getEmployeeName($req['employee_id']);
        }
        elseif($req->request_type == 'adjustment'){
            $details = 'Approved Request, '. $req->request_type . ' ' . json_decode($req['request_data'])->date . ' ' . $this->getEmployeeName($req['employee_id']);
        }
        else{
            $details = 'Approved Request, '. $req->request_type . ' ' . $this->getEmployeeName($req['employee_id']);
        }

        $this->writeLog("Employee Request", $details);
        return response()->json(["command"=>"approveRequest","result"=>"success"]);
    }

    function denyRequest(Request $request){
        $req = EmployeeRequest::find($request->input('request')['id']);
        $action_data = json_decode($req->action_data,true);

        $kkk = $this->getLastPendingIndex($action_data);
        if($kkk === false){
            return response()->json(["command"=>"approveRequest","result"=>"failed","errors"=>'This is invalid request.']);
        }

        $request_raw= json_decode($req['request_data']);
        $request_raw->status ='denied';
        $req->request_data = json_encode($request_raw);

        $action_data['approved_by'][$kkk] = array(
            'name'=> Auth::user()->name,
            'id'=>Auth::user()->id,
            'feedback'=>$request->input('notes'),
            'date'=> date('Y-m-d H:i'),
            'position_id'=>Auth::user()->position_id,
            'status'=>'denied');
        $req->action_data = json_encode($action_data);
        $req->save();

        //write log
        if($req->request_type == 'leave'){
            $details = 'Denied Request, '. $req->request_type . ' ' .
                date('m/d/Y',strtotime(json_decode($req['request_data'])->date_start)) . ' - ' .
                date('m/d/Y',strtotime(json_decode($req['request_data'])->date_end)) . ' ' . $this->getEmployeeName($req['employee_id']);
        }
        elseif($req->request_type == 'adjustment'){
            $details = 'Denied Request, '. $req->request_type . ' ' . json_decode($req['request_data'])->date . ' ' . $this->getEmployeeName($req['employee_id']);
        }
        else{
            $details = 'Denied Request, '. $req->request_type . ' ' . $this->getEmployeeName($req['employee_id']);
        }

        $this->writeLog("Employee Request", $details);
        return response()->json(["command"=>"denyRequest","result"=>"success"]);
    }

    function deleteRequest(Request $request){
        $req = EmployeeRequest::find($request->input('id'));

        if($req->request_type == 'leave'){
            $details = 'Deleted Request, '. $req->request_type . ' ' .
                date('m/d/Y',strtotime(json_decode($req['request_data'])->date_start)) . ' - ' .
                date('m/d/Y',strtotime(json_decode($req['request_data'])->date_end)) . ' ' . $this->getEmployeeName($req['employee_id']);
        }
        elseif($req->request_type=='adjustment'){
            $details = 'Deleted Request, '. $req->request_type . ' ' . json_decode($req['request_data'])->date . ' ' . $this->getEmployeeName($req['employee_id']);
        }
        elseif($req->request_type=='schedule')
            $details = 'Deleted Request, '. $req->request_type . ' ' . json_decode($req['request_data'])->date . ' ' . $this->getEmployeeName($req['employee_id']);
        elseif($req->request_type=='salary_adjustment')
            $details = 'Deleted Request, '. $req->request_type . ' ' . $req['request_note']. ' ' . $this->getEmployeeName($req['employee_id']);
        else
            $details = 'Deleted Request, '. $req->request_type . ' ' . json_decode($req['request_data'])->date_start . ' ' . $this->getEmployeeName($req['employee_id']);

        $this->writeLog("Employee Request", $details);
        EmployeeRequest::destroy($request->input('id'));
        return response()->json(["command"=>"deleteRequest","result"=>"success"]);
    }

    function getRequests(Request $request){
        $data = EmployeeRequest::leftJoin('users','employee_requests.employee_id','=','users.id')
                                ->leftJoin('positions','users.position_id','=','positions.id')
                                ->select('employee_requests.*', 'employee_requests.created_at as requested_at', 'users.name','users.id as user_id','position_data','position_name')
                                ->orderBy('employee_requests.created_at', 'DESC');
        if($request->segment(3)!='all'){
            $data = $data->where('request_type',$request->segment(3))->orderBy('employee_requests.created_at', 'DESC');
        }

        if($request->segment(4) === null)
            $data = $data->whereIn('employee_id',$this->myDownLines())->orderBy('employee_requests.created_at', 'DESC')->take(700)->get()->toArray();
        else
            $data = $data->where('employee_id',$request->segment(4))->orderBy('employee_requests.created_at', 'DESC')->take(700)->get()->toArray();

        foreach($data as $key=>$value)
            $data[$key]['for_my_approval'] = $this->isForMyApproval($value);


        return response()->json($data);
    }
    
    function isForMyApproval($data,$auth = null){
        if($auth == null){
            $auth = Auth::user();
        }

        if($data['request_type'] == 'salary_adjustment')
            if($auth->level == 5)
                return true;
            else
                return false;

        $position_data = json_decode(Position::find(User::find($data['employee_id'])->position_id)->position_data,true);

        $request_data = json_decode($data['request_data'],true);
        $action_data = json_decode($data['action_data'],true);

        if($request_data['status'] == 'pending'){
            foreach($action_data['approved_by'] as $key=>$value){
                if($value['status'] == 'Pending'){
                    if($value['position_id'] == 73){
                        $branch = $this->getCurrentBranch($data['employee_id'], date('Y-m-d'));
                        if($branch !== false){
                            if($branch['branch_head_employee_id'] == 0)
                                return ($branch['sas_id']==$auth->id);
                        }
                    }


                    if(isset($position_data['reporting_lines'][$key]['selection'])){
                        if($position_data['reporting_lines'][$key]['selection'] != 'position') {
                            foreach($position_data['reporting_lines'][$key]['ruling'] as $r=>$rr){
                                if($data['employee_id'] == $position_data['reporting_lines'][$key]['ruling'][$r]['employee_id'])
                                    return $position_data['reporting_lines'][$key]['ruling'][$r]['supervisor_id'] == $auth->id;
                            }
                        }
                        else
                            return $auth->position_id == $value['position_id'];
                    }
                    else
                        return $auth->position_id == $value['position_id'];
                }
            }
        }
        return false;
    }

    function searchValue($sample, $array, $field){
        if(!empty($array)){
            foreach($array as $key=>$value){
                if($value[$field] == $sample)
                    return $key;
            }
        }
        return false;
    }

    function finalApproveRequest($request_id){
        $get = EmployeeRequest::find($request_id);
        $data = json_decode($get->request_data,true);
        $data['status'] = 'approved';
        $get->request_data = json_encode($data);
        $get->save();
    }

    function setActionData($request_id, $index, $is_vacant){
        $get = EmployeeRequest::find($request_id);
        $data = json_decode($get->action_data,true);
        $data['approved_by'][$index] = $is_vacant?'Vacant':'Pending';
        $get->action_data = json_encode($data);
        $get->save();
    }

    function notifyUser($approval, $data){
        $message = '';
        foreach($approval as $key=>$value){
            if($value['status']=='approved'){
                $message = $value['name'] . ' just approved your '. $data->request_type .', Feedback: '. $value['feedback'] ;
            }
            if($value['status']=='denied'){
                $message = $value['name'] . ' just denied your '. $data->request_type .', Feedback: '. $value['feedback'] ;
            }
        }

        $request_data = json_decode($data->request_data);
        if($data->request_type == 'leave'){
            $request_data->leave_type_name = Leave_type::find($request_data->leave_type)->leave_type_name;
        }
        if($data->request_type == 'schedule'){
            $request_data->branch_name = Branch::find($request_data->branch_id)->branch_name;
        }
        $user = User::find($data->employee_id);
        $notification = array("name"=>$user->name,
            'request_type'=>ucfirst($data->request_type),
            'data'=>[$request_data, $approval],
            'type'=>'request',
            "reference_id"=>$data->id,
            "notes"=>$data->request_note,
            "message"=>$message);

        $mailer = new Mailer_Class;
        $mailer->sendApprovalActionNotification(User::find($data->employee_id), $notification);

        $position_data = json_decode(Position::find($user->position_id)->position_data,true);
        if($position_data['audience_data'] != null){
            //send cc here for audience positions
        }

        $notification = new Notification;
        $notification->notification_title = 'Request Confirmation';
        $notification->notification_body = $message;
        $notification->employee_id = $data->employee_id;
        $notification->is_read = 0;
        $notification->reference_id = $data->id;
        $notification->notification_type = 'request_confirmation';
        $notification->notification_data = $data->request_data;
        $notification->save();
    }

    function printRequest(Request $request){
        $pdf = new Request_Class;
        $pdf->printRequest($request->segment(3));
    }

    function sendNotification(Request $request){
        $req = EmployeeRequest::find($request->segment(3));
        $data = json_decode($req->action_data,true);

        $this->notifyApprovingOfficer($data['approved_by'], $req, $request->segment(4));

        return response()->json(["result"=>"success"]);
    }

    function sendConfirmation(Request $request){
        $req = EmployeeRequest::find($request->segment(3));
        $data = json_decode($req->action_data,true);

        $this->notifyUser($data['approved_by'], $req);

        return response()->json(["result"=>"success"]);
    }

    function updateRequest(Request $request){
        //validation rules
        $validator = Validator::make($request->all(), [
            'status' => 'required',
            'amount' => 'required',
            'feedback' => 'required',
            'target' => 'required',
            'period' => 'required',
            'discrepancy' => 'required',
        ]);
        //end of validation rules

        if ($validator->fails()) {
            return response()->json(["result"=>"failed","errors"=>$validator->errors()->all()]);
        }

        $data = [
          "status"=>$request->input('status'),
          "amount"=>$request->input('amount'),
          "feedback"=>$request->input('feedback'),
          "period"=>$request->input('period'),
          "target"=>$request->input('target'),
          "discrepancy"=>$request->input('discrepancy'),
        ];

        $find = EmployeeRequest::find($request->input('id'));
        $find->request_data = json_encode($data);
        $find->save();

        return response()->json(['result'=>'success']);
    }

    function updateNotes(Request $request){
        $find = EmployeeRequest::find($request->input('id'));
        $find->request_note = $request->input('notes');
        $find->save();

        return response()->json(['result'=>'success']);
    }
}