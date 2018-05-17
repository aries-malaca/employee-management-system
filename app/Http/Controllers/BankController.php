<?php
namespace ExactivEM\Http\Controllers;
use Illuminate\Http\Request;
use ExactivEM\Bank;
use ExactivEM\User;
use ExactivEM\Http\Requests;
use Validator;
use Cache;

class BankController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        parent::__construct();
       
        $this->data['page']['parent'] = 'Management';
        $this->data['page']['parent_url'] = '#';
        //default title and url
    }
    
    function index(){
        if(!$this->checkRestriction()){
            return view('errors.no_permission', $this->data);
        }

        return view('banks', $this->data);
    }

    function deleteBank(Request $request){
        $bank_info = Bank::find($request->input('id'));
        
        //writelog
        $details = 'Deleted Bank '. $bank_info->bank_name;
        $this->writeLog("Bank", $details);

        return response()->json(['result'=>'success']);
    }
    
    function updateEmployeeAccount(Request $request){
        $validator = Validator::make($request->all(), [
            'bank_number' => 'required|unique:users,bank_number,'.$request->input('id').'|max:255'
        ]);
        //if there are an error return to view and display errors
        if ($validator->fails()) {
            return response()->json(['result'=>'failed','errors'=>$validator->errors()->all()]);
        }

        $account = User::find($request->input('id'));
        $account->bank_number = $request->input('bank_number');
        $account->save();

        return response()->json(['result'=>'success']);
    }

    function addBank(Request $request){
        //validate inputs
        $validator = Validator::make($request->all(), [
                    'bank_name' => 'required|unique:banks,bank_name|max:255',
                    'bank_shortname' => 'required|max:255'
                ]);
        //if there are an error return to view and display errors  
        if ($validator->fails()) {
            return response()->json(['result'=>'failed','errors'=>$validator->errors()->all()]);
        }
        
        $bank = new Bank;
        $bank->bank_name = $request->input('bank_name');
        $bank->bank_shortname = $request->input('bank_shortname');
        $bank->bank_active = 1;
        $bank->save();
        
        //writelog
        $details = 'Added Bank '. $bank->bank_name;
        $this->writeLog("Bank", $details);

        return response()->json(['result'=>'success']);
    }
    
    function updateBank(Request $request){
        //validate inputs
        $validator = Validator::make($request->all(), [
                    'bank_name' => 'required|unique:banks,bank_name,'.$request->input('id').'|max:255',
                    'bank_shortname' => 'required|max:255'
                ]);
        //if there are an error return to view and display errors
        if ($validator->fails()) {
            return response()->json(['result'=>'failed','errors'=>$validator->errors()->all()]);
        }
        $bank = Bank::find($request->input('id'));
        $bank->bank_name = $request->input('bank_name');
        $bank->bank_shortname = $request->input('bank_shortname');
        $bank->save();

        //writelog
        $details = 'Updated Bank '. $bank->bank_name;
        $this->writeLog("Bank", $details);

        //redurect and send success message
        return response()->json(['result'=>'success']);
    }

    function getBanks(){
        $data = Bank::get();
        return response()->json($data);
    }
}