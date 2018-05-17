<?php
namespace ExactivEM\Libraries;

use ExactivEM\Attendance;
use ExactivEM\Leave_type;
use ExactivEM\ScheduleHistory;
use ExactivEM\EmployeeRequest;
use ExactivEM\Emergency;
use ExactivEM\Holiday;
use ExactivEM\User;
use ExactivEM\Config;
use ExactivEM\Position;
use ExactivEM\EmploymentStatus;
class Attendance_Class {

    const MAX_LATE_SECONDS = 14400;
    const HALFDAY_SECONDS = 14400;
    // constructor required to initialize
    // 2 variables..
    function __construct($employee_id, $date){
        $this->employee_id = $employee_id;
        $this->date = $date;
        $this->employee_details = User::find($this->employee_id); //object type
        
        //get grace period data
        $get = json_decode(Position::find($this->employee_details->position_id)->position_data);
        if(isset($get->grace_period_minutes)){
            $this->graceperiod = array("mins"=>$get->grace_period_minutes, "limit"=>$get->grace_period_per_month);
        }
        else{
            $this->graceperiod = array("mins"=>15, "limit"=>30);
        }
    }

    public function getSingleSchedule(){
        $single = ScheduleHistory::leftJoin('branches', 'schedule_histories.branch_id','=','branches.id')
            ->where('employee_id', $this->employee_id)
            ->where('schedule_type', 'SINGLE')
            ->where('schedule_start','LIKE', $this->date. '%')
            ->select('schedule_histories.*','branches.branch_name', 'branches.id as branch_id')
            ->get()->first();
        if(isset($single['id'])){
            return $single;
        }
        else{
            return false;
        }
    }

    public function getSchedule($mode = 'IN'){
        $sched = ScheduleHistory::where('schedule_start', '<=', $this->date)
                                    ->where('schedule_end','>=', $this->date)
                                    ->where('employee_id', $this->employee_id)
                                    ->orderBy('schedule_type','DESC')
                                    ->get()->first();
        if(empty($sched) ){
            $log = $this->getLogs($mode);

            if($log !== false){
                return date('H:i',strtotime($log[0]['scheduled_stamp']));
            }
        }
        elseif($mode == 'object'){
            return $sched;
        }
        else{
            if($mode == 'IN'){
                if($sched['schedule_type'] == 'SINGLE')
                    return $sched['schedule_data'];

                return date('H:i', strtotime($this->date.' '.json_decode($sched['schedule_data'],true )[idate('w',strtotime($this->date))]  ) );
            }
            else{
                if($sched['schedule_type'] == 'SINGLE')
                    return date('H:i',strtotime( '2000-01-01 '.$sched['schedule_data'] ." +9hours"));


                return date('H:i', strtotime($this->date.' '.json_decode($sched['schedule_data'],true )[idate('w',strtotime($this->date))] ." +9hours") );
            }
            
        }

        return '00:00';
    }
    
    public function is_flexi_time(){
        $sched = ScheduleHistory::where('schedule_start', '<=', $this->date)
                            ->where('schedule_end','>=', $this->date)
                            ->where('employee_id', $this->employee_id)
                            ->orderBy('schedule_type')
                            ->get()->first();
                            
        if(!empty($sched)){
            if($sched['is_flexi_time'] == 1){
                return true;
            }
        }        

        return false;
    } 
    
    public function getBranch(){
     
        $sched = ScheduleHistory::leftJoin('branches','schedule_histories.branch_id','=','branches.id')
                            ->where('schedule_start', '<=', $this->date)
                            ->where('schedule_end','>=', $this->date)
                            ->where('employee_id', $this->employee_id)
                            ->orderBy('schedule_type')
                            ->select('schedule_histories.*','branch_name')
                            ->get()->first();
        if(empty($sched)){
            return false;
        }
        else{
            return $sched;
        }
        
    }
    
    public function hasSchedule(){
        $sched = $this->getSchedule();
        if(!empty($sched) AND $sched !='00:00')
            return true;
        if($this->getEmergency())
            return true;

        return false;
    }

