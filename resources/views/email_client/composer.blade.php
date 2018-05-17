
<div class="row" style="display:none" id="composer_div">
	<div class="col-md-12">
		<!-- BEGIN EXTRAS PORTLET-->
		<div class="portlet box blue-hoki">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-gift"></i>New Email
					<a onclick="sendMail(this)" class="btn btn-md blue">
						<i class="fa fa-send"></i> Send 
					</a>
				</div>

			</div>
			<div class="portlet-body form">
				<form class="inbox-compose form-horizontal form-bordered" id="fileupload" action="#" method="POST" enctype="multipart/form-data">
					<div class="form-body">
						<div class="form-group">
							<label class="control-label col-lg-1">To:</label>
							<div class="col-lg-11" style="padding:0px !important">
								<div class="col-lg-6" style="padding-bottom:5px !important;padding-top:5px !important">
									<div class="input-group">
										<input type="email" class="form-control to"/>
										<span class="input-group-btn">
										<button class="btn btn-success" onlick="toggleContactPicker(this)" type="button"><i class="fa fa-users"></i></button>
										<button class="btn btn-danger" onclick="deleteField(this)" type="button"><i class="fa fa-times"></i></button>
										</span>
									</div>
								</div>
								<div class="col-lg-2" style="padding-bottom:5px !important;padding-top:5px !important">
									<a onclick="addField(this)" class="btn btn-md blue">
										<i class="fa fa-plus"></i> Add 
									</a>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-lg-1">CC:</label>
							<div class="col-lg-11" style="padding:0px !important">
								<div class="col-lg-6" style="padding-bottom:5px !important;padding-top:5px !important">
									<div class="input-group">
										<input type="email" class="form-control cc"/>
										<span class="input-group-btn">
										<button class="btn btn-success" onlick="toggleContactPicker(this)" type="button"><i class="fa fa-users"></i></button>
										<button class="btn btn-danger" onclick="deleteField(this)" type="button"><i class="fa fa-times"></i></button>
										</span>
									</div>
								</div>
								<div class="col-lg-2" style="padding-bottom:5px !important;padding-top:5px !important">
									<a onclick="addField(this)" class="btn btn-md blue">
										<i class="fa fa-plus"></i> Add 
									</a>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-lg-1">BCC:</label>
							<div class="col-lg-11" style="padding:0px !important">
								<div class="col-lg-6" style="padding-bottom:5px !important;padding-top:5px !important">
									<div class="input-group">
										<input type="email" class="form-control bcc"/>
										<span class="input-group-btn">
										<button class="btn btn-success" onlick="toggleContactPicker(this)" type="button"><i class="fa fa-users"></i></button>
										<button class="btn btn-danger" onclick="deleteField(this)" type="button"><i class="fa fa-times"></i></button>
										</span>
									</div>
								</div>
								<div class="col-lg-2" style="padding-bottom:5px !important;padding-top:5px !important">
									<a onclick="addField(this)" class="btn btn-md blue">
										<i class="fa fa-plus"></i> Add 
									</a>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-lg-1">Subject</label>
							<div class="col-lg-11" style="padding-bottom:5px !important;padding-top:5px !important">
								<input type="text" class="form-control" id="subject">
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-lg-1">Message</label>
							<div class="col-lg-11" style="padding-bottom:5px !important;padding-top:5px !important">
								<textarea class="ckeditor form-control" name="editor1" rows="6"></textarea>
							</div>
						</div>
						<div class="inbox-compose-attachment">
							<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
							<span class="btn green fileinput-button">
							<i class="fa fa-plus"></i>
							<span>
							Add files... </span>
							<input type="file" name="files[]" multiple>
							</span>
							<!-- The table listing the files available for upload/download -->
							<table role="presentation" class="table table-striped margin-top-10">
							<tbody class="files">
							</tbody>
							</table>
						</div>
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
        <td class="name" width="30%"><span>{%=file.name%}</span></td>
        <td class="size" width="40%"><span>{%=o.formatFileSize(file.size)%}</span></td>
        {% if (file.error) { %}
            <td class="error" width="20%" colspan="2"><span class="label label-danger">Error</span> {%=file.error%}</td>
        {% } else if (o.files.valid && !i) { %}
            <td>
                <p class="size">{%=o.formatFileSize(file.size)%}</p>
                <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                   <div class="progress-bar progress-bar-success" style="width:0%;"></div>
                   </div>
            </td>
        {% } else { %}
            <td colspan="2"></td>
        {% } %}
        <td class="cancel" width="10%" align="right">{% if (!i) { %}
            <button class="btn btn-sm red cancel">
                       <i class="fa fa-ban"></i>
                       <span>Cancel</span>
                   </button>
        {% } %}</td>
    </tr>
{% } %}
</script>
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download fade">
        {% if (file.error) { %}
            <td class="name" width="30%"><span>{%=file.name%}</span></td>
            <td class="size" width="40%"><span>{%=o.formatFileSize(file.size)%}</span></td>
            <td class="error" width="30%" colspan="2"><span class="label label-danger">Error</span> {%=file.error%}</td>
        {% } else { %}
            <td class="name" width="30%">
                <a href="{%=file.url%}" title="{%=file.name%}" data-gallery="{%=file.thumbnail_url&&'gallery'%}" download="{%=file.name%}">{%=file.name%}</a>
            </td>
            <td class="size" width="40%"><span>{%=o.formatFileSize(file.size)%}</span></td>
            <td colspan="2"></td>
        {% } %}
        <td class="delete" width="10%" align="right">
            <button class="btn default btn-sm" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}"{% if (file.delete_with_credentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
                <i class="fa fa-times"></i>
            </button>
        </td>
    </tr>
{% } %}
	</script>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>