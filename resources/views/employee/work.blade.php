<!-- BEGIN FORM-->
<div class="form-body">
	<div class="row">
		<div class="col-sm-3">
			<div class="form-group">
				<label class="control-label bold">Employee No.</label>
				<input type="text" v-model="newEmployee.employee_no" class="form-control" placeholder="Employee Number">
			</div>
		</div>
		<!--/span-->
		<div class="col-sm-3">
			<div class="form-group">
				<label class="control-label bold">Status</label>
				<select v-model="newEmployee.active_status" class="form-control">
					<option value="1">Active</option>
					<option value="2">Terminated</option>
					<option value="3">Resigned</option>
					<option value="4">AWOL</option>
				</select>
			</div>
		</div>
		<!--/span-->
		<div class="col-sm-3">
			<div class="form-group">
				<label class="control-label bold">Date Hired</label>
				<input class="form-control form-control-inline" v-model="newEmployee.hired_date" type="date" />
			</div>
		</div>
		<!--/span-->
        <div class="col-sm-3">
            <div class="form-group">
                <label class="control-label bold">Employment Status</label>
                <select v-model="newEmployee.employee_status" class="form-control">
                    <option v-for="status in employmentStatuses" v-bind:value="status.id">@{{ status.employment_status_name }}</option>
                </select>
            </div>
        </div>
        <!--/span-->
    </div>

    <div class="row">
        <div class="col-sm-4" v-show="newEmployee.employee_status==2">
            <div class="form-group">
                <label class="control-label bold">Regularization Date</label>
                <input class="form-control form-control-inline" v-model="newEmployee.regularization_date" type="date"/>
            </div>
        </div>
        <!--/span-->
        <div class="col-sm-4" v-show="newEmployee.active_status!=1">
            <div class="form-group">
                <label class="control-label bold">End of Employment Date</label>
                <input class="form-control form-control-inline" v-model="newEmployee.end_employment_date" type="date"/>
            </div>
        </div>
        <!--/span-->
        <div class="col-sm-4" v-show="newEmployee.active_status!=1">
            <div class="form-group">
                <label class="control-label bold">End of Employment Reason</label>
                <textarea class="form-control" v-model="newEmployee.end_employment_reason"></textarea>
            </div>
        </div>
    </div>
	<!--/row-->
	<div class="row">
	    <div class="col-sm-4">
			<div class="form-group">
				<label class="control-label bold">Department</label>
				<select v-model="newEmployee.department_id" class="form-control">
					<option v-for="department in departments" v-bind:value="department.id">@{{ department.department_name }}</option>
				</select>
			</div>
		</div>
		<!--/span-->
		
	    <div class="col-sm-4">
			<div class="form-group">
				<label class="control-label bold">Position</label>
				<select v-model="newEmployee.position_id" class="form-control">
					<option v-for="position in positions" v-if="position.department_id == newEmployee.department_id"
							v-bind:value="position.id">@{{ position.position_name }}</option>
				</select>
			</div>
		</div>
		<!--/span-->
		
		<div class="col-sm-4">
			<div class="form-group">
				<label class="control-label bold">Company</label>
				<select v-model="newEmployee.company_id" class="form-control">
					<option v-for="company in companies" v-bind:value="company.id">@{{ company.company_name  }}</option>
				</select>
			</div>
		</div>
		<!--/span-->
	</div>
	<!--/row-->
	
	<div class="row">
	    <div class="col-sm-4">
			<div class="form-group">
				<label class="control-label bold">Tax Exemption</label>
				<select v-model="newEmployee.tax_exemption_id" class="form-control">
					<option v-bind:value="tax.id" v-for="tax in tax_exemptions">@{{ tax.tax_exemption_name }}</option>
				</select>
			</div>
		</div>
		<!--/span-->
		<div class="col-sm-4">
			<div class="form-group">
				<label class="control-label bold">Batch</label>
				<select v-model="newEmployee.batch_id" class="form-control">
					<option v-for="batch in batches" v-bind:value="batch.id">@{{ batch.batch_name  }}</option>
				</select>
			</div>
		</div>
		<!--/span-->
		<div class="col-sm-4">
			<div class="form-group">
               	<label class="control-label bold">Allow OT</label>
				<select v-model="newEmployee.allow_overtime" class="form-control">
					<option value="1">Yes</option>
					<option value="0">No</option>
				</select>
			</div>
		</div>
		<!--/span-->
	</div>
	<!--/row-->
	
	<div class="row">
		<div class="col-sm-6 col-md-3">
			<div class="form-group">
               	<label class="control-label bold">Allow Offset</label>
				<select v-model="newEmployee.allow_offset" class="form-control">
					<option value="1">Yes</option>
					<option value="0">No</option>
				</select>
			</div>
		</div>
		<!--/span-->

		<div class="col-sm-6 col-md-3">
			<div class="form-group">
               	<label class="control-label bold">Allow Adjustment</label>
				<select v-model="newEmployee.allow_adjustment" class="form-control">
					<option value="1">Yes</option>
					<option value="0">No</option>
				</select>
			</div>
		</div>
		<!--/span-->
		<div class="col-sm-6 col-md-3">
			<div class="form-group">
               <label class="control-label bold">Allow Travel</label>
				<select v-model="newEmployee.allow_travel" class="form-control">
					<option value="1">Yes</option>
					<option value="0">No</option>
				</select>
			</div>
		</div>
		<!--/span-->
		<div class="col-sm-6 col-md-3">
			<div class="form-group">
               	<label class="control-label bold">Allow Leave</label>
				<select v-model="newEmployee.allow_leave" class="form-control">
					<option value="1">Yes</option>
					<option value="0">No</option>
				</select>

			</div>
		</div>
		<!--/span-->	
	</div>
	<!--/row-->
	
	<div class="row">
		<div class="col-sm-3">
			<div class="form-group">
				<label class="control-label bold">Local Phone No.</label>
				<input type="number" class="form-control" v-model="newEmployee.local_number"/>
			</div>
		</div>
		<!--/span-->
	    <div class="col-sm-3" v-show="newEmployee.view_salary">
			<div class="form-group">
                <label class="control-label bold">Bank</label>
				<select v-model="newEmployee.bank_code" class="form-control">
					<option v-bind:value="bank.id" v-for="bank in banks">@{{ bank.bank_name }}</option>
				</select>
			</div>
		</div>
		<!--/span-->
		
		<div class="col-sm-3" v-show="newEmployee.view_salary">
			<div class="form-group">
				<label class="control-label bold">Bank Account No.</label>
				<input class="form-control form-control-inline" v-model="newEmployee.bank_number" type="text"/>
			</div>
		</div>
		<!--/span-->
		<div class="col-sm-3" v-show="newEmployee.view_salary">
			<div class="form-group">
				<label class="control-label bold">Evaluation Date</label>
				<input class="form-control form-control-inline" v-model="newEmployee.next_evaluation" type="date" />
			</div>
		</div>
		<!--/span-->
	</div>
	<!--/row-->
	<div class="row">
		<div class="col-md-12">
			<div class="form-group">
				<label class="control-label bold">Skills</label>
				<textarea v-model="newEmployee.skills" class="form-control"></textarea>
			</div>
		</div>
		<!--/span-->
	</div>
	<!--/row-->
	
	<h4>Compensation <button class="btn btn-success btn-sm" v-show="newEmployee.view_salary" @click="show_salary = !show_salary"> @{{ (show_salary?'Hide Salary':'Show Salary') }}</button></h4>
	<div class="row" v-show="newEmployee.view_salary">
		<div class="col-md-3" v-for="(contribution,key) in contributions">
			<div class="form-group">
				<label class="control-label">
					<input type="checkbox" v-model="newEmployee.trans[key].checked" />
					@{{ contribution.transaction_name }}</label>
			</div>
		</div>
	</div>
	<div class="row" v-show="newEmployee.view_salary">
		<div class="col-md-3">
			<div class="form-group">
				<label class="control-label bold">SSS No.</label>
				<input class="form-control form-control-inline" v-model="newEmployee.sss_no" type="text"/>
			</div>
		</div>
		<!--/span-->
		<div class="col-md-3">
			<div class="form-group">
				<label class="control-label bold">TIN</label>
				<input class="form-control form-control-inline" v-model="newEmployee.tin_no" type="text" />
			</div>
		</div>
		<!--/span-->
		<div class="col-md-3">
			<div class="form-group">
				<label class="control-label bold">Philhealth No.</label>
				<input class="form-control form-control-inline" v-model="newEmployee.philhealth_no" type="text"/>
			</div>
		</div>
		<!--/span-->
		<div class="col-md-3">
			<div class="form-group">
				<label class="control-label bold">Pagibig No.</label>
				<input class="form-control form-control-inline" v-model="newEmployee.pagibig_no" type="text"/>
			</div>
		</div>
		<!--/span-->
	</div>
	<!--/row-->
	
	<div class="row" v-show="show_salary">
		<div v-show="newEmployee.view_salary" class="col-md-4">
			<div class="form-group">
				<label class="control-label bold">Cola/Day</label>
				<input class="form-control form-control-inline" v-model="newEmployee.cola_rate" type="number"/>
			</div>
		</div>
		<!--/span-->
		<div v-show="newEmployee.view_salary" class="col-md-5">
			<div class="form-group">
				<label class="control-label bold">@{{ salary_frequency }} Rate</label>
				<input class="form-control form-control-inline" v-model="newEmployee.salary_rate" type="number"/>
			</div>
		</div>
		<!--/span-->
		<div class="col-md-3">
			<div class="form-group">
				<label class="control-label bold">HMO No.</label>
				<input class="form-control form-control-inline" v-model="newEmployee.hmo_no" type="text"/>
			</div>
		</div>
		<!--/span-->
		
	</div>
	<!--/row-->
	<div v-show="newEmployee.view_salary && show_salary">
		<h4>Salary History</h4>
		<div class="row">
			<div class="col-md-12">
				<table class="table table-responsive">
					<tr>
						<th>Amount</th>
						<th>Effective Date</th>
						<th>Updated by</th>
						<th></th>
					</tr>
					<tr v-for="salary in salary_history">
						<td>@{{ salary.amount }}</td>
						<td>@{{ salary.start_date }} - @{{ salary.end_date }}</td>
						<td>@{{ salary.name }}</td>
						<th>
							<button class="btn btn-info btn-xs" @click="editSalary(salary)">Edit</button>
						</th>
					</tr>
				</table>
			</div>
		</div>
	</div>

    <!-- Start of add bank Modal-->
    <div class="modal fade" id="edit-salary-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">Edit Salary</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Start</label>
                                <input type="date" class="form-control" v-model="newSalary.start"/>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>End</label>
								<label>(Present
									<input type="checkbox" v-model="newSalary.is_present"/>)
								</label>
                                <input v-if="!newSalary.is_present" type="date" class="form-control" v-model="newSalary.end"/>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Amount</label>
                                <input type="number" class="form-control" v-model="newSalary.amount"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn blue" @click="updateSalary">Update</button>
                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- end of add bank Modal-->

</div>
<div class="form-actions right">
	<button type="button" @click="updateWork" class="btn blue"><i class="fa fa-check"></i> Update Work Info</button>
</div>
<!-- END FORM-->