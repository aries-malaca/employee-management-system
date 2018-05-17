@extends('layouts.main')
@section('content')
    <div id="trainees" v-cloak>
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i>Trainees
                    <button @click="showAddModal" type="button" class="btn green btn-sm">Add Trainee</button>
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
                                <a @click="setOrderBy('biometric_no',0)">Biometric No.
                                    <span v-if="pagination[0].sort_by=='biometric_no'" >
                            <i v-if="pagination[0].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                            <i v-if="pagination[0].sort_order==1" class="fa fa-angle-up pull-right"></i>
                        </span>
                                </a>
                            </th>
                            <th style="width:200px;">
                                <a @click="setOrderBy('first_name',0)">First Name
                                    <span v-if="pagination[0].sort_by=='first_name'" >
                            <i v-if="pagination[0].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                            <i v-if="pagination[0].sort_order==1" class="fa fa-angle-up pull-right"></i>
                        </span>
                                </a>
                            </th>
                            <th>
                                <a @click="setOrderBy('last_name',0)">Last Name
                                    <span v-if="pagination[0].sort_by=='last_name'" >
                            <i v-if="pagination[0].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                            <i v-if="pagination[0].sort_order==1" class="fa fa-angle-up pull-right"></i>
                        </span>
                                </a>
                            </th>
                            <th>
                                <a @click="setOrderBy('wave',0)">Wave
                                    <span v-if="pagination[0].sort_by=='wave'" >
                            <i v-if="pagination[0].sort_order==-1" class="fa fa-angle-down pull-right"></i>
                            <i v-if="pagination[0].sort_order==1" class="fa fa-angle-up pull-right"></i>
                        </span>
                                </a>
                            </th>
                            <th>
                                <a @click="setOrderBy('classification',0)">Classification
                                    <span v-if="pagination[0].sort_by=='classification'" >
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
                            <th>Assigned ID</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="trainee in filtered">
                            <td>@{{ trainee.biometric_no }}</td>
                            <td>@{{ trainee.first_name }}</td>
                            <td>@{{ trainee.last_name }}</td>
                            <td>@{{ trainee.wave }}</td>
                            <td>@{{ trainee.classification }}</td>
                            <td>@{{ trainee.status }}</td>
                            <td>@{{ trainee.assigned_id }}</td>
                            <td>
                                <button class="btn btn-xs btn-info" @click="viewTrainee(trainee)">Edit</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                @include('pagination.footer_0')
            </div>
        </div>

        <!-- Start of add bank Modal-->
        <div class="modal fade" id="add-trainee-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title" v-if="newTrainee.id==0">Add Trainee: </h4>
                        <h4 class="modal-title" v-else>Edit Trainee Info: </h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Biometric No.</label>
                                    <input type="number" class="form-control" v-model="newTrainee.biometric_no"/>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>First Name</label>
                                    <input type="text" class="form-control" v-model="newTrainee.first_name"/>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Middle Name</label>
                                    <input type="text" class="form-control" v-model="newTrainee.middle_name"/>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Last Name</label>
                                    <input type="text" class="form-control" v-model="newTrainee.last_name"/>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Wave</label>
                                    <input type="number" class="form-control" v-model="newTrainee.wave"/>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Classification</label>
                                    <select class="form-control" v-model="newTrainee.classification">
                                        <option value="franchised">Franchised</option>
                                        <option value="company-owned">Company Owned</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control" v-model="newTrainee.status">
                                        <option value="active">Active</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Assigned ID</label>
                                    <input type="number" class="form-control" v-model="newTrainee.assigned_id"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn blue" v-if="newTrainee.id==0" @click="addTrainee">Save</button>
                        <button type="button" class="btn blue" v-else @click="updateTrainee">Update</button>
                        <button type="button" class="btn default" data-dismiss="modal">Close</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- end of add bank Modal-->

    </div>
    <script src="../../assets/vuejs/instances/trainees.js?cache={{ rand() }}"></script>
@endsection
