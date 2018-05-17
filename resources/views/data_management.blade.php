@extends('layouts.main')

@section('content')

@if(session()->has('data') && session('data') > 0)
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-success alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
			<strong>{{session('data')}} Attendance Logs added.</strong>
		</div>
    </div>
</div>
@endif
@if(session()->has('data') && session('data') == 0)
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-danger alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
				<strong>No Attendance Logs has been added. </strong>
		</div>
    </div>
</div>
@endif

@if(session()->has('employee_list') && session('employee_list') > 0)
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-success alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
			<strong>{{session('employee_list')}} Employee/s added.</strong>
		</div>
    </div>
</div>
@endif
@if(session()->has('employee_list') && session('employee_list') == 0)
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-danger alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
				<strong>No Employee has been added. </strong>
		</div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-md-4">
        <div class="portlet box green-seagreen">
        	<div class="portlet-title">
        		<div class="caption">
        			<i class="fa fa-gift"></i>Import Data
        		</div>
        		<div class="tools">
        			<a href="javascript:;" class="collapse" data-original-title="" title="">
        			</a>

        		</div>
        	</div>
        	<div class="portlet-body">
                <a class="btn btn-block btn-md blue" data-toggle="modal" href="#modal-list">Upload Employee List</a>
                <a href="../../csv/sample_employee_list.csv" target="_blank" class="btn btn-block btn-md green">Employee List Template</a>
                <a class="btn btn-block btn-md purple" data-toggle="modal" href="#modal-attendance">Upload Attendance</a>
                <a href="../../biometrics/sample_attendance.txt" target="_blank" class="btn btn-block btn-md green">Attendance Template</a>
        	</div>
        </div>
    </div>
    <div class=" col-md-4">
        <div class="portlet box blue">
        	<div class="portlet-title">
        		<div class="caption">
        			<i class="fa fa-gift"></i>Export Data
        		</div>
        		<div class="tools">
        			<a href="javascript:;" class="collapse" data-original-title="" title="">
        			</a>

        		</div>
        	</div>
        	<div class="portlet-body">
                <a href="../../csv/employee_list.csv" target="_blank" class="btn btn-block btn-md green">Export Employee List</a>
        	</div>
        </div>
    </div>
</div>


<!-- Start of add level Modal-->
<div class="modal fade" id="modal-attendance" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
			    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title">Upload Attendance File</h4>
			</div>
			<div class="modal-body">
				<div class="form-body">
                    <form action="{{ url('data_management/uploadAttendance') }}" method="post" enctype="multipart/form-data">
                        <label>Select (.txt) file to upload :</label>
                        <input type="file" name="file" id="file"><br/>
                        <input type="submit" class="btn btn-sm btn-success" value="Upload" name="submit">
                        {!! csrf_field() !!}
                    </form>
                </div>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- end of add level Modal-->

<!-- Start of add level Modal-->
<div class="modal fade" id="modal-list" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
			    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title">Upload Employee List</h4>
			</div>
			<div class="modal-body">
				<div class="form-body">
                    <form action="{{ url('data_management/uploadEmployeeList') }}" method="post" enctype="multipart/form-data">
                        <label>Select (.csv) file to upload :</label>
                        <input type="file" name="file" id="file"><br/>
                        <input type="submit" class="btn btn-sm btn-success" value="Upload" name="submit">
                        {!! csrf_field() !!}
                    </form>
                </div>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- end of add level Modal-->

@endsection