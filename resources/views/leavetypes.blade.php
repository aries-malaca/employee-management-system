@extends('layouts.main')
@section('content')

<div class="row" id="leave_types">
    <div class="col-md-12">
        <div class="portlet box blue">
        	<div class="portlet-title">
        		<div class="caption">
        			<i class="fa fa-gift"></i> Leave Types
        			<a @click="showAddModal()" class="btn purple btn-sm">Add Leave Type</a>
        		</div>
        		<div class="tools">
        			<a href="javascript:;" class="collapse" data-original-title="" title=""></a>
        		</div>
        	</div>
        	<div class="portlet-body">
                <div class="scrollable">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Leave Name</th>
                            <th>Leave Description</th>
                            <th>Paid</th>
                            <th>Gender</th>
                            <th>Max per Year</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(leave,key) in leave_types">
                            <td>@{{ leave.id }}</td>
                            <td>@{{ leave.leave_type_name }}</td>
                            <td>@{{ leave.leave_type_description }}</td>
                            <td>@{{ leave.leave_type_data.paid=='true' ? 'YES':'NO' }}</td>
                            <td>@{{ leave.leave_type_data.gender.toUpperCase() }}</td>
                            <td>@{{ leave.leave_type_max }}</td>
                            <td>
                                <button class="btn btn-info btn-xs" @click="editLeaveType(leave)">Edit</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Start of add leave_type Modal-->
    <div class="modal fade" id="add-modal" tabindex="-1" role="dialog"
         aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title" v-if="newLeave.id == 0">Add Leave Type</h4>
                    <h4 class="modal-title" v-else>Edit Leave Type</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label bold">Leave Name</label>
                                <input type="text" v-model="newLeave.leave_type_name" class="form-control">
                            </div>
                        </div><!--/span-->
                        <div class="col-sm-8">
                            <div class="form-group">
                                <label class="control-label bold">Leave Description</label>
                                <textarea v-model="newLeave.leave_type_description" class="form-control"></textarea>
                            </div>
                        </div><!--/span-->
                    </div><!--/row-->
                    <div class="row">
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label class="control-label bold">Is Paid?</label>
                                <select class="form-control" v-model="newLeave.paid">
                                    <option v-bind:value="true">YES</option>
                                    <option v-bind:value="false">NO</option>
                                </select>
                            </div>
                        </div><!--/span-->
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label class="control-label bold">Gender</label>
                                <select class="form-control" v-model="newLeave.gender">
                                    <option value="female">FEMALE</option>
                                    <option value="male">MALE</option>
                                    <option value="both">BOTH</option>
                                </select>
                            </div>
                        </div><!--/span-->
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label class="control-label bold">Max Per Year</label>
                                <input type="number" v-model.number="newLeave.leave_type_max" class="form-control">
                            </div>
                        </div><!--/span-->
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label class="control-label bold">Max / LifeTime</label>
                                <input type="number" v-model.number="newLeave.limit_per_lifetime" class="form-control">
                            </div>
                        </div><!--/span-->
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label bold">Date Restriction</label>
                                <select v-model="newLeave.within_condition" class="form-control">
                                    <option v-bind:value="condition.value" v-for="condition in within">
                                        @{{ condition.label }}
                                    </option>
                                </select>
                            </div>
                        </div><!--/span-->
                    </div><!--/row-->
                    <div class="row">
                        <div class="col-sm-2">
                            <label class="control-label bold">Allow Staggered</label>
                            <select class="form-control" v-model="newLeave.is_staggered">
                                <option v-bind:value="true">YES</option>
                                <option v-bind:value="false">NO</option>
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <label class="control-label bold">Allow Half Day</label>
                            <select class="form-control" v-model="newLeave.allow_half_day">
                                <option v-bind:value="true">YES</option>
                                <option v-bind:value="false">NO</option>
                            </select>
                        </div>
                        <div class="col-sm-8">
                            <label class="control-label bold">Reminder</label>
                            <input type="text" v-model="newLeave.extra_message" class="form-control"/>
                        </div>
                    </div>
                    <h4>Civil Status Condition</h4>
                    <div class="row">
                        <div v-for="(tax,key) in selected" class="col-md-3">
                            <label>
                                <input type="checkbox" v-model="selected[key].checked"/>
                                @{{ tax.name }}
                            </label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" style="max-height:400px;overflow-y: scroll">
                            <h4>Custom Leave Credits per Employee</h4>
                            <table class="table table-responsive table-bordered table-hover">
                                <thead>
                                    <th>Employee</th>
                                    <th>Custom Max Leave</th>
                                    <th></th>
                                </thead>
                                <tbody>
                                <tr v-for="employee,key in employees">
                                    <td>@{{ employee.name }}</td>
                                    <td>
                                        <select v-if="newCustom.employee_id === employee.user_id" v-model="newCustom.max_leave" class="form-control">
                                            <option value="0">Default</option>
                                            <option v-for="x in 60" :value="x * 0.5">@{{ x * 0.5 }}</option>
                                        </select>
                                        <span v-else>
                                            <span v-if="getCustomMax(employee.user_id, newLeave.id)===0">Default</span>
                                            <span v-else> @{{ getCustomMax(employee.user_id, newLeave.id) }}</span>
                                        </span>
                                    </td>
                                    <td>
                                        <button v-if="newCustom.employee_id !== employee.user_id" class="btn btn-xs btn-info"
                                                @click="editCustomLeave(employee)">Edit</button>
                                        <button v-if="newCustom.employee_id === employee.user_id" class="btn btn-xs btn-warning"
                                                @click="cancelCustomLeave()">Cancel</button>
                                        <button v-if="newCustom.employee_id === employee.user_id" class="btn btn-xs btn-success"
                                                @click="saveCustomLeave()">Save</button>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button @click="saveLeaveType" class="btn blue">Save</button>
                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- end of add leave_type Modal-->

</div>

<script src="../../assets/vuejs/instances/leavetypes.js?cache={{ rand() }}"></script>
@endsection