@extends('layouts.main')
@section('content')

<div id="banks">
    <div class="row">
        <div class="col-md-5">
            <div class="portlet box green">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-gift"></i>Bank List
                        <a @click="showAddModal" class="btn purple btn-sm">Add Bank</a>
                    </div>
                    <div class="tools">
                        <a href="javascript:;" class="collapse" data-original-title="" title="">
                        </a>
                    </div>
                </div>
                <div class="portlet-body">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Bank Name</th>
                                <th>Bank Shortname</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="bank in banks">
                                <td>@{{ bank.bank_name }}</td>
                                <td>@{{ bank.bank_shortname }}</td>
                                <td>
                                    <button class="btn btn-info btn-xs" type="button" @click="editBank(bank)">Edit</button>
                                    <button class="btn btn-danger btn-xs" type="button" @click="deleteBank(bank)">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="portlet box green">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-gift"></i>User's Bank Accounts
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
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Bank</th>
                                <th>Account No.</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="employee in filtered">
                                <td><a target="_blank" v-bind:href="'../../employee/'+employee.user_id">@{{ employee.employee_no }}</a></td>
                                <td><a target="_blank" v-bind:href="'../../employee/'+employee.user_id">@{{ employee.name }}</a></td>
                                <td>@{{ bankName(employee.bank_code) }}</td>
                                <td>@{{ employee.bank_number }}</td>
                                <td>
                                    <button class="btn btn-info btn-xs" type="button" @click="editEmployee(employee)">Edit</button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    @include('pagination.footer_0')
                </div>
            </div>
        </div>
    </div>

    <!-- Start of add bank Modal-->
    <div class="modal fade" id="add-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title" v-if="newBank.id==0">Add Bank</h4>
                    <h4 class="modal-title" v-else>Add Bank</h4>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label bold">Bank Name</label>
                                    <input type="text" v-model="newBank.bank_name" class="form-control">
                                </div>
                            </div>
                            <!--/span-->
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label bold">Bank Shortname</label>
                                    <input type="text" v-model="newBank.bank_shortname" class="form-control">
                                </div>
                            </div>
                            <!--/span-->
                        </div>
                        <!--/row-->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn blue" @click="addBank" v-if="newBank.id==0">Save</button>
                    <button type="button" class="btn blue" @click="updateBank" v-else>Update</button>
                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- end of add bank Modal-->

    <!-- Start of add bank Modal-->
    <div class="modal fade" id="editor-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title" >Update Account: @{{ editor.name }}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label bold">Bank</label>
                                    <select v-model="editor.bank_code" class="form-control">
                                        <option v-bind:value="bank.id" v-for="bank in banks">@{{ bank.bank_name }}</option>
                                    </select>
                                </div>
                            </div>
                            <!--/span-->
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label bold">Bank Account</label>
                                    <input type="text" v-model="editor.bank_number" class="form-control">
                                </div>
                            </div>
                            <!--/span-->
                        </div>
                        <!--/row-->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn blue" @click="updateEmployee">Save</button>
                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- end of add bank Modal-->
</div>

<script src="../../assets/vuejs/instances/banks.js?cache={{ rand() }}"></script>
@endsection