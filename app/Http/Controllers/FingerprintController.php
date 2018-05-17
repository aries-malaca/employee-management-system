<?php
namespace ExactivEM\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use ExactivEM\Http\Requests;
use Illuminate\Support\Facades\Storage;
use ExactivEM\Branch;
use ExactivEM\Libraries\Attendance_Class;
use ExactivEM\User;
use ExactivEM\Config;
use ExactivEM\Trainee;
use ExactivEM\AttendanceLog;
use Response;
use ExactivEM\Libraries\Mailer_Class;

class FingerprintController extends Controller{
    function __construct(){
        $this->algorithms = array(array('name'=>'10','with_name'=>true));
        $this->current_array = array();
        $this->branches = Branch::get()->pluck('id')->toArray();
    }

    function latestCount(){
        $unique = array();
        $file = '/public_html/public/bio/users/master/10.txt';
        $blocked = '/public_html/public/bio/users/blocked/blocked.txt';
        $blocked_list = array();
        if(Storage::disk('ftp')->exists($blocked)){
            $blocked_list = json_decode(Storage::disk('ftp')->get($blocked),true);
        }
        if(Storage::disk('ftp')->exists($file)){
            $data = json_decode(Storage::disk('ftp')->get($file),true);
            foreach($data as $key=>$value){
                if(!in_array($value['finger'],$blocked_list)){
                    if(!in_array($value['id'], $unique))
                        $unique[] = $value['id'];
                }
            }
        }
        return response(sizeof($unique))->withHeaders(['Content-Type' => 'text']);
    }

    function getUnsync(){
        $unsynced = array();
        $branches = Branch::get()->toArray();

        foreach($branches as $key=>$value){
            if(!Storage::disk('ftp')->exists('/public_html/public/bio/attendance/bio_'. $value['id'] .'_'. date('Y-m-d') .'.txt')){
                $unsynced[] = $value;
            }
        }
        return response()->json($unsynced);
    }

    function convertToJSON(){
        //loop of branches
        foreach($this->branches as $branch){
            //loop of algo
            foreach($this->algorithms as $key => $algorithm){
                $file = '/public_html/public/bio/users/branch/'.$branch.'_bio_users_'.$algorithm['name'].'.txt';
                if(Storage::disk('ftp')->exists($file)) {
                    $array = array();
                    $contents = Storage::disk('ftp')->get($file);
                    $lines = preg_split('/\r\n|\n|\r/',$contents);

                    foreach($lines as $key=>$line){
                        $data = preg_split('/[\s]+/', $line);
                        if(isset($data[1])){
                            $name = isset($data[4])?$data[4]:"";
                            $priv = 0;
                            if($user = User::where('biometric_no', $data[0])->get()->first() ){
                                $name = $user['first_name'];
                                $priv = (in_array($user['level'],[1,5,8,6,3])? 3:0);
                            }

                            $index = 0;
                            if(isset($data[4])){
                                if(is_numeric($data[4]))
                                    $index = $data[4];
                                else
                                    if(is_numeric($data[5]))
                                        $index = $data[5];
                            }

                            $array[] = array("id"=>$data[0],
                                "name"=>$name,
                                "finger"=> $data[1],
                                "privilege"=> $priv,
                                "index"=> (int)$index,
                            );
                            User::where('biometric_no', $data[0])
                                    ->orWhere('trainee_biometric_no', $data[0])
                                    ->update(['has_bio'=>1]);
                        }
                    }
                    Storage::disk('ftp')->put('/public_html/public/bio/users/json/'.$branch.'_json_'.$algorithm['name'].'.txt',json_encode($array));
                }
            }
            //end loop of algo
        }
        //end loop of branches

        foreach($this->algorithms as $key => $algorithm){
            $this->current_array = array();

            foreach($this->branches as $k => $branch){
                $file = '/public_html/public/bio/users/json/'.$branch.'_json_'.$algorithm['name'].'.txt';

                if(Storage::disk('ftp')->exists($file)){
                    $contents = json_decode(Storage::disk('ftp')->get($file),true);
                    foreach($contents as $d=>$e){

                        if(!$this->isFingerPrintExists($e['id'], $e['index']) AND (User::where('biometric_no', $e['id'])->where('active_status',1)->count() > 0) )
                            $this->current_array[] = $e;


                        elseif($e['id'] > 1000){
                            $f = $this->searchFromTrainee($e);
                            if(!$this->isFingerPrintExists($f['id'], 0)){
                                $this->current_array[] = $f;
                            }
                        }

                    }
                    //end contents
                }
            }
            //end branches

            Storage::disk('ftp')->put('/public_html/public/bio/users/master/'.$algorithm['name'].'.txt',json_encode($this->current_array));

        }
        //end algo
    }



    function searchFromTrainee($e){
        $search = Trainee::where('biometric_no', $e['id'])->get()->first();

        if(isset($search['id'])){

            return array("id"=> $search['assigned_id']==0?$search['biometric_no']:$search['assigned_id'],
                         "finger"=> $e['finger'],
                         "name"=> $search['first_name'].' '.$search['last_name'],
                         "privilege"=>$e['privilege'],
                         "index"=>0
                         );
        }

        return $e;
    }

    function isFingerPrintExists($finger_id, $index=10){

        $blocked = '/public_html/public/bio/users/blocked/blocked.txt';
        $blocked_list = array();
        if(Storage::disk('ftp')->exists($blocked)){
            $blocked_list = json_decode(Storage::disk('ftp')->get($blocked),true);
        }

        foreach($this->current_array as $key=>$value){
            if($value['id'] == $finger_id AND !in_array($value['finger'], $blocked_list) && $index == $value['index'])
                return true;
        }
        return false;
    }

