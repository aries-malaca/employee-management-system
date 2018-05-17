<?php
namespace ExactivEM\Libraries;
use ExactivEM\Salary_history;
use ExactivEM\User;
use ExactivEM\Position;
use ExactivEM\Transaction;
use ExactivEM\EmploymentStatus;
use ExactivEM\Config;
use ExactivEM\TransactionCode;
use ExactivEM\TaxExemption;
use ExactivEM\Contribution;
use ExactivEM\Payslip;
use ExactivEM\EmployeeRequest;
use ExactivEM\Libraries\Attendance_Class;
class Payroll_Class {

    function __construct($employee_id, $start, $end){
        $this->defaults = array(
            'is_daily_employee'=>false,
            'working_days'=>11,
            'sss_id'=>1,
            'tax_id'=>2,
            'philhealth_id'=>3,
            'pagibig_id'=>4,
            'halfday_tardiness_minutes'=>240,
            'flexible_working_days'=>false,
        );
        $this->on_maternity_leave = false;
        $this->taxable_aditions = 0;
        $this->start = $start;
        $this->end = $end;
        $this->employee = User::find($employee_id); //object type
        $this->position = Position::find($this->employee->position_id);

        if(json_decode($this->position->position_data)->salary_frequency == 'daily')
            $this->is_daily_employee = true;
        else
            $this->is_daily_employee = $this->defaults['is_daily_employee'];


        $first = json_decode(Config::find(10)->value, true);
        $second = json_decode(Config::find(11)->value, true);

        if($first[0] == date('d',strtotime($this->start)) AND $first[1] == date('d',strtotime($this->end)))
            $this->cutoff = 'first';
        elseif($second[0] == date('d',strtotime($this->start)) AND $second[1] == date('d',strtotime($this->end)))
            $this->cutoff = 'second';
        elseif($second[0] == date('d',strtotime($this->start)) AND date('t',strtotime($this->end)) == date('d',strtotime($this->end)))
            $this->cutoff = 'second';
    }

    public function getContributions($salary_credit){
        $first_cutoff = Payslip::where('employee_id', $this->employee->id)
                                ->where('date_start','NOT LIKE', $this->start. '%')
                                ->orderBy('created_at','DESC')
                                ->get()->first();

        $original_credit = $salary_credit;
        $contributions = Contribution::leftJoin('transaction_codes', 'contributions.transaction_code_id','=','transaction_codes.id')
                                        ->get()->all();

        $rows = array();
        foreach($contributions as $key=> $contribution){
            $amount =0;
            $taxable_amount =0;
            $salary_credit = $original_credit;

            if(!in_array($contribution['transaction_code_id'], explode(',',$this->employee->contributions) ) )
                $amount = 0;
            else{
                $data = json_decode($contribution['contribution_data'],true);

                if($contribution['transaction_code_id'] == $this->defaults['pagibig_id'] ){ //pagibig is first cutoff
                    if($this->cutoff .' cutoff' == 'first cutoff'){
                        $mode = 'monthly';
                        $amount = $data[$mode];
                    }
                    $taxable_amount = $amount/2;
                }
                elseif($contribution['transaction_code_id'] == $this->defaults['philhealth_id']){ //philhealth
                    $computed = $salary_credit * 0.01375;
                    $final = ($computed<137.50 ? 137.50: $computed);

                    if($this->cutoff=='second' && isset($first_cutoff['id'])){
                        $first_cutoff_data = json_decode($first_cutoff['payslip_data'],true);

                        foreach($first_cutoff_data['contributions'] as $kkkk=>$vvvv){
                            if($vvvv['name'] == 'Philhealth'){
                                $overall = $salary_credit + $first_cutoff_data['gross_pay'];

                                $computed = $overall * 0.01375;
                                $final = $computed - $vvvv['amount'];

                                if($final <0 ){
                                    $final = 0;
                                }
                            }
                        }
                    }

                    $amount = $final;
                    $this->ph_employer = $final;
                }
                elseif($contribution['transaction_code_id'] == $this->defaults['sss_id']){ //sss
                    $this->sss_employer = 0;
                    foreach($data['data'] as $key=> $value){
                        if($value[0]['from'] <= $salary_credit AND $value[0]['to'] >= $salary_credit){
                            $amount = $value[0]['employee'];
                            $this->sss_employer = $value[0]['employer'];
                            break;
                        }
                    }

                    if($this->cutoff .' cutoff' != 'first cutoff'){
                        //find first cutoff data
                        if(isset($first_cutoff['payslip_data'])){
                            $new_gross_pay = json_decode($first_cutoff['payslip_data'],true)['gross_pay'] + $salary_credit;
                            $deducted_sss = 0;
                            foreach (json_decode($first_cutoff['payslip_data'],true)['contributions'] as $con){
                                if($con['name']=='SSS')
                                    $deducted_sss = $con['amount'];
                            }

                            foreach($data['data'] as $key=> $value){
                                if($value[0]['from'] <= $new_gross_pay AND $value[0]['to'] >= $new_gross_pay){
                                    $amount = $value[0]['employee'] - $deducted_sss;
                                    break;
                                }
                            }
                        }
                    }
                    $taxable_amount = $amount;
                }
            }

            $rows[] = array("name" => $contribution['contribution_name'],
                            "amount" => $salary_credit>$amount?$amount:0,
                            "is_taxable" => $contribution['is_taxable'],
                            "taxable_amount" => $taxable_amount );
        }

        return $rows;
    }

