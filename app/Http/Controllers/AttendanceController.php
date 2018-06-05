<?php
namespace ExactivEM\Http\Controllers;
use Illuminate\Http\Request;
use ExactivEM\Http\Requests;
use ExactivEM\Attendance;
use ExactivEM\AttendanceLog;
use ExactivEM\User;
use Illuminate\Support\Facades\Auth;
use ExactivEM\EmployeeRequest;
use ExactivEM\Leave_type;
use ExactivEM\Holiday;
use ExactivEM\ScheduleHistory;
use ExactivEM\Emergency;
use ExactivEM\Branch;
use ExactivEM\Libraries\Attendance_Class;
use ExactivEM\BlockedAttendance;
use Storage;   

class AttendanceController extends Controller{
    function __construct(){
       parent::__construct();
       $this->initColors();
    }

    function getTimesheet(Request $request){
        $start = strtotime($request->segment(4) .'-'.$request->segment(3) .'-01');
        $end = date('t',$start);
        $end = strtotime($request->segment(4) .'-'.$request->segment(3) .'-' . $end);


        if($request->segment(5) == 1){
            $start = strtotime($request->segment(4) .'-'.$request->segment(3) .'-01');
            $end = 15;
            $end = strtotime($request->segment(4) .'-'.$request->segment(3) .'-' . $end);
        }
        else{
            $start = strtotime($request->segment(4) .'-'.$request->segment(3) .'-16');
            $end = date('t',$start);
            $end = strtotime($request->segment(4) .'-'.$request->segment(3) .'-' . $end);
        }

        $data = array();

        while($start<=$end){
            if($start>time())
                break;

            $att = new Attendance_Class($request->segment(6), date('Y-m-d', $start));
            $remarks = $att->getRemarks();
            $lates =  $att->getLate();
            $undertimes =  $att->getUndertime();
            $ots =  $att->getRegularOT() + $att->getRestdayOT();
            $in = $att->getLogs('IN');
            $out = $att->getLogs('OUT');
            $sched = $att->getSchedule('IN');
            $single = $att->getSingleSchedule();
            $branch = $this->getCurrentBranch($request->segment(6), date('Y-m-d',$start));
            $user = User::find($request->segment(6));
                $data[] = array("date" => date('m/d/Y',$start),
                    "remarks" => $remarks,
                    "late_hours" => $lates/60,
                    "undertime_hours" => $undertimes/60,
                    "overtime_hours" => $ots/60,
                    "in" => $in!==false? date('h:i A',strtotime($in[0]['attendance_stamp'])):'',
                    "out" => $out!==false? date('h:i A',strtotime($out[0]['attendance_stamp'])):'',
                    "schedule" => $sched,
                    "id" => $single['id'] ,
                    "branch" => $branch['branch_id'],
                    "single" => $single !== false,
                    "is_read_only" => $single !== false?($single['is_read_only']==1?true:false):false,
                    "is_hr" => Auth::user()->level===5 || Auth::user()->level=== 1,
                    "tt" => $sched,
                    "_i" => date('Y-m-d',$start),
                    "branch_name" => $branch['branch_name'],
                    "raw_logs"=> AttendanceLog::leftJoin('branches', 'attendance_logs.branch_id','=','branches.id')
                                                ->whereIn('biometric_no',[$user->biometric_no, $user->trainee_biometric_no])
                                                ->where('datetime', 'LIKE', date('Y-m-d',$start) .'%')
                                                ->select('branch_name','datetime','attendance_logs.branch_id')
                                                ->get()->toArray()
                );


            $start+=86400;
        }

        return response()->json($data);
    }

