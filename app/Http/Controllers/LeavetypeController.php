<?php
namespace ExactivEM\Http\Controllers;
use Illuminate\Http\Request;
use ExactivEM\Leave_type;
use ExactivEM\Http\Requests;
use ExactivEM\User;
use ExactivEM\CustomLeave;
use Tymon\JWTAuth\Claims\Custom;
use Validator;

class LeavetypeController extends Controller{
    public function __construct(){
        $this->middleware('auth');
        parent::__construct();
        
        //default title and url
        $this->data['page']['parent'] = 'Payroll Setup';
        $this->data['page']['parent_url'] = '#';
    }
    
    function index(){
        //check user's restriction
        if(!$this->checkRestriction()){
            return view('errors.no_permission', $this->data);
        }

        //return the view
        return view('leavetypes', $this->data);
    }

    function getLeaveTypes(Request $request){
        $data = Leave_type::get()->toArray();
        if($request->segment(3)!== null){
            $new = array();
            $user = User::find($request->segment(3));
            foreach($data as $key=>$value){

                $d = json_decode($value['leave_type_data'],true);
                if( ($d['gender'] == 'both' OR  $d['gender'] == $user->gender) AND
                        in_array("".$user->tax_exemption_id, $d['tax_exemptions'])
                ){
                    $value['hidden'] = false;
                    $usage = $this->getLeaveUsage($value['id'], $request->segment(3));
                    $value['credits'] = $usage['credits'];
                    $value['used'] = $usage['used'];
                    $new[] = $value;
                }
            }

            return response()->json($new);
        }

        return response()->json($data);
    }

    function updateLeaveCredit(Request $request){
        //validation
        $validator = Validator::make($request->all(), [
            'leave_type_id' => 'required|not_in:0',
            'year' => 'required|numeric|not_in:0',
            'max_leave' => 'required|numeric'
        ]);
        //if there are an error return to view and display errors
        if ($validator->fails()) {
            return response()->json(["command"=>"updateLeaveCredit","result"=>"failed","errors"=>$validator->errors()->all()]);
        }

        $find = CustomLeave::where('leave_type_id', $request->input('leave_type_id'))
                                ->where('employee_id', $request->input('employee_id'))
                                ->get()->first();
        if(isset($find['id'])){
            CustomLeave::where('leave_type_id', $request->input('leave_type_id'))
                            ->where('employee_id', $request->input('employee_id'))
                            ->update(['max_leave'=>$request->input('max_leave')]);
        }else{
            $credit = new CustomLeave;
            $credit->year = $request->input('year');
            $credit->max_leave = $request->input('max_leave');
            $credit->leave_type_id = $request->input('leave_type_id');
            $credit->employee_id = $request->input('employee_id');
            $credit->save();
        }
        //return success message
        return response()->json(["command"=>"updateLeaveCredit","result"=>"success"]);
    }

    function getCustomLeaves(Request $request){
        return response()->json(CustomLeave::where('leave_type_id', $request->segment(3))->get()->toArray());
    }

    function addLeaveType(Request $request){
        //validation
        $validator = Validator::make($request->all(), [
                    'leave_type_name' => 'required|unique:leave_types,leave_type_name|max:255',
                    'leave_type_max' => 'required'
                ]);
        //if there are an error return to view and display errors  
        if ($validator->fails()) {
            return response()->json(["command"=>"addLeaveType","result"=>"failed","errors"=>$validator->errors()->all()]);
        }
        
        //new leave type object
        $type = new Leave_type;
        $type->leave_type_name = $request['leave_type_name'];
        $type->leave_type_description = $request['leave_type_description'];
        $type->leave_type_active = 1;
        $type->leave_type_max = $request['leave_type_max'];
        $type->leave_type_data = json_encode(array("gender"=>$request->input('gender'),
                                                    "paid"=>$request->input('paid'),
                                                    "limit_per_lifetime"=>$request->input('limit_per_lifetime'),
                                                    "tax_exemptions"=>$request->input('tax_exemptions'),
                                                    "allow_half_day"=>$request->input('allow_half_day'),
                                                    "is_staggered"=>$request->input('is_staggered'),
                                                    "within_condition"=>$request->input('within_condition'),
                                                    "extra_message"=>$request->input('extra_message'))
                                            );
        $type->save();

        $details = 'Added Leave Type '. $type->leave_type_name;
        $this->writeLog("Leave Type", $details);

        //return success message
        return response()->json(["command"=>"addLeaveType","result"=>"success"]);
    }
    
    function updateLeaveType(Request $request){
        //validate inputs
        $validator = Validator::make($request->all(), [
                    'leave_type_name' => 'required|unique:leave_types,leave_type_name,'.$request->input('id').'|max:255',
                    'leave_type_max' => 'required'
                ]);
        //if there are an error return to view and display errors
        if ($validator->fails()) {
            return response()->json(["command"=>"updateLeaveType","result"=>"failed","errors"=>$validator->errors()->all()]);
        }
        $type = Leave_type::find($request->input('id'));
        $type->leave_type_name = $request->input('leave_type_name');
        $type->leave_type_max = $request->input('leave_type_max');
        $type->leave_type_description =$request->input('leave_type_description');
        $type->leave_type_data = json_encode(array("gender"=>$request->input('gender'),
                "paid"=>$request->input('paid'),
                "limit_per_lifetime"=>$request->input('limit_per_lifetime'),
                "tax_exemptions"=>$request->input('tax_exemptions'),
                "allow_half_day"=>$request->input('allow_half_day'),
                "is_staggered"=>$request->input('is_staggered'),
                "within_condition"=>$request->input('within_condition'),
                "extra_message"=>$request->input('extra_message'))
        );

        $type->save();

        $details = 'Updated Leave Type '. $type->leave_type_name;
        $this->writeLog("Leave Type", $details);

        //return success message to view
        return response()->json(["command"=>"updateLeaveType","result"=>"success"]);
    }
}