    public function getTransactions($type){
        $trans = Transaction::leftJoin('transaction_codes', 'transactions.transaction_code_id','=','transaction_codes.id')
                                ->where('employee_id', $this->employee->id)
                                ->where('start_date', '<=', $this->start)
                                ->where('end_date', '>=', $this->start)
                                ->where('transaction_type', $type)
                                ->whereIn('transactions.cutoff', ['every cutoff',$this->cutoff.' cutoff'])
                                ->get()->toArray();
        $data = array("total"=>0,"transactions"=>array());
        
        foreach($trans as $tran){
            if($this->on_maternity_leave || $this->days_worked==0){
                $tran['amount'] = 0 ;
            }
            $data['transactions'][] = array("amount"=>$tran['amount'],
                                            "name"=>$tran['transaction_name'],
                                            "code"=>$tran['transaction_code_id'],
                                            "notes"=>$tran['notes'],
                                            "taxable_amount"=> $tran['is_taxable'] == 1? $tran['amount']:0,
                                            "is_taxable"=>$tran['is_taxable']);
        }

        if($type=='addition'){
            $find = EmployeeRequest::where('request_type', 'salary_adjustment')
                                    ->where('employee_id', $this->employee->id)
                                    ->get()->toArray();

            foreach($find as $key=>$value){
                $d = json_decode($value['request_data']);

                if($this->on_maternity_leave){
                    $d->amount = 0 ;
                }

                if(isset($d->target))
                    if($d->status === 'approved' && $d->target == $this->start){
                        $data['transactions'][] = array("amount"=>$d->amount,
                                                        "name"=>'Salary Adjustment',
                                                        "code"=>0,
                                                        "taxable_amount"=> $d->amount,
                                                        "notes"=>$value['request_note'] ,
                                                        "is_taxable"=>1);
                    }
            }
        }

        $data['total'] = $this->getTotalSum($data['transactions']);

        return $data;
    }
    
    public function getRates(){
        if($this->on_maternity_leave){
            return array("daily"=>0,
                "monthly"=>0,
                "minute"=>0
            );
        }

        $sal = Salary_history::where('employee_id', $this->employee->id)
                                ->where('start_date', '<=',$this->start )
                                ->orderBy('id','DESC')
                                ->get()->first()['salary_amount'];

        if($this->is_daily_employee === true){
            $daily_rate = $sal;
            $sal = $sal * (2 * $this->getStandardWorkingDays() );
        }
        else{
            $daily_rate = $sal/($this->getStandardWorkingDays()==11?261:313);
            $daily_rate = $daily_rate*12;
        }

        return array("daily"=>$daily_rate,
                     "monthly"=>$sal,
                     "minute"=>($daily_rate /480)
                    );
    }
    
    public function getStandardWorkingDays(){
        $get = json_decode($this->position->position_data);

        if(isset($get->standard_days))
            return $get->standard_days;

        return $this->default->working_days;
    }
    
