<?php
namespace ExactivEM\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use ExactivEM\Http\Requests;
use ExactivEM\User;
use ExactivEM\Salary_history;
use ExactivEM\Attendance;
use ExactivEM\File;
use ExactivEM\Branch;
use ExactivEM\Payslip;
use ExactivEM\Transaction;
use ExactivEM\ScheduleType;
use ExactivEM\ScheduleHistory;
use Validator;
use ExactivEM\Libraries\Attendance_Class;
use ExactivEM\Libraries\Mailer_Class;
use Cache;

class EmployeesController extends Controller{
    public function __construct(){
        $this->middleware('auth');
        parent::__construct();

        //default title and url
        $this->data['page']['parent'] = 'Employees';
        $this->data['page']['parent_url'] = 'employees';
    }

    function index(){
        //check user's permission
        if(!$this->checkRestriction()){
            return view('errors.no_permission', $this->data);
        }

        //why unset? 
        unset($this->data['page']['parent']) ;
        unset($this->data['page']['parent_url']);

        return view('employees', $this->data);
    }

    //function for viewing employee
    function viewEmployee(Request $request){
        $this->data['page']['title'] = 'Employee Profile';
        $this->data['page']['url'] =  'home';

        $this->data['allow_schedule_edit'] = ($this->isAllowScheduleEdit()?1:0);

        //check if the user has permission to view the employee
        if(!in_array($request->segment(2),$this->myDownLines()))
            return view('errors.invalid_employee', $this->data);

        return view('employee_view', $this->data);
    }
    //end of viewEmployee()

    function getTempID(){
        $last_id = User::find(User::max('id'))->employee_no;
        for($x = $last_id; $x<($last_id+100);$x++){
            $get = User::where('employee_no',$x)->get()->first();
            if(empty($get)){
                $data = explode("-", $x);
                return date('Y')."-".$data[1];
            }
        }
    }

    //function to upload picture to server
    public function uploadProfilePicture(Request $request){
        $id = $request->input('id');
        //uploader is located in Controller
        if($this->uploader($request ,$id,  'employees')){
            //write log
            $details = 'Updated Employee Picture';
            $this->writeLog("Employee", $details);

            //success
            return redirect()->back()->with('data', 'success');
        }
        //failed to upload
        return redirect()->back()->with('data', 'failed');
    }

    //function used to upload employee file
    public function uploadFile(Request $request){
        $id = $request->input('id');

        //uploader is located in Controller
        $valid_ext = array('jpeg', 'gif', 'png', 'jpg', 'doc', 'docx', 'pdf', 'xls','xlsx', 'txt');
        $file = $request->file('file');
        $ext = $file->getClientOriginalExtension();

        //check if extension is valid
        if(in_array($ext, $valid_ext)){
            $file->move('documents',time().'_'.$request->input('description').".". $ext );
            //create a new file
            $new_file = new File;
            $new_file->description = $request->input('description');
            $new_file->category = $request->input('category');
            $new_file->employee_id = $request->input('id');
            $new_file->file_name = time().'_'.$request->input('description').".". $ext ;
            $new_file->save();
            $details = 'Updated Employee File '. $request->input('description');
            //write log
            $this->writeLog("Employee", $details);
            //success in uploading
            return redirect()->back()->with('file', 'success');
        }
        //failed to upload
        return redirect()->back()->with('file', 'failed');
    }

    //delete a file from the server
    function deleteFile(Request $request){
        //unlink the file to the server
        if(unlink(public_path('documents/'. File::find($request->segment(3))->file_name ))){
            File::destroy($request->segment(3));
            //success in deletion
            return redirect()->back()->with('deletefile', 'success');
        }
        //failure
        return redirect()->back()->with('deletefile', 'failed');
    }

    //function to delete employee
    function deleteEmployee(Request $request){
        //delete all objects
        User::destroy($request->input('id'));
        \ExactivEM\Attendance::where('employee_id', $request->input('id'))->delete();
        \ExactivEM\EmployeeRequest::where('employee_id', $request->input('id'))->delete();
        \ExactivEM\Evaluation::where('employee_id', $request->input('id'))->delete();
        \ExactivEM\File::where('employee_id', $request->input('id'))->delete();
        \ExactivEM\Message::where('sender_employee_id', $request->input('id'))->delete();
        \ExactivEM\Message::where('receiver_employee_id', $request->input('id'))->delete();
        \ExactivEM\Note::where('created_by_id', $request->input('id'))->delete();
        \ExactivEM\Payslip::where('employee_id', $request->input('id'))->delete();
        \ExactivEM\Salary_history::where('employee_id', $request->input('id'))->delete();
        \ExactivEM\ScheduleHistory::where('employee_id', $request->input('id'))->delete();
        \ExactivEM\Transaction::where('employee_id', $request->input('id'))->delete();
        \ExactivEM\User_log::where('log_by_id', $request->input('id'))->delete();

        //return with success message
        return response()->json(["command"=>"deleteEmployee","result"=>"success"]);
    }

