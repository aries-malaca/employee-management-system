<?php

namespace ExactivEM\Http\Controllers;
use Illuminate\Http\Request;
use ExactivEM\Http\Requests;
use ExactivEM\EmploymentStatus;
use ExactivEM\Bank;
use ExactivEM\Department;
use ExactivEM\Position;
use ExactivEM\Company;
use ExactivEM\Batch;
use ExactivEM\TaxExemption;
use ExactivEM\UserLevel;
use ExactivEM\User;
use ExactivEM\Salary_history;
use Illuminate\Support\Facades\Auth;

class DataManagementController extends Controller{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
        
        //set the heders of document
        $this->headers = array("Emp No.","First Name","Middle Name","Last Name",
                         "Gender","Date of Birth","Civil Status","Email Address",
                         "Mobile","Telephone","Address","Birth Place","Town/City",
                         "Province","Country","ZIP Code","Status","Employment Status","Date Hired","Evaluation Date",
                         "Department","Position","Company","Batch","Tax Exemption","Bank","Bank Account",
                         "SSS No.","Philhealth","Pagibig","TIN","HMO No.","Cola/Day","Salary/Month",
                         "Access Level","Biometric ID");
    }
    
    function index()
    {
        //default title and url
        $this->data['page']['parent'] = 'Control Panel';
        $this->data['page']['parent_url'] = '#';
        
        //start of sample
        $samples = array("1234","Juan","Reyes","Dela Cruz",
                         "male/female","05/29/1992","single/married","test@yahoo.com",
                         "8888","911","Makati City","Nueva Ecija","San Leonardo",
                         "Nueva Ecija","Philippines","3102",$this->enumerateStatus(),
                         $this->enumerateEmploymentStatus(),"12/01/2014","12/01/2016",
                         $this->enumerateDepartment(),$this->enumeratePosition(),
                         $this->enumerateCompany(),$this->enumerateBatch(), $this->enumerateTaxExemption(),
                         $this->enumerateBank(),"123456789",
                         "1245","125","124","1245","1245","150","15000",
                         $this->enumerateLevel(),"3");
        //open an existing csv file
        $file = fopen("csv/sample_employee_list.csv","w");

        //put contents to the csv file
        fputcsv($file,$this->headers);
        fputcsv($file,$samples);
        fclose($file);
        //end of sample file
        
        //start of new file
        $file = fopen("csv/employee_list.csv","w");

        fputcsv($file,$this->headers);
        $employees = User::get()->all();
        
        //loop through each employees
        foreach($employees as $key=>$employee){
            $data = array($employee['employee_no'], utf8_decode($employee['first_name']), 
                         utf8_decode($employee['middle_name']), 
                         utf8_decode($employee['last_name']),
                         $employee['gender'], date('Y-m-d',strtotime($employee['birth_date'])), $employee['civil_status'], $employee['email'],
                         $employee['mobile'], $employee['telephone'], $employee['address'], $employee['birth_place'], $employee['city'],
                         $employee['state'], $employee['country'], $employee['zip_code'], $this->enumerateStatus($employee['active_status']),
                         $this->enumerateEmploymentStatus($employee['employee_status']), date('Y-m-d',strtotime($employee['hired_date'])), 
                         date('Y-m-d',strtotime($employee['next_evaluation'])),
                         $this->enumerateDepartment($employee['department_id']), $this->enumeratePosition($employee['position_id']),
                         $this->enumerateCompany($employee['company_id']), $this->enumerateBatch($employee['batch_id']), 
                         $this->enumerateTaxExemption($employee['tax_exemption_id']),
                         $this->enumerateBank($employee['bank_code']), $employee['bank_number'],
                         $employee['sss_no'], $employee['philhealth_no'], $employee['pagibig_no'], $employee['tin_no'],
                         $employee['hmo_no'], $employee['cola_rate'], $this->getCurrentSalary($employee['id']),
                         $this->enumerateLevel($employee['level']), $employee['biometric_no']);
            //put each employees in csv file
            fputcsv($file,$data);   
        }
        
        fclose($file);
        //end of new file
        
        return view('data_management', $this->data);
    }
    
    function uploadAttendance(Request $request){
        //uploader is located in Controller
        if($this->uploader($request , 0,  'attendance')){
            $get = file_get_contents(url('attendance/importAttendance/'.date('Y-m-d')) ."/1");
            if( $get > 0){
                //returns the uploaded count
                return redirect()->back()->with('data', $get);
            }
            
        }
        return redirect()->back()->with('data', 0);
    }
    
    function uploadEmployeeList(Request $request){
        //uploader is located in Controller
        if($this->uploader($request , 0,  'employee_list')){
            
            //open the imported file from the browser upload
            $file = fopen("csv/imported_list.csv","r");
            $key = 0;

            //loop through each lines in file
            while(!feof($file))
            {
                if($key == 0){
                  fgetcsv($file);
                  $key++;
                  continue;
                }
                $data = fgetcsv($file);
              
                //check if the employee no is present
                if($data[0] == '' OR User::where('employee_no', $data[0])->get()->all()){
                    continue;
                }
                
                //new user for the line
                $user = new User;
                $user->employee_no = $data[0];
                $user->first_name = utf8_encode($data[1]);
                $user->middle_name = utf8_encode($data[2]);
                $user->last_name = utf8_encode($data[3]);
                $user->gender = strtolower($data[4]);
                $user->birth_date = date('Y-m-d',strtotime($data[5]));
                $user->civil_status = strtolower($data[6]);
                $user->name = $data[1] ." ". $data[3];
                $user->email = $data[7];
                $user->mobile = $data[8];
                $user->telephone = $data[9];
                $user->address = $data[10];
                $user->birth_place = $data[11];
                $user->city = $data[12];
                $user->state = $data[13];
                $user->country = $data[14];
                $user->zip_code = $data[15];
                $user->about = '';
                $user->hired_date = date('Y-m-d',strtotime($data[18]));
                $user->next_evaluation = date('Y-m-d',strtotime($data[19]));
                $user->bank_number = $data[26];
                $user->active_status = $this->filterID($data[16], 'active-status');
                $user->employee_status = $this->filterID($data[17], 'employee-status');
                $user->department_id = $this->filterID($data[20], 'department');
                $user->position_id = $this->filterID($data[21], 'position');
                $user->company_id = $this->filterID($data[22], 'company');
                $user->batch_id = $this->filterID($data[23], 'batch');
                $user->tax_exemption_id = $this->filterID($data[24], 'tax-exemption');
                $user->allow_overtime = 1;
                $user->allow_adjustment = 1;
                $user->allow_leave = 1;
                $user->allow_offset = 1;
                $user->allow_travel = 1;
                $user->bank_code = $this->filterID($data[25], 'tax-exemption');
                $user->skills = '';
                $user->sss_no = $data[27];
                $user->philhealth_no = $data[28];
                $user->pagibig_no = $data[29];
                $user->tin_no = $data[30];
                $user->hmo_no = $data[31];
                $user->cola_rate = $data[32];
                $user->allow_access = 1;
                $user->allow_suspension = 1;
                $user->level = $this->filterID($data[34], 'level');
                $user->biometric_no = $data[35];
                $user->password = bcrypt('password'); 
                $user->receive_notification = 0; 
                $user->default_dashboard = 'EM'; 
                $user->grace_period_data = 'inherit'; 
                //add photo

                if($user->gender == 'male'){
                    $user->picture = 'no photo male.jpg';
                }
                else{
                     $user->picture = 'no photo female.jpg';
                }

                //finally save the employee data
                $user->save();
                
                //add salary data
                $salary = new Salary_history;
                $salary->employee_id = $user->id;
                $salary->start_date = date('Y-m-d H:i:s');
                $salary->updated_by_employee_id = Auth::user()->id;
                $salary->salary_amount = $data[33];
                //save the salary
                $salary->save();
        
                $key++;
            }

            //close the file
            fclose($file);
        }
        
        if($key>0){
            //proper redirection
            return redirect()->back()->with('employee_list', ($key-1));
        }

        //default redirection
        return redirect()->back()->with('employee_list', 0);
    }
    
    //get the ID of the string that is in the table
    function filterID($string, $table){
        if(is_numeric($string)){
            //return if id is inputed
            return $string;
        }

        // id - value separation
        if(strpos($string,'-') === FALSE){
            $id = trim(explode("-",$string)[0]);
        }
        else{
            $id = $string;
        }
        
        $get = 0;

        //choose table selection
        switch($table){
            case 'employee-status':
                if($get = EmploymentStatus::orWhere('id',$id)->orWhere('employment_status_name',$id)->get()->first()){
                     $get = $get['id'];
                }
            break;
            case 'active-status':
                $get = $this->enumerateStatus($id, true);
                
            break;
            case 'department':
                if($get = Department::orWhere('id',$id)->orWhere('department_name',$id)->get()->first()){
                     $get = $get['id'];
                }
            break;
            case 'position':
                if($get = Position::orWhere('id',$id)->orWhere('position_name',$id)->get()->first()){
                     $get = $get['id'];
                }
            break;
            case 'company':
                if($get = Company::orWhere('id',$id)->orWhere('company_name',$id)->get()->first()){
                     $get = $get['id'];
                }
            break;
            case 'batch':
               if($get = Batch::orWhere('id',$id)->orWhere('batch_name',$id)->get()->first()){
                    $get = $get['id'];
               }
            break;
            case 'tax-exemption':
                if($get = TaxExemption::orWhere('id',$id)->orWhere('tax_exemption_name',$id)->get()->first()) {
                     $get = $get['id'];
                }
            break;
            case 'bank':
                if($get = Bank::orWhere('id',$id)->orWhere('bank_name',$id)->get()->first()){
                     $get = $get['id'];
                }
            break;
            case 'level':
                if($get = UserLevel::orWhere('id',$id)->orWhere('level_name',$id)->get()->first()){
                    $get = $get['id'];
                }
            break;
        }
        
        if(is_numeric($get)){
            //return the id
            return $get;
        }
        
        //default return value
        return 0;
    }
    
    //list all employment status
    function enumerateEmploymentStatus($id=0){
        $str = '';
        $get = EmploymentStatus::get()->all();
        foreach($get as $key=> $value){
            if($id==$value['id']){
                //return id and value
                return $value['id'] . " - " . $value['employment_status_name'];
            }
            
            $str = $str . $value['id'] . " - " . $value['employment_status_name'];
            
            if( ($key+1) < sizeof($get)){
                $str = $str .", ";
            }
        }
        return $str;
    }
    
    //list all status
    function enumerateStatus($id=0, $id_only = false){
        $str = '';
        $get = array(array("id"=>1, "status_name"=>"Active"),
                     array("id"=>2, "status_name"=>"Terminated"),
                     array("id"=>3, "status_name"=>"Resigned"),
                     array("id"=>4, "status_name"=>"AWOL"));
        
        foreach($get as $key=> $value){
            if($id_only === true){
                //return id only
                return $value['id'];
            }   
            
            if($id==$value['id']){
                //return id and value
                return $value['id'] . " - " . $value['status_name'];
            }
            
            $str = $str . $value['id'] . " - " . $value['status_name'];
            
            if( ($key+1) < sizeof($get)){
                $str = $str .", ";
            }
        }
        return $str;
    }

    //list all deparments
    function enumerateDepartment($id=0){
        $str = '';
        $get = Department::get()->all();
        foreach($get as $key=> $value){
            if($id==$value['id']){
                //return id and value
                return $value['id'] . " - " . $value['department_name'];
            }
            
            $str = $str . $value['id'] . " - " . $value['department_name'];
            
            if( ($key+1) < sizeof($get)){
                $str = $str .", ";
            }
        }
        return $str;
    }
    
    //list all positions
    function enumeratePosition($id=0){
        $str = '';
        $get = Position::get()->all();
        foreach($get as $key=> $value){
            if($id==$value['id']){
                //return id and value
                return $value['id'] . " - " . $value['position_name'];
            }
            
            $str = $str . $value['id'] . " - " . $value['position_name'];
            
            if( ($key+1) < sizeof($get)){
                $str = $str .", ";
            }
        }
        return $str;
    }

    //list all companies
    function enumerateCompany($id=0){
        $str = '';
        $get = Company::get()->all();
        foreach($get as $key=> $value){
            if($id==$value['id']){
                //return id and value
                return $value['id'] . " - " . $value['company_name'];
            }
            
            $str = $str . $value['id'] . " - " . $value['company_name'];
            
            if( ($key+1) < sizeof($get)){
                $str = $str .", ";
            }
        }
        return $str;
    }

    //list all batch
    function enumerateBatch($id=0){
        $str = '';
        $get = Batch::get()->all();
        foreach($get as $key=> $value){
            if($id==$value['id']){
                //return id and value
                return $value['id'] . " - " . $value['batch_name'];
            }
            
            $str = $str . $value['id'] . " - " . $value['batch_name'];
            
            if( ($key+1) < sizeof($get)){
                $str = $str .", ";
            }
        }
        return $str;
    }

    //list all tax exemption
    function enumerateTaxExemption($id=0){
        $str = '';
        $get = TaxExemption::get()->all();
        foreach($get as $key=> $value){
            if($id==$value['id']){
                //return id and value
                return $value['id'] . " - " . $value['tax_exemption_name'];
            }
            
            $str = $str . $value['id'] . " - " . $value['tax_exemption_name'];
            
            if( ($key+1) < sizeof($get)){
                $str = $str .", ";
            }
        }
        return $str;
    }

    //list all banks
    function enumerateBank($id=0){
        $str = '';
        $get = Bank::get()->all();
        foreach($get as $key=> $value){
            if($id==$value['id']){
                //return id and value
                return $value['id'] . " - " . $value['bank_name'];
            }
            
            $str = $str . $value['id'] . " - " . $value['bank_name'];
            
            if( ($key+1) < sizeof($get)){
                $str = $str .", ";
            }
        }
        return $str;
    }

    //list all levels
    function enumerateLevel($id=0){
        $str = '';
        $get = UserLevel::get()->all();
        foreach($get as $key=> $value){
            if($id==$value['id']){
                //return id and value
                return $value['id'] . " - " . $value['level_name'];
            }
            
            $str = $str . $value['id'] . " - " . $value['level_name'];
            
            if( ($key+1) < sizeof($get)){
                $str = $str .", ";
            }
        }
        return $str;
    }
}