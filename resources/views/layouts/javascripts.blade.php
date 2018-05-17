
    <!-- BEGIN CORE PLUGINS -->
    <!--[if lt IE 9]>
    <script src="../../metronic/global/plugins/respond.min.js"></script>
    <script src="../../metronic/global/plugins/excanvas.min.js"></script>
    <![endif]-->
    <script src="../../metronic/global/plugins/jquery.min.js" type="text/javascript"></script>
    <script src="../../metronic/global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
    <!-- IMPORTANT! Load jquery-ui.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip -->
    <script src="../../metronic/global/plugins/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
    <script src="../../metronic/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="../../metronic/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js" type="text/javascript"></script>
    <script src="../../metronic/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
    <script src="../../metronic/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
    <script src="../../metronic/global/plugins/jquery.cokie.min.js" type="text/javascript"></script>
    <script src="../../metronic/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>
    <script src="../../metronic/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" type="text/javascript"></script>
    <!-- END CORE PLUGINS -->
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <script src="../../metronic/global/plugins/flot/jquery.flot.min.js" type="text/javascript"></script>
    <script src="../../metronic/global/plugins/flot/jquery.flot.resize.min.js" type="text/javascript"></script>
    <script src="../../metronic/global/plugins/flot/jquery.flot.categories.min.js" type="text/javascript"></script>
    <script src="../../metronic/global/plugins/jquery.pulsate.min.js" type="text/javascript"></script>
    <script src="../../metronic/global/plugins/bootstrap-daterangepicker/moment.min.js" type="text/javascript"></script>
    <script src="../../metronic/global/plugins/bootstrap-daterangepicker/daterangepicker.js" type="text/javascript"></script>
    <!-- IMPORTANT! fullcalendar depends on jquery-ui.min.js for drag & drop support -->
    <script src="../../metronic/global/plugins/fullcalendar/fullcalendar.min.js" type="text/javascript"></script>
    <script src="../../metronic/global/plugins/jquery-easypiechart/jquery.easypiechart.min.js" type="text/javascript"></script>
    <!-- END PAGE LEVEL PLUGINS -->

    <!-- BEGIN PAGE LEVEL SCRIPTS -->
    <script src="../../metronic/global/scripts/metronic.js" type="text/javascript"></script>
    <script src="../../metronic/admin/layout/scripts/layout.js" type="text/javascript"></script>
    <script src="../../metronic/admin/layout/scripts/quick-sidebar.js" type="text/javascript"></script>
    <script src="../../metronic/admin/layout/scripts/demo.js" type="text/javascript"></script>
    <script src="../../metronic/admin/pages/scripts/index.js" type="text/javascript"></script>
    <script src="../../metronic/admin/pages/scripts/tasks.js" type="text/javascript"></script>
    <script type="text/javascript" src="../../metronic/global/plugins/select2/select2.min.js"></script>
    <script src="../../metronic/admin/pages/scripts/form-samples.js"></script>
    <script type="text/javascript" src="../../metronic/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
    <script src="../../metronic/admin/pages/scripts/components-pickers.js"></script>
    <script type="text/javascript" src="../../metronic/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="../../metronic/global/plugins/datatables/extensions/TableTools/js/dataTables.tableTools.min.js"></script>
    <script type="text/javascript" src="../../metronic/global/plugins/datatables/extensions/ColReorder/js/dataTables.colReorder.min.js"></script>
    <script type="text/javascript" src="../../metronic/global/plugins/datatables/extensions/Scroller/js/dataTables.scroller.min.js"></script>
    <script type="text/javascript" src="../../metronic/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
    <script src="../../metronic/admin/pages/scripts/table-advanced.js"></script>
    <script src="../../metronic/admin/pages/scripts/components-dropdowns.js"></script>
    <script type="text/javascript" src="../../metronic/global/plugins/jquery-multi-select/js/jquery.multi-select.js"></script>
    <script type="text/javascript" src="../../metronic/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
    <script type="text/javascript" src="../../metronic/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
    <script src="../../metronic/admin/pages/scripts/components-editors.js"></script>
    <script type="text/javascript" src="../../metronic/global/plugins/bootstrap-wysihtml5/wysihtml5-0.3.0.js"></script>
    <script type="text/javascript" src="../../metronic/global/plugins/bootstrap-wysihtml5/bootstrap-wysihtml5.js"></script>
    <script src="../../metronic/global/plugins/bootstrap-markdown/lib/markdown.js" type="text/javascript"></script>
    <script src="../../metronic/global/plugins/bootstrap-markdown/js/bootstrap-markdown.js" type="text/javascript"></script>
    <script src="../../metronic/global/plugins/bootstrap-summernote/summernote.min.js" type="text/javascript"></script>
    <script src="../../metronic/admin/pages/scripts/timeline.js" type="text/javascript"></script>
    <script src="../../metronic/admin/pages/scripts/todo.js" type="text/javascript"></script>
    <script src="../../metronic/admin/pages/scripts/ui-general.js" type="text/javascript"></script>
    <script src="../../metronic/global/plugins/jquery-bootpag/jquery.bootpag.min.js" type="text/javascript"></script>
    <script src="../../metronic/global/plugins/jquery.pulsate.min.js" type="text/javascript"></script>
    <script src="../../metronic/global/plugins/bootstrap-toastr/toastr.min.js"></script>

    <script src="../../assets/scripts/geolocation.js"></script>
    <script src="../../assets/vuejs/vue.js"></script>
    <script src="../../assets/vuejs/libraries/Pagination.js?cache={{ rand() }}"></script>
    <!-- END PAGE LEVEL SCRIPTS -->

<script>
    jQuery(document).ready(function() {
        Metronic.init(); // init metronic core componets
        Layout.init(); // init layout
        QuickSidebar.init(); // init quick sidebar
        Demo.init(); // init demo features
        Index.init();
        Index.initCalendar(); // init index page's custom scripts
        Index.initCharts(); // init index page's custom scripts
        Index.initChat();
        Index.initMiniCharts();
        Tasks.initDashboardWidget();
        FormSamples.init();
        ComponentsPickers.init();
        UIGeneral.init();
        TableAdvanced.init();
        ComponentsDropdowns.init();
        ComponentsEditors.init();
        startTime();
    });

    function seenLogs(){
        
    }
</script>


<input type="hidden" id="my_id" value="{{ $config['my_id'] }}">
<input type="hidden" id="my_name" value="{{ Auth::user()->name }}">
<input type="hidden" id="ip_address" value="{{  $_SERVER['REMOTE_ADDR'] }}">
<input type="hidden" id="token" value="{{ csrf_token() }}"/>
<input type="hidden" id="the_url" value="{{url('')}}">
<input type="hidden" id="the_icon" value="{{url('images/app/'.$config['logo'])}}">
<input type="hidden" id="allow_add_employee" value="{{ $config['allow_add_employee'] }}">
    <script src="../../assets/vuejs/instances/header.js?cache={{ rand() }}"></script>
