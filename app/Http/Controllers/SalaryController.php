<?php
namespace ExactivEM\Http\Controllers;
use Illuminate\Http\Request;
use ExactivEM\User;
use ExactivEM\Salary_history;
use ExactivEM\Salary_base;
use ExactivEM\Http\Requests;

class SalaryController extends Controller{
    public function __construct(){
        $this->middleware('auth');
        parent::__construct();
    }
    
    function index(){
        //check user's restriction
        if(!$this->checkRestriction()){
            return view('errors.no_permission', $this->data);
        }
        $this->data['page']['parent'] = 'Management';
        $this->data['page']['parent_url'] = '#';
        
        $grades = Salary_base::groupBy('grade_number')
                                ->distinct()
                                ->orderBy('grade_number','ASC')
                                ->get()->all();
        //loop through each salary grades
        foreach($grades as $key=>$grade){
            $info = array();
            $info['id'] = $grade['id'];
            $info['grade_number'] = $grade['grade_number'];
            
            //step is only from 1 to 12
            for($x=1;$x<=12;$x++){
                $info['amounts'][] = Salary_base::where('step_number',$x)
                                                     ->where('grade_number', $grade['grade_number'])  
                                                     ->get()->first()['salary_amount'];
            }
            $this->data['salary_grades'][] = $info;
        }

        
        //loop through each salaries
        foreach($this->myActiveEmployees() as $key=>$value){
            $history = Salary_history::where('employee_id', $value['user_id'])
                                                                ->where('end_date', '0000-00-00 00:00:00')
                                                                ->orderBy('created_at', 'DESC')
                                                                ->get()->first();
            //history found                                                    
            if(!empty($history)){
                $amount = $history['salary_amount'];
                $base = Salary_base::where('salary_amount', $amount)
                                  ->get()->first();
                $base = 'Grade '. $base['grade_number'] .' - Step '. $base['step_number'];
                $effective = $history['start_date'];
            }
            else{
                $amount = 0;
                $base = 'N/A';
                $effective = 'N/A';
            }
            
            //set employee salaries as array
            $this->data['employee_salaries'][] = array("user_id"=>$value['user_id'],
                                                       "employee"=>$value['first_name']. ' '. $value['last_name'],
                                                       "salary"=>$amount,
                                                       "salary_base"=>$base,
                                                       "updated_at"=>$history['updated_at'],
                                                       "date_effective"=>$effective);
        }
        //return view with data
        return view('salaries', $this->data);
    }
    
    //edit the salary base
    function editSalaryBase(Request $request){
        foreach($request->input('step') as $key=>$step){
            Salary_base::where('grade_number', $request->input('id'))
                            ->where('step_number', ($key+1) )
                            ->update(['salary_amount' => $step]);;
        }

        //return with success message
        return redirect()->back()->with('editing', 'success');
    }

    function getSalaryHistory(Request $request){
        $data = Salary_history::where('employee_id', $request->segment(3))->orderBy('start_date','DESC')
            ->leftJoin('users', 'salary_histories.updated_by_employee_id', '=', 'users.id')
            ->leftJoin('salary_bases','salary_histories.salary_amount','=','salary_bases.salary_amount')
            ->select('salary_histories.salary_amount as amount', 'users.name', 'salary_bases.*','salary_histories.*')
            ->get()->all();

        return response()->json($data);
    }
}
