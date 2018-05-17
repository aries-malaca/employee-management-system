<div id="overtimes">
    <div class="portlet box purple">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-gift"></i> Overtime Form
                <button class="btn btn-info" @click="addOvertimeItem">+</button>
            </div>
            <div class="tools">
                <a href="javascript:;" class="collapse" data-original-title="" title=""></a>
            </div>
        </div>
        <div class="portlet-body">
            <div class="alert alert-info">
                Please indicate the Excess Hours you rendered beyond your working schedule. (Example: working schedule is 8:00 AM to 5:00 PM and you have to extend 4 hours,
                you should file 5:00 PM to 9:00 PM overtime)
            </div>
            <div class="scrollable">
                <table class="table table-hover table-bordered">
                    <thead>
                    <tr>
                        <th style="width:50px"></th>
                        <th style="width:160px">Start</th>
                        <th style="width:130px"> End</th>
                        <th style="width:50px">Hours</th>
                        <th>Notes</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item,key) in newOvertime">
                        <td>
                            <button class="btn btn-danger btn-sm" @click="removeOvertimeItem(key)">X</button>
                        </td>
                        <td>
                            <input type="date" v-model="newOvertime[key].date_start" class="form-control"/>
                            <input type="time" v-model="newOvertime[key].time_start" class="form-control"/>
                        </td>
                        <td>
                            <input type="date" v-model="newOvertime[key].date_end" class="form-control"/>
                            <input type="time" v-model="newOvertime[key].time_end" class="form-control"/>
                        </td>
                        <td>
                            <h4>
                                <span>Hours: @{{ getHours(newOvertime[key]).hours }} </span>
                                <span v-if=" getHours(newOvertime[key]).minutes != 0">Minutes:@{{ getHours(newOvertime[key]).minutes }}</span>
                            </h4>
                        </td>
                        <td>
                            <textarea class="form-control" rows="1" v-model="newOvertime[key].notes" ></textarea>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <button class="btn btn-success" @click="saveOvertime($event)" data-loading-text="Processing...">Submit</button>
            <button class="btn btn-warning" @click="clearOvertimeItems()">Clear</button>
        </div>
    </div>
    @include('employee.requests.overtime')
</div>
<script src="../../assets/vuejs/instances/overtimes.js?cache={{ rand() }}"></script>