    function getPresentEmployees(Request $request){
        $data = Attendance::leftJoin('users', 'attendances.employee_id', '=','users.id')
                            ->select('users.*', 'attendances.*', 'users.id as user_id')
                            ->where('date_credited', 'like', '%'. $request->segment(3).'%')
                            ->where('in_out', 'IN')
                            ->whereIn('users.id', $this->myDownLines())
                            ->whereIn('stamp_type', ['REGULAR', 'ADJUSTMENT', 'TRAVEL', 'OFFSET'])
                            ->orderBy('attendance_stamp')
                            ->groupBy('users.id')
                            ->get()->all();

        //collect data

        $the_data = array();
        //employee list$request
        foreach($data as $key=> $value){
            $branch = $this->getCurrentBranch($value['user_id'], $request->segment(3));

            $get_timein = Attendance::where('in_out', 'IN')
                ->where('employee_id', $value['user_id'])
                ->where('date_credited', 'like', '%'. $request->segment(3) .'%')
                ->where('stamp_type', 'REGULAR')
                ->get()->first();


            $get_timeout = Attendance::where('in_out', 'OUT')
                ->where('employee_id', $value['user_id'])
                ->where('date_credited', 'like', '%'. $request->segment(3) .'%')
                ->whereIn('stamp_type', ['REGULAR', 'ADJUSTMENT', 'TRAVEL', 'OFFSET'])
                ->get()->first();
            $the_out = '';
            $via_out = '';

            //present employees
            if(sizeof($get_timein)>0){
                $the_in = date('h:i:s A',strtotime($get_timein['attendance_stamp']));
                $via_in = $get_timein['via'];
            }
            else{
                $the_in = date('h:i:s A',strtotime($value['attendance_stamp']));
                $via_in = $value['via'];
            }

            //present employees
            if(sizeof($get_timeout)>0){
                $the_out = date('h:i:s A',strtotime($get_timeout['attendance_stamp']));
                $via_out = $get_timeout['via'];
            }


            $the_data[] = array('name' => $value['name'] , 'employee_no'=>$value['employee_no'],
                'user_id'=> $value['user_id'],
                'time_in' => $the_in,
                'time_out' => $the_out,
                'via_in'=>$via_in,
                'via_out'=>$via_out,
                'branch_name' => ($branch !== false? $branch['branch_name']:'N/A')
            );
        }

        //output the data as json
        return response()->json($the_data);
    }

    // this method adds attendance log.
    //  triggered by clicking the time in / out at the home page
    function onScreenAddLog(){
        if($this->data['config']['is_completed_time'])
             return redirect('home')->with('loging', 'failed');
        
        $att = new Attendance_Class(Auth::user()->id, date('Y-m-d'));

        //determine if the user has current schedule
        if($att->hasSchedule() == false)
            return redirect('home')->with('loging', 'failed');
        
        //determine if already timed in
        if($this->data['config']['is_timedin']) {
            $in_out = 'OUT';
            $scheduled_stamp = $att->getSchedule('OUT');
        }
        else {
            $in_out = 'IN';
            $scheduled_stamp = $att->getSchedule('IN');
        }
        
        //array data to be added
        $data = array('date_credited'=> date('Y-m-d'),
                      'employee_id' =>Auth::user()->id,
                      'attendance_stamp'=> date('Y-m-d H:i:s'),
                      'stamp_type'=>'REGULAR',
                      'via'=>'WEB',
                      'in_out'=> $in_out ,
                      'scheduled_stamp'=> date('Y-m-d H:i', strtotime(date('Y-m-d').' '.$scheduled_stamp))
                      );
        
        //pass data to the function
        // adds data to the database
        if(!$this->addAttendance($data))//return failed message if not succeed
            return redirect('home')->with('loging', 'failed');

        return redirect('home')->with('loging', 'success');
    }

