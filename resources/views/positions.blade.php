@extends('layouts.main')
@section('content')
<style>
    .google-visualization-orgchart-table{
        border-collapse: inherit;
    }
</style>
<div class="portlet box blue-madison" id="positions" v-cloak>
    <div class="portlet-title">
        <div class="caption">
            <i class="fa fa-gift"></i>Position List
            <a @click="showAddModal" type="button" class="btn grey-gallery btn-sm">Add Position</a>
            <a @click="showOrgChart" type="button" class="btn btn-warning btn-sm">Organization Chart</a>
        </div>
        <div class="tools">
            <a href="javascript:;" class="collapse" data-original-title="" title=""></a>
        </div>
    </div>
    <div class="portlet-body">
        @include('pagination.header_0')
        <div class="scrollable">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                <tr>
                    <th style="width:120px">
                        <a @click="setOrderBy('id',0)">Position ID
                            <span v-if="pagination[0].sort_by=='id'" >
                                <i v-if="pagination[0].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                                <i v-if="pagination[0].sort_order==1" class="fa fa-angle-up pull-right"></i>
                            </span>
                        </a>
                    </th>
                    <th>
                        <a @click="setOrderBy('position_name',0)">Position Name
                            <span v-if="pagination[0].sort_by=='position_name'" >
                                <i v-if="pagination[0].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                                <i v-if="pagination[0].sort_order==1" class="fa fa-angle-up pull-right"></i>
                            </span>
                        </a>
                    </th>
                    <th>
                        <a @click="setOrderBy('department_name',0)">Department
                            <span v-if="pagination[0].sort_by=='department_name'" >
                                <i v-if="pagination[0].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                                <i v-if="pagination[0].sort_order==1" class="fa fa-angle-up pull-right"></i>
                            </span>
                        </a>
                    </th>
                    <th>Reporting Lines</th>
                    <th style="width:160px"></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="position in filtered">
                    <td><a @click="showViewModal(position)">@{{ position.id }}</a></td>
                    <td>
                        <a @click="showViewModal(position)">
                            <i v-if="position.is_department_head == 1" class="icon icon-star"></i> @{{ position.position_name }}
                        </a>
                    </td>
                    <td>@{{ position.department_name }}</td>
                    <td>
                        <ol>
                            <li v-for="p in uplines(position.id)" v-if="p.employees.length>0">
                                @{{ p.position_name }}
                            </li>
                        </ol>
                    </td>
                    <td>
                        <button class="btn btn-info btn-xs" type="button" @click="editPosition(position)">Edit</button>
                        <button class="btn btn-success btn-xs" type="button"  v-if="position.position_active != 1"
                                @click="activatePosition(position)">Activate</button>
                        <button class="btn btn-danger btn-xs" type="button" v-if="position.position_active == 1"
                                @click="deactivatePosition(position)">Deactivate</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        @include('pagination.footer_0')
    </div>

    <!-- Start of add position Modal-->
    <div class="modal fade" id="add-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title" v-if="newPosition.id==0">Add Position</h4>
                    <h4 class="modal-title" v-if="newPosition.id!=0">Edit Position</h4>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label bold">Position Name</label>
                                    <input type="text" v-model="newPosition.position_name" class="form-control">
                                </div>
                            </div>
                            <!--/span-->
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label class="control-label bold">Department</label>
                                    <select v-model="newPosition.department_id" class="form-control">
                                        <option v-for="department in departments" v-bind:value="department.id">@{{ department.department_name }}</option>
                                    </select>
                                    <span v-if="currentDepartmentHead != false "
                                          class="help-block" style="color:red">
                                        Recommended Supervisor: @{{ currentDepartmentHead }}
                                    </span>
                                </div>
                            </div>
                            <!--/span-->
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label class="control-label bold"> Is Dep. Head</label>
                                    <select v-model="newPosition.is_department_head" class="form-control">
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                    <span v-if="currentDepartmentHead != false && newPosition.is_department_head == 1 "
                                            class="help-block" style="color:red">
                                        Warning: @{{ currentDepartmentHead }} currently Department Head.
                                    </span>
                                </div>
                            </div>
                            <!--/span-->
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label class="control-label bold">Position Description</label>
                                    <textarea v-model="newPosition.position_desc" class="form-control"></textarea>
                                </div>
                            </div>
                            <!--/span-->
                        </div>
                        <!--/row-->
                        <div class="row">
                            <div class="col-md-8">
                                <label class="control-label bold">
                                    Reporting Lines <button type="button" class="btn btn-success btn-xs" @click="addReportingLine">+</button>
                                </label>
                                <table class="table table-hover table-bordered">
                                    <tbody>
                                    <tr v-for="(line,key) in newPosition.reporting_lines" >
                                        <td>
                                            <button class="btn btn-danger btn-xs" @click="removeReportingLine(key)">X</button>
                                        </td>
                                        <td>
                                            <select v-if="newPosition.reporting_lines[key].selection == 'position'" v-model="newPosition.reporting_lines[key].position_id" class="form-control">
                                                <option v-if="isAvailableReportingLine(position.id,key)" v-for="position in positions" v-bind:value="position.id">
                                                    @{{ position.position_name }}
                                                </option>
                                            </select>
                                            <table class="table-responsive table-bordered" v-else>
                                                <tr v-for="(employee,k) in newPosition.reporting_lines[key].ruling">
                                                    <td>
                                                        <input type="hidden" v-model="newPosition.reporting_lines[key].ruling[k].employee_id"/>
                                                        @{{ employee.name }}
                                                    </td>
                                                    <td> => </td>
                                                    <td>
                                                        <select v-model="newPosition.reporting_lines[key].ruling[k].supervisor_id">
                                                            <option v-for="e in employees" v-bind:value="e.user_id">@{{ e.name }}</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                        <td>
                                            <select v-model="newPosition.reporting_lines[key].selection" @change="toggleSelection(key,'reporting_lines')" class="form-control">
                                                <option value="position">Position</option>
                                                <option value="custom">Custom</option>
                                            </select>
                                        </td>
                                        <td>
                                            <span class="label label-success" v-if="key==0">Supervisor</span>
                                            <span class="label label-success" v-if="key+1==newPosition.reporting_lines.length">Final Approval</span>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <hr/>
                                <label class="control-label bold">
                                    Viewer Positions <button type="button" class="btn btn-success btn-xs" @click="addAudience">+</button>
                                </label>
                                <table class="table table-hover table-bordered">
                                    <tbody>
                                    <tr v-for="(l,key) in newPosition.audience_data" >
                                        <td>
                                            <button class="btn btn-danger btn-xs" @click="removeAudience(key)">X</button>
                                        </td>
                                        <td>
                                            <select v-if="newPosition.audience_data[key].selection == 'position'" v-model="newPosition.audience_data[key].position_id" class="form-control">
                                                <option v-if="isAvailableAudience(position.id,key)"  v-for="position in positions" v-bind:value="position.id">
                                                    @{{ position.position_name }}
                                                </option>
                                            </select>
                                        </td>
                                        <td>
                                            <select v-model="newPosition.audience_data[key].selection" class="form-control">
                                                <option value="position">Position</option>
                                                <option value="custom">Custom</option>
                                            </select>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <hr/>
                                <label class="control-label bold">
                                    Custom Leave Credits <button type="button" class="btn btn-success btn-xs" @click="addCustomLeave">+</button>
                                </label>
                                <table class="table table-hover table-bordered">
                                    <tbody>
                                    <tr v-for="(l,key) in newPosition.leave_data" >
                                        <td>
                                            <button class="btn btn-danger btn-xs" @click="removeCustomLeave(key)">X</button>
                                        </td>
                                        <td>
                                            <select v-model="newPosition.leave_data[key].leave_id" class="form-control">
                                                <option v-if="isAvailableLeave(leave.id,key)" v-for="leave in leaves" v-bind:value="leave.id">
                                                    @{{ leave.leave_type_name }}
                                                </option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control" v-model="newPosition.leave_data[key].leave_count" />
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-4">
                                <label class="bold">Branch Aware <span class="btn btn-xs btn-info" title="Recommended for Branch Supervisor and Wax Technician"> ? </span></label>
                                <select class="form-control" v-model="newPosition.position_data.branch_aware"/>
                                    <option v-bind:value="true">Yes</option>
                                    <option v-bind:value="false">No</option>
                                </select>
                                <label class="bold">Salary Frequency</label>
                                <select class="form-control" v-model="newPosition.position_data.salary_frequency"/>
                                    <option value="monthly">Monthly</option>
                                    <option value="daily">Daily</option>
                                </select>
                                <label class="bold">Grace Period Mins.</label>
                                <input type="number" class="form-control" v-model="newPosition.position_data.grace_period_minutes"/>
                                <label class="bold">Grace Period/ Month</label>
                                <input type="number" class="form-control" v-model="newPosition.position_data.grace_period_per_month"/>
                                <label class="bold">Working Days/ cut-off</label>
                                <input type="number" class="form-control" v-model="newPosition.position_data.standard_days"/>
                            </div>
                        </div>
                        <!--/row-->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="savePosition" class="btn blue">Save</button>
                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- end of add position Modal-->

    <!-- Start of view position Modal-->
    <div class="modal fade" id="view-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title"> @{{ display.position_name }} </h4>
                </div>
                <div class="modal-body">
                    <h4>Reporting Lines</h4>
                    <table class="table-bordered table-hover table">
                        <tbody>
                            <tr v-for="(position,key) in reportingLines">
                                <th>
                                    @{{ position.position_name }}
                                    <span class="label label-success" v-if="key==0">Supervisor</span>
                                    <span class="label label-success" v-if="key+1==reportingLines.length">Final Approval</span>
                                </th>
                                <td>
                                    <ul>
                                        <li style="list-style-type:none" v-for="employee in position.employees">
                                            @{{ employee.name }}
                                        </li>
                                    </ul>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <h4>Down Line Positions</h4>
                    <table class="table-bordered table-hover table">
                        <tbody>
                            <tr v-for="position in downLines">
                                <th>@{{ position.position_name }}</th>
                                <td>
                                    <ul>
                                        <li style="list-style-type:none" v-for="employee in position.employees">
                                            @{{ employee.name }}
                                        </li>
                                    </ul>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- end of view position Modal-->

    <!-- Start of view position Modal-->
    <div class="modal fade" id="orgchart-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-full">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title"> Organization Chart (Positions) </h4>
                </div>
                <div class="modal-body">
                    <div id="chart_div" style="border-collapse:unset !important; overflow-x:scroll; padding-right:50px;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- end of view position Modal-->
</div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script src="../../assets/vuejs/instances/positions.js?cache={{ rand() }}"></script>
@endsection