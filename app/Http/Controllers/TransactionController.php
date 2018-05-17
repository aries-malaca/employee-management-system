<?php
namespace ExactivEM\Http\Controllers;
use Illuminate\Http\Request;
use ExactivEM\TransactionCode;
use ExactivEM\Transaction;
use ExactivEM\User;
use Excel;
use ExactivEM\Http\Requests;
use Validator;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller{
    public function __construct(){
        $this->middleware('auth');
        parent::__construct();
    }
    
    function index(){
        if(!$this->checkRestriction()){
            return view('errors.no_permission', $this->data);
        }

        //default title and url
        $this->data['page']['parent'] = 'Payroll Setup';
        $this->data['page']['parent_url'] = '#';

        return view('transactions', $this->data);
    }

    function getTransactions(Request $request){
        $data = Transaction::leftJoin('transaction_codes', 'transactions.transaction_code_id','=','transaction_codes.id')
                            ->leftJoin('users', 'transactions.employee_id','=','users.id');

        if($request->segment(3)===null)
            $data = $data->whereIn('users.id', $this->myDownLines());
        else
            $data = $data->where('users.id', $request->segment(3));

        $data = $data->select('transactions.*', 'transactions.id as transaction_id', 'transaction_codes.*',
                    'users.*', 'users.id as user_id', 'transactions.cutoff as cutoff','transaction_codes.cutoff as transactioncode_cutoff')
                    ->get();

        return response()->json($data);
    }

    function getTransactionCodes(){
        return response()->json(TransactionCode::orderBy('transaction_name')->get());
    }
    
    function addTransactionCode(Request $request){
        $validator = Validator::make($request->all(), [
                    'transaction_name' => 'required|unique:transaction_codes,transaction_name|max:255'
                ]);
        //if there are an error return to view and display errors  
        if ($validator->fails()){
            return response()-json(['result'=>'failed','errors'=>$validator->errors()->all()]);
        }
        
        $trans = new TransactionCode;
        $trans->transaction_name = $request->input('transaction_name');
        $trans->is_taxable =  $request->input('is_taxable');
        $trans->transaction_type =  $request->input('transaction_type');
        $trans->is_regular_transaction =  $request->input('is_regular_transaction');
        $trans->cutoff = ($trans->is_regular_transaction == 1? $request->input('cutoff'):'');
        $trans->save();

        $this->writeLog("Transaction Code", 'Added Transaction code - '. $trans->transaction_name);
        return response()->json(['result'=>'success']);
    }
    
    function updateTransactionCode(Request $request){
        $validator = Validator::make($request->all(), [
            'transaction_name' => 'required|unique:transaction_codes,transaction_name,'.$request->input('id').'|max:255'
        ]);
        //if there are an error return to view and display errors
        if ($validator->fails()) {
            return response()-json(['result'=>'failed','errors'=>$validator->errors()->all()]);
        }

        $trans = TransactionCode::find($request->input('id'));
        $trans->transaction_name = $request->input('transaction_name');
        $trans->is_taxable =  $request->input('is_taxable');
        $trans->transaction_type =  $request->input('transaction_type');
        $trans->is_regular_transaction =  $request->input('is_regular_transaction');
        $trans->cutoff = ($trans->is_regular_transaction == 1? $request->input('cutoff'):'');
        $trans->save();

        $this->writeLog("Transaction Code", 'Updated Transaction code - '. $trans->transaction_name);
        return response()->json(['result'=>'success']);
    }
    
    function deleteTransactionCode(Request $request){
        $trans = TransactionCode::find($request->input('id'));
        TransactionCode::destroy($request->input('id'));

        $this->writeLog("Transaction Code", 'Deleted Transaction code - '. $trans->transaction_name);
        return response()->json(['result'=>'success']);
    }
    
    function deleteTransaction(Request $request){
        $trans_info = Transaction::find($request->input('id'));
        
        //writelog
        $details = 'Deleted Transaction of '. $this->getEmployeeName($trans_info->employee_id) .', ' . $trans_info->notes;
        $this->writeLog("Transaction", $details);
        
        if($trans = Transaction::destroy($request->input('id')) ) {
            return response()->json(['result'=>'success']);
        }
    }
    
    function updateTransaction(Request $request){
        $validator = Validator::make($request->all(), [ 'start_date' => 'required',
                                                        'end_date' => 'required',
                                                        'amount' => 'numeric|required',
                                                        'transaction_code_id' =>'required|not_in:0',
                                                        'employee_id' =>'required|not_in:0'
                                                        ]);
        //if there are an error return to view and display errors
        if ($validator->fails()) {
            return response()->json(['result'=>'failed','errors'=>$validator->errors()->all()]);
        }

        $trans = Transaction::find($request->input('id'));
        $trans->start_date = date('Y-m-d',strtotime($request->input('start_date')));
        $trans->end_date =  date('Y-m-d',strtotime($request->input('end_date')));
        $trans->amount =  $request->input('amount');
        $trans->transaction_code_id =  $request->input('transaction_code_id');
        $trans->cutoff =  $request->input('cutoff');
        $trans->frequency =  $request->input('frequency');
        $trans->notes =  $request->input('notes');
        $trans->save();

        //writelog
        $details = 'Updated Transaction of '. $this->getEmployeeName($trans->employee_id) . ', '. $trans->notes;
        $this->writeLog("Transaction", $details);

        return response()->json(['result'=>'success']);
    }
    
    function addTransaction(Request $request){
        $validator = Validator::make($request->all(), ['start_date' => 'required',
                                                        'end_date' => 'required',
                                                        'amount' => 'numeric|required',
                                                        'transaction_code_id' =>'required|not_in:0',
                                                        'employee_id' =>'required|not_in:0'
                                                ]
                                            );
        //if there are an error return to view and display errors  
        if ($validator->fails()) {
            return response()->json(['result'=>'failed','errors'=>$validator->errors()->all()]);
        }

        $trans = new Transaction;
        $trans->start_date = date('Y-m-d',strtotime($request->input('start_date')));
        $trans->end_date =  date('Y-m-d',strtotime($request->input('end_date')));
        $trans->amount =  $request->input('amount');
        $trans->employee_id =  $request->input('employee_id');
        $trans->transaction_code_id =  $request->input('transaction_code_id');
        $trans->cutoff =  $request->input('cutoff');
        $trans->frequency =  $request->input('frequency');
        $trans->notes =  $request->input('notes');
        $trans->added_by_id =  Auth::user()->id;
        $trans->save();

        //writelog
        $details = 'Added Transaction of '. $this->getEmployeeName($trans->employee_id) . ', '. $trans->notes;
        $this->writeLog("Transaction", $details);

        return response()->json(['result'=>'success']);
    }

    function addBulkTransactions(){
        $rows = Excel::selectSheetsByIndex(0)->load(public_path('csv/13th.xls'))->get()->toArray();

        foreach ($rows as $key=>$value){
            $transaction = new Transaction;
            $transaction->start_date = '2018-02-01';
            $transaction->end_date = '2018-02-15';
            $transaction->amount = $value['amount'];
            $transaction->employee_id = User::where('employee_no', $value['id'])->get()->first()['id'];
            $transaction->transaction_code_id = 26;
            $transaction->cutoff = 'first cutoff';
            $transaction->frequency = 'once';
            $transaction->notes = '13month adjustment';
            $transaction->added_by_id = Auth::user()->id;
            //$transaction->save();
        }

    }

}