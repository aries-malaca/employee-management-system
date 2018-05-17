@extends('layouts.main')
@section('content')
<link href="../../metronic/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>
<link href="../../metronic/admin/pages/css/profile.css" rel="stylesheet" type="text/css"/>
<link href="../../metronic/admin/pages/css/tasks.css" rel="stylesheet" type="text/css"/>
<link href="../../metronic/global/plugins/fullcalendar/fullcalendar.min.css" rel="stylesheet"/>
<link href="../../assets/styles/loader.css" rel="stylesheet" type="text/css"/>

<style>
	.fc-time{display : none;}
    .table-bordered td{
        padding:4px !important;
    }
</style>

<div class="row" id="employees" v-cloak >
	<div class="col-md-12" v-show="newEmployee.id == 0">
        <div class="cssload-thecube">
            <div class="cssload-cube cssload-c1"></div>
            <div class="cssload-cube cssload-c2"></div>
            <div class="cssload-cube cssload-c4"></div>
            <div class="cssload-cube cssload-c3"></div>
        </div>
	</div>
    <div class="col-md-12" v-show="newEmployee.id != 0">
    	<!-- BEGIN PROFILE SIDEBAR -->
    	<div class="profile-sidebar">
    		<!-- PORTLET MAIN -->
    		<div class="portlet light profile-sidebar-portlet">
    			<!-- SIDEBAR USERPIC -->

                <div class="profile-userpic" v-if="display.picture!=''">
    				<img v-bind:src="'../../images/employees/' + display.picture" class="img-responsive" alt="">
    			</div>
    			<!-- END SIDEBAR USERPIC -->
    			<!-- SIDEBAR USER TITLE -->
    			<div class="profile-usertitle" style="margin-top: 10px;">
    				<a data-toggle="modal" href="#portlet-config" class="btn btn-xs btn-info"><i class="icon-refresh"></i> Change picture</a>
    				<br/><br/>
    				<div class="profile-usertitle-name">
						@{{ display.name }}
    				</div>
    				<div class="profile-usertitle-job">
    				    @{{ display.position_name }}
    				</div>
					<span class="badge badge-success" v-if="isOnline">Online</span>
					<span class="badge badge-danger" v-if="!isOnline">Offline</span>
    			</div>
    			<!-- END SIDEBAR USER TITLE -->
    			<!-- SIDEBAR MENU -->
    			<div class="profile-usermenu">
    			   <div class="margin-top-10 profile-usertitle-job">
                        <i class="icon-login"></i> 
                        Employee No: @{{ display.employee_no }}
                    </div>
                    <div class="margin-top-10 profile-usertitle-job">
                        <i class="icon-pointer"></i> 
                        Address: @{{ display.address }}
                    </div>
                    <div class="margin-top-10 profile-usertitle-job">
                        <i class="icon-call-end"></i> 
                        Mobile: @{{ display.mobile }}
                    </div>
                    <div class="margin-top-10 profile-usertitle-job">
                        <i class="icon-emoticon-smile"></i> 
                        Birth Date: @{{ display.birth_date }}
                    </div>
                    <div class="margin-top-10 profile-usertitle-job">
                        <i class="icon-present"></i> 
                        Gender: @{{ display.gender }}
                    </div>
					<br/>
                    <div class="margin-top-10 profile-usertitle-job">
                        <i class="fa fa-clock-o"></i>
                        Last Activity: @{{ formatDateTime(display.last_activity, 'MM/DD/YYYY LT') }}
                    </div>
                    <div class="margin-top-10 profile-usertitle-job">
                        <i class="fa fa-home"></i>
                        Current Branch: @{{ currentBranch }}
                    </div>
    			</div>
    			<!-- END MENU -->

                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="">Navigate to Employee:</label>
                            <form autocomplete="off" onsubmit="return false">
                                <select onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);"
                                        class="sl form-control" v-model="display.current_id">
                                    <option v-for="employee in employees" v-bind:value="employee.user_id">@{{ employee.name }}</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
    		</div>
    		<!-- END PORTLET MAIN -->
    	</div>
    	<!-- END BEGIN PROFILE SIDEBAR -->
    	<!-- BEGIN PROFILE CONTENT -->
    	<div  id="navbar-example2" style="border:0px;" class="navbar navbar-default navbar-static profile-content" role="navigation">
    		<div class="navbar-header">
				<button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".bs-js-navbar-scrollspy">
		    	<span class="icon-bar"></span>
		    	<span class="icon-bar"></span>
		    	<span class="icon-bar"></span>
		    	</button>
			</div>
			<div class="collapse navbar-collapse bs-js-navbar-scrollspy">
                <ul class="nav navbar-nav bold">
                	<li class="active">
                		<a href="#profile_tab"  data-toggle="tab">Profile</a>
                	</li>
                	<li>
                		<a href="#work_tab" data-toggle="tab">Work Info</a>
                	</li>
                	<li>
                		<a href="#system_tab" data-toggle="tab">System Access</a>
                	</li>
                	<li @mouseout="initSchedule(newEmployee.id)">
                		<a href="#schedules_tab" data-toggle="tab">Schedules</a>
                	</li>
                	<li>
                		<a href="#attendance_tab" data-toggle="tab">Attendance</a>
                	</li>
					<li>
						<a href="#timesheet_tab" data-toggle="tab">Time Card</a>
					</li>
                	<li>
                		<a href="#leave_credits" data-toggle="tab">Leave Credits</a>
                	</li>
                	<li v-show="newEmployee.view_salary">
                		<a href="#payroll_tab" data-toggle="tab">Payroll</a>
                	</li>
                	<li>
                		<a href="#files_tab" data-toggle="tab">Files</a>
                	</li>
                </ul>
    		</div>
    		<!--end navigation div -->
    		<div class="row">
                <div class="tab-content col-xs-12" >
					<div class="tab-pane fade" id="timesheet_tab">
						@include('employee.timesheet')
					</div>
					<div class="tab-pane fade active in" id="profile_tab">
						@include('employee.profile')
					</div>
					<div class="tab-pane fade" id="work_tab">
                        @include('employee.work')
					</div>
					<div class="tab-pane fade" id="system_tab">
                        @include('employee.system')
					</div>
					<div class="tab-pane fade" id="schedules_tab">
						@include('employee.schedules')
					</div>
					<div class="tab-pane fade" id="attendance_tab">
						@include('employee.attendance')
					</div>
					<div class="tab-pane fade" id="payroll_tab">
						@include('employee.payroll')
					</div>
					<div class="tab-pane fade" id="files_tab">
						@include('employee.files')
					</div>
					<div class="tab-pane fade" id="leave_credits">
                        @include('employee.common.leave_credits')
                    </div>
				</div>
    		</div>
    	    <!-- end tab contents -->
    	</div>
    	<!-- END PROFILE CONTENT -->
    </div>

	<div class="modal fade" v-if="setSchedule!==undefined" id="schedule-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
					<h4 class="modal-title">Modify/Add Schedule: </h4>
				</div>
				<div class="modal-body">
					<div class="form-body">
						<div class="row">
							<div class="col-md-4">
								<div class="form-group">
									<label>Branch:</label>
									<select v-model="setSchedule.branch_id" class="form-control">
										<option v-bind:value="branch.id" v-for="branch in branches">@{{ branch.branch_name  }}</option>
									</select>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label>Date:</label>
									<input type="date" class="form-control" v-model="setSchedule.date"/>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label>Shift:</label>
									<select v-model="setSchedule.time" class="form-control">
										<option v-bind:value="sched.time" v-if="sched.time != '00:00'" v-for="sched in availableSchedules">@{{ sched.schedule_name  }} (@{{ sched.time }})</option>
										<option value="00:00">Rest Day</option>
									</select>
								</div>
							</div>
						</div>
                        <div class="alert alert-danger" v-if="setSchedule.is_read_only">
                            Warning: Due to payroll process. We're not giving you access to this feature.
                        </div>
					</div>
				</div>
				<div class="modal-footer">
					<button v-if="setSchedule.single" v-bind:disabled="setSchedule.is_read_only && !setSchedule.is_hr" type="button" class="btn btn-danger pull-left" @click="deleteSchedule">Delete Custom Schedule</button>
					<button type="button" v-bind:disabled="setSchedule.is_read_only && !setSchedule.is_hr" class="btn btn-success" @click="saveSchedule">Save</button>
					<button type="button" class="btn default" data-dismiss="modal">Close</button>
				</div>
			</div>
			<!-- /.modal-content -->
		</div>
		<!-- /.modal-dialog -->
	</div>
	<!-- end fileupload Modal-->


    <div class="modal fade" id="leave-credit-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title"> Adjust Leave Max </h4>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Leave Type</label>
                                    <select v-model="newLeaveCredit.leave_type_id" class="form-control">
                                        <option v-for="leave in leave_types" v-bind:value="leave.id">@{{ leave.leave_type_name }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Custom Leave Max</label>
                                    <input type="number" v-model="newLeaveCredit.max_leave" class="form-control"/>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Year</label>
                                    <input type="number" v-model="newLeaveCredit.year" class="form-control"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" @click="updateLeaveCredit">Save</button>
                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- end fileupload Modal-->

    <!-- Start of view Modal-->
    <div class="modal fade" id="view-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title" v-if="view_attendance.basic_data.type !='LEAVE'">View Attendance Logs</h4>
                    <h4 class="modal-title" v-else>View Leave Details</h4>
                </div>
                <div class="modal-body">
                    <table class="table table-responsive" v-if="view_attendance.basic_data.type !='LEAVE'">
                        <thead>
                        <tr>
                            <th>Log Type</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th style="width:50%">Notes</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="att in view_attendance.advanced_data" v-if="!(att.stamp_type !='REGULAR' && att.stamp_type !='ADJUSTMENT' && att.in_out!='IN')">
                            <td>@{{ att.stamp_type }}</td>
                            <td>@{{ formatDateTime(att.attendance_stamp,"MM/DD/YYYY") }}</td>
                            <td>
                                <span>@{{ formatDateTime(att.attendance_stamp,"hh:mm A") }}</span>
                                <span v-if="att.stamp_type =='REGULAR' || att.stamp_type =='ADJUSTMENT'"> (@{{ att.in_out }}) </span>
                                <span v-if="att.stamp_type !='REGULAR' && att.stamp_type !='ADJUSTMENT'">
                                    @{{ getOutLog(att) }}
                                </span>
                            </td>
                            <td>
                                <span v-if="att.branch_name !== null && att.branch_name !== undefined"> Biometric @ @{{ att.branch_name }}</span>
                                <span v-if="att.request_data !== null && att.request_data !== undefined">
                                    @{{ att.request_data.request_note }}
                                </span>
                                <span v-if="att.admin_notes !== null && att.admin_notes !== undefined">
                                    Added by Admin: @{{ att.admin_notes }}
                                </span>
                            </td>
                            <td>
                                <button type="button" v-if="newEmployee.delete_attendance == true" class="btn btn-danger btn-xs" @click="deleteAttendance(att)">Delete</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <table class="table table-responsive" v-else>
                        <thead>
                        <tr>
                            <th>Leave Type</th>
                            <th>Date Covered</th>
                            <th>No. of Days</th>
                            <th>Notes</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>@{{ view_attendance.basic_data.title }}</td>
                            <td v-if="view_attendance.basic_data.__start != view_attendance.basic_data.__end">@{{ view_attendance.basic_data.__start }} - @{{ view_attendance.basic_data.__end }}</td>
                            <td v-else> @{{ view_attendance.basic_data.__start }} </td>
                            <td>
                                <span v-if="view_attendance.basic_data.mode!=='FULL'">
                                    Halfday
                                    (@{{ view_attendance.basic_data.mode==='AM'?"Morning":"Afternoon" }})
                                </span>
                                <span v-else>
                                    @{{ view_attendance.basic_data.days }}
                                </span>
                            </td>
                            <td> @{{ view_attendance.basic_data.notes }} </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" v-if="view_attendance.basic_data.type =='LEAVE'  && newEmployee.delete_attendance == true" class="btn btn-danger pull-left" @click="deleteAttendance(view_attendance.basic_data)">Delete Leave</button>
                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- end view Modal-->
</div>
<input type="hidden" value="{{Request::segment(2)}}" id="current_id">
<!-- Start of fileupload Modal-->
<div class="modal fade" id="portlet-config" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title">File Upload</h4>
			</div>
			<div class="modal-body">
				<div class="form-body">
					<form action="{{ url('employee/uploadProfilePicture') }}" method="post" enctype="multipart/form-data">
						<label>Select image to upload:</label>
						<input type="file" name="file" id="file"><br/>
						<input type="hidden" name="id" value="{{Request::segment(2)}}"/>
						<input type="submit" class="btn btn-sm btn-success" value="Upload" name="submit">
						{!! csrf_field() !!}
					</form>
				</div>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- end fileupload Modal-->

<input type="hidden" id="allow_schedule_edit" value="{{ $allow_schedule_edit }}">
<script src="../../assets/vuejs/instances/employees.js?cache={{ rand() }}"></script>
<script src="../../metronic/global/plugins/fullcalendar/fullcalendar.min.js"></script>
<script src="../../metronic/admin/pages/scripts/calendar.js"></script>
<script>
$(document).ready(function(){
    startTime();
});
</script>
@endsection