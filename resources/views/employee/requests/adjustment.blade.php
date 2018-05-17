<div class="portlet box grey">
    <div class="portlet-title">
        <div class="caption">
            <i class="fa fa-gift"></i> Time Adjustment
        </div>
        <div class="tools">
            <a href="javascript:;" class="collapse" data-original-title="" title=""></a>
        </div>
    </div>
    <div class="portlet-body">
        <ul class="nav nav-tabs" id="auth" v-cloak>
            <li class="active">
                <a href="#pending_adjustment_tab" data-toggle="tab">Pending Adjustments</a>
            </li>
            <li>
                <a href="#adjustment_history_tab" data-toggle="tab">Adjustment History</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade active in" id="pending_adjustment_tab">
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
                            <th>Adjust. Date</th>
                            <th>Adjust. Time</th>
                            <th>Mode</th>
                            <th>Notes</th>
                            <th>Approval</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(adjustment,key) in filtered">
                            <td v-if="!isEmployee">
                                <a v-bind:href="'../../employee/'+adjustment.user_id" target="_blank">@{{ adjustment.name }}</a>
                            </td>
                            <td>@{{ formatDateTime(adjustment.created_at,"MM/DD/YYYY LT") }}</td>
                            <td>@{{ formatDateTime(adjustment.request_data.date,"MM/DD/YYYY") }}</td>
                            <td>@{{ formatDateTime(adjustment.request_data.date+ " " +adjustment.request_data.time,"LT") }}</td>
                            <td>@{{ adjustment.request_data.mode }}</td>
                            <td>@{{ adjustment.request_note }}</td>
                            <td>
                                <ol>
                                    <li v-for="(feedback,key) in adjustment.action_data.approved_by">
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
                                        @click="deleteAdjustment(adjustment, $event)">Delete</button>
                                <button class="btn btn-success btn-xs" v-if="!isEmployee && adjustment.for_my_approval"
                                        @click="showActionModal(adjustment,'approve')">Approve</button>
                                <button class="btn btn-danger btn-xs" v-if="!isEmployee && adjustment.for_my_approval"
                                        @click="showActionModal(adjustment,'deny')">Deny</button>
                                <a target="_blank" v-bind:href="'../../requests/printRequest/'+adjustment.id" class="btn btn-warning btn-xs">Print</a>
                                <button class="btn purple btn-xs" v-if="trackable && !isEmployee" @click="editRequest(adjustment)">Edit</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                @include('pagination.footer_0')
            </div>
            <div class="tab-pane fade" id="adjustment_history_tab">
                @include('pagination.header_1')
                <div class="scrollable">
                    <table class="table table-hover table-bordered" style="font-size:11.5px;">
                        <thead>
                        <tr>
                            <th v-if="!isEmployee">Name</th>
                            <th>Date/Time Filed</th>
                            <th>Adjustment Date</th>
                            <th>Adjustment Time</th>
                            <th>Mode</th>
                            <th>Notes</th>
                            <th>Approval</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(adjustment,key) in filtered1">
                            <td v-if="!isEmployee">
                                <a v-bind:href="'../../employee/'+adjustment.user_id" target="_blank">@{{ adjustment.name }}</a>
                            </td>
                            <td>@{{ formatDateTime(adjustment.created_at,"MM/DD/YYYY LT") }}</td>
                            <td>@{{ formatDateTime(adjustment.request_data.date,"MM/DD/YYYY") }}</td>
                            <td>@{{ formatDateTime(adjustment.request_data.date+ " " +adjustment.request_data.time,"LT") }}</td>
                            <td>@{{ adjustment.request_data.mode }}</td>
                            <td>@{{ adjustment.request_note }}</td>
                            <td>
                                <ol>
                                    <li v-for="(feedback,key) in adjustment.action_data.approved_by" v-if="! ((feedback.status == 'Pending' || feedback.status =='Vacant') && adjustment.request_data.status=='denied' )" >
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
                                <span class="label label-danger" v-if="adjustment.request_data.status == 'denied'">Denied</span>
                                <span class="label label-success" v-if="adjustment.request_data.status == 'approved'">Approved</span>
                                <span class="label label-warning" v-if="adjustment.request_data.status == 'pending'">Pending</span>
                            </td>
                            <td>
                                <a target="_blank" v-bind:href="'../../requests/printRequest/'+adjustment.id" class="btn btn-warning btn-xs">Print</a>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                @include('pagination.footer_1')
            </div>
        </div>

        <!-- Start of add branch Modal-->
        <div class="modal fade" id="adjustment-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title" v-if="actionModal.action=='approve'">Approve Adjustment</h4>
                        <h4 class="modal-title" v-if="actionModal.action=='deny'">Deny Adjustment</h4>
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
                        <button type="button" v-if="actionModal.action=='approve'" data-loading-text="Processing..." @click="approveAdjustment($event)" class="btn blue">Okay</button>
                        <button type="button" v-if="actionModal.action=='deny'" data-loading-text="Processing..." @click="denyAdjustment($event)" class="btn red">Okay</button>
                        <button type="button" class="btn default" data-dismiss="modal">Close</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- end of approve Modal-->

        <!-- Start of add branch Modal-->
        <div class="modal fade" id="edit-adjustment" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title">Edit Request</h4>
                    </div>
                    <div class="modal-body">

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-danger pull-left" @click="deleteRequest(edit_adjustment)">Delete</button>
                        <button type="button" class="btn default" data-dismiss="modal">Close</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- end of approve Modal-->
    </div>
</div>
