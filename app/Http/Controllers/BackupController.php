<?php

namespace ExactivEM\Http\Controllers;

use Illuminate\Http\Request;
use ExactivEM\Http\Requests;
use Storage;
use Illuminate\Support\Facades\Artisan;

class BackupController extends Controller
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
        //check if the user has permission to the page
        if(!$this->checkRestriction()){
            return view('errors.no_permission', $this->data);
        }
                
        //backups 
        $this->data['backups'] = array();
        $files = Storage::disk('temp')->files();
        
        //loop through each files and add it to backup array
        foreach($files as $key=>$value){
            if($value == '.gitignore'){
                continue;
            }
            $backup = array("name"=> $value,
                           "date"=> Storage::disk('temp')->lastModified($value),
                           "size"=> (Storage::disk('temp')->size($value) / 1024 ) / 1024);
            $this->data['backups'][] = $backup;
        }
        
        return view('backups', $this->data);
    }
    
    function backupNow(){
        //run artisan command to backup the application
        Artisan::queue('backup:run',[],'--queued');

        //redirect to this index page
        return redirect()->action('BackupController@index');
    }
    
    function deleteBackup(Request $request){
        //delete the backup file and redirect to index page
        Storage::disk('temp')->delete($request->segment(3));
        return redirect()->action('BackupController@index');
    }
}