@extends('layouts.main')

@section('content')

<!-- BEGIN PAGE LEVEL STYLES -->
<link href="../../metronic/global/plugins/bootstrap-wysihtml5/bootstrap-wysihtml5.css" rel="stylesheet" type="text/css"/>
<link href="../../metronic/global/plugins/fancybox/source/jquery.fancybox.css" rel="stylesheet"/>
<!-- BEGIN:File Upload Plugin CSS files-->
<link href="../../metronic/global/plugins/jquery-file-upload/blueimp-gallery/blueimp-gallery.min.css" rel="stylesheet"/>
<link href="../../metronic/global/plugins/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet"/>
<link href="../../metronic/global/plugins/jquery-file-upload/css/jquery.fileupload-ui.css" rel="stylesheet"/>
<!-- END:File Upload Plugin CSS files-->
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE LEVEL STYLES -->
<link href="../../metronic/admin/pages/css/inbox.css" rel="stylesheet" type="text/css"/>
<!-- END PAGE LEVEL STYLES -->

<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="../../metronic/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css"/>
<link rel="stylesheet" type="text/css" href="../../metronic/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css"/>
<link rel="stylesheet" type="text/css" href="../../metronic/global/plugins/jquery-tags-input/jquery.tagsinput.css"/>
<link rel="stylesheet" type="text/css" href="../../metronic/global/plugins/bootstrap-markdown/css/bootstrap-markdown.min.css">
<link rel="stylesheet" type="text/css" href="../../metronic/global/plugins/typeahead/typeahead.css">
<!-- END PAGE LEVEL STYLES -->

<input type="hidden" id="current_page" value="1" />
<div class="row inbox">
	<div class="col-md-2">
		<ul class="inbox-nav margin-bottom-10">
			<li class="compose-btn" data-action="COMPOSE">
				<a href="javascript:;" class="btn green">
				<i class="fa fa-edit"></i> Compose </a>
			</li>
			<li class="inbox active" data-action="Inbox">
				<a href="javascript:;" class="btn">
				Inbox</a>
				<b></b>
			</li>
			<li class="sent" data-action="Sent">
				<a class="btn" id="sent-tab">
				Sent </a>
				<b></b>
			</li>
			<li class="draft" data-action="Draft">
				<a class="btn" href="javascript:;">
				Draft </a>
				<b></b>
			</li>
			<li class="trash" data-action="Trash">
				<a class="btn" href="javascript:;">
				Trash </a>
				<b></b>
			</li>
		</ul>
		<a href="#" class="btn btn-block purple">
				<i class="icon-users"></i> Contacts </a>
		<a href="{{url('mail/setupMail')}}" class="btn btn-block yellow">
				<i class="icon-settings"></i> Setup </a>
	</div>
	<div class="col-md-10">
		<br/>
		<div id="parent"></div>
		@include('email_client.composer')
		<div id="loader">
			@include('email_client.spinner')
		</div>
		<div class="inbox-content" id="inbox-content">
		</div>
	</div>
</div>

<!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
<script src="../../metronic/global/plugins/jquery-file-upload/js/vendor/jquery.ui.widget.js"></script>
<!-- The Templates plugin is included to render the upload/download listings -->
<script src="../../metronic/global/plugins/jquery-file-upload/js/vendor/tmpl.min.js"></script>
<!-- The Load Image plugin is included for the preview images and image resizing functionality -->
<script src="../../metronic/global/plugins/jquery-file-upload/js/vendor/load-image.min.js"></script>
<!-- The Canvas to Blob plugin is included for image resizing functionality -->
<script src="../../metronic/global/plugins/jquery-file-upload/js/vendor/canvas-to-blob.min.js"></script>
<!-- blueimp Gallery script -->
<script src="../../metronic/global/plugins/jquery-file-upload/blueimp-gallery/jquery.blueimp-gallery.min.js"></script>
<!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
<script src="../../metronic/global/plugins/jquery-file-upload/js/jquery.iframe-transport.js"></script>
<!-- The basic File Upload plugin -->
<script src="../../metronic/global/plugins/jquery-file-upload/js/jquery.fileupload.js"></script>
<!-- The File Upload processing plugin -->
<script src="../../metronic/global/plugins/jquery-file-upload/js/jquery.fileupload-process.js"></script>
<!-- The File Upload image preview & resize plugin -->
<script src="../../metronic/global/plugins/jquery-file-upload/js/jquery.fileupload-image.js"></script>
<!-- The File Upload audio preview plugin -->
<script src="../../metronic/global/plugins/jquery-file-upload/js/jquery.fileupload-audio.js"></script>
<!-- The File Upload video preview plugin -->
<script src="../../metronic/global/plugins/jquery-file-upload/js/jquery.fileupload-video.js"></script>
<!-- The File Upload validation plugin -->
<script src="../../metronic/global/plugins/jquery-file-upload/js/jquery.fileupload-validate.js"></script>
<!-- The File Upload user interface plugin -->
<script src="../../metronic/global/plugins/jquery-file-upload/js/jquery.fileupload-ui.js"></script>
<!-- The main application script -->
<!-- The XDomainRequest Transport is included for cross-domain file deletion for IE 8 and IE 9 -->
<!--[if (gte IE 8)&(lt IE 10)]>
    <script src="../../metronic/global/plugins/jquery-file-upload/js/cors/jquery.xdr-transport.js"></script>
    <![endif]-->
<!-- END:File Upload Plugin JS files-->
<!-- END: Page level plugins -->
<script src="../../../assets/scripts/email-client.js"></script>
<script src="../../metronic/global/plugins/bootbox/bootbox.min.js" type="text/javascript"></script>
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="../../metronic/global/plugins/ckeditor/ckeditor.js"></script>

<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="../../metronic/admin/pages/scripts/components-form-tools.js"></script>
<!-- END PAGE LEVEL SCRIPTS -->



<script type="text/javascript">
	var UIAlertDialogApi = function () {
	    return {
	        //main function to initiate the module
	        showAlert: function (message, mode) {
	            Metronic.alert({
	                container: $('#parent'), // alerts parent container(by default placed after the page breadcrumbs)
	                place: 'prepend', // append or prepent in container 
	                type: mode,  // alert's type
	                message: message,  // alert's message
	                close: true, // make alert closable
	                reset: true, // close all previouse alerts first
	                focus: false, // auto scroll to the alert after shown
	                closeInSeconds: 5, // auto close after defined seconds
	                icon: 'check' // put icon before the message
	            });
	        }
	    };

	}();


var Inbox = function () {
    return {
        //main function to initiate the module
        init: function () {

			$('#fileupload').fileupload({
	            // Uncomment the following to send cross-domain cookies:
	            //xhrFields: {withCredentials: true},
	            url: '../../public/jquery-uploader/php/',
	            autoUpload: true
	        });

	        // Upload server status check for browsers with CORS support:
	        if ($.support.cors) {
	            $.ajax({
	                url: '../../public/jquery-uploader/php/',
	                type: 'HEAD'
	            }).fail(function () {
	                $('<span class="alert alert-error"/>')
	                    .text('Upload server currently unavailable - ' +
	                    new Date())
	                    .appendTo('#fileupload');
	            });
	        }
        }

    };

}();

   Inbox.init();

</script>
@endsection