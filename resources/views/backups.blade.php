@extends('layouts.main')

@section('content')

@if(session()->has('backup') && session('backup') == 'success')
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-success alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
			<strong>Backup successfully added.</strong>
		</div>
    </div>
</div>
@endif

<div class="portlet box green">
	<div class="portlet-title">
		<div class="caption">
			<i class="fa fa-gift"></i>System Backup Files
			<a onclick="return(confirm('Backup process may took 40 seconds . Do you want to proceed?'))" href="{{url('backups/backupNow')}}" type="button" class="btn blue btn-sm">Backup Now</a>
		</div>
		<div class="tools">
			<a href="javascript:;" class="collapse" data-original-title="" title="">
			</a>
		</div>
	</div>
	<div class="portlet-body">
		<table id="sample_51" class="table dataTable table-striped table-bordered table-hover">
	        <thead>
			<tr>
			    <th>Description</th>
				<th>Backup Date</th>
				<th>Size</th>
				<th></th>
	        </tr>			
			</thead>
			<tbody> 
            @foreach($backups as $key=> $value)
            <tr>
                <td>{{ $value['name'] }}</td>
                <td>{{ dateNormal( date('Y-m-d',$value['date'])) }}</td>
                <td>{{ number_format($value['size'],2) ." MB" }}</td>
                <td>
                    <a target="_blank" href="{{url('temp/'.$value['name'])}}" type="button" class="btn blue btn-xs">Download</a>
                    <a data-toggle="modal" href="#modal_delete{{$key}}" type="button" class="btn red btn-xs">Delete</a>
	                <!-- Start of delete bank Modal-->
	                <div class="modal fade" id="modal_delete{{$key}}" tabindex="-1" role="dialog" 
	                    aria-labelledby="myModalLabel" aria-hidden="true">
	                	<div class="modal-dialog">
	                		<div class="modal-content">
	                			<div class="modal-header">
	                				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
	                				<h4 class="modal-title">Delete Backup</h4>
	                			</div>
	                			<div class="modal-body">
	                				Are you sure you want to delete this backup?
	                			</div>
	                			<div class="modal-footer">
	                				<a href="{{url('backups/deleteBackup/'. $value['name'])}}" class="btn red">Delete</a>
	                				<button type="button" class="btn default" data-dismiss="modal">Close</button>
	                			</div>
	                		</div><!-- /.modal-content -->
	                	</div><!-- /.modal-dialog -->
	                </div><!-- end of delete bank Modal-->
                </td>
            </tr>
            @endforeach
			</tbody>
		</table>
	</div>
</div>
@endsection