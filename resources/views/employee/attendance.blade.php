<div class="row">
    <div class="col-sm-12">
        <div class="portlet box blue">
        	<div class="portlet-title">
        		<div class="caption">
        			<i class="fa fa-gift"></i> Attendance
                    <button @click="showAttendanceModal" v-if="newEmployee.delete_attendance == true" type="button" class="btn green btn-sm">Add Attendance Log</button>
                    <button @click="showLeaveModal" v-if="newEmployee.delete_attendance == true" type="button" class="btn yellow btn-sm">Add Leave</button>
                </div>
        		<div class="tools">
        			<a href="javascript:;" class="collapse" data-original-title="" title="">
                    </a>
                    <a @click="initAttendance(newEmployee.id)" data-original-title="" title=""><i class="icon-reload" style="color:white"></i></a>
        		</div>
        	</div>
        	<div class="portlet-body">
                <span class="badge" style="background-color:{{ $color['timein'] }};">Time-In</span>
        
                <span class="badge" style="background-color:{{ $color['timeout'] }};" >Time-Out</span>
        
                <span class="badge" style="background-color:{{ $color['overtime'] }};">Overtime</span>
        
                <span class="badge" style="background-color:{{ $color['offset'] }}">Offset</span>
        
                <span class="badge" style="background-color:{{ $color['adjustment'] }};">Adjusted</span>
        
                <span class="badge" style="background-color:{{ $color['leave'] }};">Leave</span>
        
                <span class="badge" style="background-color:{{ $color['travel'] }}">Travel</span>
        
                <span class="badge" style="background-color:{{ $color['holiday'] }};">Holiday</span>
                
                <span class="badge" style="background-color:{{ $color['emergency'] }};">Blocked Sched.</span>

                <span class="badge" style="background-color:{{ $color['unofficial'] }};"> Raw Logs (Unofficial)</span>
                <br/><br/><label><input type="checkbox" @change="initAttendance" v-model="show_raw" /><b>Show Raw Logs</b></label>
                &nbsp; <button class="btn btn-xs btn-warning" @click="initAttendance(newEmployee.id)">Refresh</button>
                <br/><br/>
                <div class="alert alert-info">
                    Missing Logs? Try to toggle the Raw Logs.<br/><br/>

                    <strong>Raw Logs(Unofficial)</strong> Actual logs captured by biometric device, this is for reference and auditing.
                    Your attendance should be read as <span class="badge" style="background-color: #4caf50">Green</span>
                    and <span class="badge" style="background-color: #f44336">Red</span> Color.<br/> In order for EMS read your Logs as official, your schedule
                    must be updated.
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div v-if="show_loading_attendance">Loading Attendance Calendar...</div>
                        <div v-else id='calendar'></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Start of attendance Modal-->
<div class="modal fade" v-if="newEmployee.delete_attendance == true" id="attendance-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-full">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">Add Attendance Log</h4>
            </div>
            <div class="modal-body">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Type</th>
                            <th>Details</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(att,key) in newAttendances">
                            <td>
                                <button class="btn btn-sm btn-danger" @click="removeAttendanceRow(key)">X</button>
                            </td>
                            <td>
                                <select v-model="att.type" class="form-control">
                                    <option value="ADJUSTMENT">Adjustment</option>
                                    <option value="OVERTIME">Overtime</option>
                                    <option value="TRAVEL">Travel</option>
                                    <option value="OFFSET">Offset</option>
                                </select>
                            </td>
                            <td>
                                <div class="row" v-if="att.type == 'ADJUSTMENT'">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Date</label>
                                            <input type="date" v-model="att.date_start" class="form-control"/>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Time</label>
                                            <input type="time" v-model="att.time_start" class="form-control"/>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Time</label>
                                            <select v-model="att.mode" class="form-control">
                                                <option value="IN">IN</option>
                                                <option value="OUT">OUT</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" v-if="att.type == 'OVERTIME'">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Date Start</label>
                                            <input type="date" v-model="att.date_start" class="form-control"/>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Time Start</label>
                                            <input type="time" v-model="att.time_start" class="form-control"/>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Date End</label>
                                            <input type="date" v-model="att.date_end" class="form-control"/>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Time End</label>
                                            <input type="time" v-model="att.time_end" class="form-control"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" v-if="att.type == 'TRAVEL' || att.type=='OFFSET'">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Date</label>
                                            <input type="date" v-model="att.date_start" class="form-control"/>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Time Start</label>
                                            <input type="time" v-model="att.time_start" class="form-control"/>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Time End</label>
                                            <input type="time" v-model="att.time_end" class="form-control"/>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="isGraveyard(att.time_start) && att.type=='ADJUSTMENT' && att.mode=='OUT'">
                                    <div class="col-md-12" style="color:red">
                                        **Note: Out will be credited to the previous date.
                                    </div>
                                </div>
                            </td>
                            <td>
                                <textarea class="form-control" v-model="att.notes"></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <button class="btn btn-sm btn-info" @click="addAttendanceRow()">+</button>
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" @click="saveAttendance()">Save</button>
                <button type="button" class="btn default" data-dismiss="modal">Close</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- end attendance Modal-->


<!-- Start of leave Modal-->
<div class="modal fade" v-if="newEmployee.delete_attendance == true" id="leave-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-full">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">Add Employee Leave</h4>
            </div>
            <div class="modal-body">
                <table class="table table-hover table-bordered">
                    <thead>
                    <tr>
                        <th style="width:160px">Date Start</th>
                        <th style="width:160px">Date End</th>
                        <th style="width:200px">Leave Type</th>
                        <th style="width:140px">Duration</th>
                        <th>Reason</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <input type="date" @change="newLeave.mode='FULL'" v-model="newLeave.date_start" class="form-control"/>
                        </td>
                        <td>
                            <input type="date" @change="newLeave.mode='FULL'" v-bind:disabled="!allowStaggered(newLeave.leave_type_id)"
                                   v-model="newLeave.date_end" class="form-control"/>
                        </td>
                        <td>
                            <select @change="newLeave.mode='FULL', resolveRange()" v-model="newLeave.leave_type_id" class="form-control">
                                <option v-bind:value="leave.id" v-for="leave in leave_types" v-if="!leave.hidden">@{{ leave.leave_type_name }}</option>
                            </select>
                        </td>
                        <td>
                            <select v-model="newLeave.mode" class="form-control">
                                <option value="FULL">Full</option>
                                <option value="AM" v-if="newLeave.date_start == newLeave.date_end && allowHalfDay(newLeave.leave_type_id)">
                                    Morning
                                </option>
                                <option value="PM" v-if="newLeave.date_start == newLeave.date_end && allowHalfDay(newLeave.leave_type_id)">
                                    Afternoon
                                </option>
                            </select>
                        </td>
                        <td>
                            <textarea class="form-control" rows="2" v-model="newLeave.notes"></textarea>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" @click="saveLeave()">Save</button>
                <button type="button" class="btn default" data-dismiss="modal">Close</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- end leave Modal-->
