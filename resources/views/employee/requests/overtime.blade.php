<div class="portlet box purple">
	<div class="portlet-title">
		<div class="caption">
			<i class="fa fa-gift"></i> Overtimes
		</div>
		<div class="tools">
			<a href="javascript:;" class="collapse" data-original-title="" title=""></a>
		</div>
	</div>
	<div class="portlet-body">
		<ul class="nav nav-tabs" id="auth" v-cloak>
			<li class="active">
				<a href="#pending_overtime_tab" data-toggle="tab">Pending Overtime</a>
			</li>
			<li>
				<a href="#overtime_history_tab" data-toggle="tab">Overtime History</a>
			</li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane fade active in" id="pending_overtime_tab">
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
							<th>Date/Time Start</th>
							<th>Date/Time End</th>
							<th>Hours</th>
							<th>Notes</th>
							<th>Approval</th>
							<th></th>
						</tr>
						</thead>
						<tbody>
						<tr v-for="(overtime,key) in filtered">
							<td v-if="!isEmployee">
								<a v-bind:href="'../../employee/'+overtime.user_id" target="_blank">@{{ overtime.name }}</a>
							</td>
							<td>@{{ formatDateTime(overtime.created_at,"MM/DD/YYYY LT") }}</td>
							<td>
								@{{ formatDateTime(overtime.request_data.date_start +" "+ overtime.request_data.time_start,"MM/DD/YYYY LT") }}</td>
							</td>
							<td>
								@{{ formatDateTime(overtime.request_data.date_end +" "+ overtime.request_data.time_end,"MM/DD/YYYY LT") }}</td>
							</td>
							<td style="font-weight: bolder;color:red">@{{ getHours(overtime.request_data).hours + 'H' +', ' + getHours(overtime.request_data).minutes +'M' }}</td>
							<td>@{{ overtime.request_note }}</td>
							<td>
								<ol>
									<li v-for="(feedback,key) in overtime.action_data.approved_by">
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
										@click="deleteOvertime(overtime,$event)">Delete</button>
								<button class="btn btn-success btn-xs" v-if="!isEmployee && overtime.for_my_approval"
										@click="showActionModal(overtime,'approve')">Approve</button>
								<button class="btn btn-danger btn-xs" v-if="!isEmployee && overtime.for_my_approval"
										@click="showActionModal(overtime,'deny')">Deny</button>
								<a target="_blank" v-bind:href="'../../requests/printRequest/'+overtime.id" class="btn btn-warning btn-xs">Print</a>
								<button class="btn purple btn-xs" v-if="trackable && !isEmployee" @click="editRequest(overtime)">Edit</button>
							</td>
						</tr>
						</tbody>
					</table>
				</div>
				@include('pagination.footer_0')
			</div>
			<div class="tab-pane fade in" id="overtime_history_tab">
				@include('pagination.header_1')
				<div class="scrollable">
					<table class="table table-hover table-bordered" style="font-size:11.5px;">
						<thead>
						<tr>
							<th v-if="!isEmployee">Name</th>
							<th>Date/Time Filed</th>
							<th>Date/Time Start</th>
							<th>Date/Time End</th>
							<th>Hours</th>
							<th>Notes</th>
							<th>Approval</th>
							<th>Status</th>
							<th></th>
						</tr>
						</thead>
						<tbody>
						<tr v-for="(overtime,key) in filtered1">
							<td v-if="!isEmployee">
								<a v-bind:href="'../../employee/'+overtime.user_id" target="_blank">@{{ overtime.name }}</a>
							</td>
							<td>@{{ formatDateTime(overtime.created_at,"MM/DD/YYYY LT") }}</td>
							<td>
								@{{ formatDateTime(overtime.request_data.date_start +" "+ overtime.request_data.time_start,"MM/DD/YYYY LT") }}</td>
							</td>
							<td>
								@{{ formatDateTime(overtime.request_data.date_end +" "+ overtime.request_data.time_end,"MM/DD/YYYY LT") }}</td>
							</td>
							<td style="font-weight: bolder;color:red">@{{ getHours(overtime.request_data).hours + 'H' +', ' + getHours(overtime.request_data).minutes +'M' }}</td>
							<td>@{{ overtime.request_note }}</td>
							<td>
								<ol>
									<li v-for="(feedback,key) in overtime.action_data.approved_by" v-if="! ((feedback.status == 'Pending' || feedback.status =='Vacant') && overtime.request_data.status=='denied' )" >
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
								<span class="label label-danger" v-if="overtime.request_data.status == 'denied'">Denied</span>
								<span class="label label-success" v-if="overtime.request_data.status == 'approved'">Approved</span>
								<span class="label label-warning" v-if="overtime.request_data.status == 'pending'">Pending</span>
							</td>
							<td>
								<a target="_blank" v-bind:href="'../../requests/printRequest/'+overtime.id" class="btn btn-warning btn-xs">Print</a>
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
<div class="modal fade" id="overtime-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title" v-if="actionModal.action=='approve'">Approve Overtime</h4>
				<h4 class="modal-title" v-if="actionModal.action=='deny'">Deny Overtime</h4>
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
				<button type="button" v-if="actionModal.action=='approve'" @click="approveOvertime($event)" data-loading-text="Processing..." class="btn blue">Okay</button>
				<button type="button" v-if="actionModal.action=='deny'" @click="denyOvertime($event)" data-loading-text="Processing..." class="btn red">Okay</button>
				<button type="button" class="btn default" data-dismiss="modal">Close</button>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- end of approve Modal-->

<!-- Start of add branch Modal-->
<div class="modal fade" id="edit-overtime" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title">Edit Request</h4>
			</div>
			<div class="modal-body">

			</div>
			<div class="modal-footer">
				<button class="btn btn-danger pull-left" @click="deleteRequest(edit_overtime)">Delete</button>
				<button type="button" class="btn default" data-dismiss="modal">Close</button>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- end of approve Modal-->