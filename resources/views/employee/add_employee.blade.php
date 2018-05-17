<!-- Start of add Modal-->
<div class="modal fade" id="add-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-full">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">Add Employee</h4>
            </div>
            <div class="modal-body">
                <div class="form-body">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#profile" data-toggle="tab">
                                Profile </a>
                        </li>
                        <li>
                            <a href="#work" data-toggle="tab">
                                Work </a>
                        </li>
                        <li><a href="#compensation" data-toggle="tab">
                                Compensation / Access </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade active in" id="profile">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Employee No.</label>
                                        <input type="text" class="form-control" v-model="newEmployee.employee_no" placeholder="Employee Number">
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">First Name</label>
                                        <input type="text" v-model="newEmployee.first_name" class="form-control" placeholder="First Name">
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Middle Name</label>
                                        <input type="text" v-model="newEmployee.middle_name" class="form-control" placeholder="Middle Name">
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Last Name</label>
                                        <input type="text" v-model="newEmployee.last_name" class="form-control" placeholder="Last Name">
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Gender</label>
                                        <select name="gender" class="form-control" v-model="newEmployee.gender">
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                        </select>
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Civil Status</label>
                                        <select v-model="newEmployee.civil_status" class="form-control">
                                            <option value="single">Single</option>
                                            <option value="married">Married</option>
                                        </select>
                                    </div>
                                </div>
                                <!--/span-->
                            </div>
                            <!--/row-->
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Date of Birth</label>
                                        <input class="form-control form-control-inline" v-model="newEmployee.birth_date" type="date" />
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-sm-1">
                                    <div class="form-group">
                                        <label class="control-label bold">Age</label>
                                        <input type="text" v-bind:value="getAge" class="form-control" readonly>
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label bold">Address</label>
                                        <textarea class="form-control" v-model="newEmployee.address"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label bold">About</label>
                                        <textarea v-model="newEmployee.about" class="form-control"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label bold">Birth Place</label>
                                        <input type="text" v-model="newEmployee.birth_place" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <!--/row-->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label bold">Email Address</label>
                                        <input type="email" v-model="newEmployee.email" class="form-control">
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Mobile</label>
                                        <input type="number" min="0" v-model="newEmployee.mobile" class="form-control">
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Telephone</label>
                                        <input type="number" min="0" v-model="newEmployee.telephone" class="form-control"  >
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Town/City</label>
                                        <input type="text" v-model="newEmployee.city" class="form-control">
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Province/State</label>
                                        <input type="text" v-model="newEmployee.state" class="form-control">
                                    </div>
                                </div>
                                <!--/span-->
                            </div>
                            <!--/row-->
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label bold">Country</label>
                                        <input type="text" v-model="newEmployee.country" class="form-control">
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label bold">Zip Code</label>
                                        <input type="number" min="0" v-model="newEmployee.zip_code" class="form-control">
                                    </div>
                                </div>
                                <!--/span-->
                            </div>
                        </div>
                        <div class="tab-pane fade" id="work">
                            <div class="row">
                                <div class="col-md-2">
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
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Date Hired</label>
                                        <input class="form-control form-control-inline" v-model="newEmployee.hired_date" type="date" />
                                    </div>
                                </div>
                                <!--/span-->

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Evaluation Date</label>
                                        <input class="form-control form-control-inline" v-model="newEmployee.next_evaluation" type="date" />
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Employment Status</label>
                                        <select v-model="newEmployee.employee_status" class="form-control">
                                            <option v-for="status in employmentStatuses" v-bind:value="status.id">@{{ status.employment_status_name }}</option>
                                        </select>
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Batch</label>
                                        <select v-model="newEmployee.batch_id" class="form-control">
                                            <option v-for="batch in batches" v-bind:value="batch.id">@{{ batch.batch_name  }}</option>
                                        </select>
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-2">
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
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label bold">Department</label>
                                        <select v-model="newEmployee.department_id" class="form-control">
                                            <option v-for="department in departments" v-bind:value="department.id">@{{ department.department_name }}</option>
                                        </select>
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label bold">Position</label>
                                        <select v-model="newEmployee.position_id" class="form-control">
                                            <option v-for="position in positions" v-if="position.department_id == newEmployee.department_id"
                                                    v-bind:value="position.id">@{{ position.position_name }}</option>
                                        </select>
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Initial Branch</label>
                                        <select v-model="newEmployee.branch_id" class="form-control">
                                            <option v-for="branch in branches" v-bind:value="branch.id">@{{ branch.branch_name  }}</option>
                                        </select>
                                    </div>
                                </div>
                                <!--/span-->

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Biometric No.</label>
                                        <input type="number" v-model="newEmployee.biometric_no" class="form-control">
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Receive Notifications</label>
                                        <select v-model="newEmployee.receive_notification" class="form-control">
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                </div>
                                <!--/span-->
                            </div>
                            <!--/row-->

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label bold">Tax Exemption</label>
                                        <select v-model="newEmployee.tax_exemption_id" class="form-control">
                                            <option v-bind:value="tax.id" v-for="tax in tax_exemptions">@{{ tax.tax_exemption_name }}</option>
                                        </select>
                                    </div>
                                </div>
                                <!--/span-->

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Bank</label>
                                        <select v-model="newEmployee.bank_code" class="form-control">
                                            <option v-bind:value="bank.id" v-for="bank in banks">@{{ bank.bank_name }}</option>
                                        </select>
                                    </div>
                                </div>
                                <!--/span-->

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Bank Account No.</label>
                                        <input class="form-control form-control-inline" v-model="newEmployee.bank_number" type="text"/>
                                    </div>
                                </div>
                                <!--/span-->

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Allow OT</label>
                                        <select v-model="newEmployee.allow_overtime" class="form-control">
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-2">
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
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Allow Offset</label>
                                        <select v-model="newEmployee.allow_offset" class="form-control">
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Allow Adjustment</label>
                                        <select v-model="newEmployee.allow_adjustment" class="form-control">
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Allow Travel</label>
                                        <select v-model="newEmployee.allow_travel" class="form-control">
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label class="control-label bold">Local Phone #</label>
                                        <input v-model="newEmployee.local_number" class="form-control" type="number"/>
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label class="control-label bold">Skills</label>
                                        <textarea v-model="newEmployee.skills" class="form-control"></textarea>
                                    </div>
                                </div>
                                <!--/span-->
                            </div>
                            <!--/row-->
                        </div>
                        <div class="tab-pane fade" id="compensation" v-if="newEmployee.trans.length>0">
                            <div class="row">
                                <div class="col-md-3" v-for="(contribution,key) in contributions">
                                    <div class="form-group">
                                        <label class="control-label">
                                            <input type="checkbox" v-model="newEmployee.trans[key].checked" />
                                            @{{ contribution.transaction_name }}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">SSS No.</label>
                                        <input class="form-control form-control-inline" v-model="newEmployee.sss_no" type="text"/>
                                    </div>
                                </div>
                                <!--/span-->

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">TIN</label>
                                        <input class="form-control form-control-inline" v-model="newEmployee.tin_no" type="text" />
                                    </div>
                                </div>
                                <!--/span-->

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Philhealth No.</label>
                                        <input class="form-control form-control-inline" v-model="newEmployee.philhealth_no" type="text"/>
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Pagibig No.</label>
                                        <input class="form-control form-control-inline" v-model="newEmployee.pagibig_no" type="text"/>
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">HMO No.</label>
                                        <input class="form-control form-control-inline" v-model="newEmployee.hmo_no" type="text"/>
                                    </div>
                                </div>
                                <!--/span-->
                            </div>
                            <!--/row-->
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Cola</label>
                                        <input class="form-control form-control-inline" v-model="newEmployee.cola_rate" type="number"/>
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Salary/Mo.</label>
                                        <input class="form-control form-control-inline" v-model="newEmployee.salary_rate" type="number"/>
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label class="control-label bold">Access Level</label>
                                        <select v-model="newEmployee.level" required class="form-control">
                                            <option v-bind:value="level.id" v-for="level in levels">@{{ level.level_name }}</option>
                                        </select>
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Allow Access</label>
                                        <select class="form-control" v-model="newEmployee.allow_access">
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label class="control-label bold">Allow Suspension</label>
                                        <select v-model="newEmployee.allow_suspension" class="form-control">
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                </div>
                                <!--/span-->
                            </div>
                            <!--/row-->
                            <div class="row">
                                <!--/span-->

                            </div>
                            <!--/row-->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success btn-md" type="button" @click="addEmployee">Save</button>
                <button class="btn btn-default btn-md" type="button" data-dismiss="modal">Close</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- end of salary Modal-->