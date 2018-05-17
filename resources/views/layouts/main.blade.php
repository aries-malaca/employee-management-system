<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
<!--<![endif]-->

<!-- BEGIN HEAD -->
@include('layouts.head')
<!-- END HEAD -->

<!-- BEGIN BODY -->
<body class="page-header-fixed page-sidebar-fixed page-quick-sidebar-over-content page-style-square">

<!-- BEGIN HEADER -->
@include('layouts.header')
<!-- END HEADER -->

<!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
@include('layouts.javascripts')
        <!-- END JAVASCRIPTS -->


<div class="clearfix">
</div>

<!-- BEGIN CONTAINER -->
<div class="page-container">
	
	<!-- BEGIN SIDEBAR -->
	@include('layouts.sidebar')
	<!-- END SIDEBAR -->
	
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
		
			<!-- BEGIN BREADCRUMB-->
			@include('layouts.breadcrumb')
			<!-- END BREADCRUMB-->

             @yield('content')

		</div>
	</div>
	<!-- END CONTENT -->
	
	<!-- BEGIN QUICK SIDEBAR -->
	@include('layouts.chat')
	<!-- END QUICK SIDEBAR -->
</div>
<!-- END CONTAINER -->

<!-- BEGIN FOOTER -->
@include('layouts.footer')
<!-- END FOOTER -->


</body>
<!-- END BODY -->
</html>