    function isAbsent($is_daily=false){
        if($this->getEmergency() && $is_daily)
            return false;

        if(in_array('absent',$this->getRemarks()) && !in_array('holiday',$this->getRemarks()))
            return true;

        if(in_array('leave', $this->getRemarks())){
            if($this->getLeave()){
                $l = $this->getLeave();
                if($l['days']==0.5){
                    return false;
                }
            }
            return true;
        }


        if($is_daily && !in_array('rest-day',$this->getRemarks()) && !in_array('present',$this->getRemarks()) && !in_array('no-timeout',$this->getRemarks()))
            return true;

        return false;
    }

    public function getRemarks(){
        $remarks = array();
        $in = $this->getLogs('IN');
        $out = $this->getLogs('OUT');
        if($this->hasSchedule()){
            if( $in !==false AND $out !==false)
                $remarks[] = 'present';
            elseif($in !==false AND $out ===false )
                $remarks[] = 'no-timeout';
        }
        else
            $remarks[] = 'rest-day';
        
        if($this->getOvertime() !== false)
            $remarks[] = 'overtime';
        if($this->getOffset() !== false)
            $remarks[] = 'offset';
        if($this->getTravel() !== false)
            $remarks[] = 'travel';
        if($this->getLeave() !== false){
            if(!in_array('rest-day', $remarks))
                $remarks[] = 'leave';
        }
        if($this->getEmergency() !== false)
            $remarks[] = 'blocked sched.';
        if($this->getHoliday() !== false)
            $remarks[] = 'holiday';
        if(empty($remarks))
            $remarks[] = 'absent';

        return $remarks;
    }
    
    //flag - 0 normal, 1 return true/false if hit the grace period
    public function getLate($flag = 0){
        if($this->getEmergency())
            return 0;

        $in = $this->getLogs('IN');
        if(!$this->hasSchedule())
            return false;
        else{
            if(!$in AND !$this->getOffset() AND !$this->getTravel())
                return false;
            else{
                $log_time = strtotime($in[0]['attendance_stamp']);
                $sched = strtotime($this->date." ".$this->getSchedule('IN') );
                
                $diff = $log_time-$sched;
                $less = 0;
                $leave = $this->getLeave();
                if(!empty($leave)){
                    if($leave['days'] == 0.5 AND $leave['mode']=='AM' AND $leave['is_paid']==1)
                        $less = 240;
                    if($leave['mode']=='FULL' AND $leave['is_paid']==1)
                        $less = 480;
                }
                
                if($this->is_flexi_time())
                    return 0;

                if($flag == 3){
                    $diff = ($diff/60) - $less;
                    return ($diff>0? $diff:'0');
                }
                //within gp determining hits count
                if($flag == 2){
                    if( ($diff/60) <= $this->graceperiod['mins'] AND $diff > 0 )
                        return true;
                    else
                        return false;
                }

                if( ($diff/60) <= $this->graceperiod['mins'] AND $diff > 0 AND $this->countHits() < $this->graceperiod['limit']){
                    if($flag == 1)
                        return true;
                    else
                        return 0;
                }
                else{
                    if($flag == 1)
                        return false;
                }

                //determine half day
                if( ($diff >=self::MAX_LATE_SECONDS ) )
                    $diff = self::HALFDAY_SECONDS;

                $diff = ($diff/60) - $less;

                return ($diff>0? $diff:'0');
            }
        }
    }

    function isHalfDay($tardiness, $minutes){
        if($tardiness === false)
            return false;

        return ($tardiness >= $minutes);
    }
    
