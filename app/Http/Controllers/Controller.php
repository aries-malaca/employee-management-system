<?php
namespace ExactivEM\Http\Controllers;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use ExactivEM\User;
use ExactivEM\Menu;
use ExactivEM\Config;
use ExactivEM\Position;
use ExactivEM\User_log;
use ExactivEM\Company;
use ExactivEM\UserLevel;
use ExactivEM\Message;
use ExactivEM\Attendance;
use ExactivEM\EmployeeRequest;
use ExactivEM\Salary_history;
use ExactivEM\News;
use ExactivEM\Branch;
use ExactivEM\Leave_type;
use ExactivEM\ScheduleHistory;
use ExactivEM\Notification;
use ExactivEM\CustomLeave;
use ExactivEM\ScheduleType;
use DB;
use Cache;
use ExactivEM\Libraries\Attendance_Class;
use ExactivEM\Libraries\Mailer_Class;

class Controller extends BaseController{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    const MAX_DIFFERENCE_SECONDS = 18000;

    function __construct(){
        if(null !== Auth::user() ){
            //initialize application objects
            $this->initMenu();
            $this->initConfig();
            $this->initColors();
            //helper
            $this->data['submit_once'] = "onclick=this.setAttribute('style','display:none')";
            $this->data['calendar_attendance'] = '';
            $this->data['calendar_schedule'] ='';
        }
    }

    function myDownLines(){
        $user = Auth::user();
        $data = $this->getDownLines($user);

        return $data;
    }

    function getDownLines($user){
        if (Cache::has('my_employees' . $user->id)) {
            return Cache::get('my_employees' . $user->id);
        }

        $positions = array();
        $get = Position::get()->all();

        if($user->level == $this->data['config']['admin_level_id'] OR $user->level == $this->data['config']['hr_level_id'])
            return User::get()->pluck('id')->toArray();
        
        foreach($get as $key => $value){
            $data = json_decode($value['position_data'],true);
            if(isset($data['reporting_lines'])){
                foreach($data['reporting_lines'] as $k => $v){
                    if($user->position_id == (int)$v['position_id'])
                        $positions[] = $value['id'];
                }
            }

            if(isset($data['audience_data'])){
                foreach($data['audience_data'] as $k => $v){
                    if($user->position_id == (int)$v['position_id'])
                        $positions[] = $value['id'];
                }
            }
        }

        $new = array();
        $data = User::leftJoin('positions','users.position_id','=','positions.id')
                        ->whereIn('position_id',$positions)
                        ->select('users.*','position_data')
                        ->get()->toArray();

        $branches = Branch::where('branch_head_employee_id', $user->id)->get()->pluck('id')->toArray();
        if(sizeof($branches) < 1)
            if(sizeof(Branch::where('sas_id', $user->id)->get()->pluck('id')->toArray())==0 && $this->data['config']['area_supervisor_level'] == $user->level)
                return array();
            else
                $branches = Branch::get()->pluck('id')->toArray();

        $my_branch = $this->getCurrentBranch($user->id,date('Y-m-d'));
        $my_position = Position::find($user->position_id);
        $my_position_data = json_decode($my_position->position_data,true);

        foreach($data as $key => $value){
            $branch = $this->getCurrentBranch($value['id'],date('Y-m-d'));
            if($my_position_data['branch_aware'] == "true" ){
                //get current branch
                if($this->isMyDirectDownPosition($value['id']))
                    $new[] = $value['id'];
                else{

                    if($this->data['config']['area_supervisor_level'] == $user->level) {
                        if($branch !== false && !empty($branches)){
                            if(in_array($branch->branch_id, $branches))
                                $new[] = $value['id'];
                        }
                        if($this->isFreeAgent($value['id']))
                            $new[] = $value['id'];
                    }
                    elseif($this->data['config']['branch_supervisor_level'] == $user->level) {
                        if ($branch !== false && $my_branch !== false){
                            if ($branch->branch_id == $my_branch->branch_id OR $branch->bs_id == $user->id)
                                $new[] = $value['id'];
                        }
                    }
                }

            }
            else
                $new[] = $value['id'];
        }

        Cache::put('my_employees' .$user->id, $new, 20);

        return $new;
    }

