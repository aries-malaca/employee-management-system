<?php
namespace ExactivEM\Http\Controllers;
use ExactivEM\Libraries\Mailer_Class;
use Illuminate\Http\Request;
use ExactivEM\Http\Requests;
use ExactivEM\User;
use ExactivEM\Branch;
use ExactivEM\Company;
use ExactivEM\Department;
use ExactivEM\Message;
use ExactivEM\Position;
use DB;
use File;
use ExactivEM\Leave_type;
use ExactivEM\EmployeeRequest;
use Illuminate\Support\Facades\Auth;

class RestfullController extends Controller
{   
    //function used to send chat message
	function sendMessage(Request $request){
        $message = new Message;
        $message->message = $request->message;
        $message->sender_employee_id = $request->from;
        $message->receiver_employee_id = $request->to;
        $message->save();
        //print success message
        echo 'sent';
    }

    function getBranches(Request $request){
        if($request->segment(3) != 'text'){
            return response()->json(Branch::orderBy('branch_name')->get());
        }

        $branches = Branch::orderBy('branch_name')->get()->all();
        $content = '';
        foreach($branches as $key=>$value){
            $content = $content. $value['id']."\t".$value['branch_name']."\t".PHP_EOL;
        }

        return response($content)
            ->withHeaders(['Content-Type' => 'text']);
    }

    //password generator API
    function generatePassword(Request $request){
       return $this->generateNewPassword($request->segment(3));
    }
    
    //function used to fetch conversation 
    function getConversation(Request $request){
        $this->request = $request;
        $limit = $this->request->limit;

        //get the conversations beetween 2 employees
        $the_data = Message::leftJoin('users as s', 'messages.sender_employee_id', '=', 's.id')
                            ->leftJoin('users as r', 'messages.receiver_employee_id', '=', 'r.id')
                            ->select('messages.*', 's.name as sender_name', 'r.name as receiver_name','messages.updated_at as message_update',
                                    'messages.created_at as message_date','s.picture as sender_picture', 'messages.id as message_id')
                            ->where(function ($query) {
                                $query->orWhere('sender_employee_id', $this->request->with)
                                      ->orWhere('sender_employee_id', $this->request->me);
                            })
                            ->where(function ($query) {
                                $query->orWhere('receiver_employee_id', $this->request->with)
                                      ->orWhere('receiver_employee_id', $this->request->me);
                            })
                            ->get()->all();

        $convo_size = sizeof($the_data); //size of total conversation;

        //loop through each conversation and set some info
        foreach($the_data as $key => $value){

            if($value['sender_employee_id'] != Auth::user()->id){
                $message_seen = Message::find($value['message_id']);
                
                if($message_seen->is_read == 0){
                    $the_data[$key]['was_seen'] = 1;
                }
                else{
                    $the_data[$key]['was_seen'] = 0;
                }
                //message has been read by the user
                $message_seen->is_read = 1;
                $message_seen->save();
            }
            
            //format dates
            $the_data[$key]['message_date'] = date('m/d/Y h:i A',strtotime($the_data[$key]['message_date']));
            $the_data[$key]['message_update'] = date('m/d/Y h:i A',strtotime($the_data[$key]['message_update']));

            if(($convo_size - $limit ) > $key)
                continue;

            $final_data [] = $value;
        }
        
        //prints json data 
        echo json_encode(array("total_rows"=>$convo_size, "data"=>$final_data));
    }
    
    //delete the message from chat
    function deleteMessage(Request $request){
        Message::destroy($request->input('message_id'));
    }

    //function that counts unread messages
    function countUnreadMessages(Request $request){
        return json_encode(Message::where('is_read', 0)
                        ->where('receiver_employee_id', Auth::user()->id)
                        ->whereIn('sender_employee_id',  $request->input('emps') )
                        ->select(DB::raw('count(*) as hits, sender_employee_id as sender'))
                        ->groupBy('sender_employee_id')->get()->all());
    }
    
    //function used as API
    function getRemainingLeaves(Request $request){
        $has_text_file = false;

        //check if old record exists
        if(file_exists(public_path('records/leaves/'.date('Y').'.txt'))){
            $leave_file = json_decode(file_get_contents(public_path('records/leaves/'.date('Y').'.txt')),true);
            $has_text_file = true;
        }

        //get alll leave types
        $this->data['leave_types']= Leave_type::where("leave_type_data","like",'%"gender":"both"%')
                                                ->orWhere("leave_type_data","like",'%"gender":"'.User::find($request->segment(3))->gender.'"%')
                                                ->get()->all();
        //get consumed leaves this current yhear
        $this->data['this_year_leaves'] = EmployeeRequest::where('request_data', 'like', '%'.date('Y').'%')
                                                            ->where('employee_id', $request->segment(3))
                                                            ->Where('request_type', 'leave')
                                                            ->get()->all();
        foreach($this->data['this_year_leaves']  as $key=> $data){
            //check each type 
            foreach($this->data['leave_types'] as $key2=> $data2){
                
                //deduct to leave type max
                if( json_decode($data['request_data'])->leave_type == $data2['id']){
                    //subtract the used leaves
                    $this->data['leave_types'][$key2]['leave_type_max'] -= json_decode($data['request_data'])->days;
                }
            }
        }
        foreach($this->data['leave_types'] as $key2=> $data2){
            //if file was found
            if($has_text_file){
                foreach ($leave_file as $k => $v) {
                    foreach ($v['leaves'] as $kl => $vl) {
                        if($request->segment(3) == $v['id'] AND $vl['id'] == $data2['id']){
                            //subtract the used leaves
                            $this->data['leave_types'][$key2]['leave_type_max'] -= $vl['used'];
                        }
                    }
                }
            }
        }

        //return a view with the data
        return view('employee.common.leave_credits', $this->data);
    }

