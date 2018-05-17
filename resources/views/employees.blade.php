@extends('layouts.main')
@section('content')

<div id="employees" v-cloak>
    <div class="portlet box blue">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-gift"></i>Active Employee List
                <button v-if="allow_add_employee" @click="showAddModal" type="button" class="btn green btn-sm">Add Employee</button>
            </div>
            <div class="tools">
                <a href="javascript:" class="collapse" data-original-title="" title=""></a>
            </div>
        </div>
        <div class="portlet-body">
            @include('pagination.header_0')
            <div class="scrollable">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>
                            <a @click="setOrderBy('employee_no',0)">Employee No.
                                <span v-if="pagination[0].sort_by=='employee_no'" >
                            <i v-if="pagination[0].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                            <i v-if="pagination[0].sort_order==1" class="fa fa-angle-up pull-right"></i>
                        </span>
                            </a>
                        </th>
                        <th style="width:200px;">
                            <a @click="setOrderBy('name',0)">Name
                                <span v-if="pagination[0].sort_by=='name'" >
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
                        <th>
                            <a @click="setOrderBy('position_name',0)">Position
                                <span v-if="pagination[0].sort_by=='position_name'" >
                            <i v-if="pagination[0].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                            <i v-if="pagination[0].sort_order==1" class="fa fa-angle-up pull-right"></i>
                        </span>
                            </a>
                        </th>
                        <th>
                            <a @click="setOrderBy('company_name',0)">Company
                                <span v-if="pagination[0].sort_by=='company_name'" >
                            <i v-if="pagination[0].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                            <i v-if="pagination[0].sort_order==1" class="fa fa-angle-up pull-right"></i>
                        </span>
                            </a>
                        </th>
                        <th>
                            <a @click="setOrderBy('branch_name',0)">Branch
                                <span v-if="pagination[0].sort_by=='branch_name'" >
                            <i v-if="pagination[0].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                            <i v-if="pagination[0].sort_order==1" class="fa fa-angle-up pull-right"></i>
                        </span>
                            </a>
                        </th>
                        <th>
                            <a @click="setOrderBy('employment_status_name',0)">Status
                                <span v-if="pagination[0].sort_by=='employment_status_name'" >
                            <i v-if="pagination[0].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                            <i v-if="pagination[0].sort_order==1" class="fa fa-angle-up pull-right"></i>
                        </span>
                            </a>
                        </th>
                        <th>LBO</th>
                        <th>BIO</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="employee in filtered">
                        <td><a v-bind:href="'../../employee/' + employee.user_id">@{{ employee.employee_no }}</a></td>
                        <td>
                            <a v-bind:href="'../../employee/' + employee.user_id">
                                <img class="img-circle" v-bind:src="'../../images/employees/'+employee.picture" style="width:30px;" alt="">
                            </a>
                            <a v-bind:href="'../../employee/' + employee.user_id">
                                <span v-if="employee.is_online"> <i class="fa fa-circle" style="color:#18E040" aria-hidden="true"></i> </span>
                                @{{ employee.name }}
                            </a>
                        </td>
                        <td>@{{ employee.department_name }}</td>
                        <td>@{{ employee.position_name }}</td>
                        <td>@{{ employee.company_name }}</td>
                        <td>@{{ employee.branch_name }}</td>
                        <td>@{{ employee.employment_status_name }}</td>
                        <td>
                            <span v-if="employee.lbo_identifier.length>4"><i class="fa fa-check" aria-hidden="true"></i></span>
                        </td>
                        <td>
                            <span v-if="employee.has_bio==1"><i class="fa fa-check" aria-hidden="true"></i></span>
                        </td>
                        <td>
                            <button class="btn btn-danger btn-xs" v-if="allow_add_employee" type="button" @click="deleteEmployee(employee.user_id)">Delete</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            @include('pagination.footer_0')
        </div>
        @include('employee.add_employee')
    </div>
    <div class="portlet box red" v-if="inactive_employees.length>0">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-gift"></i>Inactive Employee List
            </div>
            <div class="tools">
                <a href="javascript:" class="collapse" data-original-title="" title=""></a>
            </div>
        </div>
        <div class="portlet-body">
            @include('pagination.header_2')
            <div class="scrollable">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>
                            <a @click="setOrderBy('employee_no',2)">Employee No.
                                <span v-if="pagination[2].sort_by=='employee_no'" >
                            <i v-if="pagination[2].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                            <i v-if="pagination[2].sort_order==1" class="fa fa-angle-up pull-right"></i>
                        </span>
                            </a>
                        </th>
                        <th></th>
                        <th>
                            <a @click="setOrderBy('name',2)">Name
                                <span v-if="pagination[2].sort_by=='name'" >
                            <i v-if="pagination[2].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                            <i v-if="pagination[2].sort_order==1" class="fa fa-angle-up pull-right"></i>
                        </span>
                            </a>
                        </th>
                        <th>
                            <a @click="setOrderBy('department_name',2)">Department
                                <span v-if="pagination[2].sort_by=='department_name'" >
                            <i v-if="pagination[2].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                            <i v-if="pagination[2].sort_order==1" class="fa fa-angle-up pull-right"></i>
                        </span>
                            </a>
                        </th>
                        <th>
                            <a @click="setOrderBy('position_name',2)">Position
                                <span v-if="pagination[2].sort_by=='position_name'" >
                            <i v-if="pagination[2].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                            <i v-if="pagination[2].sort_order==1" class="fa fa-angle-up pull-right"></i>
                        </span>
                            </a>
                        </th>
                        <th>
                            <a @click="setOrderBy('company_name',2)">Company
                                <span v-if="pagination[2].sort_by=='company_name'" >
                            <i v-if="pagination[2].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                            <i v-if="pagination[2].sort_order==1" class="fa fa-angle-up pull-right"></i>
                        </span>
                            </a>
                        </th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="employee in filtered2">
                        <td><a v-bind:href="'../../employee/' + employee.user_id">@{{ employee.employee_no }}</a></td>
                        <td>
                            <a v-bind:href="'../../employee/' + employee.user_id">
                                <img class="img-circle" v-bind:src="'../../images/employees/'+employee.picture" style="width:40px;" alt="">
                            </a>
                        </td>
                        <td><a v-bind:href="'../../employee/' + employee.user_id">@{{ employee.name }}</a></td>
                        <td>@{{ employee.department_name }}</td>
                        <td>@{{ employee.position_name }}</td>
                        <td>@{{ employee.company_name }}</td>
                        <td>
                            <button class="btn btn-danger btn-xs" v-if="allow_add_employee" type="button" @click="deleteEmployee(employee.user_id)">Delete</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            @include('pagination.footer_2')
        </div>
    </div>

</div>

<script src="../../assets/vuejs/instances/employees.js?cache={{ rand() }}"></script>
@endsection