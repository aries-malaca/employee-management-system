<?php

namespace ExactivEM\Http\Controllers;

use Illuminate\Http\Request;

use ExactivEM\Http\Requests;
use ExactivEM\UserLevel;
use Validator;

class UserlevelController extends Controller
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
        //check permission of user
        if(!$this->checkRestriction()){
            return view('errors.no_permission', $this->data);
        }
        
        $this->data['levels'] = UserLevel::get()->all();
        return view('levels', $this->data);
    }
    
    //delete the user level
    function processDelete(Request $request){
        $level_info = UserLevel::find($request->input('id'));
        
        //writelog
        $details = 'Deleted Level '. $level_info->level_name;
        $this->writeLog("Level", $details);
        
        
        if($level = UserLevel::destroy($request->input('id')) )
        {
            //return with success message
            return redirect()->back()->with('deleting', 'success');
        }
    }
    
    //add user level
    function processAdd(Request $request){
        //validate inputs
        $validator = Validator::make($request->all(), [
                    'level_name' => 'required|unique:user_levels,level_name|max:255',
                    'level_role' => 'required|max:255'
                ]);
        //if there are an error return to view and display errors  
        if ($validator->fails()) 
        {
            return redirect()->back()
                    ->withErrors($validator,'adding_level')
                    ->withInput();
        }
        
        //new UserLevel Object
        $level = new UserLevel;
        $level->level_name = $request['level_name'];
        $level->level_role = $request['level_role'];
        $level->level_active = 1;
        $level->levels_as_employees = (sizeof($request['employees'])>0?json_encode($request['employees']):'[]');
        $level->levels_to_approve = (sizeof($request['approves'])>0?json_encode($request['approves']):'[]');
        $level->levels_to_view = (sizeof($request['views'])>0?json_encode($request['views']):'[]');
        $level->levels_as_supervisor = '[]';
        $level->save();
        
        //writelog
        $details = 'Added Level '. $level->level_name;
        $this->writeLog("Level", $details);
        
        //return success message
        return redirect()->back()->with('adding', 'success');
    }
    
    //edit Userlevel
    function processEdit(Request $request){
        
        if($level = UserLevel::find($request->input('id')) )
        {
            //validate inputs
            $validator = Validator::make($request->all(), [
                        'level_name' => 'required|unique:user_levels,level_name,'.$request->id.'|max:255'
                    ]);
            //if there are an error return to view and display errors  
            if ($validator->fails()) 
            {
                return redirect()->back()
                        ->withErrors($validator,'editing_level')
                        ->withInput();
            }
            
            $level->level_name = $request['level_name'];
            $level->level_role = $request['level_role'];
            $level->levels_as_employees = (sizeof($request['employees'])>0?json_encode($request['employees']):'[]');
            $level->levels_to_approve = (sizeof($request['approves'])>0?json_encode($request['approves']):'[]');
            $level->levels_to_view = (sizeof($request['views'])>0?json_encode($request['views']):'[]');
            //save changes
            $level->save();
            
            //writelog
            $details = 'Updated Level '. $level->level_name;
            $this->writeLog("Level", $details);
        
            //return with success message
            return redirect()->back()->with('editing', 'success');
        }
    }

    function getLevels(){
        return response()->json(UserLevel::get());
    }
}