    function computeTax($taxable, $mode = 'semimonthly'){
        if(!in_array($this->defaults['tax_id'], explode(',',$this->employee->contributions) ) ){
            return 0;
        }

        $tax_exemption = json_decode(TaxExemption::find($this->employee->tax_exemption_id)->tax_exemption_data,true)[$mode];
        foreach($tax_exemption as $key=> $value){
           if($value[0] > $taxable){
               if($key>0)
                   return $tax_exemption[$key-1][1] + ( ($taxable- $tax_exemption[$key-1][0]) *  $tax_exemption[$key-1][2]);
               else
                   return $tax_exemption[$key][1] + ( ($taxable- $tax_exemption[$key][0]) *  $tax_exemption[$key][2]);
           } 
        }

        return 0;
    }
    
    public function computePayroll(){
        $start = strtotime($this->start);
        $absents = 0; $half_days = 0; $late_minutes = 0; $under_time_minutes = 0; $count_schedules = 0; $holiday_work=0; $paid_leaves=0;
        $leaves = array();
        $holidays = array();
        $overtimes = array();
        $i = 0;
        while($start <= strtotime($this->end)) {
            $att = new Attendance_Class($this->employee->id, date('Y-m-d', $start));
            $branch = $att->getBranch();
            $remarks = $att->getRemarks();
            //emergency date will be free


            //get values using attendance class
            $absents += $att->isAbsent($this->is_daily_employee)?1:0;
     
            $late = $att->getLate();
            $under_time = $att->getUndertime();
            $leave = $att->getLeave();
            $holiday = $att->getHoliday();
            $count_schedules += ($att->hasSchedule()?1:0);

            //leaves
            if($leave !== false){
                if(!$this->is_daily_employee)
                    if($att->hasSchedule())
                        $leaves[] = $leave;

                if($leave['is_maternity'] && $i==0){
                    $this->on_maternity_leave = true;
                }
                else{
                    if($this->is_daily_employee)
                        $leaves[] = $leave;
                }
            }

            if(!is_array($holiday)) {
                $overtimes[] = array("regular_overtime" => $att->getRegularOT(),
                                    "restday_overtime" => $att->getRestdayOT(),
                                    "restday_beyond_overtime" => $att->getRestdayOT('beyond'),
                                    "restday_nightdiff" => $att->getRestdayOT('nightdiff'),
                                    "regular_nightdiff" => $att->getRegularOT('nightdiff')
                );
            }
            else{
                $data["absent_workday"] = 0;//
                $data["absent_restday"] = 0;//
                $data["present_workday"] = 0;
                $data["present_restday"] = 0;
                $data["beyond_workday"] = 0;
                $data["beyond_restday"] = 0;
                $logs = $att->getLogs();

                if(!$holiday['is_restday'] AND !$logs ){
                    if($holiday['rates']['absent_workday'] >= 1){
                        $holiday_work++;
                        if(!$this->is_daily_employee)
                            $data["absent_workday"] = 480 * ($holiday['rates']['absent_workday'] - 1);
                        else{
                            if($leave['is_paid'])
                                $data["absent_workday"] = 480 * ($holiday['rates']['absent_workday'] -1);
                            else
                                $data["absent_workday"] = 480 * ($holiday['rates']['absent_workday']);
                        }
                    }
                    else{
                        $data["absent_workday"] = 480;
                    }
                }

                if ($holiday['is_restday'] AND (!$logs OR !$att->getOvertime() ) ){
                    $holiday_work++;

                    if($this->is_daily_employee && $holiday['holiday_type'] == 1)
                        $data["absent_restday"] = 480;

                }

                if (!$holiday['is_restday'] AND ( $logs !==false) ){
                    if(in_array("no-timeout", $remarks) ) {
                        $data["present_workday"] = 480;
                    }
                    elseif(in_array("present", $remarks) ){
                        $data["present_workday"] = 480 - ($under_time + $late);
                    }

                }

                $data["beyond_workday"] = $att->getRegularOT();
                $data["present_restday"] = $att->getRestdayOT();
                $data["beyond_restday"] = $att->getRestdayOT('beyond');
                $data["nightdiff_restday"] = $att->getRestdayOT('nightdiff');
                $data["nightdiff_workday"] = $att->getRegularOT('nightdiff');

                $copy = $holiday;
                $copy["rates"]["nightdiff_workday"] = $copy["rates"]["present_workday"] * Config::find(19)->value;
                $copy["rates"]["nightdiff_restday"] = $copy["rates"]["present_workday"] * Config::find(20)->value;
                $copy["values"] = $data;
                $copy["totals"] = array(
                    "absent_workday"=> ( ($this->is_daily_employee && $copy['holiday_type']===2?0: ($copy["rates"]["absent_workday"]) * ($copy["values"]["absent_workday"]/480)) * $this->getRates()['daily']),
                    "present_workday"=> ( ($copy["rates"]["present_workday"] - 1) * ($copy["values"]["present_workday"]/480)) * $this->getRates()['daily'],
                    "absent_restday"=> ($copy["rates"]["absent_restday"] * ($copy["values"]["absent_restday"]/480)) * $this->getRates()['daily'],
                    "present_restday"=> ($copy["rates"]["present_restday"] * ($copy["values"]["present_restday"]/480)) * $this->getRates()['daily'],
                    "beyond_workday"=> ($copy["rates"]["beyond_workday"]) * ( $copy["values"]["beyond_workday"] * $this->getRates()['minute'] ),
                    "beyond_restday"=> $copy["rates"]["beyond_restday"] * ( ($copy["values"]["beyond_restday"] * $this->getRates()['minute']) ),
                    "nightdiff_workday"=> ($copy["rates"]["nightdiff_workday"] * ($copy["values"]["nightdiff_workday"] * $this->getRates()['minute'])),
                    "nightdiff_restday"=> ($copy["rates"]["nightdiff_restday"] * ($copy["values"]["nightdiff_restday"] * $this->getRates()['minute']))
                );
                $holidays[] = $copy;
            }

            $late_minutes += ($late?$late:0);
            $under_time_minutes += ($under_time?$under_time:0);

            //late
            if($att->isHalfDay($late, $this->defaults['halfday_tardiness_minutes']) && $this->is_daily_employee){
                $half_days ++;
                $late_minutes -= $this->defaults['halfday_tardiness_minutes'];
            }

            //undertime
            if($att->isHalfDay($under_time, $this->defaults['halfday_tardiness_minutes']) && $this->is_daily_employee){
                $half_days ++;
                $under_time_minutes -= $this->defaults['halfday_tardiness_minutes'];
            }

            $start += 86400;
            $i ++;
        }

        //working days is conditional
        $working_days =  ( $this->is_daily_employee === true || $this->defaults['flexible_working_days'] ? $count_schedules : $this->getStandardWorkingDays());



        //get values
        $holidays = $this->computeHoliday($holidays);
        $overtimes = $this->computeOvertime($overtimes);
        $paid_leaves = $this->getPaidLeaves($leaves);


        $days_worked = ($working_days-($absents+($half_days/2)));
        $this->days_worked = $days_worked;

        $additions = $this->getTransactions('addition');
        $deductions = $this->getTransactions('deduction');

        $ecola = ($days_worked * $this->employee->cola_rate);
        if($this->on_maternity_leave)
            $ecola = 0;


        $under_time_amount = $under_time_minutes * $this->getRates()['minute'];
        $late_amount = $late_minutes * $this->getRates()['minute'];

        $tardiness = $under_time_amount+$late_amount;

        $tardiness = !$this->is_daily_employee?(($absents) * $this->getRates()['daily']) + $tardiness:$tardiness;

        $basic_pay = $this->getBasicPay($days_worked);
        $leave_credit = $paid_leaves * ($this->getRates()['daily'] + $this->employee->cola_rate);

        if(!$this->is_daily_employee){
            $tardiness = $tardiness-$leave_credit + ($paid_leaves*$this->employee->cola_rate);
            $leave_credit = 0;
            $absents = $absents - $paid_leaves;
        }


        if($tardiness<0){
            $tardiness = 0;
        }

        if($this->on_maternity_leave){
            $working_days=0;
            $days_worked=0;
            $absents=0;
            $basic_pay = 0;
            $leave_credit = 0;
            $paid_leaves = 0;
            $tardiness = 0;
        }


        $gross_pay = ($basic_pay - $tardiness) + $overtimes['total'] + $ecola + $leave_credit + $this->getTotalSum2($holidays);
        $total_pay = $gross_pay + $additions['total'];

        if($this->on_maternity_leave){
            $total_pay = 0;
        }

        $contributions = $this->getContributions($gross_pay);
        $taxable_deductions = 0;
        $taxable_deductions_amount = 0;
        $untaxable_deductions = 0;
        $untaxable_deductions_amount = 0;
        $taxable = 0;
        $untaxable = 0;
        $deductions_total=0;

        foreach($contributions as $contribution){
            if($contribution['is_taxable']){
                $taxable_deductions += $contribution['amount'];
                $taxable_deductions_amount += $contribution['taxable_amount'];
            }
            else{
                $untaxable_deductions += $contribution['amount'];
                $untaxable_deductions_amount += $contribution['taxable_amount'];
            }
            $deductions_total+=$contribution['amount'];
        }
        
        //compute additions
        foreach($additions['transactions'] as $addition){
            if($addition['is_taxable'])
                $taxable += $addition['amount'];
            else
                $untaxable += $addition['amount'];
        }
        //compute deductions

        foreach($deductions['transactions'] as $kkk=> $deduction){
            if($gross_pay==0)
                $deductions['transactions'][$kkk]['amount'] = 0;

            if($deduction['is_taxable'])
                $taxable_deductions += $deductions['transactions'][$kkk]['amount'];
            else
                $untaxable_deductions += $deductions['transactions'][$kkk]['amount'];

            $deductions_total+=$deduction['amount'];
        }
      
        $taxable =  ( $gross_pay - $untaxable_deductions) + $taxable;

        if($taxable<0)
            $taxable = 0;

        $tax = $this->computeTax($taxable);

        if(Config::find(28)->value >= $this->getRates()['daily'])
            $tax = 0;

        if(Config::find(29)->value >= $this->getRates()['monthly'] && !$this->is_daily_employee)
            $tax = 0;

        if($tax<0)
            $tax = 0;

        $deductions_total+=$tax;

        if($this->on_maternity_leave){
            $deductions_total = 0;
        }

        if($total_pay<$deductions_total OR $total_pay == 0){
            $deductions_total = 0;
        }
        $net_pay = $total_pay - $deductions_total;

        return array(
            "working_days"=>$working_days,
            "days_worked"=>$days_worked,
            "absents"=>$absents,
            "tax_exemption"=>$this->employee->tax_exemption_id,
            "employee_status"=>$this->employee->employee_status,
            "salary_rates"=>$this->getRates(),
            "is_daily"=> $this->is_daily_employee,

            "deductions"=> $deductions,
            "additions"=> $additions,
            "contributions"=>$contributions,
            "branch_id"=> isset($branch['branch_id'])?$branch['branch_id']:0,

            "late_minutes"=>$late_minutes,
            "late_amount"=>$late_minutes * $this->getRates()['minute'],
            "under_time_minutes"=>$under_time_minutes,
            "under_time_amount"=>$under_time_amount,
            "half_days"=>$half_days,

            "leaves"=>$leaves,
            "holidays"=>$holidays,
            "overtimes"=>$overtimes,
            "paid_leaves"=>$paid_leaves,
            "leave_credit"=> $leave_credit,
            "basic_pay"=>$basic_pay,
            "ecola"=>$ecola,
            "cola_rate"=>$this->employee->cola_rate,
            "gross_pay"=>$gross_pay,
            "total_pay"=>$total_pay,
            "tax"=>$tax,
            "taxable"=>$taxable,
            "deductions_total"=>$deductions_total,
            "net_pay"=>$net_pay,
            "sss_employer"=>$this->sss_employer,
            "ph_employer"=>$this->ph_employer,
            "regular_holiday"=> $this->extractHoliday(1, 'present_workday', $holidays),
            "regular_holiday_ot"=> $this->extractHoliday(1, 'beyond_workday', $holidays),
            "regular_holiday_restday"=> $this->extractHoliday(1, 'present_restday', $holidays),
            "regular_holiday_restday_beyond"=> $this->extractHoliday(1, 'beyond_restday', $holidays),
            "regular_holiday_nightdiff"=> $this->extractHoliday(1, 'nightdiff_workday', $holidays),

            "special_holiday"=> $this->extractHoliday(2, 'present_workday', $holidays),
            "special_holiday_ot"=> $this->extractHoliday(2, 'beyond_workday', $holidays),
            "special_holiday_restday"=> $this->extractHoliday(2, 'present_restday', $holidays),
            "special_holiday_restday_beyond"=> $this->extractHoliday(2, 'beyond_restday', $holidays),
            "special_holiday_nightdiff"=> $this->extractHoliday(2, 'nightdiff_workday', $holidays),
            "holiday_pay"=> $this->getTotalSum2($holidays),
            "tardiness"=>$tardiness
        );
    }

