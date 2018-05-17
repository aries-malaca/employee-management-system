<div id="salary_adjustments">
    <div class="portlet box grey">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-gift"></i> Salary Adjustment Form
                <button class="btn btn-info" @click="addSalaryAdjustmentItem">+</button>
            </div>
            <div class="tools">
                <a href="javascript:;" class="collapse" data-original-title="" title=""></a>
            </div>
        </div>
        <div class="portlet-body">
            <div class="alert alert-info">
                For Unpaid OT or Leave, it is also required to file an Overtime/Leave form and subject for approval to support this Salary adjustment request.
            </div>
            <div class="scrollable">
                <table class="table table-hover table-bordered">
                    <thead>
                    <tr>
                        <th style="width:50px"></th>
                        <th style="width:250px">Payroll Period</th>
                        <th style="width:200px">Discrepancy Type</th>
                        <th>Notes</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item,key) in newSalaryAdjustment">
                        <td>
                            <button class="btn btn-danger btn-sm" @click="removeSalaryAdjustmentItem(key)">X</button>
                        </td>
                        <td>
                            <select v-model="newSalaryAdjustment[key].period" class="form-control">
                                <option v-bind:value="period.start" v-for="period in periods">@{{ period.name }}</option>
                            </select>
                        </td>
                        <td>
                            <select v-model="newSalaryAdjustment[key].discrepancy" class="form-control">
                                <option value="unpaid_day">Unpaid Day</option>
                                <option value="unpaid_overtime">Unpaid Overtime</option>
                                <option value="unpaid_leave">Unpaid Leave</option>
                                <option value="deducted_tardiness">Deducted Tardiness</option>
                                <option value="unpaid_allowance">Unpaid Allowance</option>
                            </select>
                        </td>
                        <td>
                            <textarea class="form-control" rows="2" v-model="newSalaryAdjustment[key].notes"
                                placeholder="Please provide clear details about your concern. (Ex: Unpaid overtime 7PM to 9PM) and provide the date."></textarea>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <button class="btn btn-success" @click="saveSalaryAdjustment($event)" data-loading-text="Processing...">Submit</button>
            <button class="btn btn-warning" @click="clearSalaryAdjustmentItems()">Clear</button>
        </div>
    </div>
    @include('employee.requests.salary_adjustment')
</div>

<script src="../../assets/vuejs/instances/salary_adjustments.js?cache={{ rand() }}"></script>