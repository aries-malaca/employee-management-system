@extends('layouts.main')
@section('content')
<div class="row" id="branches" v-cloak>
    <div class="col-sm-12">
        <div class="portlet box blue">
        	<div class="portlet-title">
        		<div class="caption">
        			<i class="fa fa-gift"></i>Branch List
        			<a @click="showAddModal" type="button" class="btn green btn-sm">Add Branch</a>
        		</div>
        		<div class="tools">
        			<a href="javascript:;" class="collapse" data-original-title="" title="">
        			</a>
        		</div>
        	</div>
        	<div class="portlet-body">
                @include('pagination.header_0')
                <div class="scrollable">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                        <tr>
                            <th style="width:80px">
                                <a @click="setOrderBy('id',0)">Branch ID
                                    <span v-if="pagination[0].sort_by=='id'" >
                                    <i v-if="pagination[0].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                                    <i v-if="pagination[0].sort_order==1" class="fa fa-angle-up pull-right"></i>
                                </span>
                                </a>
                            </th>
                            <th>
                                <a @click="setOrderBy('branch_name',0)">Branch Name
                                    <span v-if="pagination[0].sort_by=='branch_name'" >
                                    <i v-if="pagination[0].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                                    <i v-if="pagination[0].sort_order==1" class="fa fa-angle-up pull-right"></i>
                                </span>
                                </a>
                            </th>
                            <th>
                                <a @click="setOrderBy('branch_address',0)">Address
                                    <span v-if="pagination[0].sort_by=='branch_address'" >
                                    <i v-if="pagination[0].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                                    <i v-if="pagination[0].sort_order==1" class="fa fa-angle-up pull-right"></i>
                                </span>
                                </a>
                            </th>
                            <th>
                                <a @click="setOrderBy('branch_address',0)">Email
                                    <span v-if="pagination[0].sort_by=='branch_email'" >
                                    <i v-if="pagination[0].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                                    <i v-if="pagination[0].sort_order==1" class="fa fa-angle-up pull-right"></i>
                                </span>
                                </a>
                            </th>
                            <th>
                                <a @click="setOrderBy('branch_phone',0)">Phone
                                    <span v-if="pagination[0].sort_by=='branch_phone'" >
                                    <i v-if="pagination[0].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                                    <i v-if="pagination[0].sort_order==1" class="fa fa-angle-up pull-right"></i>
                                </span>
                                </a>
                            </th>
                            <th>
                                <a @click="setOrderBy('bs_name',0)" title="Branch Supervisor">BS
                                    <span v-if="pagination[0].sort_by=='bs_name'" >
                                    <i v-if="pagination[0].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                                    <i v-if="pagination[0].sort_order==1" class="fa fa-angle-up pull-right"></i>
                                </span>
                                </a>
                            </th>
                            <th>
                                <a @click="setOrderBy('name',0)" title="Junior Area Supervisor">JAS
                                    <span v-if="pagination[0].sort_by=='name'" >
                                    <i v-if="pagination[0].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                                    <i v-if="pagination[0].sort_order==1" class="fa fa-angle-up pull-right"></i>
                                </span>
                                </a>
                            </th>
                            <th>
                                <a @click="setOrderBy('name',0)" title="Senior Area Supervisor">SAS
                                    <span v-if="pagination[0].sort_by=='2'" >
                                    <i v-if="pagination[0].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                                    <i v-if="pagination[0].sort_order==1" class="fa fa-angle-up pull-right"></i>
                                </span>
                                </a>
                            </th>
                            <th style="width:40px">BIO</th>
                            <th v-if="branches.length>5" style="width:80px"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(branch,key) in filtered">
                            <td><a @click="showViewModal(branch,key)">@{{ branch.id }}</a></td>
                            <td><a @click="showViewModal(branch,key)">@{{ branch.branch_name }}</a></td>
                            <td>@{{ branch.branch_address }}</td>
                            <td>@{{ branch.branch_email }}</td>
                            <td>@{{ branch.branch_phone }}</td>
                            <td><span v-if="branch.bs_id!=0">
                                    <a v-bind:href="'../../employee/'+ branch.bs_id" target="_blank">@{{ branch.bs_name }}</a>
                                </span>
                                <span v-else-if="branch.bs_id===5000">
                                    Vacant
                                </span>
                                <span v-else>
                                    Default
                                </span>
                            </td>
                            <td><span v-if="branch.branch_head_employee_id!=0">
                                    <a v-bind:href="'../../employee/'+ branch.branch_head_employee_id" target="_blank">@{{ branch.name }}</a>
                                </span> <span v-else>N/A</span>
                            </td>
                            <td>
                                <a v-bind:href="'../../employee/'+ branch.sas_id" target="_blank"> @{{ branch.sas_name }}</a>
                            </td>
                            <td>
                                <span  v-if="branch.branch_data.biometrics !== undefined">
                                    <span  v-if="branch.branch_data.biometrics.length>0">
                                        <span v-if="branch.branch_data.biometrics[0].em_connector_version != ''"><i aria-hidden="true" class="fa fa-check"></i></span>
                                    </span>
                                </span>
                            </td>
                            <td v-if="branches.length>5">
                                <button class="btn btn-info btn-xs" type="button" v-if="branches.length>1" @click="editBranch(branch)">Edit</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                @include('pagination.footer_0')
        	</div>
        </div>
    </div>

    <!-- Start of add branch Modal-->
    <div class="modal fade" id="add-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 v-if="newBranch.id == 0" class="modal-title">Add Branch</h4>
                    <h4 v-if="newBranch.id != 0" class="modal-title">Edit Branch</h4>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label class="control-label bold">ID</label>
                                    <input type="text" v-model="newBranch.new_id" v-bind:disabled="newBranch.id!=0" class="form-control">
                                </div>
                            </div>
                            <!--/span-->
                            <div class="col-sm-5">
                                <div class="form-group">
                                    <label class="control-label bold">Branch Name</label>
                                    <input type="text" v-model="newBranch.branch_name" class="form-control">
                                </div>
                            </div>
                            <!--/span-->
                            <div class="col-sm-5">
                                <div class="form-group">
                                    <label class="control-label bold">Branch Phone</label>
                                    <input type="text" v-model="newBranch.branch_phone" class="form-control">
                                </div>
                            </div>
                            <!--/span-->
                        </div>
                        <!--/row-->
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label class="control-label bold">Branch Address</label>
                                    <input type="text" v-model="newBranch.branch_address" class="form-control">
                                </div>
                            </div>
                            <!--/span-->
                        </div>
                        <!--/row-->
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label bold">Branch Supervisor</label>
                                    <select v-model="newBranch.bs_id" class="form-control" data-placeholder="Select...">
                                        <option v-bind:value="5000">--Vacant--</option>
                                        <option v-bind:value="0">--Default--</option>
                                        <option v-for="bs in employees" v-bind:value="bs.user_id">@{{ bs.name }}</option>
                                    </select>
                                </div>
                            </div>
                            <!--/span-->
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label bold">Jr. Area Supervisor</label>
                                    <select v-model="newBranch.branch_head_employee_id" class="form-control" data-placeholder="Select...">
                                        <option v-bind:value="0">--None--</option>
                                        <option v-for="supervisor in jas" v-bind:value="supervisor.user_id">@{{ supervisor.name }}</option>
                                    </select>
                                </div>
                            </div>
                            <!--/span-->
                        </div>
                        <!--/row-->

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label bold">Sr. Area Supervisor</label>
                                    <select v-model="newBranch.sas_id" class="form-control" data-placeholder="Select...">
                                        <option v-bind:value="0">--None--</option>
                                        <option v-for="supervisor in sas" v-bind:value="supervisor.user_id">@{{ supervisor.name }}</option>
                                    </select>
                                </div>
                            </div>
                            <!--/span-->
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label bold">Email Address</label>
                                    <input type="text" v-model="newBranch.branch_email" class="form-control">
                                </div>
                            </div>
                        </div>
                        <!--/row-->

                        <h4>Biometrics <button class="btn btn-success" @click="addBiometric">+</button></h4>
                        <div class="row" v-for="(biometric,key) in newBranch.branch_data.biometrics">
                            <div class="col-md-1">
                                <button class="btn btn-danger" @click="removeBiometric(key)">X</button>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label bold">Model</label>
                                    <select v-model="newBranch.branch_data.biometrics[key].model" class="form-control">
                                        <option v-bind:value="model" v-for="model in models">@{{ model }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label bold">Serial</label>
                                    <input type="text" v-model="newBranch.branch_data.biometrics[key].serial" class="form-control"/>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label bold">EM Connector</label>
                                    <input type="text" v-model="newBranch.branch_data.biometrics[key].em_connector_version" class="form-control"/>
                                </div>
                            </div>
                        </div>
                        <h4>Computer</h4>
                        <div class="row">
                            <div class="col-md-3">
                                <label class="control-label bold">CPU</label>
                                <input type="text" v-model="newBranch.branch_data.computer.cpu" class="form-control"/>
                            </div>
                            <div class="col-md-3">
                                <label class="control-label bold">RAM</label>
                                <input type="text" v-model="newBranch.branch_data.computer.ram" class="form-control"/>
                            </div>
                            <div class="col-md-3">
                                <label class="control-label bold">Disk</label>
                                <input type="text" v-model="newBranch.branch_data.computer.disk" class="form-control"/>
                            </div>
                            <div class="col-md-3">
                                <label class="control-label bold">O.S.</label>
                                <input type="text" v-model="newBranch.branch_data.computer.os" class="form-control"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="saveBranch" class="btn blue">Save</button>
                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- end of add branch Modal-->
    <!-- Start of add branch Modal-->
    <div class="modal fade" id="view-modal" v-if="filtered.length>0" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-full">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">@{{ display.branch_name }}</h4>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" >
                        <li class="active">
                            <a href="#info_tab" data-toggle="tab"> Employee List </a>
                        </li>
                        <li class="">
                            <a href="#sched_tab" data-toggle="tab"> Shifting Schedules </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade active in" id="info_tab">
                            @include('pagination.header_1')
                            <div class="scrollable">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th>Employee No.</th>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Position</th>
                                    </tr>
                                    </thead>
                                    <tbody v-if="filtered[display.key]!==undefined">
                                    <tr v-for="employee in filtered1">
                                        <td><a target="_blank" v-bind:href="'../../employee/' + employee.employee_id ">@{{ employee.employee_no }}</a></td>
                                        <td><a target="_blank" v-bind:href="'../../employee/' + employee.employee_id ">@{{ employee.name }}</a></td>
                                        <td>@{{ employee.department_name }}</td>
                                        <td>@{{ employee.position_name }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            @include('pagination.footer_1')
                        </div>
                        <div class="tab-pane fade" id="sched_tab">
                            <div class="scrollable">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th style="width:160px;">Schedule Name</th>
                                        <th>Color</th>
                                        <th>Is Default</th>
                                        <th>Mon</th>
                                        <th>Tue</th>
                                        <th>Wed</th>
                                        <th>Thu</th>
                                        <th>Fri</th>
                                        <th>Sat</th>
                                        <th>Sun</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody v-if="filtered[display.key]!==undefined">
                                    <tr v-for="(schedule,key) in filtered[display.key].schedules">
                                        <td>@{{ schedule.schedule_name }}</td>
                                        <td v-bind:style="'background-color:'+ schedule.schedule_color">@{{ schedule.schedule_color }}</td>
                                        <td>
                                            <span v-if="schedule.is_default == 1">Yes</span>
                                            <span v-else>No</span>
                                        </td>
                                        <td v-for="n in 7">
                                            @{{ timeLabel(filtered[display.key].schedules[key].schedule_data[n-1]) }}
                                        </td>
                                        <td>
                                            <span v-if="newSchedule.id == schedule.id">Editing.....</span>
                                            <button v-if="newSchedule.id!=schedule.id" class="btn btn-info btn-xs" type="button" @click="editSchedule(schedule)">Edit</button>
                                            <button v-if="newSchedule.id!=schedule.id" class="btn btn-danger btn-xs" type="button" @click="deleteSchedule(schedule)">Delete</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="text" v-model="newSchedule.schedule_name" class="form-control" />
                                        </td>
                                        <td>
                                            <select class="form-control" v-model="newSchedule.schedule_color">
                                                <option v-bind:value="color" v-for="color in colors">@{{ color }}</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-control" v-model="newSchedule.is_default">
                                                <option value="1">Yes</option>
                                                <option value="0">No</option>
                                            </select>
                                        </td>
                                        <td v-for="n in 7">
                                            <select class="form-control" v-model="newSchedule.schedule_data[n-1]">
                                                <option v-bind:value="time.value" v-for="time in times">@{{ time.label }}</option>
                                            </select>
                                        </td>
                                        <td>
                                            <button v-if="newSchedule.id != 0" class="btn btn-success btn-sm" type="button" @click="updateSchedule">Update</button>
                                            <button v-if="newSchedule.id != 0" class="btn btn-info btn-sm" type="button" @click="clearSchedForm">Cancel</button>
                                            <button v-if="newSchedule.id == 0" class="btn btn-success btn-sm" type="button" @click="addSchedule">Add Schedule</button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div v-if="loading_warnings">
                        Loading Warnings...
                    </div>
                    <div v-else>
                        <div>
                            <h2>Warnings</h2>
                            <table class="table-responsive table table-hover table-bordered">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Errors</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="warning in warnings" v-if="warning.errors.length>0">
                                    <td>
                                        @{{ warning.name }}
                                    </td>
                                    <td>
                                        <table class="table-responsive table table-hover table-bordered">
                                            <tr>
                                                <td>Date</td>
                                                <td>
                                                    Details
                                                </td>
                                            </tr>
                                            <tr v-for="error in warning.errors" >
                                                <td>
                                                    @{{ error.date }}
                                                </td>
                                                <td>
                                                    @{{ error.errors }}
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
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
    <!-- end of add branch Modal-->
</div>
<script src="https://cdn.socket.io/socket.io-1.2.0.js"></script>
<script src="../../assets/vuejs/instances/branches.js?cache={{ rand() }}"></script>

@endsection