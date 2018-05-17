@extends('layouts.main')
@section('content')

<div class="row" id="payroll">
    <div class="col-md-4">
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i>Generate Payroll/Report
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse" data-original-title="" title=""></a>
                </div>
            </div>
            <div class="portlet-body">
                <div class="alert alert-info">
                    <b>Note:</b> Payroll should be computed and published first before generating a Report.
                </div>
                <div class="form-group">
                    <label>Generate By:</label>
                    <select style="padding:2px" class="form-control" v-model="generate.generate_by" @change="changeBy">
                        <option value="companies">Company</option>
                        <option value="batches">Batch</option>
                        <option value="branches">Branch</option>
                    </select>
                </div>
                <div class="form-group" v-if="generate.generate_by == 'employees'">
                    <label>Select Employee:</label>
                    <select style="padding:2px" class="form-control" v-model="generate.employee_id">
                        <option v-for="employee in employees" v-if="employee.active_status==1" v-bind:value="employee.user_id">@{{ employee.name }}</option>
                    </select>
                </div>
                <div class="form-group" v-if="generate.generate_by == 'companies'">
                    <label>Select Company:</label>
                    <select style="padding:2px" class="form-control" v-model="generate.company_id">
                        <option v-for="company in companies" v-if="company.company_active==1" v-bind:value="company.id">@{{ company.company_name }}</option>
                    </select>
                </div>
                <div class="form-group" v-if="generate.generate_by == 'batches'">
                    <label>Select Batch:</label>
                    <select style="padding:2px" class="form-control" v-model="generate.batch_id">
                        <option v-for="batch in batches" v-if="batch.batch_active==1" v-bind:value="batch.id">@{{ batch.batch_name }}</option>
                    </select>
                </div>
                <div class="form-group" v-if="generate.generate_by == 'branches'">
                    <label>Select Branch:</label>
                    <select style="padding:2px" class="form-control" v-model="generate.branch_id">
                        <option v-if="branch.branch_data.biometrics !== undefined" v-bind:style="branch.branch_data.biometrics[0].em_connector_version != ''?'background-color:yellow':''"
                                v-for="branch in branches" v-bind:value="branch.id">@{{ branch.branch_name }}</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Year:</label>
                            <select style="padding:2px" class="form-control" v-model="generate.year">
                                <option v-bind:value="n" v-for="n in rangeYears">@{{ n }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Month:</label>
                            <select style="padding:2px" class="form-control" v-model="generate.month">
                                <option v-bind:value="n" v-for="n in 12">@{{ getMonthName(n) }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Cut-off:</label>
                            <select style="padding:2px" class="form-control" v-model="generate.cutoff">
                                <option v-bind:value="1">1 - 15</option>
                                <option v-bind:value="2">16 - @{{ lastDayInMonth(2017,generate.month) }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <button v-if="generate.generate_by != 'employees'" class="btn btn-success btn-md" type="button" @click="generatePayroll()" id="generate_button">Generate Payroll</button>
                <button v-else class="btn btn-success btn-md" type="button" @click="generateNow()" id="compute_button">Compute Payroll</button>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i>Previously Generated
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse" data-original-title="" title=""></a>
                </div>
            </div>
            <div class="portlet-body">
                <ul class="nav nav-tabs" >
                    <li class="active">
                        <a href="#tab_payrolls" data-toggle="tab">Payrolls</a>
                    </li>
                    <li class="">
                        <a href="#tab_adjustments" data-toggle="tab">
                            Salary Adjustments
                        </a>
                    </li>
                    <li class="">
                        <a href="#consolidated" data-toggle="tab">
                             Reports
                            <span class="badge badge-success">New!</span>
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade active in" id="tab_payrolls">
                        @include('pagination.header_0')
                        <label> Type:
                            <select v-model="filter_status" class="form-control">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                        </label>
                        <div class="scrollable">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>
                                        <a @click="setOrderBy('created_at',0)">Date Generated
                                            <span v-if="pagination[0].sort_by=='created_at'" >
                                                <i v-if="pagination[0].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                                                <i v-if="pagination[0].sort_order==1" class="fa fa-angle-up pull-right"></i>
                                            </span>
                                        </a>
                                    </th>
                                    <th>
                                        <a @click="setOrderBy('date_start',0)">Payroll Period
                                            <span v-if="pagination[0].sort_by=='date_start'" >
                                                <i v-if="pagination[0].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                                                <i v-if="pagination[0].sort_order==1" class="fa fa-angle-up pull-right"></i>
                                            </span>
                                        </a>
                                    </th>
                                    <th>
                                        <a @click="setOrderBy('category_set',0)">Generated By
                                            <span v-if="pagination[0].sort_by=='category_set'" >
                                                <i v-if="pagination[0].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                                                <i v-if="pagination[0].sort_order==1" class="fa fa-angle-up pull-right"></i>
                                            </span>
                                        </a>
                                    </th>
                                    <th>
                                        <a @click="setOrderBy('status',0)">Status
                                            <span v-if="pagination[0].sort_by=='status'" >
                                                <i v-if="pagination[0].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                                                <i v-if="pagination[0].sort_order==1" class="fa fa-angle-up pull-right"></i>
                                            </span>
                                        </a>
                                    </th>
                                    <th style="width:160px;"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="payroll in filtered">
                                    <td>
                                        @{{ formatDateTime(payroll.created_at,"MM/DD/YYYY LT") }}
                                    </td>
                                    <td>
                                        @{{ formatDateTime(payroll.date_start,"MM/DD/YYYY") }} - @{{ formatDateTime(payroll.date_end,"MM/DD/YYYY") }}
                                    </td>
                                    <td>@{{  generatedBy(payroll.category_set) }} (@{{ getValue(payroll.category_value,payroll.category_set) }})</td>
                                    <td>@{{ payroll.status }}</td>
                                    <td>
                                        <div class="btn-group btn-group-justified">
                                            <a target="_blank" v-bind:href="'../../payroll/previewMultiple/'+payroll.category_set+'/' + payroll.id +'/Multiple'" class="btn btn-xs btn-info"> Payslip </a>
                                            <a target="_blank" v-bind:href="'../../payroll/previewMultiple/'+payroll.category_set+'/' + payroll.id +'/Summary'" class="btn btn-xs btn-success"> Summary </a>
                                        </div>
                                        <div class="btn-group btn-group-justified">
                                            <a v-if="payroll.status != 'draft'" @click="draftPayroll(payroll)" class="btn btn-xs btn-warning" >Draft</a>
                                            <a v-else @click="publishPayroll(payroll)" class="btn btn-xs btn-success" >Publish</a>
                                            <a @click="deletePayroll(payroll)" class="btn btn-xs btn-danger" >Delete</a>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        @include('pagination.footer_0')
                    </div>
                    <div class="tab-pane fade" id="tab_adjustments">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Filter By Status:</label>
                                    <select v-model="filter_request" class="form-control">
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        <option value="denied">Denied</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-border table-hover table-striped" style="font-size:11px;">
                                    <thead>
                                        <th>Employee</th>
                                        <th>Period</th>
                                        <th>Discrepancy</th>
                                        <th>Notes</th>
                                        <th>Amount</th>
                                        <th>Feedback</th>
                                        <th>Status</th>
                                        <th></th>
                                    </thead>
                                    <tbody>
                                        <tr v-for="request in filtered_requests">
                                            <td><a target="_blank" v-bind:href="'../../employee/'+request.employee_id">@{{ request.name }}</a></td>
                                            <td>@{{ request.request_data.period }}</td>
                                            <td>@{{ request.request_data.discrepancy }}</td>
                                            <td>@{{ request.request_note }}</td>
                                            <td>
                                                <span v-if="request.request_data.status==='approved'">@{{ request.request_data.amount }}</span>
                                            </td>
                                            <td>@{{ request.request_data.feedback }}</td>
                                            <td>
                                                <span class="badge badge-info" v-if="request.request_data.status==='pending'">Pending</span>
                                                <span class="badge badge-success" v-else-if="request.request_data.status==='approved'">Approved</span>
                                                <span class="badge badge-info" v-else-if="request.request_data.status==='denied'">Denied</span>
                                            </td>
                                            <td>
                                                <button class="btn btn-warning btn-sm" @click="viewRequest(request)">Edit</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="consolidated">
                        @include('pagination.header_1')
                        <div class="scrollable">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>
                                        <a @click="setOrderBy('date_start',0)">Period
                                            <span v-if="pagination[0].sort_by=='date_start'" >
                                                <i v-if="pagination[0].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                                                <i v-if="pagination[0].sort_order==1" class="fa fa-angle-up pull-right"></i>
                                            </span>
                                        </a>
                                    </th>
                                    <th></th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="report in filtered1">
                                    <td>
                                        @{{ formatDateTime(report.date_start,"MM/DD/YYYY") }} - @{{ formatDateTime(report.date_end,"MM/DD/YYYY") }}
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <button class="btn btn-success btn-sm btn-block" type="button"
                                                        @click="generateReport({year:formatDateTime(report.date_start,'YYYY'), month:formatDateTime(report.date_start,'M'), cutoff:formatDateTime(report.date_start,'D')==1?1:2})"
                                                        id="generate_report">Consolidated</button>
                                            </div>
                                            <div class="col-md-6">
                                                <button class="btn btn-info btn-sm btn-block" type="button"
                                                        @click="generateOT({year:formatDateTime(report.date_start,'YYYY'), month:formatDateTime(report.date_start,'M'), cutoff:formatDateTime(report.date_start,'D')==1?1:2})"
                                                        id="generate_report">Overtime</button>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-warning btn-sm btn-block" type="button" v-if="formatDateTime(report.date_start,'D') != 1"
                                                @click="generateContributions({year:formatDateTime(report.date_start,'YYYY'), month:formatDateTime(report.date_start,'M'), cutoff:formatDateTime(report.date_start,'D')==1?1:2})"
                                                id="generate_report">Contributions</button>
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
    </div>

    <!-- Start of view position Modal-->
    <div class="modal fade" id="view-request-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"> View Request </h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Period</label>
                                <input type="date" class="form-control" v-model="view_request.period"/>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Credited To</label>
                                <input type="date" class="form-control" v-model="view_request.target"/>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Feedback</label>
                                <input type="text" class="form-control" v-model="view_request.feedback"/>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Amount</label>
                                <input type="number" class="form-control" v-model="view_request.amount">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status</label>
                                <select v-model="view_request.status" class="form-control">
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="denied">Denied</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success" @click="updateRequest">Update</button>
                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- end of view position Modal-->

    <!-- Start of view position Modal-->
    <div class="modal fade" id="view-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"> Generated Payroll, Headcount: @{{ generate.employees.length }} </h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3" v-for="employee in generate.employees">
                            <a target="_blank" v-bind:href="'../../employee/' + employee.id"><span>@{{ employee.name }}</span></a>
                            <table>
                                <tr>
                                    <td style="padding:5px">Status:
                                        <i v-if="employee.success" class="fa fa-check" aria-hidden="true"></i>
                                        <i v-if="employee.success === false" class="fa fa-times-circle" aria-hidden="true"></i>
                                    </td>
                                    <td style="padding:5px" v-if="employee.success == null">
                                        <button class="btn btn-xs btn-info" @click="regenerate({
                                                    generate_by:'employees',
                                                    employee_id:employee.id,
                                                    year:generate.year,
                                                    month:generate.month,
                                                    cutoff:generate.cutoff,
                                                    payslip_id:generate.payslip_id
                                                })">
                                            <i class="fa fa-spinner" aria-hidden="true"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding:5px" v-if="employee.success">
                                        <a class="btn btn-xs btn-success" target="_blank" v-bind:href="'../../payroll/previewSingle/' + employee.payslip_id">View</a>
                                        <button class="btn btn-xs btn-info" @click="regenerate({
                                                    generate_by:'employees',
                                                    employee_id:employee.id,
                                                    year:generate.year,
                                                    month:generate.month,
                                                    cutoff:generate.cutoff,
                                                    payslip_id:generate.payslip_id
                                                })">
                                            Regenerate
                                        </button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <br/>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" v-bind:aria-valuenow="(success_count/generate.employees.length)*100"
                                     aria-valuemin="0" aria-valuemax="100" v-bind:style="'width:'+ (success_count/generate.employees.length)*100 +'%'">
                                    @{{ ((success_count/generate.employees.length)*100).toFixed(0) }}%
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="(generate.employees.length == 0 && result !== undefined) || (generate.employees.length > 0 && generate.employees.length == success_count)">
                        <div class="scrollable" v-if="result!==undefined">
                            <table class="table-hover table table-bordered">
                                <tr>
                                    <th>Generated by: @{{ generatedBy(result.generate_by) }}</th>
                                    <th> @{{ generatedBy(result.generate_by) }} Name: @{{ getValue(result.id,result.generate_by) }}</th>
                                    <th>Range: @{{ result.date_start }} - @{{ result.date_end }}</th>
                                    <th>
                                        <a v-if="result.type == 'single'" v-bind:href="'../../payroll/previewSingle/' + result.payslip_id"
                                           class="btn btn-md btn-info" target="_blank">View Payslip</a>
                                        <a v-else v-bind:href="'../../payroll/previewMultiple/'+result.generate_by+'/' + result.payslip_id +'/Multiple'"
                                           class="btn btn-md btn-info" target="_blank">View Payslip</a>

                                        <a v-if="result.type != 'single'"  v-bind:href="'../../payroll/previewMultiple/'+result.generate_by+'/' + result.payslip_id +'/Summary'"
                                           class="btn btn-md btn-info" target="_blank">View Summary</a>
                                    </th>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn default" @click="closeGeneration()">Close</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- end of view position Modal-->

    <!-- Start of view position Modal-->
    <div class="modal fade" id="view-report-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"> View Consolidated Report </h4>
                </div>
                <div class="modal-body">
                    <a class="btn btn-success btn-lg btn-block" target="_blank" href="../../report/Consolidated.xlsx">View Report</a>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- end of view position Modal-->

    <!-- Start of view position Modal-->
    <div class="modal fade" id="view-ot-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"> View OT Report </h4>
                </div>
                <div class="modal-body">
                    <a class="btn btn-success btn-lg btn-block" target="_blank" href="../../report/OT.xlsx">View Report</a>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- end of view position Modal-->

    <div class="modal fade" id="view-contribution-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-full">
            <div class="modal-content" v-if="contribution!==null">
                <div class="modal-header">
                    <h4 class="modal-title"> View Contributions Report </h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4" v-for="company in contribution">
                            <a target="_blank" v-bind:href="'../../report/' + company.sss.name +'/' + company.sss.filename" class="btn btn-info btn-block">@{{ company.sss.name }} - SSS</a>
                            <a target="_blank" v-bind:href="'../../report/' + company.pagibig.name +'/' + company.pagibig.filename" class="btn btn-success btn-block">@{{ company.pagibig.name }} - Pagibig</a>
                            <a target="_blank" v-bind:href="'../../report/' + company.philhealth.name +'/' + company.philhealth.filename" class="btn btn-warning btn-block">@{{ company.philhealth.name }} - Philhealth</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
</div>

<script src="../../assets/vuejs/instances/payroll.js?cache={{ rand() }}"></script>
@endsection