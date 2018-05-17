<?php
namespace ExactivEM\Http\Controllers;
use Illuminate\Http\Request;
use ExactivEM\Http\Requests;
use Validator;
use ExactivEM\TaxExemption;
use ExactivEM\Contribution;
use ExactivEM\TransactionCode;

class ContributionController extends Controller{
    public function __construct(){
        $this->middleware('auth');
        parent::__construct();
    }
    
    function index(){
        //check permission for the page
        if(!$this->checkRestriction()){
            return view('errors.no_permission', $this->data);
        }

        //default title and url
        $this->data['page']['parent'] = 'Payroll Setup';
        $this->data['page']['parent_url'] = '#';

        return view('contribution', $this->data);
    }

    //edit tax exemption
    function updateTaxExemption(Request $request){
        $tax = TaxExemption::find($request->input('id'));
        //validate inputs
        $validator = Validator::make($request->all(), [
                    'tax_exemption_name' => 'required|unique:tax_exemptions,tax_exemption_name,'.$request->input('id').'|max:255'
                ]);
        //if there are an error return to view and display errors
        if ($validator->fails()) {
            return response()->json(['result'=>'failed','errors'=>$validator->errors()->all()]);
        }

        $tax->tax_exemption_name = $request->input('tax_exemption_name');
        $tax->save();

        $this->writeLog("Contributions", 'Updated Tax Exemption name - ' . $request->input('tax_exemption_name') .'.');
        //return with success message
        return response()->json(['result'=>'success']);
    }

    function getTaxExemptions(){
        return response()->json(TaxExemption::get());
    }

    function getContributions(){
        return response()->json(TransactionCode::leftJoin('contributions','transaction_codes.id','=','contributions.transaction_code_id')
                                            ->where('is_regular_transaction','1')
                                            ->select('transaction_codes.*','contribution_data')
                                            ->get());
    }
}