    function computeOvertime($overtimes){
        $array = array("total"=>0,
                       "regular_overtime"=> array("minutes"=>0, "rate"=> 0, "total"=> 0),
                       "restday_overtime"=> array("minutes"=>0, "rate"=> 0, "total"=> 0),
                       "restday_beyond_overtime"=> array("minutes"=>0, "rate"=> 0, "total"=> 0),
                       "restday_nightdiff"=> array("minutes"=>0, "rate"=> 0, "total"=> 0),
                       "regular_nightdiff"=> array("minutes"=>0, "rate"=> 0, "total"=> 0),
                    );
        foreach($overtimes as $key=>$value){
            $array['regular_overtime']['minutes'] += $value['regular_overtime'];
            $array['restday_overtime']['minutes'] += $value['restday_overtime'];
            $array['restday_beyond_overtime']['minutes'] += $value['restday_beyond_overtime'];
            $array['restday_nightdiff']['minutes'] += $value['restday_nightdiff'];
            $array['regular_nightdiff']['minutes'] += $value['regular_nightdiff'];
        }

        $array['regular_overtime']['rate'] = Config::find(16)->value;
        $array['restday_overtime']['rate'] = Config::find(17)->value;
        $array['restday_beyond_overtime']['rate'] = Config::find(18)->value;
        $array['restday_nightdiff']['rate'] = Config::find(20)->value;
        $array['regular_nightdiff']['rate'] = Config::find(19)->value;

        $array['regular_overtime']['total'] = $this->getTotal($array['regular_overtime']);
        $array['restday_overtime']['total'] = $this->getTotal($array['restday_overtime']);
        $array['restday_beyond_overtime']['total'] = $this->getTotal($array['restday_beyond_overtime']);
        $array['restday_nightdiff']['total'] = $this->getTotal($array['restday_nightdiff']);
        $array['regular_nightdiff']['total'] = $this->getTotal($array['regular_nightdiff']);

        $array['total'] = ($array['regular_overtime']['total'] + $array['restday_overtime']['total']+ $array['restday_beyond_overtime']['total']
                            + $array['restday_nightdiff']['total'] +$array['regular_nightdiff']['total']);

        return $array;
    }