    public function getUndertime(){
        if($this->getEmergency())
            return 0;

        if(!$this->hasSchedule())
            return false;
        else{
            $out = $this->getLogs('OUT');
            if(!$out AND !$this->getOffset() AND !$this->getTravel())
                return false;
            else{

                $log_time = strtotime($out[0]['attendance_stamp']);
                $sched = strtotime($this->date." ".$this->getSchedule('OUT') );
                
                $diff = $sched-$log_time;
                $less = 0;
                $leave = $this->getLeave();
                if(!empty($leave)){
                    if($leave['days'] == 0.5 AND $leave['mode']=='PM' AND $leave['is_paid']==1)
                        $less = 240;

                    if($leave['mode']=='FULL' AND $leave['is_paid']==1)
                        $less = 480;
                }

                if( $diff >= 14400 && $diff <= 18000 ) // difference is between 4 and 5 hours should be = four hours deduct
                    $diff = 14400;
                
                
                if($this->is_flexi_time()){
                    $d = strtotime($out[0]['attendance_stamp']) - strtotime($this->getLogs('IN')[0]['attendance_stamp']);
                    $diff = 32400 - $d;
                }
                
                $diff = ($diff/60) - $less;

                if($diff<0){
                    $diff = 0;
                }

                if($this->graceperiod['mins']>0){
                    $dd = 0;
                    if($this->getLate() === 0 &&isset($this->getLogs('OUT')[0]['attendance_stamp']) && isset($this->getLogs('IN')[0]['attendance_stamp'])){
                        $dd = strtotime($this->getLogs('OUT')[0]['attendance_stamp']) - strtotime($this->getLogs('IN')[0]['attendance_stamp']);

                        $dd = 540 - ($dd/60);
                        $dd -= $less;
                        if($dd<0)
                            $dd = 0;

                        return $dd;
                    }
                }

                return $diff;
            }
        }
    }
    
    public function getPlusMinus(){
        return 0;
    }
    
    //get the logs for the day
    public function getLogs($mode = 'IN'){
        $att = Attendance::where('date_credited', 'like', $this->date.'%')
                            ->where('in_out', $mode)
                            ->whereIn('stamp_type',["REGULAR", "ADJUSTMENT","TRAVEL","OFFSET"])
                            ->where('employee_id', $this->employee_id);
        if($mode=='IN')
            $att = $att->orderBy('attendance_stamp');
        else
            $att = $att->orderBy('attendance_stamp','DESC');

        $att = $att->get()->all();

        if(empty($att))
            return false;

        return $att;
    }
    
    public function getOvertime(){
        return $this->getAttendanceData('OVERTIME');
    }
    
    public function getOffset(){
        return $this->getAttendanceData('OFFSET');
    } 
    
    public function getTravel(){
        return $this->getAttendanceData('TRAVEL');
    }

    function getAttendanceData($what){
        $request = Attendance::where('date_credited', 'like', $this->date.'%')
                                ->where('stamp_type',$what)
                                ->where('employee_id', $this->employee_id)
                                ->get()->all();
        if(empty($request)){
            return false;
        }
        return $request;
    }
    
    public function getLeave(){
        $leaves = EmployeeRequest::leftJoin('users','employee_requests.employee_id','=','users.id')
                                        ->select('employee_requests.*', 'users.name','users.id as user_id', 
                                                    'employee_requests.id as request_id')
                                        ->where('employee_id', $this->employee_id)
                                        ->where('request_type','leave')
                                        ->get()->toArray();

        foreach($leaves as $key=>$value){
            $data = json_decode($value['request_data'],true);
            
            if(strtotime($data['date_start']) <= strtotime($this->date)
                AND strtotime($data['date_end']) >= strtotime($this->date)
                AND $data['status']=='approved')
            {

                if( isset($data['exclude'])){
                    if(in_array(date('Y-m-d',strtotime($this->date)), $data['exclude']) )
                        return false;
                }

                return array("leave_type"=> $data['leave_type'],
                             "leave_type_name"=> Leave_type::find($data['leave_type'])->leave_type_name,
                             "is_paid"=> in_array($data['leave_type'],explode(',',EmploymentStatus::find($this->employee_details->employee_status)->paid_leave_types)),
                             "days"=>($data['days'] == 0.5? 0.5: 1),
                             "mode"=> $data['mode'],
                             "is_maternity"=> ($data['leave_type']==2 || $data['leave_type']==10)
                );
            }
        }
        return false;
    }
    
    public function getHoliday(){
        $holiday = Holiday::leftJoin('holiday_types', 'holidays.holiday_type_id', '=', 'holiday_types.id')
                            ->where('holiday_date','LIKE', $this->date.'%')
                            ->where('is_yearly', '0')->get()->first();
        if(empty($holiday)){
             $holiday = Holiday::leftJoin('holiday_types', 'holidays.holiday_type_id', '=', 'holiday_types.id')
                            ->where('holiday_date', 'LIKE', '%'.date('-m-d',strtotime($this->date)).'%')
                            ->where('is_yearly', '1')->get()->first();
        }

        if(!empty($holiday)){
            if(in_array($this->getBranch()['branch_id'],json_decode($holiday['branch_covered'],true)) OR
                in_array(0,json_decode($holiday['branch_covered'])))
            return array("name"=> $holiday['holiday_name'],
                         "holiday_type"=>$holiday['holiday_type_id'],
                         "is_paid"=> (in_array($holiday['holiday_type_id'],explode(',',EmploymentStatus::find($this->employee_details->employee_status)->paid_holidays_types)) ?1:0),
                         "rates"=> json_decode($holiday['holiday_type_data'],true),
                         "is_restday"=> ($this->hasSchedule()?0:1),
                         "days"=> 1
                         
                         ) ;
        }
        
        return false;
    }

