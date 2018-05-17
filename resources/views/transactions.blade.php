@extends('layouts.main')
@section('content')
<ul class="nav nav-tabs" >
    <li class="active">
        <a href="#tab_1_1" data-toggle="tab">
        Employee Transactions </a>
    </li>
    <li class="">
        <a href="#tab_1_0" data-toggle="tab">
        Transaction Codes </a>
    </li>
</ul>
<div class="tab-content" id="transactions">
    <div class="tab-pane fade" id="tab_1_0">
        <div class="portlet box yellow">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i>Transaction Codes
                    <a @click="showAddModal" type="button" class="btn purple btn-sm">Add Transaction Code</a>
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
                            <th>ID</th>
                            <th>Transaction Name</th>
                            <th>Taxable</th>
                            <th>Is Regular</th>
                            <th>Type</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(code,key) in filtered">
                            <td>@{{ code.id }}</td>
                            <td>@{{ code.transaction_name }}</td>
                            <td>@{{ (code.is_taxable==1? 'YES': 'NO')}}</td>
                            <td>@{{ (code.is_regular_transaction==1? 'YES ' + '('+ code.cutoff +')': 'NO') }}</td>
                            <td>@{{ code.transaction_type }}</td>
                            <td>
                                <button type="button" @click="editTransactionCode(code)" class="btn blue btn-xs">Edit</button>
                                <button type="button" @click="deleteTransactionCode(code)" class="btn red btn-xs">Delete</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                @include('pagination.footer_0')
            </div>
        </div>
    </div>
    <div class="tab-pane fade active in" id="tab_1_1">
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i>Employee Transactions
                    <a @click="showAddTransactionModal" type="button" class="btn purple btn-sm">Add Transaction</a>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse" data-original-title="" title=""></a>
                </div>
            </div>
            <div class="portlet-body">
                @include('pagination.header_1')
                <label> Filter By:
                    <select v-model="filter_by" class="form-control">
                        <option value="active-recurring">Active - Recurring</option>
                        <option value="active-nonrecurring">Active - Non-Recurring</option>
                        <option value="inactive-recurring">Inactive - Recurring</option>
                        <option value="inactive-nonrecurring">Inactive - Non-Recurring</option>
                    </select>
                </label>
                <label> Type:
                    <select v-model="filter_type" class="form-control">
                        <option value="addition">Addition</option>
                        <option value="deduction">Deduction</option>
                    </select>
                </label>
                <div class="scrollable">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Transaction</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Recurring Amt.</th>
                            <th>Gives</th>
                            <th>Total Amt.</th>
                            <th>Frequency</th>
                            <th>Notes</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="transaction in filtered1">
                            <td>
                                <a target="_blank" v-bind:href="'../../employee/'+transaction.user_id">@{{ transaction.name }}</a>
                            </td>
                            <td>@{{ transaction.transaction_name }}</td>
                            <td>@{{ formatDateTime(transaction.start_date,'MM/DD/YYYY') }}</td>
                            <td>@{{ formatDateTime(transaction.end_date,'MM/DD/YYYY') }}</td>
                            <td>@{{ format_number(transaction.amount) }}</td>
                            <td>@{{ getTransactionTimes(transaction) }}</td>
                            <td>@{{ format_number(getTransactionTimes(transaction) * transaction.amount) }}</td>
                            <td>
                                @{{ transaction.frequency + ' ('+ transaction.cutoff +')' }}
                            </td>
                            <td>@{{ transaction.notes }}</td>
                            <td>
                                <button class="btn btn-warning btn-xs" @click="cloneTransaction(transaction)">Clone</button>
                                <button class="btn btn-info btn-xs" @click="editTransaction(transaction)">Edit</button>
                                <button class="btn btn-danger btn-xs" @click="deleteTransaction(transaction.transaction_id)">Delete</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                @include('pagination.footer_1')
            </div>
        </div>
    </div>

    <!-- Start of add transcode Modal-->
    <div class="modal fade in" id="add-code-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title" v-if="newCode.id==0">Add Transaction Code</h4>
                    <h4 class="modal-title" v-else>Update Transaction Code</h4>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-sm-8">
                                <div class="form-group">
                                    <label class="control-label bold">Transaction Name</label>
                                    <input type="text" v-model="newCode.transaction_name" class="form-control">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label bold">Is Regular Transaction</label>
                                    <select class="form-control" v-model="newCode.is_regular_transaction">
                                        <option value="1">YES</option>
                                        <option value="0">NO</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!--/row-->
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label bold">Is Taxable</label>
                                    <select class="form-control" v-model="newCode.is_taxable">
                                        <option value="1">YES</option>
                                        <option value="0">NO</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label bold">Transaction Type</label>
                                    <select class="form-control" v-model="newCode.transaction_type">
                                        <option value="deduction">Deduction</option>
                                        <option value="addition">Addition</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group" v-if="newCode.is_regular_transaction == 1">
                                    <label class="control-label bold">Cut-off</label>
                                    <select class="form-control" v-model="newCode.cutoff">
                                        <option value="first cutoff">First Cut-off</option>
                                        <option value="second cutoff">Second Cut-off</option>
                                        <option value="every cutoff">Every Cut-off</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!--/row-->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="addTransactionCode" v-if="newCode.id==0" class="btn blue">Save</button>
                    <button type="button" @click="updateTransactionCode" v-else class="btn blue">Update</button>
                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- end of add transcode Modal-->

    <!-- Start of add trans Modal-->
    <div class="modal fade in" id="add-transaction-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title" v-if="newTransaction.id==0">Add Employee Transaction</h4>
                    <h4 class="modal-title" v-else>Update Employee Transaction</h4>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label bold">Employee</label>
                                    <select class="form-control" v-model="newTransaction.employee_id">
                                        <option v-bind:value="employee.user_id" v-for="employee in employees">@{{ employee.name }}</option>
                                    </select>
                                </div>
                            </div>
                            <!--/span-->
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label bold">Transaction Name</label>
                                    <select class="form-control" v-model="newTransaction.transaction_code_id">
                                        <option v-if="transaction.is_regular_transaction ==0" v-for="transaction in transaction_codes" v-bind:value="transaction.id">
                                            @{{ transaction.transaction_name }} (@{{ transaction.transaction_type }})
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <!--/span-->
                        </div>
                        <!--/row-->
                        <div class="row">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label class="control-label bold">Rec. Amount</label>
                                    <input type="number" v-model="newTransaction.amount" class="form-control"/>
                                </div>
                            </div>
                            <!--/span-->
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label bold">Frequency</label>
                                    <select class="form-control" v-model="newTransaction.frequency">
                                        <option value="recurring">Recurring</option>
                                        <option value="once">Once</option>
                                    </select>
                                </div>
                            </div>
                            <!--/span-->
                            <div class="col-sm-5">
                                <div class="form-group">
                                    <label class="control-label bold">Cut-off</label>
                                    <select class="form-control" v-model="newTransaction.cutoff">
                                        <option value="first cutoff">First Cut-off</option>
                                        <option value="second cutoff">Second Cut-off</option>
                                        <option v-if="newTransaction.frequency == 'recurring'" value="every cutoff">Every Cut-off</option>
                                    </select>
                                </div>
                            </div>
                            <!--/span-->
                        </div>
                        <!--/row-->
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label bold">Date Start</label>
                                    <input type="date" v-model="newTransaction.start_date" class="form-control"/>
                                </div>
                            </div>
                            <!--/span-->
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label bold">Date End</label>
                                    <input type="date" v-model="newTransaction.end_date" class="form-control"/>
                                </div>
                            </div>
                            <!--/span-->
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label bold">Notes</label>
                                    <textarea v-model="newTransaction.notes" class="form-control" style="height:60px"></textarea>
                                </div>
                            </div>
                            <!--/span-->
                        </div>
                        <!--/row-->
                    </div>
                    <table class="table table-bordered" v-if="transaction_codes.length>0 && newTransaction.transaction_code_id!=0">
                        <tr>
                            <th>Recurring Amount</th>
                            <td>@{{ newTransaction.amount }}</td>
                        </tr>
                        <tr>
                            <th>Times</th>
                            <td>@{{  getTransactionTimes(newTransaction) }}</td>
                        </tr>
                        <tr>
                            <th>Total @{{ getTransaction(newTransaction.transaction_code_id).transaction_type }}</th>
                            <td>@{{  format_number(getTransactionTimes(newTransaction)* Number(newTransaction.amount)) }}</td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn blue" v-if="newTransaction.id==0" @click="addTransaction">Save</button>
                    <button type="button" class="btn blue" v-else @click="updateTransaction">Update</button>
                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- end of add trans Modal-->
</div>
<script src="../../assets/vuejs/instances/transactions.js?cache={{ rand() }}"></script>
@endsection