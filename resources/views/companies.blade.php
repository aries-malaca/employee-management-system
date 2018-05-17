@extends('layouts.main')
@section('content')

<div class="row" id="companies" v-cloak>
    <div class="col-md-12">
        <div class="portlet box blue-madison">
        	<div class="portlet-title">
        		<div class="caption">
        			<i class="fa fa-gift"></i>Company List
        			<a @click="showAddModal" type="button" class="btn grey-gallery btn-sm">Add Company</a>
        		</div>
        		<div class="tools">
        			<a href="javascript:;" class="collapse" data-original-title="" title="">
        			</a>
        		</div>
        	</div>
        	<div class="portlet-body">
                <div class="scrollable">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                        <tr>
                            <th style="width:120px">Company ID</th>
                            <th>Company Name</th>
                            <th>Address</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th style="width:160px"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="company in companies">
                            <td><a @click="showViewModal(company)"> @{{ company.id }} </a></td>
                            <td><a @click="showViewModal(company)"> @{{ company.company_name }} </a></td>
                            <td>@{{ company.company_address }}</td>
                            <td>@{{ company.company_phone }}</td>
                            <td>@{{ company.company_email }}</td>
                            <td>
                                <button class="btn btn-info btn-xs" type="button" @click="editCompany(company)">Edit</button>
                                <button v-if="company.company_active==1" type="button"
                                        @click="deactivateCompany(company)" class="btn btn-danger btn-xs">Deactivate</button>
                                <button v-if="company.company_active==0" type="button"
                                        @click="activateCompany(company)" class="btn btn-success btn-xs">Activate</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
        	</div>
        </div>
    </div>

    <!-- Start of add company Modal-->
    <div class="modal fade" id="add-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 v-if="newCompany.id != 0" class="modal-title">Edit Company</h4>
                    <h4 v-if="newCompany.id == 0" class="modal-title">Add Company</h4>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label class="control-label bold">Company Name</label>
                                    <input type="text" v-model="newCompany.company_name" class="form-control">
                                </div>
                            </div><!--/span-->
                        </div><!--/row-->
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label class="control-label bold">Company Address</label>
                                    <input type="text" v-model="newCompany.company_address" class="form-control">
                                </div>
                            </div><!--/span-->
                        </div><!--/row-->
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label bold">Company Email</label>
                                    <input type="text" v-model="newCompany.company_email" class="form-control">
                                </div>
                            </div><!--/span-->
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label bold">Company Phone</label>
                                    <input type="text" v-model="newCompany.company_phone" class="form-control">
                                </div>
                            </div><!--/span-->
                        </div><!--/row-->
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label bold">SSS ID</label>
                                    <input type="text" v-model="newCompany.company_data.sss_id" class="form-control">
                                </div>
                            </div><!--/span-->
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label bold">Philhealth ID</label>
                                    <input type="text" v-model="newCompany.company_data.philhealth_id" class="form-control">
                                </div>
                            </div><!--/span-->
                        </div><!--/row-->
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label bold">Pagibig ID</label>
                                    <input type="text" v-model="newCompany.company_data.pagibig_id" class="form-control">
                                </div>
                            </div><!--/span-->
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label bold">TIN ID</label>
                                    <input type="text" v-model="newCompany.company_data.tin_id" class="form-control">
                                </div>
                            </div><!--/span-->
                        </div><!--/row-->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="saveCompany" class="btn blue">Save</button>
                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- end of add company Modal-->

    <!-- Start of view company Modal-->
    <div class="modal fade" id="view-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">Employees @ @{{ display.company_name }}</h4>
                </div>
                <div class="modal-body">
                    @include('pagination.header_0')
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
                            <tbody>
                            <tr v-for="employee in filtered1">
                                <td><a v-bind:href="'../../employee/'+employee.user_id"> @{{ employee.employee_no }} </a></td>
                                <td><a v-bind:href="'../../employee/'+employee.user_id"> @{{ employee.name }} </a></td>
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
    <!-- end of view company Modal-->

</div>

<script src="../../assets/vuejs/instances/companies.js?cache={{ rand() }}"></script>
@endsection