    public function getEmergency(){
        $emergencies = Emergency::where('date_start', '<=',$this->date)
                                ->where('date_end', '>=',$this->date)
                                ->get()->all();

        foreach($emergencies as $value){
            $branch = $this->getBranch();

            if($branch !== false)
                if(in_array($branch->branch_id,json_decode($value['branch_covered'],true))){
                    if(!in_array($this->employee_id,json_decode($value['exempted_employees'])))
                        return $value;
                }
        }
        return false;
    }
    
    public function withinTime($start, $end){
        $start_stamp = strtotime($this->date.' '. $start);
        $end_stamp = strtotime($this->date.' '. $end);
        
        $end_sched_stamp = strtotime($this->date.' '. $this->getSchedule('OUT'));
        if($end_sched_stamp>$start_stamp){
            return true;
        }
        
        return false;
    }
 
    public function getRegularOT($type = ''){ //scheduled date with overtime on top
        $ot = $this->getOvertime();

        if($this->hasSchedule()){
            if($type == ''){
                $m = $this->countOTMinutes($ot[0]['attendance_stamp'],$ot[1]['attendance_stamp']);

                if(isset($ot[2])){
                    $m += $this->countOTMinutes($ot[2]['attendance_stamp'],$ot[3]['attendance_stamp']);
                }

                if($m < 0){
                    return 0;
                }
                return $m;
            }
            elseif($type == 'nightdiff'){
                return $this->determineNightDiff($ot);
            }
            
        }
        return 0;
    }
    
    public function getRestdayOT($type = ''){
        $ot = $this->getOvertime();
        if(!$this->hasSchedule()){
            $mins = $this->countOTMinutes($ot[0]['attendance_stamp'],$ot[1]['attendance_stamp']);

            if($type=='beyond'){
                if($mins>480){
                    return $mins - 480;
                }
            }
            elseif($type == 'nightdiff'){
                return $this->determineNightDiff($ot);
            }
            else{
                if($mins>480)
                    return 480;

                return $mins;
            }
        }
        return 0;   
    }
    
    function countOTMinutes($in, $out){
        if(strtotime($out)>strtotime($in)){
            return (strtotime($out) - strtotime($in) ) / 60;
        }
        else{
            return (strtotime($in) - strtotime($out) ) / 60;
        }
    }
    
    function determineNightDiff($data){
        $end = strtotime($data[1]['attendance_stamp']);
        if($end > strtotime($this->date . ' 22:00:00') ){
            $diff = $end- strtotime($this->date . ' 22:00');
            
            return $diff/60;
        }
        
        return 0;
    }


    function countHits(){
        $get = json_decode(Config::find(10)->value,true)[0];
        $begins = date('Y-m-') . $get;

        if(strtotime($begins)>strtotime($this->date)){
            $begins = date('Y-m-d', strtotime(date('Y-m-') . $get . " -1 month"));
        }
        
        $counter = 0;
        $start = strtotime($begins);
        while ($start<strtotime($this->date)) {
            $new = new Attendance_Class($this->employee_id, date('Y-m-d',$start));
            if($new->getLate(2)===true){
                $counter++;
            }
            $start += 86400;
        }

        return $counter;
    }


    function toStrictDeduction($mins){
        if($mins > 90){
            return 240;
        }
        elseif($mins > 60){
            return 90;
        }
        elseif($mins > 30){
            return 60;
        }
        elseif($mins > 15){
            return 30;
        }

        return 0;
    }

    function toHours($mins){
        if($mins>59){
            return ($mins/60) . " hr/s.";
        }
        else{
            return $mins ." m.";
        }
    }
    
}