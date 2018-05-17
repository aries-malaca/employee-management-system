<?php
namespace ExactivEM\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use ExactivEM\Http\Requests;
use ExactivEM\Position;
use ExactivEM\Department;
use ExactivEM\EmploymentStatus;
use ExactivEM\Company;
use ExactivEM\TaxExemption;
use ExactivEM\Batch;
use ExactivEM\Message;
use ExactivEM\User;
use ExactivEM\Bank;
use ExactivEM\Branch;
use ExactivEM\UserLevel;
use ExactivEM\Salary_base;
use ExactivEM\HolidayType;
use ExactivEM\ScheduleHistory;
use ExactivEM\ScheduleType;
use ExactivEM\User_log;
use ExactivEM\Host;

class MiscController extends Controller
{
    /// COLLECTIONS ///
    /// dropdowns data used for select comboboxes and other purposes
    /// Standard return type is Array
    public function getCollections(Request $request){
        switch($request->segment(2)){
            case 'Position':
                $collection = Position::get()->all();
                break;
            case 'Department':
                $collection = Department::get()->all();
                break;
            case 'EmploymentStatus':
                $collection = EmploymentStatus::get()->all();
                break;
            case 'Company':
                $collection = Company::get()->all();
                break;
            case 'TaxExemption':
                $collection = TaxExemption::get()->all();
                break;
            case 'Batch':
                $collection = Batch::get()->all();
                break;
            case 'Branch':
                $collection = Branch::get()->all();
                break;
            case 'Bank':
                $collection = Bank::get()->all();
                break;
            case 'UserLevel':
                $collection = UserLevel::get()->all();
                break;
            case 'HolidayType':
                $collection = HolidayType::get()->all();
                break;
            case 'SalaryGrades':
                $collection['grade'] = Salary_base::distinct()->select('grade_number')->get();
                $collection['step'] = Salary_base::where('grade_number', 1)->select('step_number')->get();
                break;
        }
        
        //prints the json collection data
        echo json_encode($collection);
    }
    
    //getting the misc values via ajax
    public function getValues(Request $request){
        if($request->segment(2) !==null AND $request->segment(3) !==null )
        {
            switch($request->segment(2)){
                case 'Position':
                    $collection = Position::where('department_id', $request->segment(3))->get()->all();
                    break;
            }

            //prints json collection data
            echo json_encode($collection);
        }
        else{
            echo 'error';
        }
    }
    
    //get the latest chat
    function checkFreshChat(Request $request){
        $get = Message::leftJoin("users","messages.sender_employee_id","=","users.id")
                        ->where("messages.created_at",">", date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")." -5 seconds") ) )
                        ->where("receiver_employee_id", $request->segment(3))
                        ->get()->all();
        $data = array();

        foreach($get as $key=>$value){
            $data[] = array("sender_id"=>$value['sender_employee_id'],
                            "title"=> "New message from: ". $value['first_name'], 
                            "message"=> (strlen($value['message'])>30? substr($value['message'], 0, 30 )."...":$value['message']) );
        }

        //prints json data
        echo json_encode($data);
    }

    //getting the salary via Ajax
    function fetchSalary(Request $request){
        $salary = Salary_base::where('grade_number', $request->segment(3))
                                ->where('step_number', $request->segment(4))
                                ->get()
                                ->first();
        if(is_numeric($salary->salary_amount)){
            //prints salary amount
            echo $salary->salary_amount;
        }              
        else{
            echo 0;
        }
    }
    
    //lockscreen controller will be redirected to lock page
    function lockScreen(){
        if(Auth::user() !==null ){
            $data['email'] =Auth::user()->email;
            $data['picture'] =Auth::user()->picture;
            $data['name'] = Auth::user()->name;  
             Auth::logout();
        }
        else{
            //redirect to login if not Auth
            return redirect('login');
        }

        //return lock screen view
        return view('auth.lock', $data);
    }

    //function to seen the logs
    function seenLogs(){

        //set all latest activities as seen by the current user
        foreach ($this->data['latest_employee_activities'] as $key => $value) {
            $get = User_log::find($value['id']);
            if(strlen($value['seen_by_ids']) > 0){
                $users = explode(',', $get->seen_by_ids);

                //push to array if not seen already
                if(!in_array(Auth::user()->id, $users)){
                    array_push($users ,Auth::user()->id);
                }
                
                $get->seen_by_ids = implode(',', $users);
            }
            else{
                $get->seen_by_ids = Auth::user()->id;
            }
            //save changes
            $get->save();
        }
    }

    //function to save employee's location
    function saveLocation(Request $request){

        $user = User::find(Auth::user()->id);
        $user->last_location = json_encode(array($request->input('latitude'),
                                                 $request->input('longitude'),
                                                 time() ));
        //encode data as json
        $user->last_ip = json_encode(array($_SERVER['REMOTE_ADDR'],time()));
        $user->save();
        return 'okay';
    }

    //get the location via IP using API
    function getIPLocation(Request $request){
        //return the data as json
        return response()->json(json_decode(file_get_contents('http://ip-api.com/json/' . $request->segment(3))));
    }

    //get the user location address uses google geo coding or host determination
    function getUserLocationAddress(Request $request){
        $user = User::find($request->segment(3));

        $location = '';

        if(strlen($user->last_ip)>0){
            //check the host ip
            $host = Host::where('ip_address',json_decode($user->last_ip)[0] )->get()->first();
            if(!empty($host)){
                echo $host['company_name'] .' ' . $host['company_address'];
                die;
            }
            elseif(json_decode($user->last_ip)[1] + 10000 > time()){
                // use getIPLocation API 
                //$location = json_decode(file_get_contents(url('api/getIPLocation/'. json_decode($user->last_ip)[0])));
               // $lat = $location->lat;
               // $long = $location->lon;
            }
        }
        elseif( strlen($user->last_location)>0){
            if(json_decode($user->last_location)[2] + 10000 > time()){
                $location = json_decode($user->last_location);
                $lat = $location[0];
                $long = $location[1];
            }
        }

        //check if the location stick not defined
        if($location == ''){
            echo 'Unknown';
            die;
        }

        //get the geolocation from google
        $data = json_decode(file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?latlng=".$lat.",". $long));

        //finally return the formatted text address
        echo $data->results[0]->formatted_address;
    }
}