    //function for adding employee
    function addEmployee(Request $request){
        //validate the inputs
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|max:255',
            'middle_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'birth_date' => 'required|date',
            'batch_id' => 'required|not_in:0',
            'company_id' => 'required|not_in:0',
            'batch_id' => 'required|not_in:0',
            'department_id' => 'required|not_in:0',
            'position_id' => 'required|not_in:0',
            'email' => 'required|email|unique:users,email|max:255',
            'employee_no' => 'required|unique:users,employee_no|max:255',
            'hired_date' => 'required|date',
            'bank_number' => 'required|max:255',
            'next_evaluation' => 'required|date',
            'cola_rate' => 'required|numeric',
            'salary_rate' => 'required|numeric',
            'biometric_no' => 'required|numeric|unique:users,biometric_no',
            'level'=>'required',
            'tax_exemption_id'=>'required',
            'employee_status'=>'required',
            'level'=>'required',
            'branch_id'=>'required|numeric',
        ]);

        //if there are an error return to view and display errors  
        if ($validator->fails()) {
            return response()->json(["command"=>"addEmployee", "result"=>"failed", "errors"=> $validator->errors()->all()]);
        }

        //new user object
        $user = new User;
        $user->first_name = $request->input('first_name');
        $user->middle_name = $request->input('middle_name');
        $user->last_name = $request->input('last_name');
        $user->gender = $request->input('gender');
        $user->birth_date = date('Y-m-d',strtotime($request->input('birth_date')));
        $user->civil_status = $request->input('civil_status');
        $user->name = $user->first_name. ' ' . $user->last_name;
        $user->email = $request->input('email');
        $user->mobile = $request->input('mobile');
        $user->telephone = $request->input('telephone');
        $user->address = $request->input('address');
        $user->birth_place = $request->input('birth_place');
        $user->city = $request->input('city');
        $user->state = $request->input('state');
        $user->country = $request->input('country');
        $user->zip_code = $request->input('zip_code');
        $user->about = $request->input('about');
        $user->employee_no = $request->input('employee_no');
        $user->hired_date = date('Y-m-d',strtotime($request->input('hired_date')));
        $user->bank_number = $request->input('bank_number');
        $user->next_evaluation = date('Y-m-d',strtotime($request->input('next_evaluation')));
        $user->active_status = $request->input('active_status');
        $user->employee_status = $request->input('employee_status');
        $user->department_id = $request->input('department_id');
        $user->position_id = $request->input('position_id');
        $user->company_id = $request->input('company_id');
        $user->tax_exemption_id = $request->input('tax_exemption_id');
        $user->batch_id = $request->input('batch_id');
        $user->allow_overtime = $request->input('allow_overtime');
        $user->allow_adjustment = $request->input('allow_adjustment');
        $user->allow_leave = $request->input('allow_leave');
        $user->allow_offset = $request->input('allow_offset');
        $user->allow_travel = $request->input('allow_travel');
        $user->bank_code = $request->input('bank_code');
        $user->local_number = $request->input('local_number');
        $user->skills = $request->input('skills');
        $user->cola_rate = $request->input('cola_rate');
        $user->sss_no = $request->input('sss_no');
        $user->tin_no = $request->input('tin_no');
        $user->philhealth_no = $request->input('philhealth_no');
        $user->pagibig_no = $request->input('pagibig_no');
        $user->hmo_no = $request->input('hmo_no');
        $user->receive_notification = $request->input('receive_notification');
        $user->allow_access = $request->input('allow_access');
        $user->allow_suspension = $request->input('allow_suspension');
        $user->level = $request->input('level');
        $user->biometric_no = $request->input('biometric_no');
        $user->lbo_identifier = 0;
        $user->password = bcrypt($request->input('password'));

        //add user contribution
        $trans = array();
        foreach($request->input('trans') as $tran ) {
            if($tran['checked'] == 'true')
                $trans[] = $tran['id'];
        }

        if(sizeof($trans) == 0)
            $user->contributions = '';
        else
            $user->contributions = implode(",",$trans);

        //add photo
        $user->picture = ($user->gender == 'male'? 'no photo male.jpg':'no photo female.jpg');
        $user->save();

        //new salary for the new employee
        $salary = new Salary_history;
        $salary->employee_id = $user->id;
        $salary->start_date = date('Y-m-1',strtotime($request->input('hired_date')));
        $salary->updated_by_employee_id = Auth::user()->id;
        $salary->salary_amount = $request->input('salary_rate');

        //save to the database
        $salary->save();

        //insert the schedule
        $sched = new ScheduleHistory;
        $sched->employee_id = $user->id;
        $sched->schedule_start = date('Y-m-1',strtotime($request->input('hired_date')));
        $sched->schedule_end = '2018-12-31';//static m must be changed!
        $get_sched_type = ScheduleType::where('branch_id',$request->input('branch_id'))
            ->where('is_default',1)
            ->get()->first();
        if(!empty($get_sched_type)){
            $data = explode(',', $get_sched_type['schedule_data']);
            $final = array();
            //times
            foreach($data as $t){
                if($t == 'closed')
                    $final[] = '00:00';
                else
                    $final[] = date('H:i',strtotime($t));
            }
            $sched->schedule_data = json_encode(array($final[6],$final[0],$final[1],$final[2],$final[3],$final[4],$final[5]));
            $sched->schedule_type = 'RANGE';
            $sched->branch_id = $request->input('branch_id');
            $sched->is_flexi_time = 0;
            //save the schedule
            $sched->save();
        }

        //writelog
        $details = 'Added Employee '. $user->first_name . ' ' . $user->last_name;
        $this->writeLog("Employee", $details);

        //return with success message
        return response()->json(["command"=>"addEmployee", "result"=>"success"]);
    }

    //function to edit employee profile
    function updateProfile(Request $request){
        if($user = User::find($request->input('id')) ) {
            //validate inputs
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|max:255',
                'middle_name' => 'required|max:255',
                'last_name' => 'required|max:255',
                'birth_date' => 'required|date'
            ]);
            //if there are an error return to view and display errors  
            if ($validator->fails()) {
                return response()->json(["command"=>"updateProfile", "result"=>"failed", "errors"=> $validator->errors()->all()]);
            }

            $user->first_name = $request->input('first_name');
            $user->middle_name = $request->input('middle_name');
            $user->last_name = $request->input('last_name');
            $user->gender = $request->input('gender');
            $user->birth_date = date('Y-m-d',strtotime($request->input('birth_date')));
            $user->civil_status = $request->input('civil_status');
            $user->name = $user->first_name .' ' . $user->last_name;
            $user->mobile = $request->input('mobile');
            $user->telephone = $request->input('telephone');
            $user->address = $request->input('address');
            $user->birth_place = $request->input('birth_place');
            $user->city = $request->input('city');
            $user->state = $request->input('state');
            $user->country = $request->input('country');
            $user->zip_code = $request->input('zip_code');
            $user->about = $request->input('about');
            $user->contact_person = $request->input('contact_person');
            $user->contact_info = $request->input('contact_info');
            $user->contact_relationship = $request->input('contact_relationship');
            //commit the changes
            $user->save();

            //writelog
            $details = 'Updated Employee Profile '. $user->first_name . ' ' . $user->last_name;
            $this->writeLog("Employee", $details);

            //bring back the view for success 
            return response()->json(["command"=>"updateProfile", "result"=>"success"]);
        }
        else
        {
            return response()->json(["command"=>"updateProfile", "result"=>"failed", "errors"=> "User mismatch."]);
        }
    }
    //end of processEditProfile()

    function updateWork(Request $request){
        if(!in_array(Auth::user()->level, [$this->data['config']['hr_level_id'], $this->data['config']['admin_level_id']])){
            return response()->json(['result'=>'failed','errors'=>'You are allowed to update User Work info.']);
        }

        $user = User::find($request->input('id'));
        //validation rules
        $validator = Validator::make($request->all(), [
            'employee_no' => 'required|unique:users,employee_no,'. $user->id .'|max:255',
            'hired_date' => 'required|date',
            'next_evaluation' => 'required|date',
            'cola_rate' => 'required|numeric',
            'salary_rate' => 'required|numeric',
            'batch_id' => 'required|not_in:0',
            'company_id' => 'required|not_in:0',
            'batch_id' => 'required|not_in:0',
            'department_id' => 'required|not_in:0',
            'position_id' => 'required|not_in:0'
        ]);
        //end of validation rules
        //if there are an error return to view and display errors
        if ($validator->fails()) {
            return response()->json(["command"=>"updateWork", "result"=>"failed", "errors"=> $validator->errors()->all()]);
        }

        $user->employee_no = $request->input('employee_no');
        $user->hired_date = date('Y-m-d',strtotime($request->input('hired_date')));
        $user->bank_number = $request->input('bank_number');
        $user->next_evaluation = date('Y-m-d',strtotime($request->input('next_evaluation')));
        $user->end_employment_reason = $request->input('end_employment_reason');
        $user->end_employment_date = date('Y-m-d',strtotime($request->input('end_employment_date')));
        $user->regularization_date = date('Y-m-d',strtotime($request->input('regularization_date')));
        $user->active_status = $request->input('active_status');
        $user->employee_status = $request->input('employee_status');
        $user->department_id = $request->input('department_id');
        $user->position_id = $request->input('position_id');
        $user->company_id = $request->input('company_id');
        $user->local_number = $request->input('local_number');
        $user->tax_exemption_id = $request->input('tax_exemption_id');
        $user->batch_id = $request->input('batch_id');
        $user->allow_overtime = $request->input('allow_overtime');
        $user->allow_adjustment = $request->input('allow_adjustment');
        $user->allow_leave = $request->input('allow_leave');
        $user->allow_offset = $request->input('allow_offset');
        $user->allow_travel = $request->input('allow_travel');
        $user->bank_code = $request->input('bank_code');
        $user->skills = $request->input('skills');
        $user->cola_rate = $request->input('cola_rate');
        $user->sss_no = $request->input('sss_no');
        $user->tin_no = $request->input('tin_no');
        $user->philhealth_no = $request->input('philhealth_no');
        $user->pagibig_no = $request->input('pagibig_no');
        $user->hmo_no = $request->input('hmo_no');

        $trans = array();
        foreach($request->input('trans') as $tran ) {
            if($tran['checked'] == 'true')
                $trans[] = $tran['id'];
        }
        if(sizeof($trans) == 0)
            $user->contributions = '';
        else
            $user->contributions = implode(",",$trans);
        //commit the changes
        $user->save();

        //writelog
        $details = 'Updated Employee Work Info '. $user->first_name . ' ' . $user->last_name;
        $this->writeLog("Employee", $details);

        //Salary updating
        if($request->input('salary_rate') != $this->getCurrentSalary($request->input('id')) ) {
            //writelog
            $details = 'Updated Employee Salary ('. $user->first_name . ' ' . $user->last_name .') from '. $this->getCurrentSalary($request->input('id')) . ' to ' . $request->input('salary_rate');
            $this->writeLog("Employee", $details);
            $this->updateSalary($request->input('id'), $request->input('salary_rate'), $this->getCurrentSalary($request->input('id')));
        }
        // updating salary end

        //bring back the view for success
        return response()->json(["command"=>"updateWork", "result"=>"success"]);
    }
    //end function processEditWork

    //function to edit system access
    function updateSystem(Request $request){
        $user = User::find($request->input('id'));
        //validate inputs
        $validator = Validator::make($request->all(), [
            'biometric_no' => 'required|numeric|unique:users,biometric_no,'. $user->id,
            'email' => 'required|email|unique:users,email,'. $user->id .'|max:255'
        ]);

        //if there are an error return to view and display errors
        if ($validator->fails())
            return response()->json(["command"=>"updateSystem", "result"=>"failed", "errors"=> $validator->errors()->all()]);

        if($request->input('password') != '') {
            if(strlen($request->input('password')) <6 )
                return response()->json(["command"=>"updateSystem", "result"=>"failed", "errors"=> "Password must be atleast 6 characters."]);

            $user->password = bcrypt($request->input('password'));
        }

        $user->allow_access = $request->input('allow_access');
        $user->allow_suspension = $request->input('allow_suspension');
        $user->level = $request->input('level');
        $user->email = $request->input('email');
        $user->biometric_no = $request->input('biometric_no');
        $user->trainee_biometric_no = $request->input('trainee_biometric_no');
        $user->receive_notification = $request->input('receive_notification');
        $user->prompt_change_password = 1;
        //commit the changes
        $user->save();

        if($request->input('password') !== '' AND $request->input('password')!== null){
            $mail = new Mailer_Class;
            $mail->passwordChangeByAdmin($user, $request->input('password'));
        }

        $details = 'Updated Employee Access '. $user->first_name . ' ' . $user->last_name;
        $this->writeLog("Employee", $details);
        //bring back the view for success
        return response()->json(["command"=>"updateSystem", "result"=>"success"]);
    }
    //end function processEditSystem

    //update employee salary
    function updateSalary($id, $amount, $old){
        Salary_history::where('employee_id', $id)
            ->where('salary_amount', $old)->orderBy('start_date','DESC')
            ->take(1)->update(['end_date' => date('Y-m-d H:i:s', strtotime(date('Y-m-d')." -1 days"))  ]);

        //check if today...
        $check_today = Salary_history::where("employee_id", $id)
            ->where("start_date","LIKE", date('Y-m-d')."%")
            ->get()->all();
        //delete
        if(!empty($check_today)){
            foreach($check_today as $value)
                Salary_history::destroy($value['id']);
        }
        //new salary object
        $salary = new Salary_history;
        $salary->employee_id = $id;
        $salary->start_date = date('Y-m-d H:i:s');
        $salary->updated_by_employee_id = Auth::user()->id;
        $salary->salary_amount = $amount;

        //save to database
        $salary->save();

    }
    //end function updateSalary


    //update employee salary
    function updateSalaryRow(Request $request){
        $salary = Salary_history::find($request->input('id'));
        $salary->salary_amount = $request->input('amount');
        $salary->start_date = $request->input('start');
        $salary->end_date = $request->input('is_present')=='true'?'0000-00-00':$request->input('end');
        $salary->updated_by_employee_id = Auth::user()->id;
        $salary->save();
        return response()->json(["command"=>"updateSalary", "result"=>"success"]);
    }
    //end function updateSalary

    function getInactiveEmployees(){

        if(!in_array(Auth::user()->level, [$this->data['config']['hr_level_id'], $this->data['config']['admin_level_id']])){
            return response()->json([]);
        }

        $data = User::leftJoin('departments', 'users.department_id', '=', 'departments.id')
            ->leftJoin('positions', 'users.position_id', '=', 'positions.id')
            ->leftJoin('companies', 'users.company_id', '=', 'companies.id')
            ->select('users.id as user_id', 'users.*', 'departments.*', 'positions.*' , 'companies.*')
            ->where('active_status','<>',1)
            ->get();

        return response()->json($data);
    }

    function getEmployees(Request $request){
        $data = $this->myActiveEmployees();

        if($request->segment(3) == 'with_schedule')
        foreach ($data as $key => $value) {
            $data[$key]['schedules'] = ScheduleHistory::leftJoin('branches', 'schedule_histories.branch_id', '=', 'branches.id')
                                                            ->where('employee_id', $value['user_id'])
                                                            ->where('schedule_type','RANGE')
                                                            ->select('schedule_histories.*', 'branches.branch_name')
                                                            ->orderBy('schedule_start','DESC')
                                                            ->orderBy('schedule_type','DESC')
                                                            ->get()->toArray();
            $data[$key]['is_online'] = (time() - strtotime($value['last_activity']) <100) ;
        }
        return response()->json($data);
    }

    function getEmployee(Request $request){
        $data = User::leftJoin('departments', 'users.department_id', '=', 'departments.id')
                        ->leftJoin('positions', 'users.position_id', '=', 'positions.id')
                        ->leftJoin('companies', 'users.company_id', '=', 'companies.id')
                        ->leftJoin('employment_statuses', 'users.employee_status', '=', 'employment_statuses.id')
                        ->select('users.id as user_id', 'users.*', 'departments.*', 'positions.*' , 'companies.*','employment_status_name')
                        ->where('users.id', $request->segment(3))
                        ->get()->first();

        $data['salary_rate'] = $this->getCurrentSalary($request->segment(3));
        $data['view_salary'] = $this->allowSalaryView();
        $data['delete_attendance'] = $this->allowDeleteAttendance();
        return response()->json($data);
    }

    function getAuth(){
        return response()->json(Auth::user());
    }
}