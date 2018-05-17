@extends('layouts.main')
@section('content')

<link href="../../metronic/global/plugins/fullcalendar/fullcalendar.min.css" rel="stylesheet"/>
<div class="row">
    <div class="col-md-8" id="calendar_page">
        <div v-if="!isChrome">
            @include('errors.not_chrome')
        </div>
        <ul class="nav nav-tabs" >
            <li class="active" >
                <a href="#tab_1_31" data-toggle="tab">
                Attendance </a>
            </li>
            <li>
                <a href="#timesheet_tab" data-toggle="tab">Time Card</a>
            </li>
            <li @mouseout="initSchedule()">
                <a href="#tab_1_41" data-toggle="tab">
                    Schedule </a>
            </li>
            <li>
                <a href="#payslip_tab" data-toggle="tab">Payslip
                <span class="badge badge-success">New!</span></a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade active in" id="tab_1_31">
                @include('employee.attendance')
            </div>
            <div class="tab-pane fade" id="tab_1_41">
                @include('employee.schedules')
            </div>
            <div class="tab-pane fade" id="payslip_tab">
                @include('forms.payslip')
            </div>
            <div class="tab-pane fade" id="timesheet_tab">
                @include('employee.timesheet')
            </div>
        </div>

        <div class="portlet box yellow">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i> News
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse" data-original-title="" title="">
                    </a>
                </div>
            </div>
            <div class="portlet-body" style="height:320px;overflow-y:scroll">
                <div class="timeline">
                @foreach($news as $key=>$value)
                    @if($key>=51) @break @endif
                    <!-- TIMELINE ITEM -->
                        <div class="timeline-item">
                            <div class="timeline-badge">
                                <img class="timeline-badge-userpic" src="{{url('images/employees/'. $value['picture'])}}">
                            </div>
                            <div class="timeline-body">
                                <div class="timeline-body-arrow">
                                </div>
                                <div class="timeline-body-head">
                                    <div class="timeline-body-head-caption">
                                        <a href="{{url('employee/'. $value['user_id'])}}" class="timeline-body-title font-blue-madison">{{ $value['name']}}</a>
                                        <span class="timeline-body-time font-grey-cascade">{{ dateNormal($value['created_at']) }}</span>
                                    </div>
                                </div>
                                <div class="timeline-body-content">
                            <span class="font-grey-cascade">
                                <h4>{{ $value['title'] }}</h4>
                                {!! $value['description'] !!}
                            </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

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
    <div class="col-md-4">
        @include('employee.tasks')
        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i> Personal Notes
                    <a data-toggle="modal" href="#portlet-config" type="button" class="btn purple btn-sm">Add Note</a>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse" data-original-title="" title="">
                    </a>
                </div>
            </div>
            <div class="portlet-body" style="height:320px;overflow-y:scroll" >
                <div class="todo-tasklist">
                    @foreach($notes as $key=>$value)
                        <div class="todo-tasklist-item todo-tasklist-item-border-blue">
                            <div class="todo-tasklist-item-title" style="word-wrap:break-word">
                                {{ $value['title'] }}
                            </div>
                            <div class="todo-tasklist-item-text" style="word-wrap:break-word">
                                {!! $value['description'] !!}
                            </div>
                            <div class="todo-tasklist-controls pull-left">
                                <span class="todo-tasklist-date"><i class="fa fa-calendar"></i> {{ dateNormal($value['created_at']) }} </span>

                                <a data-toggle="modal" href="#modal_delete{{$value['id']}}" type="button" class="btn red btn-xs">Delete</a>
                                <!-- Start of delete note Modal-->
                                <div class="modal fade" id="modal_delete{{$value['id']}}" tabindex="-1" role="dialog"
                                     aria-labelledby="myModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{url('calendar/notes/processDelete')}}" method="post">
                                                <input type="hidden" value="{{$value['id']}}" name="id">
                                                {!! csrf_field() !!}
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                                    <h4 class="modal-title">Delete Note</h4>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete this note?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn red">Delete</button>
                                                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                                                </div>
                                            </form>
                                        </div><!-- /.modal-content -->
                                    </div><!-- /.modal-dialog -->
                                </div><!-- end of delete note Modal-->
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <!-- Start of add note Modal-->
        <div class="modal fade" id="portlet-config" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{url('calendar/notes/processAdd')}}" method="post">
                        {!! csrf_field() !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                            <h4 class="modal-title">Add Note</h4>
                        </div>
                        <div class="modal-body">
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label class="control-label bold">Title</label>
                                            <input type="text" name="title" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <!--/row-->
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label class="control-label bold">Description</label>
                                            <textarea name="description" class="wysihtml5 form-control"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <!--/row-->
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn blue">Save</button>
                            <button type="button" class="btn default" data-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- end of add note Modal-->

    </div>
</div>
<script src="../../assets/vuejs/instances/calendar.js?cache={{ rand() }}"></script>
<script src="../../assets/vuejs/instances/tasks.js?cache={{ rand() }}"></script>
<script src="../../metronic/global/plugins/fullcalendar/fullcalendar.min.js"></script>
@endsection