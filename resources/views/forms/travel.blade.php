<div id="travels">
    <div class="portlet box red-pink">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-gift"></i> Travel Form <button class="btn btn-info" @click="addTravelItem">+</button>
            </div>
            <div class="tools">
                <a href="javascript:;" class="collapse" data-original-title="" title=""></a>
            </div>
        </div>
        <div class="portlet-body">
            <div class="scrollable">
                <table class="table table-hover table-bordered">
                    <thead>
                    <tr>
                        <th style="width:50px"></th>
                        <th style="width:160px">Travel Date</th>
                        <th style="width:130px">Time Start</th>
                        <th style="width:130px"> Time End</th>
                        <th>Notes</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item,key) in newTravel">
                        <td>
                            <button class="btn btn-danger btn-sm" @click="removeTravelItem(key)">X</button>
                        </td>
                        <td>
                            <input type="date" v-model="newTravel[key].date_start" class="form-control"/>
                        </td>
                        <td>
                            <input type="time" v-model="newTravel[key].time_start" class="form-control"/>
                        </td>
                        <td>
                            <input type="time" v-model="newTravel[key].time_end" class="form-control"/>
                        </td>
                        <td>
                            <textarea class="form-control" rows="1" v-model="newTravel[key].notes"></textarea>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <button class="btn btn-success" @click="saveTravel($event)" data-loading-text="Processing...">Submit</button>
            <button class="btn btn-warning" @click="clearTravelItems()">Clear</button>
        </div>
    </div>
    @include('employee.requests.travel')
</div>
<script src="../../assets/vuejs/instances/travels.js?cache={{ rand() }}"></script>