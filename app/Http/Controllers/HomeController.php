<?php
namespace ExactivEM\Http\Controllers;

use ExactivEM\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ExactivEM\User;
use ExactivEM\Branch;
use ExactivEM\News;
use ExactivEM\Attendance;
use ExactivEM\Libraries\Attendance_Class;
use ExactivEM\EmployeeRequest;
use Illuminate\Support\Facades\Auth;
use Mail;

class HomeController extends Controller{
    public function __construct(){
        $this->middleware('auth');
        parent::__construct();
    }
    
    public function index(){
        $this->listNews();
        if(Auth::user()->level === 1)
            return view('home', $this->data);

        if($this->data['config']['employee_level_id'] != Auth::user()->level ){
            return view('home', $this->data);
        }
        else{
            //employees will be redirected to calendar page
            return redirect('calendar');
        }
    }

}