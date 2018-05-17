<?php

namespace ExactivEM\Http\Controllers;

use Illuminate\Http\Request;
use ExactivEM\Http\Requests;
use ExactivEM\Trainee;
use Validator;

class TraineeController extends Controller{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){
        $this->middleware('auth');
        parent::__construct();

        $this->data['page']['parent'] = 'Management';
        $this->data['page']['parent_url'] = '#';
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        return view('trainees', $this->data);
    }

    function getTrainees(){
        $data = Trainee::get()->toArray();
        return response()->json($data);
    }

    function addTrainee(Request $request){
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'wave' => 'required|numeric',
            'classification' => 'required|in:company-owned,franchised',
            'status' => 'required',
            'biometric_no' => 'required|unique:trainees,biometric_no',
        ]);
        //if there are an error return to view and display errors
        if ($validator->fails()) {
            return response()->json(['result'=>'failed','errors'=>$validator->errors()->all()]);
        }

        $trainee = new Trainee;
        $trainee->biometric_no = $request->input('biometric_no');
        $trainee->first_name = $request->input('first_name');
        $trainee->last_name = $request->input('last_name');
        $trainee->middle_name = $request->input('middle_name');
        $trainee->wave = $request->input('wave');
        $trainee->classification = $request->input('classification');
        $trainee->status = $request->input('status');
        $trainee->assigned_id = $request->input('assigned_id');
        $trainee->save();
        return response()->json(['result'=>'success']);
    }

    function updateTrainee(Request $request){
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'wave' => 'required|numeric',
            'classification' => 'required|in:company-owned,franchised',
            'status' => 'required',
            'biometric_no' => 'required|unique:trainees,biometric_no,'.$request->input('id'),
        ]);
        //if there are an error return to view and display errors
        if ($validator->fails()) {
            return response()->json(['result'=>'failed','errors'=>$validator->errors()->all()]);
        }

        $trainee = Trainee::find($request->input('id'));
        $trainee->biometric_no = $request->input('biometric_no');
        $trainee->first_name = $request->input('first_name');
        $trainee->last_name = $request->input('last_name');
        $trainee->middle_name = $request->input('middle_name');
        $trainee->wave = $request->input('wave');
        $trainee->classification = $request->input('classification');
        $trainee->status = $request->input('status');
        $trainee->assigned_id = $request->input('assigned_id');
        $trainee->save();

        return response()->json(['result'=>'success']);
    }
}