    function submitAttendances(Request $request){
        foreach($request->input('data') as $key=>$value){
            $att = new Attendance_Class($value['employee_id'], $value['date_start']);

            //determine if the user has current schedule
            if($att->hasSchedule() == false AND $value['type'] != 'OVERTIME')
                return response()->json(["command"=>"saveAdjusted","result"=>"failed","errors"=>"No schedule on selected date row #" . ($key+1) ]);
            elseif($att->hasSchedule() != false AND $value['type'] == 'OVERTIME')
                if($att->withinTime($value["time_start"], $value["time_end"]) )
            if($value['type'] != 'OVERTIME' && $value['type'] != 'TRAVEL' && $value['type'] != 'OFFSET')
                if(Attendance::where('in_out',$value['mode'])
                        ->where('employee_id', $value['employee_id'])
                        ->whereIN('stamp_type',['REGULAR','ADJUSTMENT'])
                        ->where('date_credited','like', $value['date_start'] .'%')
                        ->count() > 0 ){
                    return response()->json(["command"=>"saveAdjusted","result"=>"failed","errors"=>"Duplicate log in #" . ($key+1)]);
                }
        }

        foreach($request->input('data') as $key=>$value) {
            $att = new Attendance_Class($value['employee_id'], $value['date_start']);
            //array data to be added

            if($value['type'] == 'ADJUSTMENT'){
                $date_credited = $value['date_start'];
                //graveyard
                if($value['mode'] == 'OUT' AND date('G', strtotime($value['date_start'] ." ". $value['time_start'] )) < 4){
                    $date_credited = date('Y-m-d',strtotime($value['date_start'] . " -1 day"));
                }

                $data = array('date_credited' => $date_credited,
                    'employee_id' => $value['employee_id'],
                    'attendance_stamp' => date('Y-m-d H:i', strtotime($value['date_start'] . ' ' . $value['time_start'])),
                    'stamp_type' => $value['type'],
                    'via' => 'ADMIN',
                    'in_out' => $value['mode'],
                    'more_info' => array("notes" => $value['notes']),
                    'scheduled_stamp' => date('Y-m-d H:i', strtotime($value['date_start'] . ' ' . $att->getSchedule($value['mode'])))
                );
                //pass data to the function
                $this->addAttendance($data);
            }
            else{
                $data = array('date_credited' => $value['date_start'],
                    'employee_id' => $value['employee_id'],
                    'attendance_stamp' => date('Y-m-d H:i', strtotime($value['date_start'] . ' ' . $value['time_start'])),
                    'stamp_type' => $value['type'],
                    'via' => 'ADMIN',
                    'in_out' => 'IN',
                    'more_info' => array("notes" => $value['notes']),
                    'scheduled_stamp' => date('Y-m-d H:i', strtotime($value['date_start'] . ' ' . $att->getSchedule('IN')))
                );
                //pass data to the function
                $this->addAttendance($data, false);
                if($value['type'] == 'OVERTIME'){
                    $data = array('date_credited' => $value['date_start'],
                        'employee_id' => $value['employee_id'],
                        'attendance_stamp' => date('Y-m-d H:i', strtotime($value['date_end'] . ' ' . $value['time_end'])),
                        'stamp_type' => $value['type'],
                        'via' => 'ADMIN',
                        'in_out' => 'OUT',
                        'more_info' => array("notes" => $value['notes']),
                        'scheduled_stamp' => date('Y-m-d H:i', strtotime($value['date_start'] . ' ' . $att->getSchedule('OUT')))
                    );
                }
                else{
                    $data = array('date_credited' => $value['date_start'],
                        'employee_id' => $value['employee_id'],
                        'attendance_stamp' => date('Y-m-d H:i', strtotime($value['date_start'] . ' ' . $value['time_end'])),
                        'stamp_type' => $value['type'],
                        'via' => 'ADMIN',
                        'in_out' => 'OUT',
                        'more_info' => array("notes" => $value['notes']),
                        'scheduled_stamp' => date('Y-m-d H:i', strtotime($value['date_start'] . ' ' . $att->getSchedule('OUT')))
                    );
                }

                //pass data to the function
                $this->addAttendance($data, false);
            }
        }

        return response()->json(["command"=>"saveAdjusted","result"=>"success"]);
    }

    function saveLeave(Request $request){
        $leave_data = json_decode(Leave_type::find($request->input('leave_type_id'))->leave_type_data,true);
        $error = '';

        $days = $this->getLeaveDays($request->input(), $request->input('employee_id'));
        $usage = $this->getLeaveUsage($request->input('leave_type_id'), $request->input('employee_id'));

        if($days > $usage['credits'])
            $error = 'Not enough leave credits for item. ('.$days.')';

        if($days == 0)
            $error = 'Leave does not covered any working day in item.';

        if($leave_data['is_staggered'] == 'false'){
            $d = $this->getLeaveDays($request->input(), $request->input('employee_id'), 'all');
            if( $d  != ($usage['credits'] + $usage['used']) )
                $error = 'Staggered not allowed.('.$d.')';
        }

        if($days > $usage['credits'])
            $error = 'Not enough leave credits for item.('.$days.')';


        if($error != '')
            return response()->json(["command"=>"addLeave","result"=>"failed","errors"=> $error]);


        $leave_data = json_decode(Leave_type::find($request->input('leave_type_id'))->leave_type_data,true);
        $req = new EmployeeRequest;
        $req->request_type = 'leave';
        $req->request_note = $request->input('notes');
        $req->employee_id = $request->input('employee_id');
        $data = array("date_start" =>  date('Y-m-d',strtotime($request->input('date_start'))),
            "date_end" => date('Y-m-d',strtotime($request->input('date_end') ."+23 hours")),
            "status" => 'approved',
            "leave_type"=>$request->input('leave_type_id'),
            "paid"=> ($leave_data['paid']=='true'?1:0),
            "mode"=> $request->input('mode'),
            "days"=> $this->getLeaveDays($request->input(), $request->input('employee_id'), ($leave_data['is_staggered']=='true'?'scheduled':'all') ));

        $req->request_data = json_encode($data);
        $req->action_data = json_encode(array("approved_by"=> array()));

        $req->save();
        $details = 'Added Leave for '. User::find($req->employee_id)->name .' '. $request->input("date_start") .' - '. $request->input("date_end");
        $this->writeLog("Employee Request", $details);

        return response()->json(["command"=>"addLeave","result"=>"success"]);
    }


