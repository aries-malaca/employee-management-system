<?php
namespace ExactivEM\Http\Controllers;
use Illuminate\Http\Request;
use ExactivEM\Config;
use ExactivEM\Http\Requests;
use ExactivEM\UserLevel;

class ConfigController extends Controller
{
     public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
        
        //default title and url
        $this->data['page']['parent'] = 'Control Panel';
        $this->data['page']['parent_url'] = '#';
    }
    
    function index()
    {
        //check user permissions to view this page
        if(!$this->checkRestriction()){
            return view('errors.no_permission', $this->data);
        }
    
        $this->data['levels'] = UserLevel::get()->all();
        return view('settings', $this->data);
    }
    
    function processEdit(Request $request){
        //loop through each configs
        foreach($request->input('config') as $key=>$value){
            $config = Config::find($key);
            
            if(is_array($value)){
                $config->value = json_encode($value);
            }
            else{
                $config->value = $value;
            }
            $config->save();
        }
        
        //writelog
        $details = 'Updated Application Settings';
        $this->writeLog("Settings", $details);
        
        //return with success message
        return redirect()->back()->with('update', 'success');
    }
}