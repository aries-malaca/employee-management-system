<?php
namespace ExactivEM\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use ExactivEM\Http\Requests;
use ExactivEM\User;
use ExactivEM\Salary_history;
use ExactivEM\EmploymentStatuses;
use ExactivEM\Attendance;
use Validator;
use Hash;
use DateTime;
class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }
    
    function index(){
        //set the page info
        $this->data['page']['title'] = 'My Profile';
        $this->data['page']['url'] =  'profile';

        //return view with data
        return view('profile', $this->data);
    }
   
    public function uploadProfilePicture(Request $request){
        $id = $request->input('id');
        
        //uploader is located in Controller
        if($this->uploader($request ,$id,  'employees')){
            
            //writelog
            $details = 'Changed profile picture';
            $this->writeLog("Profile", $details);
            
            //success message
            return redirect()->back()->with('data', 'success');
        }
        //failed to upload picture
        return redirect()->back()->with('data', 'failed');
    }
    
    public function updatePassword(Request $request){
        $old_password = Auth::user()->password;
        
        //check if the old password is correct using Hash object
        if(Hash::check($request->input('old_password'), $old_password )){

            if(strlen($request->input('password'))< 6){
                return response()->json(["command"=>"changePassword","result"=>"failed","errors"=>"Password should be atleast 6 characters."]);
            }

            //match the passwords
            if($request->input('password') == $request->input('password2')){
                $user = User::find(Auth::user()->id);
                $user->password = bcrypt($request->input('password'));
                $user->prompt_change_password =0;
                $user->save();
                
                //writelog
                $details = 'Changed password';
                $this->writeLog("Profile", $details);
                
                //success message
                return response()->json(["command"=>"changePassword","result"=>"success"]);
            }
            else{
                return response()->json(["command"=>"changePassword","result"=>"failed","errors"=>"Passwords not matched."]);
            }
        }
        
        //failed to update password
        return response()->json(["command"=>"changePassword","result"=>"failed", "errors"=> "Incorrect old password."]);
    }
    
    function updateProfile(Request $request)
    {
        if($user = User::find(Auth::user()->id) ) {
            //validate inputs
            $validator = Validator::make($request->all(), [
                        'first_name' => 'required|max:255',
                        'middle_name' => 'required|max:255',
                        'last_name' => 'required|max:255',
                        'email' => 'required|email|unique:users,email,'. Auth::user()->id .'|max:255'
                    ]);
            //if there are an error return to view and display errors  
            if ($validator->fails()) 
            {
                return response()->json(["command"=>"updateProfile", "result"=>"failed", "errors"=> $validator->errors()->all()]);
            }
            
            $user->first_name = $request->input('first_name');
            $user->middle_name = $request->input('middle_name');
            $user->last_name = $request->input('last_name');
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
            $user->contact_person = $request->input('contact_person');
            $user->contact_info = $request->input('contact_info');
            $user->contact_relationship = $request->input('contact_relationship');
            //commit the changes
            $user->save();
            
            //write the log
            $this->writeLog("Profile", "Updated ".($user->gender=='male'?'his':'her')." Profile");
            
            //bring back the view for success 
            return response()->json(["command"=>"updateProfile", "result"=>"success"]);
        }
        else
        {
            return response()->json(["command"=>"updateProfile", "result"=>"failed", "errors"=> "Profile mismatch."]);
        }
    }
    //end of processEditProfile()
}