    function deleteLeave(Request $request){
        EmployeeRequest::destroy($request->input('request_id'));
        return response()->json(["command"=>"deleteLeave", "result"=>"success"]);
    }

    function deleteAttendance(Request $request){
        if($request->input('via') == 'BIO'){
            $new = new BlockedAttendance;
            $new->employee_id = $request->input('employee_id');
            $new->datetime = $request->input('datetime');
            $new->added_by_id = Auth::user()->id;
            $new->save();
        }

        //determine if have partner log
        $get = Attendance::find($request->input('id'));
        if(in_array($get->stamp_type, array('TRAVEL','OVERTIME','OFFSET'))){
            Attendance::where('stamp_type', $get->stamp_type)
                        ->where('employee_id', $get->employee_id)
                        ->where('date_credited', $get->date_credited)
                        ->take(4)
                        ->delete();
        }
        else{
            Attendance::destroy($request->input('id'));
        }

        return response()->json(["command"=>"deleteAttendance", "result"=>"success"]);
    }
    
    //get the attendance data for the user
    //this function is frequently used in calendar viewing
    function getAttendance(Request $request){
        $user = User::find($request->segment(3));
        header('Access-Control-Allow-Origin: *');
        if($request->segment(4)!==null && $request->segment(5)===null ){
            $attendance = Attendance::where('employee_id', $request->segment(3))
                                        ->where('date_credited','like',  $request->segment(4). '%')->get()->toArray();

            foreach($attendance as $key=>$att){
                $more_info = json_decode($att['more_info']);
                if(isset($more_info->branch_id)){
                    $attendance[$key]['branch_name'] = Branch::find($more_info->branch_id)->branch_name;
                }
                else{
                    if($att['stamp_type']!='ADJUSTMENT' && $att['in_out']=='IN'){
                       $attendance[$key]['request_data'] = EmployeeRequest::where('employee_id', $request->segment(3))
                                                                            ->where('request_data', 'LIKE', '%"date_start":"'. $request->segment(4) .'"%')
                                                                            ->where('request_data', 'LIKE', '%"time_start":"'. date('H:i',strtotime($att['attendance_stamp'])) .'"%')
                                                                            ->select('request_note','id')
                                                                            ->get()->first();
                    }
                    elseif($att['stamp_type']=='ADJUSTMENT'){
                        $attendance[$key]['request_data'] = EmployeeRequest::where('employee_id', $request->segment(3))
                            ->where('request_data', 'LIKE', '%"date":"'. $request->segment(4) .'"%')
                            ->where('request_data', 'LIKE', '%"time":"'. date('H:i',strtotime($att['attendance_stamp'])) .'"%')
                            ->select('request_note','id')
                            ->get()->first();
                    }

                    if($att['via'] == 'ADMIN'){
                        $attendance[$key]['admin_notes'] = json_decode($att['more_info'])->notes;
                    }
                }
            }

            return response()->json($attendance);
        }

        $attendance = Attendance::where('employee_id', $request->segment(3))->get()->all();          
        $the_data = array();

        foreach($attendance as $key=> $value){
            //attendance type 
            if($value['stamp_type']=='REGULAR'){
                if($value['in_out']=='IN')
                    $color = $this->data['color']['timein'];
                else
                    $color = $this->data['color']['timeout'];
            }
            elseif($value['stamp_type']=='ADJUSTMENT')
                $color = $this->data['color']['adjustment'];
            elseif($value['stamp_type']=='OVERTIME')
                $color = $this->data['color']['overtime'];
            elseif($value['stamp_type']=='TRAVEL')
                $color = $this->data['color']['travel'];
            elseif($value['stamp_type']=='OFFSET')
                $color = $this->data['color']['offset'];
            else
                $color = $this->data['color']['adjustment'];
            
            //create a data array for each attendance data 
            if(date('Y-m-d',strtotime($value['attendance_stamp'])) == date('Y-m-d',strtotime($value['date_credited'])))
                $time = date('h:i A',strtotime($value['attendance_stamp']));
            else
                 $time = "***".date('h:i A',strtotime($value['attendance_stamp']));
            

            //each data array represents 1 attendance in calendar
            $the_data[] = array('title'=> $time . ($value['stamp_type']!='REGULAR'?'('.$value['in_out'].')':''),
                                'start'=> dateShort($value['date_credited']) .' '. timeShort($value['attendance_stamp']),
                                'end'=> dateShort($value['date_credited']) .' '. timeShort($value['attendance_stamp']),
                                'backgroundColor' => $color,
                                'type'=>$value['stamp_type'],
                                'mode'=>$value['in_out'],
                                'id'=>$value['id'],
                                'employee_id'=>$request->segment(3),
                                'via'=>$value['via'],
                                'date_credited'=>dateShort($value['date_credited']),
                                'datetime'=>dateShort($value['date_credited']) .' '. timeShort($value['attendance_stamp']));
        }

        $leaves = EmployeeRequest::leftJoin('users','employee_requests.employee_id','=','users.id')
            ->select('employee_requests.*', 'users.name','users.id as user_id', 'employee_requests.id as request_id')
            ->where('employee_id',$request->segment(3) )
            ->where('request_type','leave')
            ->get()->all();

        if( !empty($leaves) ){
            foreach($leaves as $key=>$value){
                $find = Leave_type::find(json_decode($value['request_data'])->leave_type);
                $leaves[$key]['leave_name'] = $find->leave_type_name;
            }
        }

        //loop through approved leaves 
        foreach($leaves as $key=>$value){
            $leave_data = json_decode($value['request_data']);

            if($leave_data->status == 'pending' OR $leave_data->status == 'denied')
                continue;
            
            $find = Leave_type::find(json_decode($value['request_data'])->leave_type);

            $txt = '';
            if(isset(json_decode($value['request_data'])->exclude) )
                $txt = ' (Excluded:'. implode(',', json_decode($value['request_data'])->exclude) .')';

            //each data array represents 1 attendance in calendar    
            $the_data [] = array('title'=> $find->leave_type_name . $txt,
                                'start'=> json_decode($value['request_data'])->date_start,
                                'end'=> json_decode($value['request_data'])->date_end .' ' .'23:59',
                                '__start'=> dateNormal(json_decode($value['request_data'])->date_start),
                                '__end'=> dateNormal(json_decode($value['request_data'])->date_end),
                                'backgroundColor' => $this->data['color']['leave'],
                                'type'=>'LEAVE',
                                'notes'=>$value['request_note'],
                                'mode'=>json_decode($value['request_data'])->mode,
                                'paid'=>json_decode($value['request_data'])->paid,
                                'days'=>json_decode($value['request_data'])->days,
                                'employee_id'=>$request->segment(3),
                                'id'=>$value['request_id']
                                );
        }
        
        //get the recurring and show for the 10 years.
        $holidays = Holiday::where('is_yearly',1)->get()->all();
        
        //loop through holidays
        foreach($holidays as $key=>$value){
            $date_from = strtotime($value['holiday_date']);
            $date_until = strtotime($value['holiday_date'] ." + 4 years");

            while($date_from< $date_until){
                if(in_array($this->getCurrentBranch($request->segment(3), date('Y-m-d'))['branch_id'],json_decode($value['branch_covered'],true)) OR
                    in_array(0,json_decode($value['branch_covered'])))
                $the_data [] = array('title'=> $value['holiday_name'],
                                'start'=> date('Y-m-d',$date_from),
                                'end'=> date('Y-m-d',$date_from),
                                'backgroundColor' => $this->data['color']['holiday']);
                
                $date_from = strtotime(date('Y-m-d',$date_from)."+ 1 year");
            }
        }
        
        //get the non-recurring and show for the 10 years.
        $holidays = Holiday::where('is_yearly',0)->get()->all();
        
        //add each holidays to the calendar
        foreach($holidays as $key=>$value){
            if(in_array($this->getCurrentBranch($request->segment(3), date('Y-m-d'))['branch_id'],json_decode($value['branch_covered'],true)) OR
                in_array(0,json_decode($value['branch_covered'])))
            $the_data [] = array('title'=> $value['holiday_name'],
                            'start'=> date('Y-m-d',strtotime($value['holiday_date'])),
                            'end'=> date('Y-m-d',strtotime($value['holiday_date'])),
                            'backgroundColor' => $this->data['color']['holiday'],
                            "description"=>"Holiday"
                            );
        }
        
        //get all the emergencies just like holiday
        $emergencies = Emergency::get()->all();

        //loop through emergencies and add to calendar
        foreach($emergencies as $key=>$value){
            $my_branch = $this->getCurrentBranch($request->segment(3),date('Y-m-d',strtotime($value['date_start'])));
            if($my_branch !== false){
                if(in_array($my_branch['branch_id'], json_decode($value['branch_covered']))){
                    $the_data [] = array('title'=> $value['emergency_name'],
                        'start'=> date('Y-m-d H:i',strtotime($value['date_start'])),
                        'end'=> date('Y-m-d H:i',strtotime($value['date_end'] .' 23:59:59')),
                        'backgroundColor' => $this->data['color']['emergency']
                    );
                }
            }
        }

        if($request->segment(5)==1){
            //show also raw logs.
            $u = User::find($request->segment(3));

            $logs = AttendanceLog::whereIn('biometric_no', [$u->biometric_no, $u->trainee_biometric_no])
                                    ->orderBy('id','DESC')->take(150)->get()->toArray();

            if(!empty($logs))
                foreach ($logs as $key => $value) {
                    $the_data [] = array('title' => date('h:i A', strtotime($value['datetime'])) . ' @' . Branch::find($value['branch_id'])->branch_name,
                        'start' => date('Y-m-d', strtotime($value['datetime'])),
                        'end' => date('Y-m-d', strtotime($value['datetime'])),
                        'backgroundColor' => $this->data['color']['unofficial']
                    );
                }

        }


        //output the data as json to be used in calendar
        return response()->json($the_data);
    }

