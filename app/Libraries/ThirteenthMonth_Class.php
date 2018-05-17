<?php
namespace ExactivEM\Libraries;
use ExactivEM\Libraries\PDF\FPDF;
use ExactivEM\Payslip;

class ThirteenthMonth_Class{

	function __construct($employee_id, $year){
		$this->employee_id = $employee_id;

		$this->data = array();
		$text_data = array();
		//get data from textfile....
		if(file_exists(public_path('records/payslips/' . $year.'.txt'))){
			$textfile = json_decode(file_get_contents(public_path('records/payslips/'.$year.'.txt')),true);
			foreach ($textfile as $key => $value) {
				if($value['id'] == $this->employee_id){
					$text_data = $value['payrolls'];
				}
			}
		}
		
		// by months
		for ($i = 1; $i <= 12; $i++) { 
			$payslips = Payslip::where('employee_id', $this->employee_id)
								 ->where('date_start', 'LIKE', $year.'-'.sprintf("%02d", $i).'%')
								 ->where('status', 'published')
								 ->get()
								 ->all();
			$net = 0;
            foreach ($payslips as $key => $value) {
            	$net+= json_decode($value['payslip_data'])->basic_pay;
            }

            //try to get from text file....
            if($net == 0 AND sizeof($text_data)> ($i-1) ){
            	$net = $text_data[($i-1)];
            }


            $this->data[] = $net;
		}
	}


	function getThirteenthMonth(){
		return $this->data;
	}


	function getTotal(){
		$total = 0;
		foreach ($this->data as $key => $value) {
			$total += $value;
		}
		return $total;
	}

	function getFinal(){
		return ($this->getTotal() / 12);
	}
}

?>