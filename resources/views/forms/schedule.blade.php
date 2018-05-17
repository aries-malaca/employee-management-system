<div id="schedules">
	<div class="portlet box grey">
		<div class="portlet-title">
			<div class="caption">
				<i class="fa fa-gift"></i> Change Shift Request Form
			</div>
			<div class="tools">
				<a href="javascript:;" class="collapse" data-original-title="" title=""></a>
			</div>
		</div>
		<div class="portlet-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" class="form-control" v-model="setSingleSchedule.date"/>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Branch</label>
                        <select class="form-control" v-model="setSingleSchedule.branch_id">
                            <option v-bind:value="branch.id" v-for="branch in branches">@{{ branch.branch_name }}</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Shift</label>
                        <select class="form-control" v-model="setSingleSchedule.time">
                            <option v-bind:value="sched.time" v-for="sched in availableSchedules">@{{ sched.schedule_name  }} (@{{ sched.time }} ) </option>
                            <option value="00:00">Rest Day</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4" v-if="setSingleSchedule.time=='00:00'">
                    <div class="alert alert-info">
                        <b>Once approved, </b> The Default Restday for the week requested will be changed to Working Day.
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" v-model="setSingleSchedule.notes"></textarea>
                    </div>
                </div>
            </div>
			<button class="btn btn-success" @click="requestSchedule($event)" data-loading-text="Processing...">Submit</button>
		</div>
	</div>
    @include('employee.requests.schedule')
</div>
<script src="../../assets/vuejs/instances/schedules.js?cache={{ rand() }}"></script>