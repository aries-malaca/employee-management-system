@extends('layouts.main')
@section('content')

<div class="portlet box blue" id="schedules" v-cloak>
	<div class="portlet-title">
		<div class="caption">
			<i class="fa fa-calendar"></i>Set Employee Schedules
		</div>
		<div class="tools">
			<a href="javascript:;" class="collapse" data-original-title="" title="">
			</a>
		</div>
	</div>
	<div class="portlet-body">
		<div class="row">
			<div class="form-group col-md-3">
                <select class="form-control" v-model="filter">
                    <option value="active">With Schedule</option>
                    <option value="floating">No Schedule</option>
                </select>
				<input type="text" class="form-control" v-model="search" placeholder="Search" />
                <div style="max-height:300px;overflow-y:scroll">
                    <table class="table table-bordered table-responsive">
                        <tr v-for="employee in filteredEmployees">
                            <td><a @click="showViewModal(employee)">@{{ employee.employee_no }}</a></td>
                            <td><a @click="showViewModal(employee)">
                                    @{{ employee.name }}</a>
                                <span v-if="employee.has_conflict">
                                    <br/>
                                    <small style="color:red">Note: Schedule conflict.</small>
                                </span>
                                <span v-if="employee.range_scheds > 1">
                                    <br/>
                                    <small style="color:red">Note: Range Schedules: @{{ employee.range_scheds }}.</small>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-success btn-xs" @click="addItem(employee)">Add</button>
                            </td>
                        </tr>
                    </table>
                </div>
			</div>
            <div class="form-group col-md-9">
                <div style="overflow-x:scroll">
                    <table class="table table-bordered table-responsive" style="width:1400px;font-size:11.5px;">
                        <tr>
                            <th style="width:40px;"></th>
                            <th>Employee</th>
                            <th>Branch</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Mon</th>
                            <th>Tue</th>
                            <th>Wed</th>
                            <th>Thu</th>
                            <th>Fri</th>
                            <th>Sat</th>
                            <th>Sun</th>
                        </tr>
                        <tr v-for="(schedule,key) in setSchedule">
                            <td>
                                <button @click="removeItem(key)" class="btn btn-danger btn-xs" title="Remove"><i class="fa fa-times"></i></button>
                                <button v-if="key>0" @click="copyLast(key)" class="btn btn-warning btn-xs" title="Copy Last"><i class="fa fa-files-o"></i></button>
                            </td>
                            <td>
                                @{{ schedule.employee_name }}

                            </td>
                            <td>
                                <select style="font-size:11px;" v-model="setSchedule[key].branch_id" @change="clearBranchData(key)" class="form-control">
                                    <option v-bind:value="branch.id" v-for="branch in branches">@{{ branch.branch_name }}</option>
                                </select>
                            </td>
                            <td><input style="width:140px;font-size:11px;" type="date" v-model="setSchedule[key].date_start" class="form-control"/></td>
                            <td>

                                <input style="width:140px;font-size:11px;" type="date" v-model="setSchedule[key].date_end" class="form-control"/>

                            </td>
                            <td v-for="n in 7">
                                <select class="form-control" v-model="setSchedule[key].schedule_data[n-1]" style="font-size:11px;">
                                    <option v-bind:title="d.schedule_data[n-1]" v-for="d in branches[getKey(setSchedule[key].branch_id,'branches')].schedules"
                                            v-if="d.schedule_data[n-1] != '00:00'"
                                            v-bind:value="d.schedule_data[n-1]">@{{ d.schedule_name }}
                                    </option>
                                    <option value="00:00">Rest Day</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
                <br/>
                <button class="btn btn-sm btn-success" @click="saveSchedule">Save Schedule/s</button>
            </div>
		</div>
        <div class="alert alert-info">
            Notes: <b>Click</b> the name/ID to View/Edit Employee's Schedule. If you want to add schedule, please make sure it will not overlaps with other schedule.
        </div>
    </div>
    <!-- Start of schedule Modal-->
    <div class="modal fade" id="view-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-full">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">Schedule Info: @{{ display.name }}</h4>
                </div>
                <div class="modal-body">
                    <div style="overflow-x:scroll">
                        <table class="table table-hover table-responsive table-bordered">
                        <thead>
                            <th>Branch</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Mon</th>
                            <th>Tue</th>
                            <th>Wed</th>
                            <th>Thu</th>
                            <th>Fri</th>
                            <th>Sat</th>
                            <th>Sun</th>
                            <th style="width:120px"></th>
                        </thead>
                        <tbody>
                            <tr v-for="sched in display.schedules" v-if="sched.schedule_type=='RANGE'" v-bind:style="isActiveSchedule(sched)?'background-color:#e1ffe3':'background-color:#ffe4e4'">
                                <td v-if="editing.id!=sched.id">@{{ sched.branch_name }}</td>
                                <td v-if="editing.id!=sched.id">@{{ readableDate(sched.schedule_start) }}</td>
                                <td v-if="editing.id!=sched.id"> @{{ readableDate(sched.schedule_end) }} </td>
                                <td v-if="editing.id!=sched.id"  v-bind:style="'color:' +getScheduleColor(sched.schedule_data[1], sched.branch_id,0) ">@{{ getScheduleName(sched.schedule_data[1], sched.branch_id,0) }} </td>
                                <td v-if="editing.id!=sched.id" v-bind:style="'color:' +getScheduleColor(sched.schedule_data[2], sched.branch_id,1) ">@{{ getScheduleName(sched.schedule_data[2], sched.branch_id,1) }} </td>
                                <td v-if="editing.id!=sched.id" v-bind:style="'color:' +getScheduleColor(sched.schedule_data[3], sched.branch_id,2) ">@{{ getScheduleName(sched.schedule_data[3], sched.branch_id,2) }} </td>
                                <td v-if="editing.id!=sched.id" v-bind:style="'color:' +getScheduleColor(sched.schedule_data[4], sched.branch_id,3) ">@{{ getScheduleName(sched.schedule_data[4], sched.branch_id,3) }} </td>
                                <td v-if="editing.id!=sched.id" v-bind:style="'color:' +getScheduleColor(sched.schedule_data[5], sched.branch_id,4) ">@{{ getScheduleName(sched.schedule_data[5], sched.branch_id,4) }} </td>
                                <td v-if="editing.id!=sched.id" v-bind:style="'color:' +getScheduleColor(sched.schedule_data[6], sched.branch_id,5) ">@{{ getScheduleName(sched.schedule_data[6], sched.branch_id,5) }} </td>
                                <td v-if="editing.id!=sched.id" v-bind:style="'color:' +getScheduleColor(sched.schedule_data[0], sched.branch_id,6) ">@{{ getScheduleName(sched.schedule_data[0], sched.branch_id,6) }} </td>

                                <td v-if="editing.id==sched.id">
                                    <select style="font-size:11px;" v-model="editing.branch_id"  class="form-control">
                                        <option v-bind:value="branch.id" v-for="branch in branches">@{{ branch.branch_name }}</option>
                                    </select>
                                </td>
                                <td v-if="editing.id==sched.id">
                                    <input type="date" class="form-control" v-model="editing.date_start"/>
                                </td>
                                <td v-if="editing.id==sched.id">
                                    <input type="date" class="form-control" v-model="editing.date_end"/>
                                    <div v-if="isActiveSchedule(sched) && Number(moment().format('X')) > Number(moment(editing.date_end).format('X')) " style="color: red; max-width: 200px !important;">WARNING: Editing this schedule will set employee into FLOAT Status. IF she has new schedule, please Add it First.</div>
                                </td>
                                <td v-for="n in 7" v-if="editing.id==sched.id">
                                    <select class="form-control" v-model="editing.schedule_data[n-1]" style="font-size:11px;">
                                        <option v-bind:title="d.schedule_data[n-1]" v-for="d in branches[getKey(editing.branch_id,'branches')].schedules"
                                                v-if="d.schedule_data[n-1] != '00:00'"
                                                v-bind:value="d.schedule_data[n-1]">@{{ d.schedule_name }}
                                        </option>
                                        <option value="00:00">Rest Day</option>
                                    </select>
                                </td>
                                <td>
                                    <button class="btn btn-xs btn-info" @click="editSchedule(sched)" v-if="editing.id!=sched.id">Edit</button>
                                    <button class="btn btn-xs btn-info" @click="updateSchedule" v-if="editing.id==sched.id">Save</button>
                                    <button class="btn btn-xs btn-warning" v-if="editing.id==sched.id" @click="editing.id=0">Cancel</button>
                                    <button class="btn btn-xs btn-danger" @click="deleteSchedule(sched,sched.employee_id)">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    <div class="alert alert-info">
                        Notes: You may edit Employee's Single day schedule or change Rest-day in <b>Employee's Profile</b> within <b>Schedules</b> Tab or Using the Branch Schedule History Below.
                    </div>
                </div>
                <div class="modal-footer">
                    <a v-bind:href="'../../employee/'+display.id" target="_blank" class="btn btn-warning" >View @{{ display.name }}'s Profile </a>
                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- end of schedule Modal-->
