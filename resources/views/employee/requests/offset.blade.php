<div class="portlet box blue">
    <div class="portlet-title">
        <div class="caption">
            <i class="fa fa-gift"></i> Offsets
        </div>
        <div class="tools">
            <a href="javascript:;" class="collapse" data-original-title="" title=""></a>
        </div>
    </div>
    <div class="portlet-body">
        <ul class="nav nav-tabs" id="auth" v-cloak>
            <li class="active">
                <a href="#pending_offset_tab" data-toggle="tab">Pending Offset</a>
            </li>
            <li>
                <a href="#offset_history_tab" data-toggle="tab">Offset History</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade active in" id="pending_offset_tab">
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
                            <th>Offset In</th>
                            <th>Offset Out</th>
                            <th>Hours</th>
                            <th>Notes</th>
                            <th>Approval</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(offset,key) in filtered">
                            <td v-if="!isEmployee">
                                <a v-bind:href="'../../employee/'+offset.user_id" target="_blank">@{{ offset.name }}</a>
                            </td>
                            <td>@{{ formatDateTime(offset.created_at,"MM/DD/YYYY LT") }}</td>
                            <td>
                                @{{ formatDateTime(offset.request_data.date_start +" "+ offset.request_data.time_start,"MM/DD/YYYY LT") }}</td>
                            </td>
                            <td>
                                @{{ formatDateTime(offset.request_data.date_end +" "+ offset.request_data.time_end,"MM/DD/YYYY LT") }}</td>
                            </td>
                            <td>@{{ getHours(offset.request_data).hours + 'H' +', ' + getHours(offset.request_data).minutes +'M' }}</td>
                            <td>
                                @{{ offset.request_note }}
                                <br/>
                                <a href="javascript:;" @click="showOffsetDetails(offset)">Details</a>
                            </td>
                            <td>
                                <ol>
                                    <li v-for="(feedback,key) in offset.action_data.approved_by">
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
                                        @click="deleteOffset(offset,$event)">Delete</button>
                                <button class="btn btn-success btn-xs" v-if="!isEmployee && offset.for_my_approval"
                                        @click="showActionModal(offset,'approve')">Approve</button>
                                <button class="btn btn-danger btn-xs" v-if="!isEmployee && offset.for_my_approval"
                                        @click="showActionModal(offset,'deny')">Deny</button>
                                <a target="_blank" v-bind:href="'../../requests/printRequest/'+offset.id" class="btn btn-warning btn-xs">Print</a>
                                <button class="btn purple btn-xs" v-if="trackable && !isEmployee" @click="editRequest(offset)">Edit</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                @include('pagination.footer_0')
            </div>
            <div class="tab-pane fade in" id="offset_history_tab">
                @include('pagination.header_1')
                <div class="scrollable">
                    <table class="table table-hover table-bordered" style="font-size:11.5px;">
                        <thead>
                        <tr>
                            <th v-if="!isEmployee">Name</th>
                            <th>Date/Time Filed</th>
                            <th>Offset In</th>
                            <th>Offset Out</th>
                            <th>Hours</th>
                            <th>Notes</th>
                            <th>Approval</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(offset,key) in filtered1">
                            <td v-if="!isEmployee">
                                <a v-bind:href="'../../employee/'+offset.user_id" target="_blank">@{{ offset.name }}</a>
                            </td>
                            <td>@{{ formatDateTime(offset.created_at,"MM/DD/YYYY LT") }}</td>
                            <td>
                                @{{ formatDateTime(offset.request_data.date_start +" "+ offset.request_data.time_start,"MM/DD/YYYY LT") }}</td>
                            </td>
                            <td>
                                @{{ formatDateTime(offset.request_data.date_end +" "+ offset.request_data.time_end,"MM/DD/YYYY LT") }}</td>
                            </td>
                            <td>@{{ getHours(offset.request_data).hours + 'H' +', ' + getHours(offset.request_data).minutes +'M' }}</td>
                            <td>
                                @{{ offset.request_note }}
                                <br/>
                                <a href="javascript:;" @click="showOffsetDetails(offset)">Details</a>
                            </td>
                            <td>
                                <ol>
                                    <li v-for="(feedback,key) in offset.action_data.approved_by" v-if="! ((feedback.status == 'Pending' || feedback.status =='Vacant') && offset.request_data.status=='denied' )" >
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
                                <span class="label label-danger" v-if="offset.request_data.status == 'denied'">Denied</span>
                                <span class="label label-success" v-if="offset.request_data.status == 'approved'">Approved</span>
                                <span class="label label-warning" v-if="offset.request_data.status == 'pending'">Pending</span>
                            </td>
                            <td>
                                <a target="_blank" v-bind:href="'../../requests/printRequest/'+offset.id" class="btn btn-warning btn-xs">Print</a>
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



<!-- Start of add branch Modal-->
<div class="modal fade" id="offset-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title" v-if="actionModal.action=='approve'">Approve Offset</h4>
                <h4 class="modal-title" v-if="actionModal.action=='deny'">Deny Offset</h4>
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
                <button type="button" v-if="actionModal.action=='approve'" @click="approveOffset($event)" data-loading-text="Processing..." class="btn blue">Okay</button>
                <button type="button" v-if="actionModal.action=='deny'" @click="denyOffset($event)" class="btn red" data-loading-text="Processing...">Okay</button>
                <button type="button" class="btn default" data-dismiss="modal">Close</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- end of approve Modal-->


<!-- Start of add branch Modal-->
<div class="modal fade" id="view-offset-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Rendered Duties</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body">
                <div class="scrollable">
                    <table v-if="viewOffset.request_data !== undefined" class="table table-hover table-bordered">
                        <thead>
                        <tr>
                            <th>Duty Start</th>
                            <th>Duty End</th>
                            <th>Notes</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="duty in viewOffset.request_data.duties">
                            <td>
                                @{{ formatDateTime(duty.date_start,"MM/DD/YYYY") }} @{{ formatDateTime(duty.date_start+" "+duty.time_start,"LT") }}
                            </td>
                            <td>
                                @{{ formatDateTime(duty.date_end,"MM/DD/YYYY") }} @{{ formatDateTime(duty.date_end+" "+duty.time_end,"LT") }}
                            </td>
                            <td>
                                @{{ duty.notes }}
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn default" data-dismiss="modal">Close</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- end of approve Modal-->

<!-- Start of add branch Modal-->
<div class="modal fade" id="edit-offset" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">Edit Request</h4>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <button class="btn btn-danger pull-left" @click="deleteRequest(edit_offset)">Delete</button>
                <button type="button" class="btn default" data-dismiss="modal">Close</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- end of approve Modal-->