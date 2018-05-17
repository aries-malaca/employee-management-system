<div class="portlet box purple">
	<div class="portlet-title">
		<div class="caption">
			<i class="fa fa-gift"></i> Travels
		</div>
		<div class="tools">
			<a href="javascript:;" class="collapse" data-original-title="" title=""></a>
		</div>
	</div>
	<div class="portlet-body">
		<ul class="nav nav-tabs" id="auth" v-cloak>
			<li class="active">
				<a href="#pending_travel_tab" data-toggle="tab">Pending Travel</a>
			</li>
			<li>
				<a href="#travel_history_tab" data-toggle="tab">Travel History</a>
			</li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane fade active in" id="pending_travel_tab">
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
						<tr v-for="(travel,key) in filtered">
							<td v-if="!isEmployee">
								<a v-bind:href="'../../employee/'+travel.user_id" target="_blank">@{{ travel.name }}</a>
							</td>
							<td>@{{ formatDateTime(travel.created_at,"MM/DD/YYYY LT") }}</td>
							<td>
								@{{ formatDateTime(travel.request_data.date_start +" "+ travel.request_data.time_start,"MM/DD/YYYY LT") }}</td>
							</td>
							<td>
								@{{ formatDateTime(travel.request_data.date_end +" "+ travel.request_data.time_end,"MM/DD/YYYY LT") }}</td>
							</td>
							<td>@{{ getHours(travel) }}</td>
							<td>@{{ travel.request_note }}</td>
							<td>
								<ol>
									<li v-for="(feedback,key) in travel.action_data.approved_by">
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
										@click="deleteTravel(travel,$event)">Delete</button>
								<button class="btn btn-success btn-xs" v-if="!isEmployee && travel.for_my_approval"
										@click="showActionModal(travel,'approve')">Approve</button>
								<button class="btn btn-danger btn-xs" v-if="!isEmployee && travel.for_my_approval"
										@click="showActionModal(travel,'deny')">Deny</button>
								<a target="_blank" v-bind:href="'../../requests/printRequest/'+travel.id" class="btn btn-warning btn-xs">Print</a>
								<button class="btn purple btn-xs" v-if="trackable && !isEmployee" @click="editRequest(travel)">Edit</button>
							</td>
						</tr>
						</tbody>
					</table>
				</div>
				@include('pagination.footer_0')
			</div>
			<div class="tab-pane fade in" id="travel_history_tab">
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
						<tr v-for="(travel,key) in filtered1">
							<td v-if="!isEmployee">
								<a v-bind:href="'../../employee/'+travel.user_id" target="_blank">@{{ travel.name }}</a>
							</td>
							<td>@{{ formatDateTime(travel.created_at,"MM/DD/YYYY LT") }}</td>
							<td>
								@{{ formatDateTime(travel.request_data.date_start +" "+ travel.request_data.time_start,"MM/DD/YYYY LT") }}</td>
							</td>
							<td>
								@{{ formatDateTime(travel.request_data.date_end +" "+ travel.request_data.time_end,"MM/DD/YYYY LT") }}</td>
							</td>
							<td>@{{ getHours(travel) }}</td>
							<td>@{{ travel.request_note }}</td>
							<td>
								<ol>
									<li v-for="(feedback,key) in travel.action_data.approved_by" v-if="! ((feedback.status == 'Pending' || feedback.status =='Vacant') && travel.request_data.status=='denied' )" >
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
								<span class="label label-danger" v-if="travel.request_data.status == 'denied'">Denied</span>
								<span class="label label-success" v-if="travel.request_data.status == 'approved'">Approved</span>
								<span class="label label-warning" v-if="travel.request_data.status == 'pending'">Pending</span>
							</td>
							<td>
								<a target="_blank" v-bind:href="'../../requests/printRequest/'+travel.id" class="btn btn-warning btn-xs">Print</a>
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
<div class="modal fade" id="travel-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title" v-if="actionModal.action=='approve'">Approve Travel</h4>
				<h4 class="modal-title" v-if="actionModal.action=='deny'">Deny Travel</h4>
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
				<button type="button" v-if="actionModal.action=='approve'" data-loading-text="Processing..." @click="approveTravel($event)" class="btn blue">Okay</button>
				<button type="button" v-if="actionModal.action=='deny'" data-loading-text="Processing..." @click="denyTravel($event)" class="btn red">Okay</button>
				<button type="button" class="btn default" data-dismiss="modal">Close</button>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- end of approve Modal-->

<!-- Start of add branch Modal-->
<div class="modal fade" id="edit-travel" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title">Edit Request</h4>
			</div>
			<div class="modal-body">

			</div>
			<div class="modal-footer">
				<button class="btn btn-danger pull-left" @click="deleteRequest(edit_travel)">Delete</button>
				<button type="button" class="btn default" data-dismiss="modal">Close</button>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- end of approve Modal-->