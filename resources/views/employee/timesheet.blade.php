<div class="portlet box yellow-casablanca">
    <div class="portlet-title">
        <div class="caption">
            <i class="fa fa-gift"></i> Time Sheet
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
                    <label>Year:</label>
                    <select class="form-control" v-model="timeSheet.year" @change="getTimeSheet">
                        <option v-bind:value="n" v-for="n in rangeYears">@{{ n }}</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Month:</label>
                    <select class="form-control" v-model="timeSheet.month" @change="getTimeSheet">
                        <option v-bind:value="n" v-for="n in 12">@{{ getMonthName(n) }}</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Cutoff:</label>
                    <select class="form-control" v-model="timeSheet.cutoff" @change="getTimeSheet">
                        <option value="1">1st</option>
                        <option value="2">2nd</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>&nbsp; <br/><br/></label>
                    <button class="btn btn-success" @click="getTimeSheet">Refresh</button>
                </div>
            </div>
            <div class="col-md-3">
                <span id="loading"><br/><br/>Loading...</span>
            </div>
        </div>
        <div class="scrollable">
            <div class="alert alert-warning" v-if="attendances.length==0">
                Data not found, please select other Year/Month
            </div>
            <div v-else>
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>Day</th>
                        <th>Schedule</th>
                        <th>Remarks</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Late (m)</th>
                        <th>U.T. (m)</th>
                        <th>O.T. (h)</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="attendance in attendances">
                        <td>@{{ attendance.date }} (@{{ formatDateTime(attendance.date,"ddd") }})</td>
                        <td>
                            <a @click="editSchedule(attendance)" v-bind:title="attendance.branch_name">
                                <span v-if="attendance.schedule != '00:00'">@{{ formatDateTime(attendance.date+" "+ attendance.schedule,"hh:mm A") }}</span>
                                <span v-else>Rest Day</span>
                            </a>
                        </td>
                        <td>
                            <div v-for="remark in attendance.remarks">
                                <span v-if="remark == 'absent'" class="badge" style="background-color: rgb(244, 67, 54);"> Absent </span>
                                <span v-if="remark == 'present' || remark == 'no-timeout'" class="badge" style="background-color: rgb(76, 175, 80);"> Present </span>
                                <span v-if="remark == 'overtime'" class="badge" style="background-color: rgb(156, 39, 176);"> Overtime </span>
                                <span v-if="remark == 'offset'" class="badge" style="background-color: rgb(0, 46, 255);"> Offset </span>
                                <span v-if="remark == 'leave'" class="badge" style="background-color: rgb(218, 150, 6);"> Leave </span>
                                <span v-if="remark == 'holiday'" class="badge" style="background-color: rgb(0, 176, 255);"> Holiday </span>
                                <span v-if="remark == 'blocked sched.'" class="badge" style="background-color: rgb(85, 0, 0);"> Block Sched. </span>
                                <span v-if="remark == 'travel'" class="badge" style="background-color: rgb(236, 94, 141);"> Travel </span>
                                <span v-if="remark == 'rest-day'" class="badge" style="background-color: rgb(0, 0, 0);"> Rest Day </span>
                            </div>
                        </td>
                        <td><a @click="viewAttendance({date_credited:attendance._i})">@{{ attendance.in }}</a></td>
                        <td><a @click="viewAttendance({date_credited:attendance._i})">@{{ attendance.out }}</a></td>
                        <td v-bind:style="attendance.late_hours > 0?'font-weight:bold':''">@{{ (attendance.late_hours*60).toFixed(2) }}</td>
                        <td v-bind:style="attendance.undertime_hours>0?'font-weight:bold':''">@{{ (attendance.undertime_hours*60).toFixed(2) }}</td>
                        <td v-bind:style="attendance.overtime_hours>0?'font-weight:bold':''">@{{ attendance.overtime_hours.toFixed(2) }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="font-weight:bold">@{{ (totalTime.late*60).toFixed(2) }}</td>
                        <td style="font-weight:bold">@{{ (totalTime.undertime*60).toFixed(2) }}</td>
                        <td style="font-weight:bold">@{{ totalTime.ot.toFixed(2) }}</td>
                    </tr>
                    </tbody>
                </table>
                <button @click="finalizeSchedule" v-if="newEmployee.delete_attendance == true" type="button" class="btn yellow btn-sm">Lock Schedules</button>
                <button @click="fixSchedule" v-if="newEmployee.delete_attendance == true" type="button" class="btn green btn-sm">Fix Schedules</button>
            </div>
        </div>
    </div>
</div>