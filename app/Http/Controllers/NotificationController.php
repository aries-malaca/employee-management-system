<?php
namespace ExactivEM\Http\Controllers;
use Illuminate\Http\Request;
use ExactivEM\Http\Requests;
use Illuminate\Support\Facades\Auth;
use ExactivEM\Notification;
use ExactivEM\User;
use ExactivEM\Libraries\Attendance_Class;
use ExactivEM\Libraries\Mailer_Class;

class NotificationController extends Controller{
    function getNotifications(){
        return response()->json(Notification::where('employee_id', Auth::user()->id)
                                            ->orderBy('is_read')
                                            ->orderBy('created_at', 'DESC')
                                            ->take(100)->get());
    }

    function seenNotification(Request $request){
        Notification::where('id', $request->input('id'))->update(['is_read'=>1]);
        return response()->json(['result'=>'success']);
    }

    function generateNotifications(Request $request){
        $users = User::where('active_status', 1)->get()->toArray();
        foreach($users as $key=>$value){
            if(strtotime($value['hired_date']) <= strtotime($request->segment(3))){
                $att = new Attendance_Class($value['id'], $request->segment(3));
                $remarks = $att->getRemarks();

                if(in_array('absent', $remarks)){
                    $pending = $this->findPendingRequests($value['id']);
                    $has_request = false;

                    foreach($pending as $req){
                        $data = json_decode($req['request_data'], true);
                        if(isset($data['date'])){
                            if($data['date'] == $request->segment(3)){
                                $has_request = true;
                            }
                        }
                        else{
                            if(strtotime($data['date_start']) <= strtotime($request->segment(3)) AND strtotime($data['date_end']) >= strtotime($request->segment(3))){
                                $has_request = true;
                            }
                        }
                    }

                    if(!$has_request){
                        if(Notification::where('notification_data', '{"date":"'. $request->segment(3) .'"}')
                                        ->where('employee_id', $value['id'])
                                        ->where('notification_title', 'Attendance Notice')->count() == 0){
                            $notification = new Notification;
                            $notification->notification_title = 'Attendance Notice';
                            $notification->notification_body = 'You missed having an attendance';
                            $notification->employee_id = $value['id'];
                            $notification->is_read = 0;
                            $notification->reference_id = 0;
                            $notification->notification_type = 'missing_attendance';
                            $notification->notification_data = '{"date":"'. $request->segment(3) .'"}';
                            $notification->save();
                        }
                    }
                }
            }
        }
    }

    function sendAbsentNotification(Request $request){
        $notifications = Notification::where('notification_type', 'missing_attendance')
                                        ->where('notification_data', '{"date":"'.$request->segment(3).'"}')
                                        ->get()->toArray();
        foreach($notifications as $key=>$notification){
            $user = User::where('id', $notification['employee_id'])->get()->first();
            $mailer = new Mailer_Class;
            if(in_array($user['position_id'],[49,50,68,69])){
                $branch = $this->getCurrentBranch($user['id'], $request->segment(3));
                if($branch !== false){
                    $mailer->sendAbsentNotificationOnBranch($user, $notification, $branch->toArray());
                }
            }
            else
                $branch = $this->getCurrentBranch($user['id'], $request->segment(3));
                if($branch !== false){
                    if($branch->branch_id == 1)
                        $mailer->sendAbsentNotification($user, $notification);
                }

            Notification::where('id', $notification['id'])->delete();
        }
    }

    function cleanNotifications(){
        $notifications = Notification::where('notification_type', 'missing_attendance')
                                        ->get()->toArray();

        foreach($notifications as $key=>$notification){
            $att = new Attendance_Class($notification['employee_id'], json_decode($notification['notification_data'])->date);
            $remarks = $att->getRemarks();
            $has_request = false;

            if(in_array('absent', $remarks)){
                $pending = $this->findPendingRequests($notification['employee_id']);

                foreach($pending as $req){
                    $data = json_decode($req['request_data'], true);
                    if(isset($data['date'])){
                        if($data['date'] == json_decode($notification['notification_data'])->date)
                            $has_request = true;
                    }
                    else{
                        if(strtotime($data['date_start']) <= strtotime(json_decode($notification['notification_data'])->date) AND
                                strtotime($data['date_end']) >= strtotime(json_decode($notification['notification_data'])->date))
                            $has_request = true;
                    }
                }
            }
            else
                $has_request = true;

            if($has_request){
                Notification::where('id', $notification['id'])->delete();
                echo 'deleted '. $notification['id'] .'<br/>';
            }
        }
    }
}