    // this function is used for cron job import attendance
    // also used by data management tool
    function importAttendance(Request $request){
        //get all branches
        $branches = Branch::get()->all();

        //loop through branches and perform fopen for each current day file
        foreach ($branches as $key => $branch) {
            if($request->segment(4) != 'all' && $request->segment(4) != $branch['id'])
                continue;

            // segment 4 == 1 means import attendance via data management
            if ($request->segment(5) !== null) {
                $logs = AttendanceLog::where('branch_id', $branch['id'])
                    ->where('biometric_no', $request->segment(5))
                    ->orderBy('datetime')
                    ->get()->toArray();
            } else {
                $logs = AttendanceLog::where('branch_id', $branch['id'])
                    ->orderBy('datetime')
                    ->get()->toArray();
            }

            //loop through each lines
            foreach ($logs as $key =>$log) {
               //if from cron - get only the latest 1 week logs
               $diff = time() - strtotime($log['datetime']);
               if($diff < 2814400){
                   $employee = User::where('biometric_no', $log['biometric_no'])
                                        ->orWhere('trainee_biometric_no', $log['biometric_no'])
                                        ->get()->first();

                   if(empty($employee))
                       continue;

                   if($request->segment(5) != null )
                       if($request->segment(5) != $employee['id'] )
                           continue;

                   $check_blocked = BlockedAttendance::where('employee_id', $employee->id )
                       ->where('datetime', 'LIKE', date('Y-m-d',strtotime($log['datetime'])) .'%')
                       ->get()->toArray();

                   if(!empty($check_blocked))
                       continue;

                   $att = new Attendance_Class($employee->id, date('Y-m-d',strtotime($log['datetime'])));

                   //check if the log is a valid in
                   if(Attendance::where('employee_id',$employee->id)
                           ->where('date_credited','like', date('Y-m-d',strtotime($log['datetime'])) .'%')
                           ->where('in_out', 'IN')
                           ->where('stamp_type','REGULAR')
                           ->count() == 0){

                       $sched = $att->getSchedule('IN');

                       if($sched === false  AND date('G', strtotime($log['datetime'])) > 3)
                           continue;

                       //input data ready for database insertion
                       $input = array('date_credited'=>  date('Y-m-d',strtotime($log['datetime'])) ,
                           'employee_id' => $employee->id,
                           'attendance_stamp'=> $log['datetime'],
                           'stamp_type'=>'REGULAR',
                           'via'=>'BIO',
                           'in_out'=> 'IN' ,
                           'more_info'=>array("branch_id"=>$branch['id']),
                           'scheduled_stamp'=>  date('Y-m-d',strtotime($log['datetime'])) . ' '. $sched);

                       //pass data to the function addAttendance in Controller.php
                       if($this->addAttendance($input))
                           echo 'ok';
                   }

                   //check if the log is valid for out
                   if(Attendance::where('employee_id',$employee->id)
                           ->where('date_credited','like', date('Y-m-d',strtotime($log['datetime']))  .'%')
                           ->where('in_out','<>' ,'OUT')
                           ->whereIn('stamp_type',['REGULAR','ADJUSTMENT','TRAVEL','OFFSET'])
                           ->count() > 0){

                       $sched = $att->getSchedule('OUT');

                       //check if the sched out is valid or not false... and not more than 3AM!
                       if($sched === false AND date('G', strtotime($log['datetime'])) > 3)
                           continue;

                       //input for insertion
                       $input = array('date_credited'=> date('Y-m-d',strtotime($log['datetime'])),
                           'employee_id' => $employee->id,
                           'attendance_stamp'=>$log['datetime'],
                           'stamp_type'=>'REGULAR',
                           'via'=>'BIO',
                           'in_out'=> 'OUT' ,
                           'more_info'=>array("branch_id"=>$branch['id']),
                           'scheduled_stamp'=> date('Y-m-d',strtotime($log['datetime'])) . ' '. $sched);

                       //pass data to the function addAttendance in Controller.php
                       if($this->addAttendance($input))
                           echo 'ok';
                   }
               }
            }
        }
    }