</div>

<div class="portlet box blue" id="branch_history" v-cloak>
    <div class="portlet-title">
        <div class="caption">
            <i class="fa fa-calendar"></i>Branch Schedule History
        </div>
        <div class="tools">
            <a href="javascript:;" class="collapse" data-original-title="" title="">
            </a>
        </div>
    </div>
    <div class="portlet-body">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Branch</label>
                    <select v-model="branch_id" class="form-control">
                        <option v-bind:value="branch.id" v-for="branch in branches">@{{ branch.branch_name }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Start</label>
                    <input type="date" v-model="start" class="form-control" />
                </div>
                <div class="form-group">
                    <label>End</label>
                    <input type="date" v-model="end" class="form-control" />
                </div>
                <div class="form-group">
                    <label>&nbsp;</label><br/>
                    <button class="btn btn-success btn-md" @click="getBranchHistory">Search</button>
                </div>

                <br/>
                <br/>

                Branch Schedules: @{{ branchInfo.branch_name }}
                <table class="table table-bordered">
                    <tr v-for="schedule in branchInfo.schedules">
                        <td v-bind:style="'color:white;background-color:' + schedule.schedule_color">@{{ schedule.schedule_color }}</td>
                        <td>@{{ schedule.schedule_name }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-9">
                <div v-show="loading">Loading...</div>
                <div id='calendar2'></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="schedule-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">Modify/Add Schedule: @{{ setSchedule.name }}</h4>
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
                                        <option v-bind:value="sched.time" v-if="sched.time != '00:00'" v-for="sched in availableSchedules">@{{ sched.schedule_name  }} </option>
                                        <option value="00:00">Rest Day</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button v-if="setSchedule.single" type="button" class="btn btn-danger pull-left" @click="deleteSchedule">Delete this Schedule</button>
                    <button type="button" class="btn btn-success" @click="saveSchedule">Save</button>
                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- end fileupload Modal-->

</div>

<script src="../../assets/vuejs/instances/schedules.js?cache={{ rand() }}"></script>
<script src="../../assets/vuejs/instances/branch_history.js?cache={{ rand() }}"></script>
@endsection