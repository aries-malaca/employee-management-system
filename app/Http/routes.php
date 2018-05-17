<?php
/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/
Use ExactivEM\User;
use ExactivEM\ScheduleHistory;
use ExactivEM\EmployeeRequest;
use ExactivEM\Branch;
use ExactivEM\Attendance;
use Illuminate\Http\Request;
use ExactivEM\Http\Requests;
Route::group(['middleware' => ['web']], function () {
    Route::auth();
    Route::get('/' ,  'HomeController@index');
    Route::post('/auth/login' ,  'UserController@login');
    Route::get('/auth/logout' ,  'UserController@logout');
    Route::post('/auth/forgot' ,  'UserController@forgotPassword');
    Route::get('/requestPassword/{email}/{remember_token}' ,  'UserController@requestPassword');
    Route::get('/forgot' ,  'UserController@forgotView');
});

Route::group(['middleware' => 'web'], function () {
    /*
    | APPLICATION ROOT ROUTES
    */
    Route::auth();

    //page routes - served using blade
    Route::get('/backups', 'BackupController@index');
    Route::get('/banks', 'BankController@index');
    Route::get('/batches', 'BatchController@index');
    Route::get('/branches', 'BranchController@index');
    Route::get('/calendar', 'CalendarController@index');
    Route::get('/companies', 'CompanyController@index');
    Route::get('/contributions', 'ContributionController@index');
    Route::get('/departments', 'DepartmentController@index');
    Route::get('/emergencies', 'EmergencyController@index');
    Route::get('/employees', 'EmployeesController@index');
    Route::get('/employee/{id}', 'EmployeesController@viewEmployee');
    Route::get('/employment', 'EmploymentController@index');
    Route::get('/forms', 'FormController@index');
    Route::get('/holidays', 'HolidayController@index');
    Route::get('/home', 'HomeController@index');
    Route::get('/leave_types', 'LeavetypeController@index');
    Route::get('/logs', 'LogsController@index');
    Route::get('/logs/{scope}', 'LogsController@viewLogs');
    Route::get('/mail', 'MailController@index');
    Route::get('/news', 'NewsController@index');
    Route::get('/payroll', 'PayrollController@index');
    Route::get('/premiums', 'PremiumsController@index');
    Route::get('/positions', 'PositionController@index');
    Route::get('/profile', 'ProfileController@index');
    Route::get('/reports', 'ReportController@index');
    Route::get('/requests', 'RequestController@index');
    Route::get('/schedules', 'ScheduleController@index');
    Route::get('/transactions', 'TransactionController@index');
    Route::get('/trainees', 'TraineeController@index');
    //end page routes

    //attendance API
    Route::get('attendances/tuneupAttendances/{branch_id}', 'AttendanceController@tuneupAttendances');
    Route::post('attendance/deleteAttendance', 'AttendanceController@deleteAttendance');
    Route::post('attendance/deleteLeave', 'AttendanceController@deleteLeave');
    Route::get('/attendance/getPresentEmployees/{date}', 'AttendanceController@getPresentEmployees');
    Route::post('attendance/submitAttendances', 'AttendanceController@submitAttendances');
    Route::post('attendance/saveLeave', 'AttendanceController@saveLeave');
    Route::post('attendance/lockSchedules', 'AttendanceController@lockSchedules');
    Route::get('attendance/getAttendanceErrors/{branch_id}/{start}/{end}', 'AttendanceController@getAttendanceErrors');
    Route::post('attendance/fixSchedule', 'AttendanceController@fixSchedule');
    //end attendance API

    //banks API
    Route::get('/banks/getBanks', 'BankController@getBanks');
    Route::post('/banks/addBank', 'BankController@addBank');
    Route::post('/banks/updateBank', 'BankController@updateBank');
    Route::post('/banks/deleteBank', 'BankController@deleteBank');
    Route::post('/banks/updateEmployeeAccount', 'BankController@updateEmployeeAccount');
    //end banks API

    //batches API
    Route::get('/batches/getBatches', 'BatchController@getBatches');
    Route::post('/batches/addBatch', 'BatchController@addBatch');
    Route::post('/batches/updateBatch', 'BatchController@updateBatch');
    Route::post('/batches/deleteBatch', 'BatchController@deleteBatch');
    //end batches API

    //birthday API
    Route::get('/birthday/broadcastCelebrant/{month}/{day}', 'RestfullController@broadcastCelebrant');
    Route::get('/birthday/broadcastCelebrant/{month}', 'RestfullController@broadcastCelebrant');
    //end birthday API

    //branches API
    Route::get('/branches/getBranches', 'BranchController@getBranches');
    Route::get('/branches/getAllBranches', 'BranchController@getAllBranches');
    Route::post('/branches/addBranch', 'BranchController@addBranch');
    Route::post('/branches/updateBranch', 'BranchController@updateBranch');
    Route::post('/branches/deleteSchedule', 'BranchController@deleteSchedule');
    Route::post('/branches/addSchedule', 'BranchController@addSchedule');
    Route::post('/branches/updateSchedule', 'BranchController@updateSchedule');
    //end branches API

    //companies API
    Route::get('/companies/getCompanies', 'CompanyController@getCompanies');
    Route::post('/companies/addCompany', 'CompanyController@addCompany');
    Route::post('/companies/updateCompany', 'CompanyController@updateCompany');
    Route::post('/companies/activateCompany', 'CompanyController@activateCompany');
    Route::post('/companies/deactivateCompany', 'CompanyController@deactivateCompany');
    //end companies API

    //contributions API
    Route::post('/contributions/updateTaxExemption', 'ContributionController@updateTaxExemption');
    Route::get('/contributions/getTaxExemptions', 'ContributionController@getTaxExemptions');
    Route::get('/contributions/getContributions', 'ContributionController@getContributions');
    //end contributions API

    //departments API
    Route::get('/departments/getDepartments', 'DepartmentController@getDepartments');
    Route::post('/departments/addDepartment', 'DepartmentController@addDepartment');
    Route::post('/departments/updateDepartment', 'DepartmentController@updateDepartment');
    Route::post('/departments/deactivateDepartment', 'DepartmentController@deactivateDepartment');
    Route::post('/departments/activateDepartment', 'DepartmentController@activateDepartment');
    //end departments API

    //emergencies API
    Route::get('/emergencies/getEmergencies', 'EmergencyController@getEmergencies');
    Route::post('/emergencies/addEmergency', 'EmergencyController@addEmergency');
    Route::post('/emergencies/updateEmergency', 'EmergencyController@updateEmergency');
    Route::post('/emergencies/deleteEmergency', 'EmergencyController@deleteEmergency');
    //end emergencies API

    //employees API
    Route::get('/employees/getTempID', 'EmployeesController@getTempID');
    Route::get('/employees/getEmployees', 'EmployeesController@getEmployees');
    Route::get('/employees/getInactiveEmployees', 'EmployeesController@getInactiveEmployees');
    Route::get('/employees/getEmployees/{flag}', 'EmployeesController@getEmployees');
    Route::get('/employees/getEmployee/{id}', 'EmployeesController@getEmployee');
    Route::post('/employees/addEmployee', 'EmployeesController@addEmployee');
    Route::post('/employees/updateProfile', 'EmployeesController@updateProfile');
    Route::post('/employees/updateWork', 'EmployeesController@updateWork');
    Route::post('/employees/updateSalaryRow', 'EmployeesController@updateSalaryRow');
    Route::post('/employees/updateSystem', 'EmployeesController@updateSystem');
    Route::post('/employees/deleteEmployee', 'EmployeesController@deleteEmployee');
    Route::post('/employee/uploadProfilePicture', 'EmployeesController@uploadProfilePicture');
    Route::get('/employees/getOfficeEmployees', 'RestfullController@getOfficeEmployees');
    Route::get('/employees/getMyData/{id}', 'RestfullController@getMyData');
    Route::get('/employees/getJAS', 'RestfullController@getJAS');
    Route::get('/employees/getSAS', 'RestfullController@getSAS');
    Route::get('/employees/getAllEmployees', 'RestfullController@getAllEmployees');
    Route::get('/employees/getAuth', 'EmployeesController@getAuth');
    //end employees API

    //file API
    Route::get('/files/getFiles/{employee_id}', 'FileController@getFiles');
    Route::get('/files/getFiles', 'FileController@getFiles');
    Route::post('/files/uploadFile', 'FileController@uploadFile');
    Route::post('/files/deleteFile', 'FileController@deleteFile');
    //end file API

    //fingerprint API
    Route::get('/fingerprint/convertToJSON', 'FingerprintController@convertToJSON');
    Route::get('/fingerprint/createMasterFile', 'FingerprintController@createMasterFile');
    Route::get('/fingerprint/getMasterFile/{algorithm}', 'FingerprintController@getMasterFile');
    Route::get('/bio/getSetupFile', 'FingerprintController@getSetupFile');
    //Route::get('/fingerprint/getSDK', 'FingerprintController@getSDK');
    //Route::get('/fingerprint/getSoftware', 'FingerprintController@getSoftware');
    Route::get('/fingerprint/logEvent/exported/{branch_id}', 'FingerprintController@logEvent');
    Route::get('/fingerprint/logEvent/imported/{branch_id}/{count}', 'FingerprintController@logEvent');
    Route::get('/fingerprint/collectLogs/{id}', 'FingerprintController@collectLogs');
    Route::get('/fingerprint/getEnrolledCount', 'FingerprintController@getEnrolledCount');
    Route::get('/fingerprint/getUnsync', 'FingerprintController@getUnsync');
    Route::get('/fingerprint/latestCount', 'FingerprintController@latestCount');
    //end fingerprint API

    //forms API
    Route::post('/forms/addAdjustment', 'FormController@addAdjustment');
    Route::post('/forms/addOffset', 'FormController@addOffset');
    Route::post('/forms/addLeave', 'FormController@addLeave');
    Route::post('/forms/addOvertime', 'FormController@addOvertime');
    Route::post('/forms/addTravel', 'FormController@addTravel');
    Route::post('/forms/addSchedule', 'FormController@addSchedule');
    Route::post('/forms/addSalaryAdjustment', 'FormController@addSalaryAdjustment');
    //end forms  API

    //leaves API
    Route::get('/leave_types/getLeaveTypes', 'LeavetypeController@getLeaveTypes');
    Route::get('/leave_types/getCustomLeaves/{id}', 'LeavetypeController@getCustomLeaves');
    Route::get('/leave_types/getLeaveTypes/{id}', 'LeavetypeController@getLeaveTypes');
    Route::post('/leave_types/addLeaveType', 'LeavetypeController@addLeaveType');
    Route::post('/leave_types/updateLeaveType', 'LeavetypeController@updateLeaveType');
    Route::post('/leave/updateLeaveCredit', 'LeavetypeController@updateLeaveCredit');
    //end leaves API

    //logs API
    Route::get('/logs/api/getMyLogs', 'LogsController@getMyLogs');
    Route::get('/logs/api/getEmployeeLogs', 'LogsController@getEmployeeLogs');
    //end logs API

    //news API
    Route::post('/news/addNews', 'NewsController@addNews');
    Route::post('/news/updateNews', 'NewsController@updateNews');
    Route::post('/news/deleteNews', 'NewsController@deleteNews');
    Route::get('/news/getNews', 'NewsController@getNews');
    Route::get('/news/getActiveNews', 'NewsController@getActiveNews');
    //end news API

    //notifications API
    Route::get('/notifications/getNotifications', 'NotificationController@getNotifications');
    Route::get('/notifications/generateNotifications/{date}', 'NotificationController@generateNotifications');
    Route::get('/notifications/sendAbsentNotification/{date}', 'NotificationController@sendAbsentNotification');
    Route::post('/notifications/seenNotification', 'NotificationController@seenNotification');
    Route::get('/notifications/cleanNotifications', 'NotificationController@cleanNotifications');
    //end notifications API

    //payroll API
    Route::post('/payroll/deletePayroll', 'PayrollController@deletePayroll');
    Route::post('/payroll/publishPayroll', 'PayrollController@publishPayroll');
    Route::post('/payroll/draftPayroll', 'PayrollController@draftPayroll');
    Route::get('/payroll/previewSingle/{id}', 'PayrollController@previewSingle');
    Route::get('/payroll/previewMultiple/{type}/{id}/{flag}', 'PayrollController@previewMultiple');

    Route::get('/payroll/getPayrolls', 'PayrollController@getPayrolls');
    Route::get('/payroll/getPayslips/{employee_id}', 'PayrollController@getPayslips');
    Route::post('/payroll/preparePayroll', 'PayrollController@preparePayroll');
    Route::post('/payroll/generatePayroll', 'PayrollController@generatePayroll');
    Route::post('/payroll/generateReport', 'PayrollController@generateReport');
    Route::post('/payroll/generateOT', 'PayrollController@generateOT');
    Route::post('/payroll/generateContributions', 'PayrollController@generateContributions');
    Route::get('/payroll/getPayrollPeriods/{id}', 'PayrollController@getPayrollPeriods');
    //end payroll API

    //positions API
    Route::get('/positions/getPositions', 'PositionController@getPositions');
    Route::get('/positions/getOrgChart', 'PositionController@getOrgChart');
    Route::post('/positions/addPosition', 'PositionController@addPosition');
    Route::post('/positions/updatePosition', 'PositionController@updatePosition');
    Route::post('/positions/activatePosition', 'PositionController@activatePosition');
    Route::post('/positions/deactivatePosition', 'PositionController@deactivatePosition');
    //end positions API

    //premiums API
    Route::get('/premiums/getPremiumSettings', 'PremiumsController@getPremiumSettings');
    Route::post('/premiums/updatePremiumSettings', 'PremiumsController@updatePremiumSettings');
    //end premiums API

    //profile API
    Route::post('/profile/updateProfile', 'ProfileController@updateProfile');
    Route::post('/profile/updatePassword', 'ProfileController@updatePassword');
    Route::post('/profile/uploadProfilePicture', 'ProfileController@uploadProfilePicture');
    //end profile API

    //reports API
    Route::get('/reports/getReports', 'ReportController@getReports');
    Route::post('/reports/generateReport', 'ReportController@generateReport');
    Route::post('/reports/deleteReport', 'ReportController@deleteReport');
    //end reports

    //requests API
    Route::get('/requests/getRequests/{type}/{employee_id}', 'RequestController@getRequests');
    Route::get('/requests/sendNotification/{id}/{user}', 'RequestController@sendNotification');
    Route::get('/requests/sendConfirmation/{id}', 'RequestController@sendConfirmation');
    Route::get('/requests/getRequests/{type}', 'RequestController@getRequests');
    Route::get('/requests/printRequest/{id}', 'RequestController@printRequest');
    Route::get('/requests/sanitizeRequests', 'RequestController@getRequests');
    Route::post('/requests/deleteRequest', 'RequestController@deleteRequest');
    Route::post('/requests/approveRequest', 'RequestController@approveRequest');
    Route::post('/requests/denyRequest', 'RequestController@denyRequest');
    Route::post('/requests/resetRequestApproval', 'RequestController@resetRequestApproval');
    Route::post('/requests/updateRequest', 'RequestController@updateRequest');
    Route::post('/requests/updateNotes', 'RequestController@updateNotes');
    //end requests API

    //schedules API
    Route::get('/schedules/getSchedule/{id}', 'ScheduleController@getSchedule');
    Route::post('/schedules/deleteSchedule', 'ScheduleController@deleteSchedule');
    Route::post('schedules/addSchedules', 'ScheduleController@addSchedule');
    Route::post('schedules/updateSchedule', 'ScheduleController@updateSchedule');
    Route::post('schedules/saveSingleSchedule', 'ScheduleController@saveSingleSchedule');
    Route::post('schedules/deleteSingleSchedule', 'ScheduleController@deleteSingleSchedule');
    Route::get('schedules/getBranchHistory/{branch_id}/{start}/{end}', 'ScheduleController@getBranchHistory');
    //end schedules API

    //tasks API
    Route::get('/tasks/getMyTasks', 'TaskController@getMyTasks');
    Route::get('/tasks/getEmployeeTasks', 'TaskController@getEmployeeTasks');
    Route::post('/tasks/addTask', 'TaskController@addTask');
    Route::post('/tasks/deleteTask', 'TaskController@deleteTask');
    Route::post('/tasks/updateTask', 'TaskController@updateTask');
    //end tasks API

    //transactions API
    Route::get('/transactions/getTransactionCodes', 'TransactionController@getTransactionCodes');
    Route::get('/transactions/addBulkTransactions', 'TransactionController@addBulkTransactions');
    Route::get('/transactions/getTransactions', 'TransactionController@getTransactions');
    Route::get('/transactions/getTransactions/{employee_id}', 'TransactionController@getTransactions');
    Route::post('/transactions/addTransactionCode', 'TransactionController@addTransactionCode');
    Route::post('/transactions/updateTransactionCode', 'TransactionController@updateTransactionCode');
    Route::post('/transactions/deleteTransactionCode', 'TransactionController@deleteTransactionCode');
    Route::post('/transactions/addTransaction', 'TransactionController@addTransaction');
    Route::post('/transactions/deleteTransaction', 'TransactionController@deleteTransaction');
    Route::post('/transactions/updateTransaction', 'TransactionController@updateTransaction');
    //end transactions API

    //trainees API
    Route::get('/trainees/getTrainees', 'TraineeController@getTrainees');
    Route::post('/trainees/addTrainee', 'TraineeController@addTrainee');
    Route::post('/trainees/updateTrainee', 'TraineeController@updateTrainee');
    //end trainees API

    //rest
    Route::get('/api/getPositions', 'RestfullController@getPositions');
    Route::get('/api/getDepartments', 'RestfullController@getDepartments');
    Route::get('/api/getBranches/{format}', 'RestfullController@getBranches');
    Route::get('/api/getBirthdayCelebrants/{month}', 'RestfullController@getBirthdayCelebrants');
    Route::get('/api/getBirthdayCelebrants/{month}/{day}', 'RestfullController@getBirthdayCelebrants');
    //end rest go work!

    Route::get('/api/getTechnicians', function(){
        header('Access-Control-Allow-Origin: *');
    	$data = User::leftJoin('positions', 'users.position_id','=','positions.id')
    					->whereIn('position_id', [49,69])
    					->where('active_status', 1)
    					->select('address','name','first_name','middle_name','last_name','mobile','email',
    								'civil_status','position_name','birth_date','hired_date','employee_no','users.id','picture','gender')
    					->get()->toArray();

    	foreach($data as $key=>$value){
    		$data[$key]['schedules'] = ScheduleHistory::where('employee_id', $value['id'])
                                        ->where(function($query){
                                            $query->where('schedule_start', '>=', date('Y-m-d'))
                                                ->orWhere('schedule_type', 'RANGE');
                                        })
    									->select('branch_id','schedule_data','schedule_start','schedule_end','schedule_type')
    									->get()->toArray();
    		foreach($data[$key]['schedules'] as $k=>$v){
    			$data[$key]['schedules'][$k]['schedule_data'] = $v['schedule_type']=='RANGE'?json_decode($v['schedule_data'],true):$v['schedule_data'];
    		}						
    	}
    	return response()->json($data);
    });

    Route::get('/api/getTechnicianAttendance/{employee_no}/{date}', function(Request $request){
        header('Access-Control-Allow-Origin: *');
        $user = User::where('employee_no', $request->segment(3))->get()->first();
        $data = false;
        if(isset($user['id'])){

            $ot = EmployeeRequest::where('employee_id', $user['id'])
                                ->where('request_type', 'overtime')
                                ->where('request_data','LIKE','%"date_start":"'.$request->segment(4).'"%')
                                ->select('request_data','request_note')
                                ->get()->toArray();
            foreach($ot as $key=>$value){
                $ot[$key]['request_data'] = json_decode($value['request_data']);
            }

            $data = array("attendance"=> Attendance::where('employee_id', $user['id'])
                                                ->where('date_credited','LIKE',$request->segment(4).'%')
                                                ->select('attendance_stamp','in_out')
                                                ->get()->toArray(),
                          "overtime" => $ot
                          );
        }

        return response()->json($data);
    });

    Route::get('/mysql/killProcesses', function(){
        $data = DB::select(DB::raw('SHOW FULL PROCESSLIST'));
        foreach($data as $key=>$value){
            if($value->Time>480)
                DB::statement("KILL " . $value->Id);
        }
        return response()->json($data);
    });

    Route::get('/holidays/getAsean2017', function(){
        $data = Branch::whereNotIn('id', [209,228,223,70,222,225,186,215,226,207])->pluck('id')->toArray();
        return response()->json($data);
    });

    //holidays
    Route::post('/holidays/processDelete', 'HolidayController@processDelete');
    Route::post('/holidays/processAdd', 'HolidayController@processAdd');
    Route::post('/holidays/processEdit', 'HolidayController@processEdit');
    
    Route::post('/holidays/type/processDelete', 'HolidayController@processDeleteType');
    Route::post('/holidays/type/processAdd', 'HolidayController@processAddType');
    Route::post('/holidays/type/processEdit', 'HolidayController@processEditType');

    //evaluation
    Route::get('/evaluation', 'EvaluationController@index');
    Route::post('/evaluation/processAdd', 'EvaluationController@processAdd');

    //notes
    Route::post('/calendar/notes/processAdd', 'CalendarController@processAdd');
    Route::post('/calendar/notes/processDelete', 'CalendarController@processDelete');

    //employment

    Route::post('/employment/processAdd', 'EmploymentController@processAdd');
    Route::post('/employment/processDelete', 'EmploymentController@processDelete');
    Route::post('/employment/processEdit', 'EmploymentController@processEdit');
    Route::get('/employment/getEmploymentStatuses', 'EmploymentController@getEmploymentStatuses');
    //frequency
    Route::get('/frequency', 'FrequencyController@index');

    //schedule ajax
    Route::get('ajax/checkFreshChat/{id}', 'ScheduleController@checkFreshChat');

    //collection get might be used in ajax as API
    Route::get('/collect/{model}', 'MiscController@getCollections');
    Route::get('/get/{model}/{id}', 'MiscController@getValues');
    Route::get('/fetch/Salary/{grade}/{step}', 'MiscController@fetchSalary');
    
    //logs / Attendances
    Route::get('/attendance/onScreenAddLog', 'AttendanceController@onScreenAddLog');

    //API
    Route::get('/attendance/getAttendance/{id}/flag/{no}', 'AttendanceController@getAttendance');
    Route::get('/attendance/getAttendance/{id}/{date}', 'AttendanceController@getAttendance');
    Route::get('/attendance/getPresentEmployees/{date}/{host}', 'AttendanceController@getPresentEmployees');
    Route::get('/attendance/getAbsentEmployees/{date}/{host}', 'AttendanceController@getAbsentEmployees');
    Route::get('/attendance/getTimesheet/{month}/{year}/{employee_id}', 'AttendanceController@getTimesheet');
    Route::get('/attendance/getTimesheet/{month}/{year}/{cutoff}/{employee_id}', 'AttendanceController@getTimesheet');
    //flag 0 - for all branches cron, 1 for import to file
    Route::get('attendance/importAttendance/{date}/{flag}', 'AttendanceController@importAttendance');
    Route::get('attendance/importAttendance/{date}/{flag}/{employee}', 'AttendanceController@importAttendance');

    //log seen api
    Route::get('/api/seenLogs', 'MiscController@seenLogs');
    Route::post('/api/saveLocation', 'MiscController@saveLocation');
    Route::get('/api/getIPLocation/{ip}', 'MiscController@getIPLocation');
    Route::get('/api/getUserLocationAddress/{id}', 'MiscController@getUserLocationAddress');

    //message api
    Route::post('/api/sendMessage', 'RestfullController@sendMessage');
    Route::post('/api/getConversation', 'RestfullController@getConversation');
    Route::post('/messages/deleteMessage', 'RestfullController@deleteMessage');
    Route::post('/messages/countUnreadMessages', 'RestfullController@countUnreadMessages');
    Route::get('/api/getRemainingLeaves/{id}', 'RestfullController@getRemainingLeaves');
    Route::get('/api/generatePassword/{length}', 'RestfullController@generatePassword'); //length must be even number

    //levels
    Route::get('/levels', 'UserlevelController@index');
    Route::post('/levels/processAdd', 'UserlevelController@processAdd');
    Route::post('/levels/processEdit', 'UserlevelController@processEdit');
    Route::post('/levels/processDelete', 'UserlevelController@processDelete');
    Route::get('/levels/getLevels', 'UserlevelController@getLevels');

    //backups
    Route::get('/backups/backupNow', 'BackupController@backupNow');
    Route::get('/backups/deleteBackup/{name}', 'BackupController@deleteBackup');

    //settings
    Route::get('/settings', 'ConfigController@index');
    Route::post('/settings/processEdit', 'ConfigController@processEdit');
    
    //pages
    Route::get('/pages', 'PageController@index');
    Route::post('/pages/processEdit', 'PageController@processEdit');
    //pages

    //lockscreen
    Route::get('/lockscreen', 'MiscController@lockScreen');

    //salary
    Route::get('/salaries', 'SalaryController@index');
    Route::post('/salaries/editSalaryBase', 'SalaryController@editSalaryBase');
    Route::get('/salaries/getSalaryHistory/{id}', 'SalaryController@getSalaryHistory');
    
    //data management
    Route::get('/data_management', 'DataManagementController@index');
    Route::get('/data_management/getEmployeeListTemplate', 'DataManagementController@getEmployeeListTemplate');
    Route::post('/data_management/uploadAttendance', 'DataManagementController@uploadAttendance');
    Route::post('/data_management/uploadEmployeeList', 'DataManagementController@uploadEmployeeList');
    
    //host 
    Route::get('/hosts', 'HostController@index');
    Route::post('/hosts/processDelete', 'HostController@processDelete');
    Route::post('/hosts/processEdit', 'HostController@processEdit');
    Route::post('/hosts/processAdd', 'HostController@processAdd');
    //mails

    Route::get('/mail/setupMail', 'MailController@setupMail');
    Route::get('/mail/setupMail/{error}', 'MailController@setupMail');
    Route::post('/mail/processEdit', 'MailController@processEdit');
    Route::post('/mail/sendMail', 'MailController@sendMail');
    Route::post('/mail/getMails', 'MailController@getMails');
    Route::post('/mail/deleteMail', 'MailController@deleteMail');
    Route::get('/mail/showMail/{folder}/{id}', 'MailController@showMail');
    Route::post('/mail/addFlag', 'MailController@addFlag');
    Route::post('/mail/clearFlag', 'MailController@clearFlag');
    Route::post('/mail/moveMail', 'MailController@moveMail');
});