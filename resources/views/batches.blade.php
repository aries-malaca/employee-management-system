@extends('layouts.main')
@section('content')

<div class="row" id="batches">
    <div class="col-sm-8">
        <div class="portlet box green-seagreen">
        	<div class="portlet-title">
        		<div class="caption">
        			<i class="fa fa-gift"></i>Batch List
        			<a @click="showAddModal" type="button" class="btn yellow btn-sm">Add Batch</a>
        		</div>
        		<div class="tools">
        			<a href="javascript:;" class="collapse" data-original-title="" title="">
        			</a>
        		</div>
        	</div>
        	<div class="portlet-body">
        	    <table class="table dataTable table-striped table-bordered table-hover">
					<thead>
						<tr>
							<th style="width:120px">Batch ID</th>
							<th>Batch Name</th>
							<th style="width:120px"></th>
						</tr>
					</thead>
					<tbody>
                        <tr v-for="(batch,key) in batches">
                            <td>@{{ batch.id }}</td>
                            <td><a @click="showViewModal(batch)">@{{ batch.batch_name }}</a></td>
                            <td>
                                <button class="btn btn-info btn-xs" type="button" @click="editBatch(batch)">Edit</button>
                                <button class="btn btn-danger btn-xs" type="button" @click="deleteBatch(batch)">Delete</button>
                            </td>
                        </tr>
					</tbody>
        	    </table>
        	</div>
        </div>
    </div>

	<!-- Start of add batch Modal-->
	<div class="modal fade" id="add-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
					<h4 v-if="newBatch.id==0" class="modal-title">Add Batch</h4>
					<h4 v-else class="modal-title">Update Batch</h4>
				</div>
				<div class="modal-body">
					<div class="form-body">
						<div class="row">
							<div class="col-sm-12">
								<div class="form-group">
									<label class="control-label bold">Batch Name</label>
									<input type="text" v-model="newBatch.batch_name" class="form-control">
								</div>
							</div>
							<!--/span-->
						</div>
						<!--/row-->
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" @click="addBatch" v-if="newBatch.id==0" class="btn blue">Save</button>
					<button type="submit" @click="updateBatch" v-else class="btn blue">Update</button>
					<button type="button" class="btn default" data-dismiss="modal">Close</button>
				</div>
			</div>
			<!-- /.modal-content -->
		</div>
		<!-- /.modal-dialog -->
	</div>
	<!-- end of add batch Modal-->

    <!-- Start of view batch Modal-->
    <div class="modal fade" id="view-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">Batch @{{ display.name }} Employees</h4>
                </div>
                <div class="modal-body">
                    @include('pagination.header_0')
					<div class="scrollable">
						<table class="table table-striped table-bordered table-hover">
							<thead>
							<tr>
								<th>Employee ID</th>
								<th>Name</th>
								<th>Department</th>
								<th>Position</th>
							</tr>
							</thead>
							<tbody>
							<tr v-for="employee in filtered">
								<td><a target="_blank" v-bind:href="'../../employee/'+employee.user_id">@{{ employee.employee_no }}</a></td>
								<td><a target="_blank" v-bind:href="'../../employee/'+employee.user_id">@{{ employee.name }}</a></td>
								<td>@{{ employee.department_name }}</td>
								<td>@{{ employee.position_name }}</td>
							</tr>
							</tbody>
						</table>
					</div>
                    @include('pagination.footer_0')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- end of view batch Modal-->

</div>
<script src="../../assets/vuejs/instances/batches.js?cache={{ rand() }}"></script>
@endsection