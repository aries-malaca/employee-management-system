<?php

namespace ExactivEM\Http\Controllers;

use Illuminate\Http\Request;
use ExactivEM\Host;
use ExactivEM\Http\Requests;
use Validator;

class HostController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();

        $this->data['page']['parent'] = 'Control Panel';
        $this->data['page']['parent_url'] = '#';
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->data['hosts'] = Host::get()->all();
        return view('hosts', $this->data);
    }

    function processDelete(Request $request){
        Host::destroy($request->input('id'));
        //deleted and return success message
        return redirect()->back()->with('delete','success');
    }

    function processAdd(Request $request){
        //validate inputs
        $validator = Validator::make($request->all(), [
                    'ip_address' => 'required|unique:hosts,ip_address|max:255'
                ]);
        //if there are an error return to view and display errors  
        if ($validator->fails()) 
        {
            return redirect()->back()
                    ->withErrors($validator,'add')
                    ->withInput();
        }

        //new host object
        $host = new Host;
        $host->ip_address = $request->input('ip_address');
        $host->company_name = $request->input('company_name');
        $host->company_address = $request->input('company_address');
        $host->company_contact = $request->input('company_contact');
        $host->save();

        //return success message
        return redirect()->back()->with('add','success');
    }


    function processEdit(Request $request){
        //validate inputs
        $validator = Validator::make($request->all(), [
                    'ip_address' => 'required|unique:hosts,ip_address,'.$request->input('id').'|max:255'
                ]);
        //if there are an error return to view and display errors  
        if ($validator->fails()) 
        {
            return redirect()->back()
                    ->withErrors($validator,'edit')
                    ->withInput();
        }

        //find Host object
        $host = Host::find($request->input('id'));
        $host->ip_address = $request->input('ip_address');
        $host->company_name = $request->input('company_name');
        $host->company_address = $request->input('company_address');
        $host->company_contact = $request->input('company_contact');
        $host->save();

        //return success message
        return redirect()->back()->with('edit','success');
    }
}