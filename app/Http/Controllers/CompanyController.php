<?php

namespace ExactivEM\Http\Controllers;

use Illuminate\Http\Request;
use ExactivEM\Company;
use ExactivEM\User;
use Validator;
use ExactivEM\Http\Requests;

class CompanyController extends Controller{
    public function __construct(){
        $this->middleware('auth');
        parent::__construct();
        
        //default title and url
        $this->data['page']['parent'] = 'Management';
        $this->data['page']['parent_url'] = '#';
    }
    
    function index(){
        //check if the user has permission to the page
        if(!$this->checkRestriction()){
            return view('errors.no_permission', $this->data);
        }
        return view('companies', $this->data);
    }

    function getCompanies(){
        $data = Company::get()->toArray();

        foreach($data as $key=>$value){
            $data[$key]['company_data'] = json_decode($value['company_data']);
        }

        return response()->json($data);
    }

    //edit company info
    function updateCompany(Request $request){
        $company = Company::find($request->input('id'));

        //validation rules
        $validator = Validator::make($request->all(), [
                    'company_name' => 'required|unique:companies,company_name,'. $company->id .'|max:255'
                ]);
        //end of validation rules

        //if there are an error return to view and display errors
        if ($validator->fails()) {
            return response()->json(["command"=>"updateCompany","result"=>"failed","errors"=>$validator->errors()->all()]);
        }

        $company->company_name = $request->input('company_name');
        $company->company_address = $request->input('company_address');
        $company->company_email = $request->input('company_email');
        $company->company_phone = $request->input('company_phone');
        $company->company_data = json_encode($request->input('company_data'));
        //company picture
        $id = $request->input('id');

        //uploader is located in Controller
        $this->uploader($request ,$id,  'companies');
        //end company picture

        //commit the changes
        $company->save();

        //writelog
        $details = 'Updated Company '. $company->company_name;
        $this->writeLog("Company", $details);

        //bring back the view for success
        return response()->json(["command"=>"updateCompany","result"=>"success"]);
    }
    
    //add company to the database
    function addCompany(Request $request){
        $company = new Company;
        //validation rules
        $validator = Validator::make($request->all(), [
                    'company_name' => 'required|unique:companies,company_name|max:255'
                ]);
        //end of validation rules

        if ($validator->fails()) {
            return response()->json(["command"=>"addCompany","result"=>"failed","errors"=>$validator->errors()->all()]);
        }

        $company->company_name = $request->input('company_name');
        $company->company_address = $request->input('company_address');
        $company->company_email = $request->input('company_email');
        $company->company_phone = $request->input('company_phone');
        $company->company_active =1;
        $company->company_data = json_encode($request->input('company_data'));
        $company->save();

        //writelog
        $details = 'Added Company '. $company->company_name;
        $this->writeLog("Company", $details);
        //bring back the view for success 
        return response()->json(["command"=>"addCompany","result"=>"success"]);
    }

    function activateCompany(Request $request){
        $company = Company::find($request->input('id'));
        $company->company_active = 1;
        $company->save();
        return response()->json(["command"=>"activateCompany","result"=>"success"]);
    }

    function deactivateCompany(Request $request){
        $company = Company::find($request->input('id'));
        $company->company_active = 0;
        $company->save();
        return response()->json(["command"=>"deactivateCompany","result"=>"success"]);
    }
}