<?php

namespace ExactivEM\Http\Controllers;

use Illuminate\Http\Request;

use ExactivEM\Http\Requests;
use ExactivEM\EvaluationTemplate;
use ExactivEM\Evaluation;
use ExactivEM\EmploymentStatus;
use ExactivEM\User;
use Illuminate\Support\Facades\Auth;

class EvaluationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
        
        //default title and url
        $this->data['page']['parent'] = 'Management';
        $this->data['page']['parent_url'] = '#';
        $this->data['page']['title'] = 'Evaluations';
        $this->data['page']['url'] =  'evaluations';
    }
    
    function index()
    {

        //check user's permission to access the page
        if(!$this->checkRestriction()){
            return view('errors.no_permission', $this->data);
        }



        //get the template data
        $this->data['template'] = json_decode(EvaluationTemplate::get()->first()['evaluation_template_data']);

        //get all evaluations
        $this->data['evaluations'] = Evaluation::get()->all();
        
        //loop through each evaluations and set names
        foreach($this->data['evaluations'] as $key=>$value){
            $this->data['evaluations'][$key]['employee_name'] = User::find($value['employee_id'])->name;
            $this->data['evaluations'][$key]['evaluated_by_name'] = User::find($value['evaluated_by_id'])->name;
        }

        return view('errors.503');

        //return view('evaluation', $this->data);
    }
    
    //function for adding evaluation
    function processAdd(Request $request){
        //new evaluation object
        $eval = new Evaluation;
        $eval->evaluated_by_id = Auth::user()->id;
        $eval->date_evaluated = date('Y-m-d');
        $eval->employee_id = $request['employee_id'];
        
        $items = array();
        //loop for questions
        foreach($request['question'] as $key=>$value){
            $items[] = array($value, $request['answer'][$key]);
        }
        
        $items_text = array();
        //loop for text questions
        foreach($request['text_question'] as $key=>$value){
            $items_text[] = array($value, $request['text_answer'][$key]);
        }
        
        //get user data
        $user = User::find($request['employee_id']);
        
        //get user employment status
        $eval_offset = EmploymentStatus::find($user->employee_status)->evaluation_months;
        
        $user->next_evaluation = date('Y-m-d',strtotime($user->next_evaluation . " + " . $eval_offset . " months"));

        //edit the next evaluation date of the user
        $user->save();
        
        $eval->evaluation_data = json_encode(array("items"=>$items, "items_text"=>$items_text));
        //save the evaluation data
        $eval->save();
        
        //writelog
        
        $details = 'Evaluated '. $user->name;
        $this->writeLog("Evaluation", $details);
        
        //return success message
        return redirect()->back()->with('adding', 'success');
    }
}
