<div class="portlet box yellow-casablanca" id="tasks" v-if="1==0">
    <div class="portlet-title">
        <div class="caption">
            <i class="fa fa-gift"></i> Task Management
            <button type="button" class="btn green-haze btn-sm" @click="showAddModal">Add Task</button>
        </div>
        <div class="tools">
            <a href="javascript:;" class="collapse" data-original-title="" title="">
            </a>
        </div>
    </div>
    <div class="portlet-body" style="height:520px;overflow-y:scroll">

    </div>

    <!-- Start of add emergency Modal-->
    <div class="modal fade" id="add-task-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title" v-if="newTask.id==0">Add Task</h4>
                    <h4 class="modal-title" v-else>Edit Task</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Task Title</label>
                                <input type="text" class="form-control" v-model="newTask.task_title" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Start Date</label>
                                <input type="date" class="form-control" v-model="newTask.task_started_date" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Target Completion Date</label>
                                <input type="date" class="form-control" v-model="newTask.task_target_completion_date" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Task Description</label>
                                <textarea class="form-control" v-model="newTask.task_description"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Priority</label>
                                <select v-model="newTask.task_priority" class="form-control">
                                    <option value="1">Low</option>
                                    <option value="2">Medium</option>
                                    <option value="3">High</option>
                                    <option value="4">Urgent</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Status</label>
                                <select v-model="newTask.task_status" class="form-control" @change="changeStatus">
                                    <option value="open">Open</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="hold">Hold</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group" v-if="newTask.task_status == 'open' || newTask.task_status == 'completed'">
                                <label>Progress (%)</label>
                                <input type="number" v-model="newTask.task_progress" class="form-control"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info" @click="addTask">Save</button>
                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- end of add energency Modal-->
</div>