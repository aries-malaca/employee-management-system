<head>
    <meta charset="utf-8"/>
    <title>{{ $config['app_name']  }} | {{ $page['title'] or "Home" }}</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1,maximum-scale=1, user-scalable=no" name="viewport"/>
    <meta content="" name="description"/>
    <meta content="" name="author"/>
    <!-- BEGIN GLOBAL MANDATORY STYLES -->

    @push('styles')
        <link href="//fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
        <link href="../../metronic/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
        <link href="../../metronic/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
        <link href="../../metronic/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <link href="../../metronic/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
        <link href="../../metronic/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css"/>
        <!-- END GLOBAL MANDATORY STYLES -->
        <!-- BEGIN PAGE LEVEL PLUGIN STYLES -->
        <link href="../../metronic/global/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css"/>
        <link href="../../metronic/global/plugins/fullcalendar/fullcalendar.min.css" rel="stylesheet" type="text/css"/>
        <link href="../../metronic/global/plugins/jqvmap/jqvmap/jqvmap.css" rel="stylesheet" type="text/css"/>
        <!-- END PAGE LEVEL PLUGIN STYLES -->
        <!-- BEGIN PAGE STYLES -->
        <link href="../../metronic/admin/pages/css/tasks.css" rel="stylesheet" type="text/css"/>
        <!-- END PAGE STYLES -->
        <!-- BEGIN THEME STYLES -->
        <!-- DOC: To use 'rounded corners' style just load 'components-rounded.css' stylesheet instead of 'components.css' in the below style tag -->
        <link href="../../metronic/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
        <link href="../../metronic/global/css/plugins.css" rel="stylesheet" type="text/css"/>
        <link href="../../metronic/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
        <link href="../../metronic/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css" id="style_color"/>
        <link href="../../metronic/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>

        <link rel="stylesheet" type="text/css" href="../../metronic/global/plugins/datatables/extensions/Scroller/css/dataTables.scroller.min.css"/>
        <link rel="stylesheet" type="text/css" href="../../metronic/global/plugins/datatables/extensions/ColReorder/css/dataTables.colReorder.min.css"/>
        <link rel="stylesheet" type="text/css" href="../../metronic/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>

        <link rel="stylesheet" type="text/css" href="../../metronic/global/plugins/bootstrap-datepicker/css/datepicker3.css"/>
        <link rel="stylesheet" type="text/css" href="../../metronic/global/plugins/select2/select2.css"/>

        <link rel="stylesheet" type="text/css" href="../../metronic/global/plugins/bootstrap-select/bootstrap-select.min.css"/>
        <link rel="stylesheet" type="text/css" href="../../metronic/global/plugins/jquery-multi-select/css/multi-select.css"/>

        <link rel="stylesheet" type="text/css" href="../../metronic/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
        <link rel="stylesheet" type="text/css" href="../../metronic/global/plugins/bootstrap-wysihtml5/bootstrap-wysihtml5.css"/>
        <link rel="stylesheet" type="text/css" href="../../metronic/global/plugins/bootstrap-markdown/css/bootstrap-markdown.min.css">
        <link href="../../metronic/admin/pages/css/timeline.css" rel="stylesheet" type="text/css"/>
        <link href="../../metronic/admin/pages/css/todo.css" rel="stylesheet" type="text/css"/>
        <link rel="stylesheet" type="text/css" href="../../metronic/global/plugins/bootstrap-toastr/toastr.min.css"/>
    @endpush

    @stack('styles')

    <style>
        .scrollable{
            overflow-x:scroll;
        }

    </style>

    <!-- END THEME STYLES -->
    <link rel="shortcut icon" href="../../favicon.png"/>
</head>