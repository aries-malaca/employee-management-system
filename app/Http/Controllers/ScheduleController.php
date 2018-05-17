<?php
namespace ExactivEM\Http\Controllers;
use Illuminate\Http\Request;
use ExactivEM\Http\Requests;
use Illuminate\Support\Facades\Auth;
use ExactivEM\ScheduleHistory;
use ExactivEM\User;
use ExactivEM\Branch;
use ExactivEM\ScheduleType;
use ExactivEM\Libraries\Attendance_Class;
use Validator;
use Cache;

class ScheduleController extends Controller{
    function index(){
        $this->middleware('auth');
        parent::__construct();

        //default title and url
        $this->data['page']['parent'] = 'Management';
        $this->data['page']['parent_url'] = '#';

        //check user's restriction
        if(!$this->checkRestriction()){
            return view('errors.no_permission', $this->data);
        }
        return view('schedules', $this->data);
    }
    
    //function used to print schedule data of employee
    function getSchedule(Request $request){
        $get_all =  ScheduleHistory::leftJoin('branches', 'schedule_histories.branch_id','=','branches.id')
                            ->where('employee_id', $request->segment(3))
                            ->where('schedule_type', 'RANGE')
                            ->select('schedule_histories.*','branches.*', 'branches.id as branch_id')
                            ->get()->all();
        
        $schedule_data = array();
        foreach($get_all as $key=>$value){
            //get the color
            //convert the starting to integer
            $starting = strtotime($value['schedule_start']);
            $ending = strtotime($value['schedule_end']);

            //json data to be converted to array
            $sched = json_decode($value['schedule_data'], true);
            
            //loop through each day
            while($starting < $ending){
                //loop through each schedule data
                $single = ScheduleHistory::leftJoin('branches', 'schedule_histories.branch_id','=','branches.id')
                                            ->where('employee_id', $request->segment(3))
                                            ->where('schedule_type', 'SINGLE')
                                            ->where('schedule_start','LIKE', date('Y-m-d',$starting). '%')
                                            ->select('schedule_histories.*','branches.branch_name', 'branches.id as branch_id')
                                            ->get()->first();

                if(isset($single['employee_id'])) {
                    if($single['schedule_data'] != '00:00'){
                        $schedule_data [] = array('title'=> date('h:i A',strtotime(date('Y-m-d')." ".$single['schedule_data']))  .' @'. $single['branch_name'],
                            'start'=> date('Y-m-d', $starting),
                            'end'=> date('Y-m-d', $starting),
                            'backgroundColor' => $this->getScheduleColor($value['branch_id'], $single['schedule_data'], idate('w',$starting)),
                            'tt'=> date('H:i',strtotime(date('Y-m-d')." ".$single['schedule_data'])),
                            'branch'=>$single['branch_id'],
                            'single'=>true,
                            'id'=>$single['id'],
                            '_i' => date('Y-m-d', $starting)
                        );
                    }
                    else{
                        $schedule_data [] = array("title"=>'Rest day',
                            'start'=> date('Y-m-d', $starting),
                            'end'=> date('Y-m-d', $starting),
                            'backgroundColor' => '#000000',
                            'tt'=> date('H:i',strtotime(date('Y-m-d')." ".$single['schedule_data'])),
                            'branch'=>$single['branch_id'],
                            'single'=>true,
                            'id'=>$single['id'],
                            '_i' => date('Y-m-d', $starting)
                        );
                    }

                }
                else{
                    if($sched[idate('w', $starting)] != '00:00'){

                        //add to schedule_data array
                        $schedule_data [] = array('title'=> date('h:i A',strtotime(date('Y-m-d')." ".$sched[idate('w', $starting)]))  .' @'. $value['branch_name'],
                            'start'=> date('Y-m-d', $starting),
                            'end'=> date('Y-m-d', $starting),
                            'backgroundColor' => $this->getScheduleColor($value['branch_id'], $sched[idate('w', $starting)], idate('w',$starting)),
                            'tt'=> date('H:i',strtotime(date('Y-m-d')." ".$sched[idate('w', $starting)])),
                            'branch'=> $value['branch_id'],
                            '_i' => date('Y-m-d', $starting)
                        );
                    }
                    else{
                        $schedule_data [] = array('title'=>'Rest day',
                            'start'=> date('Y-m-d', $starting),
                            'end'=> date('Y-m-d', $starting),
                            'backgroundColor' => '#000000',
                            'tt'=> date('H:i',strtotime(date('Y-m-d')." ".$sched[idate('w', $starting)])),
                            'branch'=> $value['branch_id'],
                            '_i' => date('Y-m-d', $starting)
                        );
                    }
                }

                $starting+= 86400; 
            }
        }

        //prints json data
        return response()->json($schedule_data);
    }

