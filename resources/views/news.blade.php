@extends('layouts.main')
@section('content')

<div class="portlet box green" id="news">
	<div class="portlet-title">
		<div class="caption">
			<i class="fa fa-gift"></i>News and Updates
			<a type="button" @click="showAddModal" class="btn purple btn-sm">Add News</a>
		</div>
		<div class="tools">
			<a href="javascript:;" class="collapse" data-original-title="" title="">
			</a>

		</div>
	</div>
	<div class="portlet-body">
        <div class="timeline">
        <!-- TIMELINE ITEM -->
            <div class="timeline-item" v-for="(item,key) in news">
                <div class="timeline-badge">
                    <img class="timeline-badge-userpic" v-bind:src="'../../images/employees/'+item.picture">
                </div>
                <div class="timeline-body">
                    <div class="timeline-body-arrow">
                    </div>
                    <div class="timeline-body-head">
                        <div class="timeline-body-head-caption">
                            <a v-bind:href="'../../employee/'+item.user_id" class="timeline-body-title font-blue-madison">@{{ item.name }}</a>
                            <span class="timeline-body-time font-grey-cascade"> @{{ formatDateTime(item.created_at,"MM/DD/YYYY LT") }} </span>
                            <button class="btn btn-xs btn-info" @click="editNews(item)">Update</button>
                            <button class="btn btn-xs btn-danger" @click="deleteNews(item)">Delete</button>
                        </div>
                    </div>
                    <div class="timeline-body-content">
                        <h4>@{{ item.title }}</h4>
                        <p>@{{ item.description }}</p>
                        <br/>
                        Priority:
                        <span class="label label-info" v-if="item.priority==3">High</span>
                        <span class="label label-success" v-if="item.priority==2">Normal</span>
                        <span class="label label-warning" v-if="item.priority==1">Low</span>

                        Status:
                        <span class="label label-info" v-if="item.is_active==1">Active</span>
                        <span class="label label-danger" v-if="item.is_active==0">Inactive</span>
                    </div>
                </div>
            </div>
        </div>
	</div>

    <!-- Start of add news Modal-->
    <div class="modal fade" id="add-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">Add News and Updates</h4>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label class="control-label bold">Title</label>
                                    <input type="text" v-model="newNews.title" class="form-control">
                                </div>
                            </div>
                        </div>
                        <!--/row-->

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label bold">Priority</label>
                                    <select v-model="newNews.priority" class="form-control">
                                        <option value="1">Low</option>
                                        <option value="2">Normal</option>
                                        <option value="3">High</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label bold">Status</label>
                                    <select v-model="newNews.is_active" class="form-control">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label class="control-label bold">Description</label>
                                    <textarea v-model="newNews.description" rows="6" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                        <!--/row-->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn blue" @click="saveNews" v-if="newNews.id == 0">Save</button>
                    <button type="button" class="btn blue" @click="updateNews" v-else>Update</button>
                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- end of add news Modal-->
</div>

<script src="../../assets/vuejs/instances/news.js?cache={{ rand() }}"></script>
@endsection