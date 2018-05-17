@extends('layouts.main')
@section('content')
<div id="reports">
    <div class="portlet box blue">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-gift"></i>Generate Report
            </div>
            <div class="tools">
                <a href="javascript:;" class="collapse" data-original-title="" title=""></a>
            </div>
        </div>
        <div class="portlet-body">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Generate By:</label>
                        <select class="form-control" v-model="generate.generate_by">
                            <option value="companies">Company</option>
                            <option value="batches">Batch</option>
                            <option value="branches">Branch</option>
                            <option value="employees">Employee</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4" v-if="generate.generate_by == 'employees'">
                    <div class="form-group">
                        <label>Select Employee:</label>
                        <select class="form-control" v-model="generate.employee_id">
                            <option v-for="employee in employees" v-if="employee.active_status==1" v-bind:value="employee.user_id">@{{ employee.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4" v-if="generate.generate_by == 'companies'">
                    <div class="form-group">
                        <label>Select Company:</label>
                        <select class="form-control" v-model="generate.company_id">
                            <option v-for="company in companies" v-if="company.company_active==1" v-bind:value="company.id">@{{ company.company_name }}</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4" v-if="generate.generate_by == 'batches'">
                    <div class="form-group">
                        <label>Select Batch:</label>
                        <select class="form-control" v-model="generate.batch_id">
                            <option v-for="batch in batches" v-if="batch.batch_active==1" v-bind:value="batch.id">@{{ batch.batch_name }}</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4" v-if="generate.generate_by == 'branches'">
                    <div class="form-group">
                        <label>Select Branch:</label>
                        <select class="form-control" v-model="generate.branch_id">
                            <option v-if="branch.branch_data.biometrics !== undefined" v-bind:style="branch.branch_data.biometrics[0].em_connector_version != ''?'background-color:yellow':''"
                                    v-for="branch in branches" v-bind:value="branch.id">@{{ branch.branch_name }}</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Report Type:</label>
                        <select v-model="generate.report_type" class="form-control">
                            <option value="timesheet">Time Sheet</option>
                            <option value="timelogs">Time Keeping</option>
                            <option value="plantilla">Plantilla</option>
                            <option value="leavecredits">Leave Credits</option>
                            <option value="tardiness">Tardiness Report</option>
                        </select>
                    </div>
                </div>
                <div v-if="generate.report_type != 'contributions'">
                    <div class="col-md-2" v-if="generate.report_type == 'leavecredits'">
                        <div class="form-group">
                            <label>Year:</label>
                            <select v-model="generate.date_start" class="form-control">
                                <option value="2017-01-01">2017</option>
                                <option value="2016-01-01">2016</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2" v-if="generate.report_type != 'plantilla' && generate.report_type != 'leavecredits'">
                        <div class="form-group">
                            <label>Date Start:</label>
                            <input type="date" class="form-control" v-model="generate.date_start"/>
                        </div>
                    </div>
                    <div class="col-md-2" v-if="generate.report_type != 'plantilla' && generate.report_type != 'leavecredits'">
                        <div class="form-group">
                            <label>Date End:</label>
                            <input type="date" class="form-control" v-model="generate.date_end"/>
                        </div>
                    </div>
                    <div class="col-md-2" v-if="generate.report_type == 'plantilla' && generate.report_type != 'leavecredits'">
                        <div class="form-group">
                            <label>Format:</label>
                            <select v-model="generate.format" class="form-control">
                                <option value="PDF">PDF</option>
                                <option value="EXCEL">EXCEL</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <button class="btn btn-success btn-lg" type="button" @click="generateNow()" id="generate_button">Generate</button>
                </div>
                <div class="col-md-10" v-if="result != undefined ">
                    <div class="scrollable">
                        <table class="table table-hover table-bordered">
                            <tr>
                                <th>Report: @{{ result.report_type }}</th>
                                <th>Generated by: @{{ generatedBy(result.generate_by) }}</th>
                                <th> @{{ generatedBy(result.generate_by) }} Name: @{{ getValue(result.id,result.generate_by) }}</th>
                                <th>Range: @{{ formatDateTime(result.date_start,"MM/DD/YYYY") }} - @{{ formatDateTime(result.date_end,"MM/DD/YYYY")  }}</th>
                                <th>
                                    Format: @{{ identifyFormat(result.filename) }}
                                </th>
                                <th>
                                    <a v-bind:href="'../../report/'+result.filename"
                                       class="btn btn-md btn-info" target="_blank">View Generated</a>
                                </th>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../../assets/vuejs/instances/reports.js?cache={{ rand() }}"></script>
@endsection