    function getBasicPay($days_worked){
        if($this->is_daily_employee === true)
            return  ($this->getRates()['daily'] * $days_worked);

        return $this->getRates()['monthly']/2;
    }

    function computeHoliday($holidays){
        return $holidays;
    }

    function getPaidLeaves($leaves){
        $count = 0;
        foreach($leaves as $key=>$value){
            if($value['days'] >= 1){
                $count += $value['is_paid']?1:0;
            }
        }


        return $count;
    }

    function isAbsent($remarks){
        if(sizeof($remarks) > 0){
            if(in_array('absent',$remarks))
                return true;
        }

        return false;
    }

    function extractHoliday($holiday_type_id, $field, $data){
        $total_minutes = 0;
        $rate = 0;

        foreach($data as $key=>$value){
            if($holiday_type_id == $value['holiday_type']){
                $rate = $value['rates'][$field];
                $total_minutes += $value['values'][$field];
            }

        }

        if($rate>1 && strpos($field, 'beyond') === false )
            $rate--;

        return array("rate"=>$rate, "minutes"=> $total_minutes);
    }

    function getTotal($array){
        return $array['minutes'] * $array['rate'] * $this->getRates()['minute'];
    }
    
    function getTotalSum($array){
        $sum = 0;
        foreach($array as $value)
            $sum += $value['amount'] ;

        return $sum;
    }

    function getTotalSum2($array){
        $sum = 0;
        foreach($array as $value){
            foreach($value['totals'] as $v){
                $sum += $v;
            }
        }

        return $sum;
    }
}