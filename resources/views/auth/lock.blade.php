<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
<meta charset="utf-8"/>
<title>Lock Screen</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta http-equiv="refresh" content="3600">
<meta http-equiv="Content-type" content="text/html; charset=utf-8">
<meta content="" name="description"/>
<meta content="" name="author"/>
<!-- BEGIN GLOBAL MANDATORY STYLES -->
<link href="//fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
<link href="../../metronic/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
<link href="../../metronic/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
<link href="../../metronic/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
<link href="../../metronic/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
<!-- END GLOBAL MANDATORY STYLES -->
<!-- BEGIN PAGE LEVEL STYLES -->
<link href="../../metronic/admin/pages/css/lock.css" rel="stylesheet" type="text/css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN THEME STYLES -->
<link href="../../metronic/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
<link href="../../metronic/global/css/plugins.css" rel="stylesheet" type="text/css"/>
<link href="../../metronic/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
<link href="../../metronic/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css" id="style_color"/>
<link href="../../metronic/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
<!-- END THEME STYLES -->
<link rel="shortcut icon" href="../../favicon.png"/>
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body>
<div class="page-lock">
	<div class="page-logo">
	<a href="">
    	<img src="../../images/app/hr-logo.png" alt=""/>
	</a>
	</div>
	<div class="page-body">
		<div class="lock-head">
			 Locked
		</div>
		<div class="lock-body">
			<div class="pull-left lock-avatar-block">
				<img src="{{ asset('images/employees/'. $picture) }}" class="lock-avatar">
			</div>
			<form class="lock-form pull-left" action="{{ url('login') }}" method="post">
			    {!! csrf_field() !!}
				<h4>{{$name}}</h4>
				<div class="form-group">
				    <input type="hidden" name="email" value="{{$email}}"/>
					<input class="form-control placeholder-no-fix" type="password" autocomplete="off" placeholder="Password" name="password"/>
				</div>
				<div class="form-actions">
					<button type="submit" class="btn blue uppercase pull-right">Login</button>
				</div>
			</form>
		</div>
		<div class="lock-bottom">
			<span style="color:white">Not</span> <a href="{{url('logout')}}">{{$name}}?</a>
		</div>
	</div>
	<div class="page-footer-custom">
		{{ date('Y') }} @ Laybare EMS.
	</div>
</div>
<!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
<!-- BEGIN CORE PLUGINS -->
<!--[if lt IE 9]>
<script src="../../metronic/global/plugins/respond.min.js"></script>
<script src="../../metronic/global/plugins/excanvas.min.js"></script>
<![endif]-->
<script src="../../metronic/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="../../metronic/global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
<script src="../../metronic/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="../../metronic/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
<script src="../../metronic/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>
<script src="../../metronic/global/plugins/jquery.cokie.min.js" type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="../../metronic/global/plugins/backstretch/jquery.backstretch.min.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="../../metronic/global/scripts/metronic.js" type="text/javascript"></script>
<script src="../../metronic/admin/layout/scripts/demo.js" type="text/javascript"></script>
<script src="../../metronic/admin/layout/scripts/layout.js" type="text/javascript"></script>
<script>
jQuery(document).ready(function() {    
Metronic.init(); // init metronic core components
Layout.init(); // init current layout
Demo.init();
});


$("button[type!='button']").click(function(){
$(this).hide();
$(this).delay(2000).fadeIn();
});
     


</script>
<!-- END JAVASCRIPTS -->
</body>
<!-- END BODY -->
</html>