    function tuneupAttendances(Request $request){
        $employees = $this->getBranchEmployees($request->segment(3),date('Y-m-d'));
        foreach($employees as $key=>$value){
            $attendances = Attendance::where('employee_id', $value['employee_id'])->get()->toArray();
            foreach($attendances as $k=>$val){
                $mode = $val['in_out'];
                $att = new Attendance_Class($value['employee_id'], date('Y-m-d',strtotime($val['date_credited'])));
                $attendance_stamp = $att->getSchedule($mode);

                if($attendance_stamp !== false ) {
                    Attendance::where('id', $val['id'])->update(['scheduled_stamp'=> date('Y-m-d H:i',strtotime(date('Y-m-d',strtotime($val['date_credited']))  .' ' . $attendance_stamp)) ]);
                }
            }
        }

        return response()->json(['result'=>'success']);
    }

    function lockSchedules(Request $request){
        $data = $request->input('attendances');
        foreach($data as $key=>$value){
            $this->finalizeSchedule($request->input('employee_id'), $value['date'], $value['date']);
        }

        return response()->json(["result"=>"success"]);
    }

    function fixSchedule(Request $request){
        $data = $request->input('attendances');
        $branches = array();
        foreach($data as $key=>$value){
            if(in_array('rest-day', $value['remarks']) && !empty($value['raw_logs'])){
                $branch_schedules = $this->getAvailableSchedules($value['raw_logs'][0]['branch_id'],idate('w', strtotime($value['_i'])));
                rsort($branch_schedules);

                $this->patchSchedule($value['_i'], $this->resolveTime($value, $branch_schedules), $request->input('employee_id'), $value['raw_logs'][0]['branch_id']);
                $branches[] = $value['raw_logs'][0]['branch_id'];
            }
            else{
                $branch_id = !empty($value['raw_logs'])?$value['raw_logs'][0]['branch_id']:$value['branch'];
                $branch_schedules = $this->getAvailableSchedules($branch_id, idate('w', strtotime($value['_i'])));
                sort($branch_schedules);

                if(($time = $this->getCorrectSchedule($value, $branch_schedules)) && $value['in'] != ""){
                    $this->patchSchedule($value['_i'], $time, $request->input('employee_id'), $branch_id);
                    $branches[] = $branch_id;
                }
            }
        }



        return response()->json(["result"=>"success", "branches"=>array_unique($branches)]);
    }


