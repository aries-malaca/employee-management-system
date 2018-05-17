<?php

namespace ExactivEM\Http\Controllers;

use Illuminate\Http\Request;
use ExactivEM\Menu;
use ExactivEM\UserLevel;
use ExactivEM\Http\Requests;

class PageController extends Controller
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
        //check user's permission
        if(!$this->checkRestriction()){
            return view('errors.no_permission', $this->data);
        }
        
        $this->data['pages'] = Menu::get()->all();
        $this->data['levels'] = UserLevel::get()->all();
        
        //return view
        return view('pages', $this->data);
    }
    
    //function to edit page permissions
    function processEdit(Request $request){
        foreach($request->input('level') as $key=> $value){
            $page_id = $key;
            
            $page = Menu::find($key);
            $levels_array = array();

            //levels array
            foreach($value as $val){
                $levels_array[] = $val;
            }
            
            //status
            if(isset($request['status'][$key])){
                $page->menu_active = 1;
            }
            else{
                $page->menu_active = 0;
            }
            
            //levels...
            $page->levels = implode(",",$levels_array);
            $page->save();
        }
        
        //writelog
        $details = 'Updated System Pages/Restriction';
        $this->writeLog("Pages", $details);
        
        //return success message
        return redirect()->back()->with('update', 'success');
    }
}