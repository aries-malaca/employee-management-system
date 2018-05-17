<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
<meta charset="utf-8"/>
<title>Login Page</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="refresh" content="3600">
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
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
<link href="../../metronic/admin/pages/css/login.css" rel="stylesheet" type="text/css"/>
<!-- END PAGE LEVEL SCRIPTS -->
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
<body class="login">
<!-- BEGIN SIDEBAR TOGGLER BUTTON -->
<div class="menu-toggler sidebar-toggler">
</div>
<!-- END SIDEBAR TOGGLER BUTTON -->
<!-- BEGIN LOGO -->
<div class="logo">
	<a href="">
	<img src="../../images/app/hr-logo.png" alt=""/>
	</a>
</div>
<!-- END LOGO -->
<!-- BEGIN LOGIN -->
<div class="content" style="margin-top:0px !important;">
	<!-- BEGIN LOGIN FORM -->
	<form class="login-form" @if(session()->has('success')) style="display:none" @else  @endif action="{{ url('auth/login') }}" method="post">
	      {!! csrf_field() !!}
		<h3 class="form-title">Sign In</h3>

		<div class="alert alert-danger display-hide">
			<button class="close" data-close="alert"></button>
			<span>
			Enter any username and password. </span>
		</div>
		@if(session()->has('referrer') )
			<input type="hidden" name="referrer" value="{{ltrim(session('referrer'),'/')}}"/>
		@else
			<input type="hidden" name="referrer" value=""/>
		@endif

        @if(session()->has('suspended') )
		<div class="alert alert-danger">
			<button class="close" data-close="alert"></button>
			Suspended or inactive account
		</div>
        @endif

        @if(session()->has('failed') )
            @if(session('failed')>2)
            <div class="alert alert-danger">
                <button class="close" data-close="alert"></button>
                You've failed to login with {{session('failed')}} attempts, please try to reset your password.
            </div>
            @else
                <div class="alert alert-danger">
                    <button class="close" data-close="alert"></button>
                    Failed to login, Wrong Username/password.
                </div>
            @endif
        @endif

		<div class="form-group">
			<!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
			<label class="control-label visible-ie8 visible-ie9">Username</label>
			
            <input class="form-control form-control-solid placeholder-no-fix" 
                placeholder="Email or Employee ID" name="email" value="{{ old('email') }}">
            @if(!session()->has('failed'))
                <input name="failed" type="hidden" value="0"/>
            @else
                <input name="failed" type="hidden" value="{{session('failed')}}"/>
            @endif

		</div>
		<div class="form-group">
			<label class="control-label visible-ie8 visible-ie9">Password</label>
			<input type="password" class="form-control form-control-solid placeholder-no-fix" 
			    autocomplete="off" placeholder="Password" name="password">
		</div>
		<div class="form-actions">
			<button type="submit" class="btn btn-success uppercase">Login</button>
		</div>
		<a id="forgot" href="#" class="pull-right">Forgot password?</a>
	</form>
	<!-- END LOGIN FORM -->

    <!-- BEGIN LOGIN FORM -->
    <form action="{{ url('auth/forgot') }}" @if(session()->has('success')) @else style="display:none" @endif class="forgot-form" method="post">
        {!! csrf_field() !!}
        <h3 class="form-title">Forgot Password</h3>
        <div class="form-group">
            @if(session()->has('success') && session('success') == 'success')
                <div class="alert alert-success">
                    <button class="close" data-close="alert"></button>
                    Request successfully sent, Please Check your email.
                </div>
            @endif

            @if(session()->has('success') && session('success') != 'success')
                <div class="alert alert-danger">
                    <button class="close" data-close="alert"></button>
                   Unable to send your request, {{ session('success') }}
                </div>
            @endif
            <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
            <label class="control-label visible-ie8 visible-ie9">Username</label>
            <input class="form-control form-control-solid placeholder-no-fix" type="email" required
                   placeholder="Email Address" name="email"/>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-success uppercase">Submit</button>
        </div>
        <a href="#" class="pull-right" id="login">Back to User Login</a>
    </form>
    <!-- END LOGIN FORM -->
</div>
<div class="copyright">
	 {{ date('Y') }} @ Laybare EMS.
</div>
<!-- END LOGIN -->
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
<script src="../../metronic/global/plugins/jquery.cokie.min.js" type="text/javascript"></script>
<script src="../../metronic/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="../../metronic/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="../../metronic/global/scripts/metronic.js" type="text/javascript"></script>
<script src="../../metronic/admin/layout/scripts/layout.js" type="text/javascript"></script>
<script src="../../metronic/admin/layout/scripts/demo.js" type="text/javascript"></script>
<script src="../../metronic/admin/pages/scripts/login.js" type="text/javascript"></script>
<!-- END PAGE LEVEL SCRIPTS -->
<script>
    jQuery(document).ready(function() {
    Metronic.init(); // init metronic core components
    Layout.init(); // init current layout
    Login.init();
    Demo.init();
    });

    $("button[type!='button']").click(function(){
        $(this).hide();
        $(this).delay(2000).fadeIn();
    });

    $("#forgot").click(function(){
        $(".forgot-form").show();
        $(".login-form").hide();
    });

    $("#login").click(function(){
        $(".forgot-form").hide();
        $(".login-form").show();
    });
</script>
<!-- END JAVASCRIPTS -->
</body>
<!-- END BODY -->
</html>