    function getCorrectSchedule($attendance, $schedules){
        $in = $attendance['_i'] .' '. $attendance['in'];
        $out = $attendance['_i'] .' ' . $attendance['out'];

        if(strtotime($in)> strtotime($out))
            $out = date('Y-m-d h:i A', strtotime($out . " +1 day"));

        $cases = array();

        foreach($schedules as $key=>$value){
            if(date('h',strtotime($out))==0)
                $out = date('Y-m-d h:i A', strtotime($in . " +9 hours"));

            $late = strtotime($in) - strtotime($attendance['_i'].' '.$value);

            if($late<0)
                $late = 0;

            if($late >= 7200)
                $late = 14400;

            $undertime = strtotime($attendance['_i'].' '.$value . " +9 hours") - strtotime($out);
            if($undertime<0)
                $undertime = 0;

            $cases[] = array(
                "schedule" => $attendance['_i'].' '.$value,
                "tardiness"=> $late+$undertime,
            );
        }

        usort($cases, function($a, $b) { return $a['tardiness']>$b['tardiness']; });
        foreach($cases as $key=>$value){
            if($key==0)
                return  date('H:i', strtotime($value['schedule']));
        }

        return false;
    }

    function resolveTime($attendance, $schedules){
        $timein = $attendance['raw_logs'][0]['datetime'];
        foreach($schedules as $key=>$value){
            $diff = strtotime($attendance['_i'].' '.$value) - strtotime($timein);
            if($diff < 3600)
                return $value;
        }

        return $schedules[0];
    }