    function isFreeAgent($id){
        $schedules = ScheduleHistory::where('employee_id', $id)
                                        ->where('schedule_type','RANGE')
                                        ->where('schedule_start', '<=', date('Y-m-d'))
                                        ->where('schedule_end','>=', date('Y-m-d'))
                                        ->get()->toArray();
        foreach($schedules as $key=>$value){
            if(time() >= strtotime($value['schedule_start']) AND time()<= strtotime($value['schedule_end']))
                return false;
        }
        return true;
    }

    function getAvailableSchedules($branch_id, $day_index){
        $scheds = array();
        $types = ScheduleType::where('branch_id', $branch_id)
                                ->get()->toArray();

        foreach($types as $key=>$value){
            $data = explode(",", $value['schedule_data']);
            $scheds[] = $data[$day_index];
        }

        return $scheds;
    }

    function isMyDirectDownPosition($employee_id){

        $positions = Position::where("position_data",'LIKE','%'. '"supervisor_id":"'.Auth::user()->id.'"' .'%')->get()->toArray();

        foreach($positions as $key=>$value){
            $data = json_decode($value['position_data'],true);
            foreach($data['reporting_lines'] as $k=>$val){
                if(isset($val['ruling'])){
                    foreach($val['ruling'] as $rule){
                        if($rule['employee_id'] == $employee_id && $rule['supervisor_id'] == Auth::user()->id){
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    //color selector for calendar use
    function initColors(){
        $this->data['color']['timein'] = '#4CAF50';
        $this->data['color']['timeout'] = '#F44336';
        $this->data['color']['overtime'] = '#9C27B0';
        $this->data['color']['adjustment'] = '#9E9E9E';
        $this->data['color']['travel'] = '#EC5E8D';
        $this->data['color']['leave'] = '#DA9606';
        $this->data['color']['holiday'] = '#00B0FF';
        $this->data['color']['offset'] = '#002EFF';
        $this->data['color']['emergency'] = '#550000';
        $this->data['color']['emergency'] = '#550000';
        $this->data['color']['unofficial'] = '#004d4d';
    }

    //initialize app sidebar menu data
    function initMenu(){
        $menu_main = Menu::where('menu_active', 1)->where('parent_id', 0)
            ->where('levels','like', '%'.Auth::user()->level.'%')
            ->orderBy('menu_order', 'asc')
            ->get();
        $final_array = array();
        $current_page = array();

        //loop to get the submenus
        foreach($menu_main as $key => $menu){

            //check if has submenu
            $sub_menu = Menu::where('menu_active', 1)->where('parent_id', $menu['id'])
                ->where('levels','like', '%'.Auth::user()->level.'%')
                ->orderBy('menu_title', 'asc')
                ->get();

            $final_array[] = array("title" => $menu['menu_title'], "url" => $menu['menu_url'],
                "icon" => $menu['menu_icon']);

            if(sizeof($sub_menu) > 0){
                $final_array[$key]['has_sub'] = 1;

                foreach($sub_menu as $k => $sub){
                    //add the submenu on each parent menu
                    $final_array[$key]['subs'][] = array("title" => $sub['menu_title'], "url" => $sub['menu_url'],
                        "icon" => $sub['menu_icon']);

                    if($sub['menu_url'] == Request::segment(1))
                        $current_page = array("url" => $sub['menu_url'],
                            "title" => $sub['menu_title'],
                            "description" => $sub['menu_description']);
                }
            }
            else{
                $final_array[$key]['has_sub'] = 0;

                if($menu['menu_url'] == Request::segment(1))
                    $current_page = array("url" => $menu['menu_url'],
                        "title" => $menu['menu_title'],
                        "description" => $menu['menu_description']);
            }
        }
        $this->data['menus'] = $final_array;

        if(sizeof($current_page)>0)
            $this->data['page'] = $current_page;
    }
    //end of initMenu()

    //list all news
    function listNews(){
        $this->data['news'] = News::leftJoin('users','news.posted_by_id','=','users.id')
            ->select('news.*', 'users.id as user_id', 'users.name','users.picture', 'news.id as news_id')
            ->orderBy('news.created_at','DESC')
            ->orderBy('priority','DESC')->get()->all();

    }

    //get myActiveEmployees
    function myActiveEmployees(){
        $data = User::leftJoin('departments', 'users.department_id', '=', 'departments.id')
            ->leftJoin('positions', 'users.position_id', '=', 'positions.id')
            ->leftJoin('companies', 'users.company_id', '=', 'companies.id')
            ->leftJoin('employment_statuses', 'users.employee_status', '=', 'employment_statuses.id')
            ->select('users.id as user_id', 'users.*', 'departments.*', 'positions.*' , 'companies.*', 'employment_status_name')
            ->where('active_status', 1)
            ->whereIn('users.id', $this->myDownLines())
            ->orderBy('name')->get()->toArray();
        return $data;
    }



    function getLeaveDays($data, $employee_id, $flag ='scheduled'){
        $days = 0;
        if($data['date_start'] == $data['date_end']){
            if($data['mode'] == 'FULL')
                return 1;
            else
                return 0.5;
        }

        $start = strtotime($data['date_start']);
        while($start<= strtotime($data['date_end'])){
            if($flag == 'scheduled'){
                $att = new Attendance_Class($employee_id, date('Y-m-d',$start));
                if($att->hasSchedule() !== false)
                    $days++;
            }
            else
                $days++;

            $start+=86400;
        }
        return $days;
    }

    //get remaining leaves 
    function getLeaveUsage($leave_type_id, $employee_id){
        $position_id = User::find($employee_id)->position_id;
        $position_data = json_decode(Position::find($position_id)->position_data,true);
        $max_leave = Leave_type::find($leave_type_id)->leave_type_max;
        $used_leave = 0;

        if($position_data['leave_data'] !== null){
            foreach($position_data['leave_data'] as $key=>$value){
                if($value['leave_id'] == $leave_type_id)
                    $max_leave = $value['leave_count'];
            }
        }

        $u = CustomLeave::where('leave_type_id', $leave_type_id)
                        ->where('employee_id', $employee_id)
                        ->where('max_leave', '<>', 0)
                        ->where('year', date('Y'))
                        ->get()->toArray();

        foreach($u as $key=>$value){
            $max_leave = $value['max_leave'];
        }

        //get this year's leaves
        $year_leaves = EmployeeRequest::where('request_data', 'like', '%'.date('Y').'%')
                                        ->where('employee_id', $employee_id)
                                        ->where('request_type', 'leave')
                                        ->get()->all();
        foreach($year_leaves  as $key=> $data){
            //check types
            $parsed = json_decode($data['request_data'],true);
            if( $parsed['leave_type'] == $leave_type_id && ($parsed['status']== 'approved' OR $parsed['status'] == 'pending')){
                $max_leave -= $parsed['days'];
                $used_leave += $parsed['days'];
            }
        }

        return ["credits"=>$max_leave, "used"=>$used_leave];
    }

    public function initConfig(){
        $this->attendance = new Attendance_Class(Auth::user()->id, date('Y-m-d'));
        //application config variables
        $this->data['config']['my_id'] = Auth::user()->id;

        //config getter
        $config = Config::get()->all();
        foreach ($config as $key => $value) {
            switch ($value['id']) {
                case '1':
                    $this->data['config']['app_name'] = $value['value'];
                    break;
                case '2':
                    $this->data['config']['app_description'] = $value['value'];
                    break;
                case '3':
                    $this->data['config']['copyright'] = $value['value'];
                    break;
                case '4':
                    $this->data['config']['logo'] = $value['value'];
                    break;
                case '5':
                    $this->data['config']['app_restriction'] = $value['value'];
                    break;
                case '6':
                    $this->data['config']['hr_level_id'] = $value['value'];
                    break;
                case '7':
                    $this->data['config']['show_timein_button'] = $value['value'];
                    break;
                case '8':
                    $this->data['config']['admin_level_id'] = $value['value'];
                    break;
                case '9':
                    $this->data['config']['sub_admin_level_id'] = $value['value'];
                    break;
                case '10':
                    $this->data['config']['first_cutoff'] = json_decode($value['value']);
                    break;
                case '11':
                    $this->data['config']['second_cutoff'] = json_decode($value['value']);
                    break;
                case '14':
                    $this->data['config']['employee_level_id'] = $value['value'];
                    break;
                case '21':
                    $this->data['config']['no_timeout_policy'] = $value['value'];
                    break;
                case '22':
                    $this->data['config']['salary_visible_to'] = json_decode($value['value'],true);
                    break;
                case '23':
                    $this->data['config']['application_url'] = $value['value'];
                    break;
                case '24':
                    $this->data['config']['enable_chat'] = $value['value'];
                    break;
                case '25':
                    $this->data['config']['deduction_conversion'] = $value['value'];
                    break;
                case '26':
                    $this->data['config']['send_notifications'] = $value['value'];
                    break;
                case '27':
                    $this->data['config']['email_client_defaults'] = json_decode($value['value'],true);
                    break;
                case '28':
                    $this->data['config']['minimum_wage'] = $value['value'];
                    break;
                case '29':
                    $this->data['config']['minimum_monthly_wage'] = $value['value'];
                    break;
                case '101':
                    $this->data['config']['area_supervisor_level'] = $value['value'];
                    break;
                case '102':
                    $this->data['config']['branch_supervisor_level'] = $value['value'];
                    break;
                case '103':
                    $this->data['config']['head_position_id'] = $value['value'];
                    break;
            }
        }

        //allow add employee
        $this->data['config']['allow_add_employee'] = in_array(Auth::user()->level, array($this->data['config']['sub_admin_level_id'],$this->data['config']['admin_level_id'],$this->data['config']['hr_level_id']));

        //end config getter
        if(in_array(31, $this->data['config']['second_cutoff']))
            $this->data['config']['second_cutoff'][1] = date('t');

        //determine the active cutoff
        if(date('j') >= $this->data['config']['first_cutoff'][0] AND date('j') <= $this->data['config']['first_cutoff'][1])
            $this->data['config']['active_cutoff'] = $this->data['config']['first_cutoff'];
        else
            $this->data['config']['active_cutoff'] = $this->data['config']['second_cutoff'];

        //end application config variables
        $time_in = $this->attendance->getLogs('IN');
        $time_out = $this->attendance->getLogs('OUT');

        $this->data['config']['is_timedin'] = false;
        $this->data['config']['is_completed_time'] = false;

        //check if timed in
        if($time_in !== false)
            $this->data['config']['is_timedin'] = true;

        //check if completed time in
        if($time_out !== false && $time_in !== false)
            $this->data['config']['is_completed_time'] = true;

        //update online
        if(time()%3 == 0){
            $user_now = User::find(Auth::user()->id);
            $user_now->last_activity = date('Y-m-d H:i:s');
            $user_now->last_ip = json_encode(array($_SERVER['REMOTE_ADDR'],time()));
            $user_now->save();
        }
        //end update online
    }

    //function used to restrict unauthorized users
    //from accessing pages
    public function checkRestriction(){
        if(Request::method() != 'GET')
            return;

        if(null !== Request::segment(1))
            $url = Request::segment(1);

        if(null !== Request::segment(2)){
            if( !is_numeric(Request::segment(2)) )
                $url = $url .'/'. Request::segment(2);
        }

        //select the path
        $select = Menu::where('menu_url', $url)
            ->where('levels', 'like', '%'. Auth::user()->level . '%')->get()->all();

        return (sizeof($select) > 0 ? true:false);
    }

    //check if able to view salaries
    public function allowSalaryView(){
        return in_array(Auth::user()->level, $this->data['config']['salary_visible_to']);
    }


    public function isAllowScheduleEdit(){
        if(Auth::user()->level != $this->data['config']['employee_level_id'] 
                    AND Auth::user()->level !=  $this->data['config']['branch_supervisor_level'] ){
            return true;
        }

        if(in_array(Auth::user()->id, [362]))
            return true;

        return false;
    }

    //check if able to view salaries
    public function allowDeleteAttendance(){
        if(Auth::user()->level ==  $this->data['config']['admin_level_id'] OR Auth::user()->level ==  $this->data['config']['sub_admin_level_id']
            OR Auth::user()->level ==  $this->data['config']['hr_level_id'])
            return true;

        return false;
    }

    //log writter function
    public function writeLog($category, $data, $user_id = 0){
        $log = new User_log;
        $log->log_by_id = ($user_id == 0 ? Auth::user()->id : $user_id);
        $log->log_category = $category;
        $log->log_details = $data . ' (' . $_SERVER['REMOTE_ADDR'] .')';
        $log->save();
    }

    //function for uploading files
    public function uploader($request, $id, $destination){
        //valid extensions
        $valid_ext = array('jpeg', 'gif', 'png', 'jpg');
        //check if the file is submitted
        if($request->hasFile('file')){
            $file = $request->file('file');

            switch($destination){
                //employees
                case 'employees':
                    if($user = User::find($request->input('id')) ) {
                        $ext = $file->getClientOriginalExtension();
                        //check if extension is valid

                        if(in_array($ext, $valid_ext)){
                            $file->move('images/employees', $request->input('id').'_'.$file->getClientOriginalName());
                            $user->picture = $request->input('id').'_'.$file->getClientOriginalName();
                            $user->save();
                            return true;
                        }
                    }
                    break;
                //company 
                case 'companies':
                    if($user = Company::find($request->input('id')) ) {
                        $ext = $file->getClientOriginalExtension();
                        //check if extension is valid
                        if(in_array($ext, $valid_ext)){
                            $file->move('images/companies', $file->getClientOriginalName());
                            $user->company_logo = $file->getClientOriginalName();
                            $user->save();
                            return true;
                        }
                    }
                    break;
                case 'attendance':
                    $ext = $file->getClientOriginalExtension();
                    //check if extension is valid
                    if($ext == 'txt'){
                        $file->move('biometrics',date('Y-m-d').".txt");
                        return true;
                    }
                    break;
                case 'employee_list':
                    $ext = $file->getClientOriginalExtension();
                    //check if extension is valid
                    if($ext == 'csv'){
                        $file->move('csv',"imported_list.csv");
                        return true;
                    }
                    break;
            }
        }
        return false;
    }

    //function for getting current salary of the employee
    function getCurrentSalary($id){
        $salary = Salary_history::where('employee_id', $id)->orderBy('start_date','DESC')
            ->leftJoin('users', 'salary_histories.updated_by_employee_id', '=', 'users.id')
            ->leftJoin('salary_bases','salary_histories.salary_amount','=','salary_bases.salary_amount')
            ->select('salary_histories.salary_amount as amount')
            ->get()->all();
        if(!empty($salary))
            return $salary[0]['amount'];

        return "0";
    }

    //concatenate names
    function getEmployeeName($id){
        $employee = User::find($id);
        return $employee->first_name .' '. $employee->last_name ;
    }

    //add attendance to the database
    function addAttendance($data){
        $user = User::find($data['employee_id']);
        if(strtotime($user->hired_date) > strtotime($data['date_credited']))
            return false;

        //eliminate the redundant data for the exact same input
        if(!empty(Attendance::where('date_credited',$data['date_credited'])
                ->where('employee_id', $data['employee_id'])
                ->where('attendance_stamp',$data['attendance_stamp'] )
                ->where('stamp_type',$data['stamp_type'])
                ->where('via', $data['via'])
                ->get()->all()) AND date('G', strtotime($data['attendance_stamp'])) >3){
            return false;
        }

        //eliminate the posibility of overlate
        //note: this allows the early out
        $diff = strtotime($data['attendance_stamp']) - strtotime($data['scheduled_stamp']);
        if(abs($diff) > self::MAX_DIFFERENCE_SECONDS AND $data['in_out'] == 'IN' AND $data['stamp_type']=='REGULAR' AND date('G', strtotime($data['attendance_stamp'])) >3){
            return false; //
        }

        //this will ignore the employee log without working 1 hour below.
        if($data['via'] == 'BIO' AND $data['in_out']=='OUT'){
            //get the in
            $in = Attendance::where('date_credited','like', $data['date_credited'].'%')
                ->where('employee_id', $data['employee_id'])
                ->get()->first();

            $in_diff = strtotime($data['attendance_stamp']) - strtotime($in['attendance_stamp']);

            ///check the difference of logs.. must be atleast 1 hour
            if( $in_diff < (60*60) )
                return false;

            //redundancy check, if the user is already been timed out then return false...
            if(Attendance::where('date_credited','like', $data['date_credited'].'%')
                    ->where('employee_id', $data['employee_id'])
                    ->where('in_out','OUT')
                    ->whereIn('stamp_type', array('REGULAR','ADJUSTMENT'))
                    ->count() > 0 ){
                //return false;

                $iin = Attendance::where('date_credited','like', $data['date_credited'].'%')
                    ->where('employee_id', $data['employee_id'])
                    ->where('in_out','OUT')
                    ->whereIn('stamp_type', array('REGULAR','ADJUSTMENT'))
                    ->get()->first();

                if(strtotime($iin['attendance_stamp']) < strtotime($data['attendance_stamp'])){
                    Attendance::destroy($iin['id']);

                    $attendance = new Attendance;
                    $attendance->date_credited = $data['date_credited'];
                    $attendance->employee_id = $data['employee_id'];
                    $attendance->attendance_stamp = $data['attendance_stamp'];
                    $attendance->stamp_type = $data['stamp_type'];
                    $attendance->via = $data['via'];
                    $attendance->in_out ='OUT';
                    $attendance->scheduled_stamp = $data['scheduled_stamp'];
                    if(isset($data['more_info']))
                        $attendance->more_info = json_encode($data['more_info']);
                    else
                        $attendance->more_info = json_encode(array());
                    //save the attendance
                    $attendance->save();
                }
                return false;
            }
        }

        //prevents inserting regular if there is an adjustment or regular log already
        if($data['via'] == 'BIO' AND $data['in_out']=='IN'){
            if(Attendance::where('date_credited','like', $data['date_credited'].'%')
                    ->where('employee_id', $data['employee_id'])
                    ->where('in_out','IN')
                    ->whereIn('stamp_type', array('REGULAR','ADJUSTMENT'))
                    ->count() > 0 ){
                return false;
            }
        }

        //check if the time is graveyard then it will be determined as OUT
        if(date('G', strtotime($data['attendance_stamp'])) < 4 AND $data['in_out'] == 'IN'){
            $graveyard = new Attendance_Class($data['employee_id'], date('Y-m-d', strtotime($data['date_credited']. " -1 day")) );
            if($graveyard->getLogs('OUT') === false AND $graveyard->hasSchedule()){
                $data['date_credited'] = $graveyard->date;
                $data['scheduled_stamp'] = $graveyard->date." ".$graveyard->getSchedule('OUT');
                $data['in_out'] = 'OUT';
            }
            else{
                return false;
            }
        }

        $attendance = new Attendance;
        $attendance->date_credited = $data['date_credited'];
        $attendance->employee_id = $data['employee_id'];
        $attendance->attendance_stamp = $data['attendance_stamp'];
        $attendance->stamp_type = $data['stamp_type'];
        $attendance->via = $data['via'];
        $attendance->in_out = $data['in_out'];
        $attendance->scheduled_stamp = $data['scheduled_stamp'];
        if(isset($data['more_info']))
            $attendance->more_info = json_encode($data['more_info']);
        else
            $attendance->more_info = json_encode(array());
        //save the attendance
        $attendance->save();
        //return true if successfully saved the data
        return true;
    }

    function getBranchEmployees($branch_id, $date, $order='name'){
        $get = ScheduleHistory::leftJoin('users','schedule_histories.employee_id','=','users.id')
            ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
            ->leftJoin('positions', 'users.position_id', '=', 'positions.id')
            ->where('branch_id',$branch_id)
            ->where('schedule_start', '<=', $date)
            ->where('schedule_end', '>=', $date)
            ->where('schedule_type', 'RANGE')
            ->where('active_status',1)
            ->select('schedule_histories.employee_id','users.*','name','employee_no', 'department_name','position_name')
            ->groupBy('employee_id')
            ->orderBy($order)
            ->get()->toArray();

        return $get;
    }

    function isPositionVacant($up_line_position_id, $request_employee_id, $position_data){
        $request_position_data = $position_data;
        $request_branch = $this->getCurrentBranch($request_employee_id,date('Y-m-d'));

        $users = User::where('position_id', $up_line_position_id)
                        ->where('active_status',1)
                        ->get()->all();

        if($request_position_data['branch_aware']=='true'){
            foreach($users as $key=>$value) {
                $user_branch = $this->getCurrentBranch($value['id'],date('Y-m-d'));



                if($value['position_id'] == 18 AND $request_branch !== false){
                    $branches = Branch::where('branch_head_employee_id', $value['id'])->get()->pluck('id')->toArray();
                    if(in_array($request_branch['branch_id'], $branches))
                        return false;
                }

                if(in_array($value['position_id'], [50,68]) AND $user_branch !== false AND $request_branch !== false){
                    if($request_branch['branch_id'] == $user_branch['branch_id']) {
                        if ($user_branch) {
                            if ($user_branch['bs_id'] === 5000)
                                return true;
                        }
                        return false;
                    }
                }


                if(!in_array($value['position_id'], [50,68]) AND $value['position_id'] != 18){
                    return false;
                }
            }
        }
        else{
            if(sizeof($users)>0){
                return false;
            }
        }

        return true;
    }

    function getEmployeeInfo($employee_id){
        return User::leftJoin('departments','users.department_id','=','departments.id')
            ->leftJoin('positions','users.position_id','=','positions.id')
            ->leftJoin('tax_exemptions','users.tax_exemption_id','=','tax_exemptions.id')
            ->leftJoin('employment_statuses','users.employee_status','=','employment_statuses.id')
            ->select('users.*','department_name','position_name','tax_exemption_shortname','employment_status_name')
            ->where('users.id',$employee_id)->get()->first()->toArray();
    }

    function getCurrentBranch($employee_id, $date){

        $data = ScheduleHistory::leftJoin('branches','schedule_histories.branch_id','=','branches.id')
            ->where('schedule_start', '<=', $date)
            ->where('schedule_end','>=', $date)
            ->where('employee_id', $employee_id)
            ->orderBy('schedule_type','DESC')
            ->select('schedule_histories.branch_id','branch_name','branch_email','bs_id','branch_head_employee_id', 'sas_id')
            ->get()->first();

        if(sizeof($data)>0){
            return $data;
        }

        $attendance = Attendance::where('employee_id', $employee_id)
            ->where('date_credited', 'LIKE', $date.'%')
            ->get()->first();

        if(isset($attendance['id'])){
            $data = json_decode($attendance['more_info'],true);
            if(isset($data['branch_id'])){
                $branch = Branch::where('id',$data['branch_id'])->select('id as branch_id','branch_name')->get()->first();
                if(isset($branch['branch_id']))
                    return $branch;
            }
        }

        return false;
    }

    //check existence of the log
    function isRequestExists($type, $data, $employee_id){
        // adjustment or overtime
        $check = 0;
        if($type=='adjustment'){
            $check = EmployeeRequest::where('request_data', 'like', '%'.'"date":"'.date('Y-m-d',strtotime($data['date'])).'"'.'%')
                ->where('request_data', 'like', '%'.'"mode":"'.$data['mode'].'"'.'%')
                ->where('request_data', 'like', '%'.'"status":"pending"'.'%')
                ->where('employee_id', $employee_id)
                ->where('request_type',$type)
                ->get();
        }
        elseif($type=='overtime' OR $type=='travel' OR $type=='offset'){
            $check = EmployeeRequest::where('request_data', 'like', '%'.'"date_start":"'.date('Y-m-d',strtotime($data['date_start'])).'"'.'%')
                ->where('request_type',$type)
                ->where('employee_id', $employee_id)
                ->get();
        }


        if(!empty($check)){
            foreach($check as $key=>$value){
                $data = json_decode($value['request_data']);
                if($data->status != 'denied'){
                    return true;
                }
            }
        }


        return false;
    }

    function generateNewPassword($length = 9, $add_dashes = false, $available_sets = 'luds'){
        $sets = array();
        if(strpos($available_sets, 'l') !== false)
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        if(strpos($available_sets, 'u') !== false)
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        if(strpos($available_sets, 'd') !== false)
            $sets[] = '23456789';
        if(strpos($available_sets, 's') !== false)
            $sets[] = '!@#$%&*?';
        $all = '';
        $password = '';
        foreach($sets as $set)
        {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }
        $all = str_split($all);
        for($i = 0; $i < $length - count($sets); $i++)
            $password .= $all[array_rand($all)];
        $password = str_shuffle($password);
        if(!$add_dashes)
            return $password;
        $dash_len = floor(sqrt($length));
        $dash_str = '';
        while(strlen($password) > $dash_len)
        {
            $dash_str .= substr($password, 0, $dash_len) . '-';
            $password = substr($password, $dash_len);
        }
        $dash_str .= $password;
        return $dash_str;
    }

    function getActionData($position_id, $requesting_id){
        $position_data = json_decode(Position::find($position_id)->position_data,true);
        $generatedArray = array();

        if($position_data['reporting_lines'] !== null){
            foreach($position_data['reporting_lines'] as $k=>$v){
                $generatedArray[] = array("status"=>
                    ($this->isPositionVacant($v['position_id'], $requesting_id, $position_data)?'Vacant':'Pending') ,
                    "position_id"=>$v['position_id']);
            }
        }
        return $generatedArray;
    }

    function notifyApprovingOfficer($approval, $data, $auth=null){
        if($auth === null)
            $auth = Auth::user()->id;

        $request_data = json_decode($data->request_data);
        if($data->request_type == 'leave')
            $request_data->leave_type_name = Leave_type::find($request_data->leave_type)->leave_type_name;

        if($data->request_type == 'schedule')
            $request_data->branch_name = Branch::find($request_data->branch_id)->branch_name;

        $position_data = json_decode(Position::find(User::find($data->employee_id)->position_id)->position_data,true);

        foreach($approval as $key=>$value){
            if($value['status'] == 'Pending'){
                $users = User::where('position_id', $value['position_id'])->where('active_status',1)->get()->toArray();
                if(isset($position_data['reporting_lines'][$key]['selection'])){
                    if($position_data['reporting_lines'][$key]['selection'] != 'position') {
                        foreach($position_data['reporting_lines'][$key]['ruling'] as $r=>$rr){
                            if($data->employee_id == $position_data['reporting_lines'][$key]['ruling'][$r]['employee_id'])
                                $users = User::where('id', $position_data['reporting_lines'][$key]['ruling'][$r]['supervisor_id'])->get()->toArray();
                        }
                    }
                }

                if($value['position_id'] == 73){
                    $branch = $this->getCurrentBranch($data->employee_id, date('Y-m-d'));
                    if($branch !== false){
                        if($branch['branch_head_employee_id'] == 0)
                            return ($branch['sas_id']==$auth->id);
                    }
                }

                foreach ($users as $user){
                    if(in_array($auth, $this->getDownLines(User::find($user['id'])))){

                        $notification = array("name"=>User::find($data->employee_id)->name,
                                              'request_type'=>ucfirst($data->request_type),
                                              'data'=>[$request_data, $approval],
                                              'type'=>'request',
                                              "reference_id"=>$data->id,
                                              "notes"=>$data->request_note);
                        $mailer = new Mailer_Class;
                        $mailer->sendRequestNotification($user, $notification, $this->getCurrentBranch($user['id'], date('Y-m-d')));

                        $notification_h = new Notification;
                        $notification_h->notification_title = 'New Employee Request';
                        $notification_h->notification_body = $notification['name'] .' requested ' . $data->request_type.'.';
                        $notification_h->employee_id = $user['id'];
                        $notification_h->is_read = 0;
                        $notification_h->reference_id = $data->id;
                        $notification_h->notification_type = 'new_request';
                        $notification_h->notification_data = $data->request_data;
                        $notification_h->save();
                    }
                }
                break;
            }
        }
    }

    function finalizeSchedule($employee_id, $start, $end){
        $start = strtotime($start);

        while($start <= strtotime($end)){
            $att = new Attendance_Class($employee_id, date('Y-m-d', $start));

            if(!$att->getSingleSchedule()){

                if($branch = $att->getBranch()){
                    $in_schedule = $att->getSchedule('IN');

                    $sched = new ScheduleHistory;
                    $sched->schedule_start = date('Y-m-d',$start);
                    $sched->schedule_end = date('Y-m-d',$start) .' 23:59:59';
                    $sched->schedule_data = $in_schedule;
                    $sched->schedule_type = 'SINGLE';
                    $sched->branch_id = $branch['branch_id'];
                    $sched->employee_id = $employee_id;
                    $sched->is_read_only = 1;
                    //save the schedule
                    $sched->save();
                }
            }
            else{
                $schedule = $att->getSingleSchedule();
                if(isset($schedule['id'])){
                    $ee = ScheduleHistory::find($schedule['id']);
                    $ee->is_read_only = 1;
                    $ee->save();
                }

            }

            $start += 86400;
        }

    }


    function patchSchedule($date, $time, $employee_id, $branch_id){
        $get = ScheduleHistory::where('schedule_start','like', date('Y-m-d',strtotime($date)).'%')
            ->where('schedule_end', 'like', date('Y-m-d',strtotime($date)) .' 23:59:59' )
            ->where('employee_id', $employee_id)
            ->get()->first();

        if(isset($get['branch_id'])){
            $sched = ScheduleHistory::find($get['id']);
            $sched->schedule_data = $time;
            $sched->branch_id = $branch_id;
            //save the schedule
            $sched->save();
        }
        else{
            $sched = new ScheduleHistory;
            $sched->schedule_start = date('Y-m-d',strtotime($date));
            $sched->schedule_end = date('Y-m-d',strtotime($date)) .' 23:59:59';
            $sched->schedule_data = $time;
            $sched->schedule_type = 'SINGLE';
            $sched->branch_id = $branch_id;
            $sched->employee_id = $employee_id;
            //save the schedule
            $sched->save();
        }
    }

    function findPendingRequests($employee_id){
        $pending = EmployeeRequest::where('employee_id', $employee_id)
            ->where('request_data', 'LIKE', '%"status":"pending"%')
            ->where('request_type', '<>', 'salary_adjustment')
            ->get()->toArray();
        return $pending;
    }
}