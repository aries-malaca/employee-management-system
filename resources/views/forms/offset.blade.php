<div id="offsets">
    <div class="portlet box red-pink">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-gift"></i> Offset Request Form
            </div>
            <div class="tools">
                <a href="javascript:;" class="collapse" data-original-title="" title=""></a>
            </div>
        </div>
        <div class="portlet-body">
            <div class="scrollable">
                <table class="table table-hover table-bordered">
                    <tr>
                        <th colspan="4">Offset From Duty (Excess Hours Rendered, Do not include the Official Work Time) - For reference <button class="btn btn-info" @click="addDutyItem">+</button> </th>
                    </tr>
                    <tr>
                        <th></th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Notes</th>
                    </tr>
                    <tr v-for="(item,key) in newOffset.duties">
                        <td>
                            <button class="btn btn-danger btn-sm" @click="removeDutyItem(key)">X</button>
                        </td>
                        <td>
                            <input type="date" v-model="newOffset.duties[key].date_start" class="form-control"/>
                            <input type="time" v-model="newOffset.duties[key].time_start" class="form-control"/>
                        </td>
                        <td>
                            <input type="date" v-model="newOffset.duties[key].date_end" class="form-control"/>
                            <input type="time" v-model="newOffset.duties[key].time_end" class="form-control"/>
                        </td>
                        <td>
                            <textarea class="form-control" rows="2" v-model="newOffset.duties[key].notes"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="4">
                            <div class="alert alert-info">Total Excess Hours: <b>@{{  total_duty_hours }}</b></div>
                        </th>
                    </tr>
                    <tr>
                        <th colspan="4">Use Offset (Add as Attendance) <button class="btn btn-info" @click="addOffsetItem">+</button></th>
                    </tr>
                    <tr>
                        <th></th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Notes</th>
                    </tr>
                    <tr v-for="(item,key) in newOffset.offsets">
                        <td>
                            <button class="btn btn-danger btn-sm" @click="removeOffsetItem(key)">X</button>
                        </td>
                        <td>
                            <input type="date" v-model="newOffset.offsets[key].date_start" class="form-control"/>
                            <input type="time" v-model="newOffset.offsets[key].time_start" class="form-control"/>
                        </td>
                        <td>
                            <input type="date" v-model="newOffset.offsets[key].date_end" class="form-control"/>
                            <input type="time" v-model="newOffset.offsets[key].time_end" class="form-control"/>
                        </td>
                        <td>
                            <textarea class="form-control" rows="2" v-model="newOffset.offsets[key].notes"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="alert alert-info">
                                Total Offset Hours: <b>@{{  total_offset_hours }}</b>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <button class="btn btn-success" @click="saveOffset($event)" data-loading-text="Processing...">Submit</button>
        </div>
    </div>
    @include('employee.requests.offset')
</div>

<script src="../../assets/vuejs/instances/offsets.js?cache={{ rand() }}"></script>