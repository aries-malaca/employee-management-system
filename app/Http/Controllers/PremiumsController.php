<?php
namespace ExactivEM\Http\Controllers;
use Illuminate\Http\Request;

use ExactivEM\Http\Requests;
use ExactivEM\Config;
use ExactivEM\User;
use ExactivEM\Libraries\Attendance_Class;
class PremiumsController extends Controller{
    public function __construct(){
        $this->middleware('auth');
        parent::__construct();
    }
    
    function index(){
        //check user's permission to the page
        if(!$this->checkRestriction()){
            return view('errors.no_permission', $this->data);
        }

        //default title and url
        $this->data['page']['parent'] = 'Payroll Setup';
        $this->data['page']['parent_url'] = '#';

        //return to view, with the data of premium employees
        return view('premiums', $this->data);
    }

    function getPremiumSettings(){
        return response()->json(['regular_overtime'=>Config::find(16)->value,
                                 'restday_overtime'=>Config::find(17)->value,
                                 'restday_beyond_overtime'=>Config::find(18)->value,
                                 'regular_nightdiff'=>Config::find(19)->value,
                                 'restday_nightdiff'=>Config::find(20)->value
                                ]
                            );
    }

    //function to update premium configurations
    function updatePremiumSettings(Request $request){
        $c = Config::find(16);
        $c->value = $request->input('regular_overtime');
        $c->save();
        
        $c = Config::find(17);
        $c->value = $request->input('restday_overtime');
        $c->save();
        
        $c = Config::find(18);
        $c->value = $request->input('restday_beyond_overtime');
        $c->save();
        
        $c = Config::find(19);
        $c->value = $request->input('regular_nightdiff');
        $c->save();
        
        $c = Config::find(20);
        $c->value = $request->input('restday_nightdiff');
        $c->save();
        
        $this->writeLog("Premiums", 'Updated premium settings.');

        //return with success message
        return response()->json(['result'=>'success']);
    }
}