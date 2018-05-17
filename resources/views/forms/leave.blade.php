<div id="leaves">
    <div class="portlet box grey">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-gift"></i> Leave Form
                <button class="btn btn-info" @click="addLeaveItem">+</button>
            </div>
            <div class="tools">
                <a href="javascript:;" class="collapse" data-original-title="" title=""></a>
            </div>
        </div>
        <div class="portlet-body">
            <div class="alert alert-info">
                (Sick/Vacation Leave 2017) Credits can only be used until March 31,2018.
            </div>
            <div class="scrollable">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th style="width:50px"></th>                            
                            <th style="width:200px">Leave Type</th>
                            <th style="width:160px">Date Start</th>
                            <th style="width:160px">Date End</th>
                            <th style="width:140px">Duration</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item,key) in newLeave">
                            <td>
                                <button class="btn btn-danger btn-sm" @click="removeLeaveItem(key)">X</button>
                            </td>
                            <td>
                                <select style="width:120px" @change="newLeave[key].mode='FULL', resolveRange(key)" v-model="newLeave[key].leave_type_id" class="form-control">
                                    <option v-bind:value="leave.id" v-for="leave in leave_types" v-if="!leave.hidden && leave.leave_type_active===1">@{{ leave.leave_type_name }}</option>
                                </select>
                            </td>
                            <td>
                                <input type="date" @change="newLeave[key].mode='FULL'" v-bind:disabled="newLeave[key].leave_type_id === 0"  v-model="newLeave[key].date_start" class="form-control"/>
                            </td>
                            <td>
                                <input type="date" @change="newLeave[key].mode='FULL'" v-bind:disabled="!allowStaggered(newLeave[key].leave_type_id)"
                                       v-model="newLeave[key].date_end" class="form-control"/>
                            </td>
                            <td>
                                <select style="width:100px" v-model="newLeave[key].mode" class="form-control">
                                    <option value="FULL">Full</option>
                                    <option value="AM" v-if="newLeave[key].date_start == newLeave[key].date_end && allowHalfDay(newLeave[key].leave_type_id)">
                                        Morning
                                    </option>
                                    <option value="PM" v-if="newLeave[key].date_start == newLeave[key].date_end && allowHalfDay(newLeave[key].leave_type_id)">
                                        Afternoon
                                    </option>
                                </select>
                            </td>
                            <td>
                                <textarea class="form-control" rows="2" v-model="newLeave[key].notes"></textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <button class="btn btn-success" @click="saveLeave($event)" data-loading-text="Processing...">Submit</button>
            <button class="btn btn-warning" @click="clearLeaveItems()">Clear</button>
        </div>
    </div>
    @include('employee.common.leave_credits')
    @include('employee.requests.leave')
</div>

<script src="../../assets/vuejs/instances/leaves.js?cache={{ rand() }}"></script>