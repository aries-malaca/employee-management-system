<?php
namespace ExactivEM\Libraries;

use Mail;
use Illuminate\Support\Facades\Auth;
use ExactivEM\User;
use ExactivEM\Config;
use ExactivEM\Branch;
use ExactivEM\Host;

class Mailer_Class {
    function __construct(){
        $this->config['app_name'] = Config::find(1)->value;
        $this->config['app_url'] = Config::find(23)->value;
        $this->config['send_notifications'] = Config::find(26)->value;

        $this->config['admins'] = User::whereIn('level', [Config::find(6)->value, Config::find(8)->value])
                                        ->where('active_status', 1)
                                        ->where('receive_notification',1)
                                        ->select('email','name')
                                        ->get()->toArray();
        $this->config['all'] = User::where('active_status', 1)
                                        ->where('receive_notification',1)
                                        ->select('email','name')
                                        ->get()->toArray();
    }

    function sendFailedLoginMail($data){
        if($this->config['send_notifications'] == 0){
            return false;
        }
        foreach($this->config['admins'] as $receiver){
            Mail::queue('emails.failed_login', ['config'=>$this->config, 'receiver'=>$receiver, 'inputs'=>$data], function ($message) use($receiver) {
                $message->from('ems@lay-bare.com', 'Employee Management System');
                $message->subject('Failed User Login');
                $message->to($receiver['email'], $receiver['name']);
            });
        }
    }

    function sendPasswordRequestMail($user){
        Mail::queue('emails.forgot_password', ['config'=>$this->config, 'user'=>$user], function ($message) use($user) {
            $message->from('ems@lay-bare.com', 'Employee Management System');
            $message->subject('Forgot Password');
            $message->to($user['email'], $user['name']);
        });
    }

    function sendTemporaryPasswordMail($user, $new_password){
        Mail::queue('emails.password_confirmation', ['config'=>$this->config,'new_password'=>$new_password, 'user'=>$user], function ($message) use($user) {
            $message->from('ems@lay-bare.com', 'Employee Management System');
            $message->subject('New Password Confirmation');
            $message->to($user['email'], $user['name']);
        });
    }


    function sendDailyBirthdayBroadCast($celebrants, $type){
        if($this->config['send_notifications'] == 0){
            return false;
        }
        
        if($type == 'monthly'){
            foreach($this->config['all'] as $receiver){
                Mail::queue('emails.birthday_broadcast', ['config'=>$this->config,'celebrants'=>$celebrants,'type'=>$type], function ($message) use($receiver){
                    $message->from('ems@lay-bare.com', 'Employee Management System');
                    $message->subject('Birthday Notification');
                    $message->to($receiver['email'], $receiver['name']);
                    $message->bcc('aries@lay-bare.com');
                });
            }
        }
        else{
            //greet the celebrant
            foreach($celebrants as $celebrant){
                Mail::queue('emails.birthday_greetings', ['config'=>$this->config,'celebrant'=>$celebrant], function ($message) use($celebrant){
                    $message->from('ems@lay-bare.com', 'Employee Management System');
                    $message->subject('Birthday Greetings');
                    $message->to($celebrant['email'], $celebrant['name']);
                    $message->bcc('aries@lay-bare.com');
                });
            }
        }
    }

    function sendRequestNotification($user, $notification, $branch){
        if($user['email']=='francesca@lay-bare.com' || $user['email']=='marian@lay-bare.com' )
            return false;


        Mail::queue('emails.request_approval', ['config'=>$this->config,'notification'=>$notification,'user'=>$user], function ($message) use($user, $branch) {
            $message->from('ems@lay-bare.com', 'Employee Management System');
            $message->subject('Request Approval Notification');
            $message->to($user['email'], $user['name']);
            $message->bcc('aries@lay-bare.com', $user['name']);

            if(Config::find(102)->value == $user['level']){
                $message->cc($branch['branch_email'], $branch['branch_name']);
            }
        });
    }

    function sendApprovalActionNotification($user, $notification){

        Mail::queue('emails.approval_action', ['config'=>$this->config,'notification'=>$notification,'user'=>$user], function ($message) use($user) {
            $message->from('ems@lay-bare.com', 'Employee Management System');
            $message->subject('Request Confirmation');
            $message->to($user['email'], $user['name']);
            $message->bcc('aries@lay-bare.com', $user['name']);
        });
    }

    function passwordChangeByAdmin($user, $password){
        Mail::queue('emails.password_reset', ['config'=>$this->config,'user'=>$user, 'password'=>$password], function ($message) use($user) {
            $message->from('ems@lay-bare.com', 'Employee Management System');
            $message->subject('Password Reset');
            $message->to($user['email'], $user['name']);
            $message->bcc('aries@lay-bare.com', $user['name']);
        });
    }

    function sendAbsentNotification($user, $notification){
        Mail::queue('emails.attendance_notice', ['config'=>$this->config,'user'=>$user, 'notification'=>$notification], function ($message) use($user) {
            $message->from('ems@lay-bare.com', 'Employee Management System');
            $message->subject('Attendance Notice');
            $message->to($user['email'], $user['name']);
            $message->bcc('aries@lay-bare.com', $user['name']);
        });
    }

    function sendAbsentNotificationOnBranch($user, $notification, $branch){
        Mail::queue('emails.attendance_notice_on_branch', ['config'=>$this->config,'user'=>$user, 'notification'=>$notification, 'branch'=>$branch], function ($message) use($user,$branch) {
            $message->from('ems@lay-bare.com', 'Employee Management System');
            $message->subject('Attendance Notice');
            $message->to($branch['branch_email'], $branch['branch_name']);
            $message->bcc('aries@lay-bare.com', $branch['branch_name']);
        });
    }

    function sendLogEvent($me){
        Mail::queue('emails.biometrics_io', ['config'=>$this->config, 'me'=>$me], function ($message) {
            $message->from('ems@lay-bare.com', 'Employee Management System');
            $message->subject('Biometric IO Logs');
            $message->to('aries@lay-bare.com');
        });
    }
}