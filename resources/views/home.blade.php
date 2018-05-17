@extends('layouts.main')
@section('content')

<div class="row">
    <div class="col-md-7" id="home" v-cloak>
        <div v-if="!isChrome">
            @include('errors.not_chrome')
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                <div class="dashboard-stat blue-steel">
                    <div class="visual">
                        <i class="icon-users"></i>
                    </div>
                    <div class="details">
                        <div class="number">
                            @{{ employees.length }}
                        </div>
                        <div class="desc">
                            Employees
                        </div>
                    </div>
                    <a class="more" href="{{url('employees')}}">
                        View more <i class="m-icon-swapright m-icon-white"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                <div class="dashboard-stat red-sunglo">
                    <div class="visual">
                        <i class="fa icon-envelope-letter"></i>
                    </div>
                    <div class="details">
                        <div class="number">
                            0
                        </div>
                        <div class="desc">
                            Evaluations
                        </div>
                    </div>
                    <a class="more" href="{{url('evaluation')}}">
                        View more <i class="m-icon-swapright m-icon-white"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                <div class="dashboard-stat green-haze">
                    <div class="visual">
                        <i class="icon-question"></i>
                    </div>
                    <div class="details">
                        <div class="number">
                            @{{ countRequests }}
                        </div>
                        <div class="desc">
                            Requests
                        </div>
                    </div>
                    <a class="more" href="{{url('requests')}}">
                        View more <i class="m-icon-swapright m-icon-white"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i>Today's Employees Monitoring ({{date('F j, Y')}})
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse" data-original-title="" title="">
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <ul class="nav nav-tabs" >
                    <li class="active">
                        <a href="#tab_present" data-toggle="tab">Present </a>
                    </li>
                    <li class="">
                        <a href="#tab_absent" data-toggle="tab">Absent </a>
                    </li>
                    <li class="">
                        <a href="#tab_restday" data-toggle="tab">Rest Day </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade active in" id="tab_present">
                        @include('pagination.header_0')
                        <div class="scrollable">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>Employee No.</th>
                                    <th>Name</th>
                                    <th>TimeIn</th>
                                    <th>TimeOut</th>
                                    <th>Branch</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="employee in filtered">
                                    <td style="width:120px">
                                        <a v-bind:href="'../../employee/' + employee.user_id">@{{ employee.employee_no }}</a>
                                    </td>
                                    <td>
                                        <a v-bind:href="'../../employee/' + employee.user_id">@{{ employee.name }}</a>
                                    </td>
                                    <td>
                                        <i class="fa fa-camera" v-if="employee.via_in == 'BIO'"></i>
                                        <i class="fa fa-gear" v-if="employee.via_in == 'REQUEST' || employee.via_in == 'ADMIN'"></i>
                                        <a @click="viewAttendance(employee.user_id)">
                                            @{{ employee.time_in }}
                                        </a>
                                    </td>
                                    <td>
                                        <i class="fa fa-camera" v-if="employee.via_out == 'BIO'"></i>
                                        <i class="fa fa-gear" v-if="employee.via_out == 'REQUEST'"></i>
                                        <i class="fa fa-globe" v-if="employee.via_out == 'WEB'"></i>
                                        <a @click="viewAttendance(employee.user_id)">
                                            @{{ employee.time_out }}
                                        </a>
                                    </td>
                                    <td>
                                        @{{ employee.branch_name }}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        @include('pagination.footer_0')
                    </div>
                    <div class="tab-pane fade" id="tab_absent">
                        @include('pagination.header_1')
                        <div class="scrollable">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>Employee No.</th>
                                    <th>Name</th>
                                    <th>Branch</th>
                                    <th>Remarks</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="employee in filtered1">
                                    <td>
                                        <a v-bind:href="'../../employee/' + employee.user_id">@{{ employee.employee_no }}</a>
                                    </td>
                                    <td>
                                        <a v-bind:href="'../../employee/' + employee.user_id">@{{ employee.name }}</a>
                                    </td>
                                    <td>
                                        @{{ employee.branch_name }}
                                    </td>
                                    <td>
                                        <span class="label label-danger label-xs">Absent</span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        @include('pagination.footer_1')
                    </div>
                    <div class="tab-pane fade" id="tab_restday">
                        @include('pagination.header_2')
                        <div class="scrollable">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>Employee No.</th>
                                    <th>Name</th>
                                    <th>Branch</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="employee in filtered2">
                                    <td>
                                        <a v-bind:href="'../../employee/' + employee.user_id">@{{ employee.employee_no }}</a>
                                    </td>
                                    <td>
                                        <a v-bind:href="'../../employee/' + employee.user_id">@{{ employee.name }}</a>
                                    </td>
                                    <td>
                                        @{{ employee.branch_name }}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        @include('pagination.footer_2')
                    </div>
                </div>
            </div>
        </div>
        <div class="portlet box blue" v-if="celebrants.length>0">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i>Today's Birthday Celebrant/s
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse" data-original-title="" title="">
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Position</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="celebrant in celebrants">
                        <td><a v-bind:href="'../../employee/'+celebrant.id">@{{ celebrant.name }}</a></td>
                        <td>@{{ celebrant.position_name }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="portlet box blue" v-if="unsync.length > 0">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i> Unsynced Biometric - Branches
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse" data-original-title="" title="">
                    </a>
                </div>
            </div>
            <div class="portlet-body" style="height:120px;overflow-y:scroll">
                <div class="row">
                    <div class="col-md-4" v-for="u in unsync"> @{{ u.branch_name }}</div>
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
                                <td> @{{ view_attendance.basic_data.days }} </td>
                                <td> @{{ view_attendance.basic_data.notes }} </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn default" data-dismiss="modal">Close</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- end view Modal-->

    </div>
    <div class="col-md-5">
        @include('employee.tasks')

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
            <div class="portlet-body" style="height:420px;overflow-y:scroll">
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

    </div>
</div>

<script src="../../assets/vuejs/instances/home.js?cache={{ rand() }}"></script>
<script src="../../assets/vuejs/instances/tasks.js?cache={{ rand() }}"></script>
@endsection