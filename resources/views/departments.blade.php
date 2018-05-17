@extends('layouts.main')
@section('content')

<div id="departments" class="portlet box green-seagreen" v-cloak>
	<div class="portlet-title">
		<div class="caption">
			<i class="fa fa-gift"></i>Department List
			<a @click="showAddModal" type="button" class="btn purple btn-sm">Add Department</a>
		</div>
		<div class="tools">
			<a href="javascript:;" class="collapse" data-original-title="" title="">
			</a>
		</div>
	</div>
	<div class="portlet-body">
		@include('pagination.header_0')
		<div class="scrollable">
			<table class="table dataTable table-striped table-bordered table-hover">
				<thead>
				<tr>
					<th style="width:120px">
						<a @click="setOrderBy('id',0)">Department ID.
							<span v-if="pagination[0].sort_by=='id'" >
									<i v-if="pagination[0].sort_order==-1" class="fa fa-angle-down pull-right"></i>
									<i v-if="pagination[0].sort_order==1" class="fa fa-angle-up pull-right"></i>
								</span>
						</a>
					</th>
					<th>
						<a @click="setOrderBy('department_name',0)">Department Name
							<span v-if="pagination[0].sort_by=='department_name'" >
									<i v-if="pagination[0].sort_order==-1" class="fa fa-angle-down pull-right"></i>
									<i v-if="pagination[0].sort_order==1" class="fa fa-angle-up pull-right"></i>
								</span>
						</a>
					</th>
					<th>Department Description</th>
					<th></th>
				</tr>
				</thead>
				<tbody>
				<tr v-for="department in filtered">
					<td><a @click="showViewModal(department)"> @{{ department.id }} </a></td>
					<td><a @click="showViewModal(department)">@{{ department.department_name }}</a></td>
					<td>@{{ department.department_desc }}</td>
					<td>
						<button class="btn btn-info btn-xs" type="button" @click="editDepartment(department)">Edit</button>
						<button class="btn btn-danger btn-xs" type="button" v-if="department.department_active == 1"
								@click="deactivateDepartment(department)">Deactivate</button>
						<button class="btn btn-success btn-xs" type="button" v-if="department.department_active != 1"
								@click="activateDepartment(department)">Activate</button>
					</td>
				</tr>
				</tbody>
			</table>
		</div>
		@include('pagination.footer_0')
	</div>

	<!-- Start of add department Modal-->
	<div class="modal fade" id="add-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
					<h4 class="modal-title" v-if="newDepartment.id == 0 ">Add Department</h4>
                    <h4 class="modal-title" v-if="newDepartment.id != 0 ">Edit Department</h4>
				</div>
				<div class="modal-body">
					<div class="form-body">
						<div class="row">
							<div class="col-sm-4">
								<div class="form-group">
									<label class="control-label bold">Department Name</label>
									<input type="text" v-model="newDepartment.department_name" class="form-control">
								</div>
							</div>
							<!--/span-->
							<div class="col-sm-8">
								<div class="form-group">
									<label class="control-label bold">Description</label>
									<input type="text" v-model="newDepartment.department_desc" class="form-control">
								</div>
							</div>
							<!--/span-->
						</div>
						<!--/row-->
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" @click="saveDepartment" class="btn blue">Save</button>
					<button type="button" class="btn default" data-dismiss="modal">Close</button>
				</div>
			</div>
			<!-- /.modal-content -->
		</div>
		<!-- /.modal-dialog -->
	</div>
	<!-- end of add department Modal-->


	<!-- Start of add department Modal-->
	<div class="modal fade" id="view-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
					<h4 class="modal-title" v-if="newDepartment.id == 0 ">@{{ display.department_name }}</h4>
				</div>
				<div class="modal-body">
					<div class="form-body">
                        @include('pagination.header_1')
						<div class="scrollable">
							<table class="table table-striped table-bordered table-hover">
								<thead>
								<tr>
									<th>Employee No.</th>
									<th>Name</th>
									<th>Position</th>
								</tr>
								</thead>
								<tbody>
								<tr v-for="employee in filtered1">
									<td><a v-bind:href="'../../employee/'+employee.user_id"> @{{ employee.employee_no }} </a></td>
									<td><a v-bind:href="'../../employee/'+employee.user_id"> @{{ employee.name }} </a></td>
									<td>@{{ employee.position_name }}</td>
								</tr>
								</tbody>
							</table>
						</div>
                        @include('pagination.footer_1')
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
	<!-- end of add department Modal-->
</div>

<script src="../../assets/vuejs/instances/departments.js?cache={{ rand() }}"></script>
@endsection