    function getOfficeEmployees(){
        return response()->json(User::leftJoin('departments', 'users.department_id', '=', 'departments.id')
                                        ->leftJoin('positions', 'users.position_id', '=', 'positions.id')
                                        ->leftJoin('companies', 'users.company_id', '=', 'companies.id')
                                        ->whereNotIn('position_id',[49,50])
                                        ->select('users.id as user_id', 'users.*', 'departments.department_name',
                                                'positions.position_name' , 'companies.company_name','company_phone')
                                        ->orderBy('name')
                                        ->get());
    }

    function getAllEmployees(){
        return response()->json(User::leftJoin('positions', 'users.position_id', '=', 'positions.id')
            ->select('users.id as user_id', 'users.*','positions.position_name')
            ->orderBy('name')
            ->get());
    }

    function getJAS(){
        return response()->json(User::leftJoin('departments', 'users.department_id', '=', 'departments.id')
            ->leftJoin('positions', 'users.position_id', '=', 'positions.id')
            ->leftJoin('companies', 'users.company_id', '=', 'companies.id')
            ->whereIn('position_id',[73])
            ->select('users.id as user_id', 'users.*', 'departments.department_name',
                'positions.position_name' , 'companies.company_name','company_phone')
            ->orderBy('name')
            ->get());
    }

    function getSAS(){
        return response()->json(User::leftJoin('departments', 'users.department_id', '=', 'departments.id')
            ->leftJoin('positions', 'users.position_id', '=', 'positions.id')
            ->leftJoin('companies', 'users.company_id', '=', 'companies.id')
            ->whereIn('position_id',[76])
            ->select('users.id as user_id', 'users.*', 'departments.department_name',
                'positions.position_name' , 'companies.company_name','company_phone')
            ->orderBy('name')
            ->get());
    }

    function getMyData(Request $request){
        $data = User::leftJoin('departments', 'users.department_id', '=', 'departments.id')
            ->leftJoin('positions', 'users.position_id', '=', 'positions.id')
            ->leftJoin('companies', 'users.company_id', '=', 'companies.id')
            ->select('users.id as user_id', 'users.*', 'departments.*', 'positions.*' , 'companies.*')
            ->where('users.id', $request->segment(3))
            ->get()->first();
        return response()->json($data);
    }
    
    function getDepartments(){
        return response()->json(Department::get());
    }

    function getPositions(){
        return response()->json(Position::leftJoin('departments','positions.department_id','=','departments.id')
            ->select('positions.*','departments.department_name')
            ->orderBy('position_name')
            ->get());
    }

    function broadcastCelebrant(Request $request){
        $users = User::leftJoin('departments', 'users.department_id', '=', 'departments.id')
                        ->leftJoin('positions', 'users.position_id', '=', 'positions.id')
                        ->leftJoin('companies', 'users.company_id', '=', 'companies.id');

        if($request->segment(4) !== null)
            $users=$users->where('active_status',1)->where('birth_date', 'LIKE', '%'. $request->segment(3) .'-' . $request->segment(4) .'%');
        else
            $users=$users->where('active_status',1)->where('birth_date', 'LIKE', '%-' . $request->segment(3) .'-%');

        $users = $users->select('birth_date', 'department_name', 'company_name', 'position_name', 'name', 'email')->get()->toArray();

        $type = ($request->segment(4) !== null ? 'daily':'monthly');

        if(!empty($users)){
            $mail = new Mailer_Class;
            $mail->sendDailyBirthdayBroadCast($users, $type);
        }
    }

    function getBirthdayCelebrants(Request $request){
        $users = User::leftJoin('departments', 'users.department_id', '=', 'departments.id')
            ->leftJoin('positions', 'users.position_id', '=', 'positions.id')
            ->leftJoin('companies', 'users.company_id', '=', 'companies.id');

        $users=$users->where('birth_date', 'LIKE', '%'. $request->segment(3) .'-' . $request->segment(4) .'%')
                        ->whereIn('users.id',$this->myDownLines())
                        ->where('active_status',1);
        $users = $users->select('birth_date', 'department_name', 'company_name', 'position_name', 'name', 'email','users.id')->get()->toArray();
        return response()->json($users);
    }
}