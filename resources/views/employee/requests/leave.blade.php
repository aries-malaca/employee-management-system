<div class="portlet box yellow">
	<div class="portlet-title">
		<div class="caption">
			<i class="fa fa-gift"></i> Leaves
		</div>
		<div class="tools">
			<a href="javascript:;" class="collapse" data-original-title="" title=""></a>
		</div>
	</div>
	<div class="portlet-body">
		<ul class="nav nav-tabs" id="auth" v-cloak>
			<li class="active">
				<a href="#pending_leave_tab" data-toggle="tab">Pending Leave</a>
			</li>
			<li>
				<a href="#leave_history_tab" data-toggle="tab">Leave History</a>
			</li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane fade active in" id="pending_leave_tab">
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
							<th>Date of Leave</th>
							<th>Days</th>
							<th>Leave Type</th>
							<th>Mode</th>
							<th>Reason</th>
							<th>Approval</th>
							<th></th>
						</tr>
						</thead>
						<tbody>
						<tr v-for="(leave,key) in filtered">
							<td v-if="!isEmployee">
								<a v-bind:href="'../../employee/'+leave.user_id" target="_blank">@{{ leave.name }}</a>
							</td>
							<td>@{{ formatDateTime(leave.created_at,"MM/DD/YYYY LT") }}</td>
							<td>
								@{{ formatDateTime(leave.request_data.date_start,"MM/DD/YYYY") }}
								<span v-if="leave.request_data.date_start != leave.request_data.date_end">
						- @{{ formatDateTime(leave.request_data.date_end,"MM/DD/YYYY") }}
					</span>
							</td>
							<td>@{{ leave.request_data.days }}</td>
							<td>@{{ getLeaveTypeName(leave.request_data.leave_type) }}</td>
							<td>@{{ leave.request_data.mode }}</td>
							<td>@{{ leave.request_note }}</td>
							<td>
								<ol>
									<li v-for="(feedback,key) in leave.action_data.approved_by">
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
										@click="deleteLeave(leave, $event)">Delete</button>
								<button class="btn btn-success btn-xs" v-if="!isEmployee && leave.for_my_approval"
										@click="showActionModal(leave,'approve')">Approve</button>
								<button class="btn btn-danger btn-xs" v-if="!isEmployee && leave.for_my_approval"
										@click="showActionModal(leave,'deny')">Deny</button>
								<button class="btn btn-xs btn-info" v-if="!isEmployee && leave.for_my_approval"
										@click="showCreditsModal(leave)">View Credits</button>
								<a target="_blank" v-bind:href="'../../requests/printRequest/'+leave.id" class="btn btn-warning btn-xs">Print</a>
								<button class="btn purple btn-xs" v-if="trackable && !isEmployee" @click="editRequest(leave)">Edit</button>
							</td>
						</tr>
						</tbody>
					</table>
				</div>
				@include('pagination.footer_0')
			</div>
			<div class="tab-pane fade in" id="leave_history_tab">
				@include('pagination.header_1')
				<div class="scrollable">
					<table class="table table-hover table-bordered" style="font-size:11.5px;">
						<thead>
						<tr>
							<th v-if="!isEmployee">Name</th>
							<th>Date/Time Filed</th>
							<th>Date of Leave</th>
							<th>Days</th>
							<th>Leave Type</th>
							<th>Mode</th>
							<th>Reason</th>
							<th>Approval</th>
							<th>Status</th>
							<th></th>
						</tr>
						</thead>
						<tbody>
						<tr v-for="(leave,key) in filtered1">
							<td v-if="!isEmployee">
								<a v-bind:href="'../../employee/'+leave.user_id" target="_blank">@{{ leave.name }}</a>
							</td>
							<td>@{{ formatDateTime(leave.created_at,"MM/DD/YYYY LT") }}</td>
							<td>
								@{{ formatDateTime(leave.request_data.date_start,"MM/DD/YYYY") }}
								<span v-if="leave.request_data.date_start != leave.request_data.date_end">
						- @{{ formatDateTime(leave.request_data.date_end,"MM/DD/YYYY") }}
					</span>
							</td>
							<td>@{{ leave.request_data.days }}</td>
							<td>@{{ getLeaveTypeName(leave.request_data.leave_type) }}</td>
							<td>@{{ leave.request_data.mode }}</td>
							<td>@{{ leave.request_note }}</td>
							<td>
								<ol>
									<li v-for="(feedback,key) in leave.action_data.approved_by" v-if="! ((feedback.status == 'Pending' || feedback.status =='Vacant') && leave.request_data.status=='denied' )" >
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
								<span class="label label-danger" v-if="leave.request_data.status == 'denied'">Denied</span>
								<span class="label label-success" v-if="leave.request_data.status == 'approved'">Approved</span>
								<span class="label label-warning" v-if="leave.request_data.status == 'pending'">Pending</span>
							</td>
							<td>
								<a target="_blank" v-bind:href="'../../requests/printRequest/'+leave.id" class="btn btn-warning btn-xs">Print</a>
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

<!-- Start of approve Modal-->
<div class="modal fade" id="leave-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title" v-if="actionModal.action=='approve'">Approve Leave</h4>
				<h4 class="modal-title" v-if="actionModal.action=='deny'">Deny Leave</h4>
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
				<button type="button" v-if="actionModal.action=='approve'" @click="approveLeave($event)" data-loading-text="Processing..." class="btn blue">Okay</button>
				<button type="button" v-if="actionModal.action=='deny'" @click="denyLeave($event)" data-loading-text="Processing..." class="btn red">Okay</button>
				<button type="button" class="btn default" data-dismiss="modal">Close</button>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- end of approve Modal-->

<!-- Start of credits Modal-->
<div class="modal fade" id="credits-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title">Leave Credits</h4>
			</div>
			<div class="modal-body">
				@include('employee.common.leave_credits')
			</div>
			<div class="modal-footer">
				<button type="button" class="btn default" data-dismiss="modal">Close</button>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- end of credits Modal-->

<!-- Start of add branch Modal-->
<div class="modal fade" id="edit-leave" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title">Edit Request</h4>
			</div>
			<div class="modal-body">

			</div>
			<div class="modal-footer">
				<button class="btn btn-danger pull-left" @click="deleteRequest(edit_leave)">Delete</button>
				<button type="button" class="btn default" data-dismiss="modal">Close</button>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- end of approve Modal-->