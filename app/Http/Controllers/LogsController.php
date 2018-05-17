<?php
namespace ExactivEM\Http\Controllers;

use Illuminate\Http\Request;
use ExactivEM\Http\Requests;
use ExactivEM\User_log;
use Illuminate\Support\Facades\Auth;

class LogsController extends Controller{
    public function __construct(){
        $this->middleware('auth');
        parent::__construct();
    }
    
    function index(){
        //default title and url
        $this->data['page']['parent'] = 'Control Panel';
        $this->data['page']['parent_url'] = '#';
        
        //check restriction of the page
        if(!$this->checkRestriction()){
            return view('errors.no_permission', $this->data);
        }
        
        //get all logs
        $this->data['logs'] = User_log::leftJoin('users', 'user_logs.log_by_id','=', 'users.id')
                                        ->select('users.*', 'users.id as user_id', 'user_logs.created_at as log_date', 'user_logs.*')
                                        ->orderBy('user_logs.id','DESC')->get()->all();
        
        return view('logs', $this->data);
    }
    
    function viewLogs(Request $request){
        $this->data['page']['title_url'] = '#';
        //default title and url
        if($request->segment(2) == 'myLogs') {
            $this->data['page']['title'] = 'My Logs';
            //get all  my logs
            $this->data['logs'] = User_log::leftJoin('users', 'user_logs.log_by_id','=', 'users.id')
                                            ->where('log_by_id', Auth::user()->id)
                                            ->select('users.*', 'users.id as user_id','user_logs.created_at as log_date', 'user_logs.*')
                                            ->orderBy('user_logs.id','DESC')->get()->all();
        }
        elseif($request->segment(2) == 'myEmployeeLogs'){
            $this->data['page']['title'] = 'My Employee Logs';
            //get all  my logs
            $this->data['logs'] = User_log::leftJoin('users', 'user_logs.log_by_id','=', 'users.id')
                                            ->whereIn('log_by_id', $this->myDownLines())
                                            ->where('log_by_id','<>', Auth::user()->id)
                                            ->select('users.*', 'users.id as user_id','user_logs.created_at as log_date', 'user_logs.*')
                                            ->orderBy('user_logs.id','DESC')->get()->all();
        }
        else{
            return view('errors.404');
        }

        return view('logs', $this->data);
    }

    function getMyLogs(){
        return response()->json(User_log::where('log_by_id', Auth::user()->id)
                                            ->orderBy('user_logs.created_at','desc')
                                            ->take(50)
                                            ->get());
    }

    function getEmployeeLogs(){
        return response()->json(User_log::leftJoin('users','user_logs.log_by_id','=','users.id')
                                        ->whereIn('log_by_id', $this->myDownLines())
                                        ->where('log_by_id','<>', Auth::user()->id)
                                        ->select('user_logs.*','name')
                                        ->orderBy('user_logs.created_at','desc')
                                        ->take(50)
                                        ->get());
    }
}