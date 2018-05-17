<div class="row">
    <div class="col-sm-12">
        <div class="portlet box blue">
        	<div class="portlet-title">
        		<div class="caption">
        			<i class="fa fa-gift"></i> Employee Files
        			<a @click="showFileModal()" class="btn btn-sm btn-success"> Upload File </a>
        		</div>
        		<div class="tools">
        			<a href="javascript:;" class="collapse" data-original-title="" title=""></a>
        		</div>
        	</div>
        	<div class="portlet-body"> 
            	<table class="table table-striped table-bordered table-hover">
                    <thead>
                	<tr>
                		<th>File Name</th>
                		<th>Category</th>
                		<th>Date Uploaded</th>
                		<th style="width:140px"></th>
                    </tr>			
                	</thead>
                	<tbody>
						<tr v-for="file in files">
							<td><a v-bind:href="'../../documents/'+file.file_name">@{{ file.description }}</a></td>
							<td>@{{ file.category }}</td>
							<td>@{{ file.created_at }}</td>
							<td>
								<button @click="deleteFile(file)" type="button" class="btn btn-danger btn-xs">Delete</button>
							</td>
						</tr>
                	</tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Start of fileupload Modal-->
<div class="modal fade" id="file-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title">File Upload</h4>
			</div>
			<div class="modal-body">
				<div class="form-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label>Description</label>
                            <input class="form-control form-control-inline" v-model="newFile.description" type="text" />
                        </div>
                        <div class="col-md-6">
                            <label>Category</label>
                            <input class="form-control form-control-inline" v-model="newFile.category" type="text"  />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <label>Select image to upload:</label>
                            <input type="file" id="file"><br/>
                        </div>
                    </div>

                </div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" @click="uploadFile()">Upload</button>
				<button type="button" class="btn default" data-dismiss="modal">Close</button>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- end fileupload Modal-->