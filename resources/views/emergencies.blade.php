@extends('layouts.main')
@section('content')

<div class="portlet box green" id="emergencies">
	<div class="portlet-title">
		<div class="caption">
			<i class="fa fa-gift"></i>Emergency Attendance List
			<button class="btn purple btn-sm" @click="showAddModal">Add Emergency</button>
		</div>
		<div class="tools">
			<a href="javascript:;" class="collapse" data-original-title="" title=""></a>
		</div>
	</div>
	<div class="portlet-body">
        @include('pagination.header_0')
        <div class="scrollable">
            <table class="table dataTable table-striped table-bordered table-hover">
            <thead>
            <tr>
                <th>Emergency Name</th>
                <th>Date</th>
                <th>Notes</th>
                <th>Branch</th>
                <th>Employees</th>
                <th style="width:120px"></th>
            </tr>
            </thead>
            <tbody>
                <tr v-for="emergency in filtered">
                    <td>@{{ emergency.emergency_name }}</td>
                    <td>@{{ formatDateTime(emergency.date_start,'MM/DD/YYYY') }} @{{ (emergency.date_start!=emergency.date_end?' - ' + formatDateTime(emergency.date_end,'MM/DD/YYYY'):'' ) }}</td>
                    <td>@{{ emergency.notes }}</td>
                    <td>@{{ emergency.branch_covered.length }}</td>
                    <td>@{{ emergency.exempted_employees.length }}</td>
                    <td>
                        <button @click="editEmergency(emergency)" class="btn btn-sm btn-info">Edit</button>
                        <button @click="deleteEmergency(emergency)" class="btn btn-sm btn-danger">Delete</button>
                    </td>
                </tr>
            </tbody>
            </table>
        </div>
        @include('pagination.footer_0')
	</div>

    <!-- Start of add emergency Modal-->
    <div class="modal fade" id="add-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title" v-if="newEmergency.id==0">Add Emergency</h4>
                    <h4 class="modal-title" v-else>Edit Emergency</h4>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label bold">Emergency Name</label>
                                    <input type="text" v-model="newEmergency.emergency_name" class="form-control">
                                </div>
                            </div>
                            <!--/span-->
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label class="control-label bold">Date Start</label>
                                    <input type="date" v-model="newEmergency.date_start" class="form-control"/>
                                </div>
                            </div>
                            <!--/span-->
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label class="control-label bold">Date End</label>
                                    <input type="date" v-model="newEmergency.date_end" class="form-control"/>
                                </div>
                            </div>
                            <!--/span-->
                        </div>
                        <!--/row-->
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label class="control-label bold">Notes</label>
                                    <textarea v-model="newEmergency.notes" class="form-control"></textarea>
                                </div>
                            </div>
                            <!--/span-->
                        </div>
                        <!--/row-->
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label bold">Branch Covered</label>
                                    <select class="form-control" v-model="newEmergency.branch_covered" multiple>
                                        <option v-bind:value="branch.id" v-for="branch in branches">@{{ branch.branch_name }}</option>
                                    </select>
                                </div>
                            </div>
                            <!--/span-->
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label bold">Exempted Employees</label>
                                    <select class="form-control" v-model="newEmergency.exempted_employees" multiple>
                                        <option v-bind:value="employee.user_id" v-for="employee in employees">@{{ employee.name }}</option>
                                    </select>
                                </div>
                            </div>
                            <!--/span-->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" @click="addEmergency" v-if="newEmergency.id==0" class="btn blue">Save</button>
                    <button type="submit" @click="updateEmergency" v-else class="btn blue">Update</button>
                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                </div>
                </form>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- end of add energency Modal-->
</div>

<script src="../../assets/vuejs/instances/emergencies.js?cache={{ rand() }}"></script>
@endsection