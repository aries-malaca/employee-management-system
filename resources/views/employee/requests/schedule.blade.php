<div class="portlet box blue">
    <div class="portlet-title">
        <div class="caption">
            <i class="fa fa-gift"></i> Schedule Requests
        </div>
        <div class="tools">
            <a href="javascript:;" class="collapse" data-original-title="" title=""></a>
        </div>
    </div>
    <div class="portlet-body">
        <ul class="nav nav-tabs" id="auth" v-cloak>
            <li class="active">
                <a href="#pending_schedule_tab" data-toggle="tab">Pending Schedule Req.</a>
            </li>
            <li>
                <a href="#schedule_history_tab" data-toggle="tab">Schedule Req. History</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade active in" id="pending_schedule_tab">
                @include('pagination.header_0')
                <label v-if="trackable && !isEmployee">
                    <input type="checkbox" v-model="show_all"/> Show non-approval
                </label>
                <div class="scrollable">
                    <table class="table table-hover table-bordered" style="font-size:11.5px;">
                        <thead>
                        <tr>
                            <th v-if="!isEmployee">Name</th>
                            <th>Date/Time Filed</th>
                            <th>Date</th>
                            <th>Shift</th>
                            <th>Branch</th>
                            <th>Notes</th>
                            <th>Approval</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(schedule,key) in filtered">
                            <td v-if="!isEmployee">
                                <a v-bind:href="'../../employee/'+schedule.user_id" target="_blank">@{{ schedule.name }}</a>
                            </td>
                            <td>@{{ formatDateTime(schedule.created_at,"MM/DD/YYYY LT") }}</td>
                            <td>
                                @{{ formatDateTime(schedule.request_data.date,"MM/DD/YYYY") }}</td>
                            </td>
                            <td>
                                @{{ (schedule.request_data.time=='00:00'?'Rest Day':formatDateTime(schedule.request_data.date +" " + schedule.request_data.time,"hh:mm A")) }}</td>
                            </td>
                            <td v-if="branches.length>0 && getKey(schedule.request_data.branch_id,'branches') !==false">
                                @{{ branches[getKey(schedule.request_data.branch_id,'branches')].branch_name }}
                            </td>
                            <td v-else></td>
                            <td>
                                @{{ schedule.request_note }} <br/>
                                <span style="color:blue" v-if="schedule.request_data.time=='00:00'">
                            <b>Once approved</b>,The Default Restday for the week requested will be changed to Working Day.
                        </span>
                            </td>
                            <td>
                                <ol>
                                    <li v-for="(feedback,key) in schedule.action_data.approved_by">
							<span v-if="feedback.status == 'Vacant'">
								Position Vacant - @{{ getPositionName(feedback.position_id) }}
							</span>
                                        <span v-if="feedback.status == 'Pending'">
								For Approval of - @{{ getPositionName(feedback.position_id) }}
							</span>
                                        <b v-if="feedback.status != 'Pending' && feedback.status != 'Vacant'">
                                            @{{ feedback.status }} by - @{{ feedback.name }}, Feedback: @{{ feedback.feedback }}, Date: @{{ formatDateTime(feedback.date,"MM/DD/YYYY hh:mm A") }}
                                        </b>
                                    </li>
                                </ol>
                            </td>
                            <td>
                                <button class="btn btn-danger btn-xs" v-if="isEmployee" data-loading-text="Processing..."
                                        @click="deleteRequest(schedule,$event)">Delete</button>
                                <button class="btn btn-success btn-xs" v-if="!isEmployee && schedule.for_my_approval"
                                        @click="showActionModal(schedule,'approve')">Approve</button>
                                <button class="btn btn-danger btn-xs" v-if="!isEmployee && schedule.for_my_approval"
                                        @click="showActionModal(schedule,'deny')">Deny</button>
                                <a target="_blank" v-bind:href="'../../requests/printRequest/'+schedule.id" class="btn btn-warning btn-xs">Print</a>
                                <button class="btn purple btn-xs" v-if="trackable && !isEmployee" @click="editRequest(schedule)">Edit</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                @include('pagination.footer_0')
            </div>
            <div class="tab-pane fade in" id="schedule_history_tab">
                @include('pagination.header_1')
                <div class="scrollable">
                    <table class="table table-hover table-bordered" style="font-size:11.5px;">
                        <thead>
                        <tr>
                            <th v-if="!isEmployee">Name</th>
                            <th>Date/Time Filed</th>
                            <th>Date</th>
                            <th>Shift</th>
                            <th>Branch</th>
                            <th>Notes</th>
                            <th>Approval</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(schedule,key) in filtered1">
                            <td v-if="!isEmployee">
                                <a v-bind:href="'../../employee/'+schedule.user_id" target="_blank">@{{ schedule.name }}</a>
                            </td>
                            <td>@{{ formatDateTime(schedule.created_at,"MM/DD/YYYY LT") }}</td>
                            <td>
                                @{{ formatDateTime(schedule.request_data.date,"MM/DD/YYYY") }}</td>
                            </td>
                            <td>
                                @{{ (schedule.request_data.time=='00:00'?'Rest Day': formatDateTime(schedule.request_data.date +" " + schedule.request_data.time,"hh:mm A") ) }}</td>
                            </td>
                            <td v-if="branches.length>0 && getKey(schedule.request_data.branch_id,'branches') !==false">
                                @{{ branches[getKey(schedule.request_data.branch_id,'branches')].branch_name }}
                            </td>
                            <td v-else></td>
                            <td>
                                @{{ schedule.request_note }}
                            </td>
                            <td>
                                <ol>
                                    <li v-for="(feedback,key) in schedule.action_data.approved_by" v-if="! ((feedback.status == 'Pending' || feedback.status =='Vacant') && schedule.request_data.status=='denied' )" >
							<span v-if="feedback.status == 'Vacant'">
								Position Vacant - @{{ getPositionName(feedback.position_id) }}
							</span>
                                        <span v-if="feedback.status == 'Pending'">
								For Approval of - @{{ getPositionName(feedback.position_id) }}
							</span>
                                        <b v-if="feedback.status != 'Pending' && feedback.status != 'Vacant'">
                                            @{{ feedback.status }} by - @{{ feedback.name }}, Feedback: @{{ feedback.feedback }}, Date: @{{ formatDateTime(feedback.date,"MM/DD/YYYY hh:mm A") }}
                                        </b>
                                    </li>
                                </ol>
                            </td>
                            <td>
                                <span class="label label-danger" v-if="schedule.request_data.status == 'denied'">Denied</span>
                                <span class="label label-success" v-if="schedule.request_data.status == 'approved'">Approved</span>
                                <span class="label label-warning" v-if="schedule.request_data.status == 'pending'">Pending</span>
                            </td>
                            <td>
                                <a target="_blank" v-bind:href="'../../requests/printRequest/' + schedule.id" class="btn btn-warning btn-xs">Print</a>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                @include('pagination.footer_1')
            </div>
        </div>
    </div>
</div>

<!-- Start of  Modal-->
<div class="modal fade" id="action-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title" v-if="actionModal.action=='approve'">Approve Schedule</h4>
                <h4 class="modal-title" v-if="actionModal.action=='deny'">Deny Schedule</h4>
            </div>
            <div class="modal-body">
                <div class="form-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="control-label bold">Notes</label>
                                <input type="text" v-model="actionModal.notes" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" v-if="actionModal.action=='approve'" data-loading-text="Processing..." @click="approveSchedule($event)" class="btn blue">Okay</button>
                <button type="button" v-if="actionModal.action=='deny'" data-loading-text="Processing..." @click="denySchedule($event)" class="btn red">Okay</button>
                <button type="button" class="btn default" data-dismiss="modal">Close</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- end of approve Modal-->

<!-- Start of add branch Modal-->
<div class="modal fade" id="edit-schedule" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">Edit Request</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <button class="btn btn-info pull-left" @click="resetRequest(edit_schedule)">Reset Approval</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger pull-left" @click="deleteRequest(edit_schedule)">Delete</button>
                <button type="button" class="btn default" data-dismiss="modal">Close</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- end of approve Modal-->