    function getMasterFile(Request $request){
        $content = '';

        //check if the support is online updating is from 10 to 15
        $user = Config::find(105)->value;
        $att = new Attendance_Class($user, date('Y-m-d'));
        if($att->getLogs() === false OR date('G')>17 OR date('G')<14 ){

            //disables updating
            return response($content)
                ->withHeaders(['Content-Type' => 'text']);
        }

        $file = '/public_html/public/bio/users/master/'.$request->segment(3).'.txt';
        $blocked = '/public_html/public/bio/users/blocked/blocked.txt';
        $blocked_list = array();
        if(Storage::disk('ftp')->exists($blocked)){
            $blocked_list = json_decode(Storage::disk('ftp')->get($blocked),true);
        }
        if(Storage::disk('ftp')->exists($file)){
            $data = json_decode(Storage::disk('ftp')->get($file),true);
            foreach($data as $key=>$value){
                if(!in_array($value['finger'],$blocked_list)){
                    $content = $content. $value['id']."\t".$value['name']."\t".$value['finger']."\t".$value['privilege']."\t".$value['index'].PHP_EOL;
                }
            }
            return response($content)
                    ->withHeaders(['Content-Type' => 'text']);
        }
        else{
            return response($content);
        }
    }

    function getSetupFile(){
        $fs = Storage::disk('ftp')->getDriver();
        $stream = $fs->readStream('/public_html/public/installer/setup.exe');

        return response()->stream(function() use($stream) {
            fpassthru($stream);
        }, 200, [
            "Content-Type" => $fs->getMimetype('/public_html/public/installer/setup.exe'),
            "Content-Length" => $fs->getSize('/public_html/public/installer/setup.exe'),
            "Content-disposition" => "attachment; filename=\"" . basename('/public_html/public/installer/setup.exe') . "\"",
        ]);
    }

    function getSDK(){
        $fs = Storage::disk('ftp')->getDriver();
        $stream = $fs->readStream('/public_html/public/installer/sdk.zip');

        return response()->stream(function() use($stream) {
            fpassthru($stream);
        }, 200, [
            "Content-Type" => $fs->getMimetype('/public_html/public/installer/sdk.zip'),
            "Content-Length" => $fs->getSize('/public_html/public/installer/sdk.zip'),
            "Content-disposition" => "attachment; filename=\"" . basename('/public_html/public/installer/sdk.zip') . "\"",
        ]);
    }

    function getSoftware(){
        $fs = Storage::disk('ftp')->getDriver();
        $stream = $fs->readStream('/public_html/public/installer/software.zip');

        return response()->stream(function() use($stream) {
            fpassthru($stream);
        }, 200, [
            "Content-Type" => $fs->getMimetype('/public_html/public/installer/software.zip'),
            "Content-Length" => $fs->getSize('/public_html/public/installer/software.zip'),
            "Content-disposition" => "attachment; filename=\"" . basename('/public_html/public/installer/software.zip') . "\"",
        ]);
    }

    function logEvent(Request $request){
        $branch = Branch::where('id', $request->segment(4))->get()->first();
        $message = $branch['branch_name'] . ' ' . $request->segment(3) .' ';
        $message .= ($request->segment(5)!==null ? $request->segment(5): $this->getExportedCount($request->segment(4))) . ' users';
        $mail = new Mailer_Class;
        $mail->sendLogEvent($message);
    }

    function getExportedCount($branch_id){
        $file = '/public_html/public/bio/users/json/'.$branch_id.'_json_10.txt';
        if(Storage::disk('ftp')->exists($file)){
            $data = json_decode(Storage::disk('ftp')->get($file),true);
            return sizeof($data);
        }
        return 0;
    }

    function collectLogs(Request $request){
        $folder = '/public_html/public/bio/attendance';
        $files = Storage::disk('ftp')->files($folder);

        foreach($files as $file){
            $f = explode('/',$file);
            $branch_id = explode('_',$f[4])[1];
            $date = explode('_',$f[4])[2];

            if(!Storage::disk('ftp')->exists($file))
                continue;

            if(str_replace('.txt','',$date) != date('Y-m-d'))
                continue;

            if($request->segment(3) != 'all' && $request->segment(3) != $branch_id)
                continue;

            $contents = Storage::disk('ftp')->get($file);
            $lines = preg_split('/\r\n|\n|\r/',$contents);

            foreach($lines as $line){
                $log = preg_split("/[\s]+/", $line);

                if(sizeof($log)>1){
                    $diff = time() - strtotime($log[1].' '. $log[2]);
                    if($diff <= 1296000){
                        $search = AttendanceLog::where('datetime', $log[1].' '. $log[2])
                            ->where('biometric_no', $log[0])
                            ->count();

                        if($search == 0 ){
                            $attendance = new AttendanceLog;
                            $attendance->branch_id = $branch_id;
                            $attendance->datetime = $log[1].' '. $log[2];
                            $attendance->biometric_no = $log[0];
                            $attendance->save();
                            echo 'ok';
                        }

                    }
                }
            }
        }
    }
    
    function checkArray($array, $value){
        foreach($array as $k=>$v){
            if($v['id'] == $value)
                return $k;
        }
        return false;
    }

    function logExists($array, $compare){

        foreach($array as $key=>$value){
            if($value['id'] == $compare['id'] && $value['datetime'] == $compare['datetime'])
                return true;
        }

        return false;
    }

    function getEnrolledCount(){
        $file = '/public_html/public/bio/users/master/10.txt';
        $data = array();
        if(Storage::disk('ftp')->exists($file))
            $data = json_decode(Storage::disk('ftp')->get($file),true);

        return response()->json(sizeof($data));
    }
}