<div id="adjustments">
    <div class="portlet box grey">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-gift"></i> Adjustment Form
                <button class="btn btn-info" @click="addAdjustmentItem">+</button>
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
                        <th style="width:160px">Date</th>
                        <th style="width:130px">Time</th>
                        <th style="width:100px">Mode</th>
                        <th>Notes</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item,key) in newAdjustment">
                        <td>
                            <button class="btn btn-danger btn-sm" @click="removeAdjustmentItem(key)">X</button>
                        </td>
                        <td>
                            <input type="date" v-model="newAdjustment[key].date" class="form-control"/>
                        </td>
                        <td>
                            <input type="time" v-model="newAdjustment[key].time" class="form-control"/>
                        </td>
                        <td>
                            <select v-model="newAdjustment[key].mode" class="form-control" style="width:100px">
                                <option value="IN">IN</option>
                                <option value="OUT">OUT</option>
                            </select>
                        </td>
                        <td>
                            <textarea class="form-control" rows="1" v-model="newAdjustment[key].notes"></textarea>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <button class="btn btn-success" @click="saveAdjustment($event)" data-loading-text="Processing...">Submit</button>
            <button class="btn btn-warning" @click="clearAdjustmentItems()">Clear</button>
        </div>
    </div>
   @include('employee.requests.adjustment')
</div>

<script src="../../assets/vuejs/instances/adjustments.js?cache={{ rand() }}"></script>