    function deleteSchedule(Request $request){
        $sched = ScheduleHistory::find($request->input('id'));

        ScheduleHistory::destroy($request->input('id'));
        $this->writeLog("Schedule", 'Deleted schedule - '. User::find($sched->employee_id)->name . ' ('. Branch::find($sched->branch_id)->branch_name .')');
        return response()->json(["command"=>"deleteSchedule", "result"=>"success"]);
    }

    //function to set schedule
    function addSchedule(Request $request){
        if(empty($request->input('data'))){
            return response()->json(["command"=>"addSchedule", "result"=>"failed","errors"=>"No Data to be submitted."]);
        }

        foreach($request->input('data') as $key=>$value){
            if($value['date_start'] == $value['date_end'])
                return response()->json(["command"=>"addSchedule", "result"=>"failed","errors"=>"Cannot add schedule for single day # ". ($key+1) .", use Employee Profile -> timecard instead."]);


            if($this->hasConflictRange($value['date_start'], $value['date_end'], $value['employee_id']))
                return response()->json(["command"=>"updateSchedule", "result"=>"failed","errors"=>"Cannot add schedule, date range has conflict with other schedule. Item # ". ($key+1)]);
        }

        foreach($request->input('data') as $key=>$value){
            $data = array($value['schedule_data'][6]);
            for($x=0;$x<6;$x++)
                $data[] = $value['schedule_data'][$x];

            //insert the schedule
            $sched = new ScheduleHistory;
            $sched->employee_id = $value['employee_id'];
            $sched->schedule_start = date('Y-m-d',strtotime($value['date_start']));
            $sched->schedule_end = date('Y-m-d',strtotime($value['date_end'])) .' 23:59:59';
            $sched->schedule_data = json_encode($data);
            $sched->schedule_type = "RANGE";
            $sched->branch_id = $value['branch_id'];
            $sched->is_flexi_time = $value['is_flexi_time'];
            //save the schedule

            Cache::forget('my_employees' .Auth::user()->id);
            $sched->save();
            $this->writeLog("Schedule", 'Added schedule - '. User::find($sched->employee_id)->name . ' ('. Branch::find($sched->branch_id)->branch_name .')');
        }

        //prints success
        return response()->json(["command"=>"addSchedule", "result"=>"success"]);
    }

    function getScheduleColor($branch_id, $time, $day){
        if($time == '00:00')
            return '#000000';

        $types = ScheduleType::where('branch_id', $branch_id)->get()->toArray();
        foreach($types as $key=>$value){
            $data = explode(',',$value['schedule_data']);
            array_unshift($data, $data[6]);
            array_pop($data);

            foreach($data as $k=>$v){
                if($k == $day && $v == $time)
                    return $value['schedule_color'];
            }
        }

        return '#FFAAAA';
    }

    function getScheduleName($branch_id, $time, $day){
        if($time == '00:00')
            return 'Rest Day';

        $types = ScheduleType::where('branch_id', $branch_id)->get()->toArray();

        foreach($types as $key=>$value){
            $data = explode(',',$value['schedule_data']);

            array_unshift($data, $data[6]);
            array_pop($data);

            foreach($data as $k=>$v){
                if($k == $day && $v == $time)
                    return $value['schedule_name'];
            }
        }

        return 'N/A';
    }

       //function to set schedule
    function updateSchedule(Request $request){
       //filter the type
        if($request->input('date_start') == '' || $request->input('date_end') == '')
            return response()->json(["command"=>"updateSchedule", "result"=>"failed","errors"=>"Fill up date."]);

        if( strtotime($request->input('date_start')) == strtotime($request->input('date_end')) )
            return response()->json(["command"=>"addSchedule", "result"=>"failed","errors"=>"Cannot add schedule for single day, use Employee Profile -> timecard instead."]);

        $type="RANGE";

        $data = array($request->input('schedule_data')[6]);
        for($x=0;$x<6;$x++)
            $data[] = $request->input('schedule_data')[$x];

        //insert the schedule
        $sched = ScheduleHistory::find($request->input('id'));

        if($this->hasConflictRange($request->input('date_start'), $request->input('date_end'), $sched->employee_id, $request->input('id')))
            return response()->json(["command"=>"updateSchedule", "result"=>"failed","errors"=>"Cannot add schedule, date range has conflict with other schedule."]);

        $sched->schedule_start = date('Y-m-d',strtotime($request->input('date_start')));
        $sched->schedule_end = date('Y-m-d',strtotime($request->input('date_end'))) .' 23:59:59';
        $sched->schedule_data =json_encode($data);
        $sched->schedule_type = $type;
        $sched->branch_id = $request->input('branch_id');
        Cache::forget('my_employees' .Auth::user()->id);
        //save the schedule
        $sched->save();
        $this->writeLog("Schedule", 'Updated schedule - '. User::find($sched->employee_id)->name . ' ('. Branch::find($sched->branch_id)->branch_name .')');
        //prints success
        return response()->json(["command"=>"updateSchedule", "result"=>"success"]);
    }

