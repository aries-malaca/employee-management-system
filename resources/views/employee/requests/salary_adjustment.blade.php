<div class="portlet box blue">
    <div class="portlet-title">
        <div class="caption">
            <i class="fa fa-gift"></i> Pending Salary Adjustments
        </div>
        <div class="tools">
            <a href="javascript:;" class="collapse" data-original-title="" title=""></a>
        </div>
    </div>
    <div class="portlet-body">
        @include('pagination.header_0')
        <label v-if="trackable && !isEmployee">
            <input type="checkbox" v-model="show_all"/> Show non-approval
        </label>
        <div class="scrollable">
            <table class="table table-hover table-bordered" style="font-size:11.5px;">
                <thead>
                <tr>
                    <th v-if="!isEmployee">Name</th>
                    <th>Date/Time Filed</th>
                    <th>Discrepancy Type</th>
                    <th>Notes</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(adjustment,key) in filtered">
                    <td v-if="!isEmployee">
                        <a v-bind:href="'../../employee/'+adjustment.user_id" target="_blank">@{{ adjustment.name }}</a>
                    </td>
                    <td>@{{ formatDateTime(adjustment.created_at,"MM/DD/YYYY LT") }}</td>
                    <td>@{{ adjustment.request_data.discrepancy }}</td>
                    <td>@{{ adjustment.request_note }}</td>
                    <td>
                        <button class="btn btn-danger btn-xs" v-if="isEmployee" data-loading-text="Processing..."
                                @click="deleteAdjustment(adjustment, $event)">Delete</button>
                        <button class="btn btn-warning btn-xs" @click="showPrompt(adjustment)">Edit</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        @include('pagination.footer_0')
    </div>
</div>

<div class="portlet box green">
    <div class="portlet-title">
        <div class="caption">
            <i class="fa fa-gift"></i> Salary Adjustments History
        </div>
        <div class="tools">
            <a href="javascript:;" class="collapse" data-original-title="" title=""></a>
        </div>
    </div>
    <div class="portlet-body">
        @include('pagination.header_1')
        <div class="scrollable">
            <table class="table table-hover table-bordered" style="font-size:11.5px;">
                <thead>
                <tr>
                    <th v-if="!isEmployee">Name</th>
                    <th>Date/Time Filed</th>
                    <th>Discrepancy Type</th>
                    <th>Notes</th>
                    <th>Approved Amount</th>
                    <th>Feedback</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(adjustment,key) in filtered1">
                    <td v-if="!isEmployee">
                        <a v-bind:href="'../../employee/'+adjustment.user_id" target="_blank">@{{ adjustment.name }}</a>
                    </td>
                    <td>@{{ formatDateTime(adjustment.created_at,"MM/DD/YYYY LT") }}</td>
                    <td>@{{ adjustment.request_data.discrepancy }}</td>
                    <td>@{{ adjustment.request_note }}</td>
                    <td>@{{ adjustment.request_data.amount }}</td>
                    <td>@{{ adjustment.request_data.feedback }}</td>
                    <td>
                        <span class="label label-danger" v-if="adjustment.request_data.status == 'denied'">Denied</span>
                        <span class="label label-success" v-if="adjustment.request_data.status == 'approved'">Approved</span>
                        <span class="label label-warning" v-if="adjustment.request_data.status == 'pending'">Pending</span>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        @include('pagination.footer_1')
    </div>
</div>

<!-- Start of add branch Modal-->
<div class="modal fade" id="adjustment-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title" v-if="actionModal.action=='approve'">Approve Adjustment</h4>
                <h4 class="modal-title" v-if="actionModal.action=='deny'">Deny Adjustment</h4>
            </div>
            <div class="modal-body">
                <div class="form-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="control-label bold">Notes</label>
                                <input type="text" v-model="actionModal.notes" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" v-if="actionModal.action=='approve'" data-loading-text="Processing..." @click="approveAdjustment($event)" class="btn blue">Okay</button>
                <button type="button" v-if="actionModal.action=='deny'" data-loading-text="Processing..." @click="denyAdjustment($event)" class="btn red">Okay</button>
                <button type="button" class="btn default" data-dismiss="modal">Close</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- end of approve Modal-->