    function getAttendanceErrors(Request $request){
        $employees = $this->getBranchEmployees($request->segment(3), $request->segment(4));

        $errors = array();
        foreach($employees as $key => $value){
            $start = strtotime($request->segment(4));
            $end = strtotime($request->segment(5));

            $data = array();
            while($start <= $end){

                $att = new Attendance_Class($value['id'], date('Y-m-d', $start));
                $remarks = $att->getRemarks();
                $lates =  $att->getLate();
                $undertimes =  $att->getUndertime();
                $ots =  $att->getRegularOT() + $att->getRestdayOT();
                $in = $att->getLogs('IN');
                $out = $att->getLogs('OUT');
                $sched = $att->getSchedule('IN');
                $single = $att->getSingleSchedule();
                $branch = $this->getCurrentBranch($value['id'], date('Y-m-d',$start));
                $user = User::find($value['id']);
                $catched = array("date" => date('m/d/Y',$start),
                            "remarks" => $remarks,
                            "late_hours" => $lates/60,
                            "undertime_hours" => $undertimes/60,
                            "overtime_hours" => $ots/60,
                            "in" => $in!==false? date('h:i A',strtotime($in[0]['attendance_stamp'])):'',
                            "out" => $out!==false? date('h:i A',strtotime($out[0]['attendance_stamp'])):'',
                            "schedule" => $sched,
                            "id" => $single['id'] ,
                            "branch" => $branch['branch_id'],
                            "single" => $single !== false,
                            "tt" => $sched,
                            "_i" => date('Y-m-d',$start),
                            "branch_name" => $branch['branch_name'],
                            "raw_logs"=> AttendanceLog::leftJoin('branches', 'attendance_logs.branch_id','=','branches.id')
                                ->whereIn('biometric_no',[$user->biometric_no, $user->trainee_biometric_no])
                                ->where('datetime', 'LIKE', date('Y-m-d',$start) .'%')
                                ->select('branch_name','datetime','attendance_logs.branch_id')
                                ->get()->toArray()
                );

                $err = $this->hasErrors($catched);

                if(sizeof($err)>0){
                    $catched['errors'] = $err;
                    $data[] = $catched;
                }

                $start+=86400;
            }

            $errors[] = array("errors"=>$data, "name"=>$value['name']);
        }

        return $errors;
    }

    function hasErrors($data){
        $raw = $data['raw_logs'];
        $errors = array();
        if($data['overtime_hours'] >5)
            $errors[] = "Too much OT";

        if($data['late_hours'] > 0.50)
            $errors[] = "Too much late";

        if($data['undertime_hours'] > 0.25)
            $errors[] = "Too much undertime";

        if($data['schedule'] == '00:00' AND sizeof($raw) > 0)
            $errors[] = "Rest Day with logs";

        if($data['in'] == '' AND sizeof($raw) > 0 AND $data['schedule'] != '00:00')
            $errors[] = "No attendance with raw logs.";

        return $errors;
    }
}