    function deleteSingleSchedule(Request $request){
        $get = ScheduleHistory::find($request->input('id'));
        ScheduleHistory::destroy($request->input('id'));

        $this->writeLog("Schedule", 'Deleted Single schedule - '. User::find($request->input('employee_id'))->name . ' ('. Branch::find($get->branch_id)->branch_name .')');
        return response()->json(["command"=>"deleteSchedule", "result"=>"success"]);
    }

    function saveSingleSchedule(Request $request){
        //validate inputs
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required|not_in:0|max:255',
            'employee_id' => 'required|not_in:0|max:255',
            'time' => 'required',
            'date' => 'required'
        ]);

        //if there are an error return to view and display errors
        if ($validator->fails())
            return response()->json(["command"=>"addSchedule", "result"=>"failed", "errors"=> $validator->errors()->all()]);

        $this->patchSchedule($request->input('date'), $request->input('time'), $request->input('employee_id'), $request->input('branch_id'));

        $this->writeLog("Schedule", 'Updated schedule - '. User::find($request->input('employee_id'))->name . ' ('. Branch::find($request->input('branch_id'))->branch_name .')');
        //prints success
        return response()->json(["command"=>"saveSchedule", "result"=>"success"]);
    }

    function getBranchHistory(Request $request){
        $start = strtotime($request->segment(4));
        $end = strtotime($request->segment(5));
        $schedule_data = array();
        $branch_name = Branch::find($request->segment(3))->branch_name;
        while($start<=$end){
            $get = $this->getBranchEmployees($request->segment(3), date('Y-m-d', $start));

            foreach($get as $key=>$value){
                //add to schedule_data array
                $branch = $this->getCurrentBranch($value['employee_id'], date('Y-m-d', $start));
                if($branch !== false){
                    if($branch['branch_id'] == $request->segment(3)){
                        $att = new Attendance_Class($value['employee_id'],date('Y-m-d', $start));
                        $schedule = $att->getSchedule('IN');

                        $finder = ScheduleHistory::where('schedule_type','SINGLE')
                                                    ->where('schedule_start','LIKE',date('Y-m-d', $start) .'%')
                                                    ->where('employee_id',$value['employee_id'] )
                                                    ->get()->first();

                        if(in_array($value['employee_id'], $this->getDownLines(Auth::user()))){
                            $schedule_data [] =
                            array('title'=> ($schedule=='00:00'?'Rest Day': date('h:i A',strtotime(date('Y-m-d'). ' ' .$schedule))) .' - '.$value['name'],
                                'start'=> date('Y-m-d', $start),
                                'name'=>$value['name'],
                                'end'=> date('Y-m-d', $start),
                                'backgroundColor' => $this->getScheduleColor($branch['branch_id'],$schedule, idate('w',$start)),
                                'branch'=> $branch_name,
                                'data'=>$branch,
                                'employee_id'=> $value['employee_id'],
                                'id'=> isset($finder['id'])?$finder['id']:null,
                                '_i'=>date('Y-m-d', $start),
                                '_tt'=>$schedule,
                                'single'=>isset($finder['id'])?true:false
                            );
                        }
                    }
                }
            }
            $start += 86400;
        }

        return response()->json($schedule_data);
    }

    function hasConflictRange($start, $end, $id, $except=0){
        $schedules = ScheduleHistory::where('employee_id', $id)
                                            ->where('id','<>' , $except)
                                            ->where('schedule_type', 'RANGE')
                                            ->get()->toArray();

        foreach($schedules as $key=>$value){
            if((strtotime($value['schedule_start']) <= strtotime($start) AND strtotime($value['schedule_end']) >= strtotime($start) ) OR
                (strtotime($value['schedule_start']) <= strtotime($end) AND strtotime($value['schedule_end']) >= strtotime($end)) OR
                (strtotime($value['schedule_start']) >= strtotime($start)AND strtotime($value['schedule_end']) <= strtotime($end)))
                return true;
        }

        return false;
    }
}