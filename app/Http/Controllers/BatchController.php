<?php
namespace ExactivEM\Http\Controllers;
use Illuminate\Http\Request;
use ExactivEM\Batch;
use ExactivEM\Http\Requests;
use ExactivEM\User;
use Validator;
use Cache;

class BatchController extends Controller{
    public function __construct(){
        $this->middleware('auth');
        parent::__construct();
        
        //default title and url
        $this->data['page']['parent'] = 'Management';
        $this->data['page']['parent_url'] = '#';
    }
    
    function index(){
        //check if the user has permission to this page
        if(!$this->checkRestriction()){
            return view('errors.no_permission', $this->data);
        }

        return view('batches', $this->data);
    }
    
    //deletes the batch
    function deleteBatch(Request $request){
        $batch_info = Batch::find($request->input('id'));
        //writelog
        $details = 'Deleted Batch '. $batch_info->batch_name;
        $this->writeLog("Batch", $details);
        
        
        Batch::destroy($request->input('id'));
        return response()->json(['result'=>'success']);
    }
    
    //add the batch 
    function addBatch(Request $request){
        //validate inputs
        $validator = Validator::make($request->all(), [
                    'batch_name' => 'required|unique:batches,batch_name|max:255'
                ]);
        //if there are an error return to view and display errors  
        if ($validator->fails()) {
            return response()->json(['result'=>'failed', 'errors'=>$validator->errors()->all()]);
        }
        
        $batch = new Batch;
        $batch->batch_name = $request->input('batch_name');
        $batch->batch_active = 1;
        $batch->save();
        
        //writelog
        $details = 'Added Batch '. $batch->batch_name;
        $this->writeLog("Batch", $details);
        
        return response()->json(['result'=>'success']);
    }
    
    function updateBatch(Request $request){
        $batch = Batch::find($request->input('id'));
        //validate inputs
        $validator = Validator::make($request->all(), [
                    'batch_name' => 'required|unique:batches,batch_name,'.$request->input('id').'|max:255'
                ]);
        //if there are an error return to view and display errors
        if ($validator->fails()) {
            return response()->json(['result'=>'failed','errors'=>$validator->errors()->all()]);
        }

        $batch->batch_name = $request->input('batch_name');
        $batch->save();

        //writelog
        $details = 'Updated Batch '. $batch->batch_name;
        $this->writeLog("Batch", $details);

        //return with success message
        return response()->json(['result'=>'success']);
    }

    function getBatches(){
        $data = Batch::get();
        return response()->json($data);
    }
}