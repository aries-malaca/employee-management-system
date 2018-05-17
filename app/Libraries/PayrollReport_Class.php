<?php
namespace ExactivEM\Libraries;
use ExactivEM\Libraries\Attendance_Class;
use ExactivEM\Company;
use ExactivEM\Batch;
use ExactivEM\User;
use ExactivEM\Department;
use ExactivEM\Position;
use ExactivEM\Config;
use ExactivEM\Branch;
use Excel;

class PayrollReport_Class extends \ExactivEM\Http\Controllers\Controller{

    function createExcelReport($data){
        $this->filename = $data['title'] .' ('.$data['date_start'].' - '. $data['date_end'] .')';
        Excel::create($this->filename, function($excel) use ($data){
            $excel->sheet('Payroll Summary', function($sheet) use ($data) {
                //make table header


            });
        })->store('xlsx',public_path('report'));

        return $this->filename;
    }
}