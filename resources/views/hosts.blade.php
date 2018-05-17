@extends('layouts.main')
@section('content')

@if(session()->has('add') && session('add') == 'success')
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-success alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
			<strong>Host successfully added.</strong>
		</div>
    </div>
</div>
@elseif(!empty($errors->add->all()))
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-danger alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
				<strong>Failed to add Host. </strong>
				<br/>
				@foreach($errors->add->all() as $key=>$value)
					{{ $value }} <br/>
				@endforeach
		</div>
    </div>
</div>
@endif

@if(session()->has('edit') && session('edit') == 'success')
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-success alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
			<strong>Host successfully updated.</strong>
		</div>
    </div>
</div>
@elseif(!empty($errors->edit->all()))
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-danger alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
				<strong>Failed to update Host. </strong>
				<br/>
				@foreach($errors->edit->all() as $key=>$value)
					{{ $value }} <br/>
				@endforeach
		</div>
    </div>
</div>
@endif

@if(session()->has('delete') && session('delete') == 'success')
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-success alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
			<strong>Host successfully deleted.</strong>
		</div>
    </div>
</div>
@endif



<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue">
        	<div class="portlet-title">
        		<div class="caption">
        			<i class="fa fa-gift"></i> Hosts
        			<a data-toggle="modal" href="#modal_add" type="button" class="btn purple btn-sm">Add Host</a>
        		</div>
        		<div class="tools">
        			<a href="javascript:;" class="collapse" data-original-title="" title="">
        			</a>

        		</div>
        	</div>
        	<div class="portlet-body">
 				<table  id="sample_5" class="table dataTable table-striped table-bordered table-hover">
        	        <thead>
        			<tr>
        			    <th>ID</th>
        				<th>IP Address</th>
        				<th>Company Name</th>
        				<th>Address</th>
        				<th>Contact</th>
        				<th></th>
                    </tr>			
        			</thead>
        			<tbody> 
  					  @foreach($hosts as $key=>$value)
        		      <tr>
        		          <td>{{ $value['id'] }}</td>
        		          <td>{{ $value['ip_address'] }}</td>
        		          <td>{{ $value['company_name'] }}</td>
        		          <td>{{ $value['company_address'] }}</td>
        		          <td>{{ $value['company_contact'] }}</td>
        		          <td>
                            <a data-toggle="modal" href="#modal_edit{{$value['id']}}" type="button" class="btn blue btn-xs">Edit</a>
        	                <!-- Start of edit leave_type Modal-->
        	                <div class="modal fade" id="modal_edit{{$value['id']}}" tabindex="-1" role="dialog" 
        	                    aria-labelledby="myModalLabel" aria-hidden="true">
        	                	<div class="modal-dialog">
        	                		<div class="modal-content">
        	                			<form action="{{url('hosts/processEdit')}}" method="post">
        	                			<input type="hidden" value="{{$value['id']}}" name="id">
        	                			{!! csrf_field() !!}
        	                			<div class="modal-header">
        	                				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        	                				<h4 class="modal-title">Edit Host</h4>
        	                			</div>
        	                			<div class="modal-body">
        	                				<div class="form-body">
												<div class="row">
							                		<div class="col-sm-6">
							                			<div class="form-group">
							                				<label class="control-label bold">IP Address</label>
							                				<input required type="text" name="ip_address" 
							                				    value="{{ $value['ip_address'] }}" class="form-control">
							                			</div>
							                		</div><!--/span-->
							                		<div class="col-sm-6">
							                			<div class="form-group">
							                				<label class="control-label bold">Company Name</label>
							                				<input type="text" name="company_name" 
							                				    value="{{ $value['company_name'] }}" class="form-control">
							                			</div>
							                		</div><!--/span-->
							                	</div><!--/row-->
												<div class="row">
							                		<div class="col-sm-6">
							                			<div class="form-group">
							                				<label class="control-label bold">Company Address</label>
							                				<input type="text" name="company_address" 
							                				    value="{{ $value['company_address'] }}" class="form-control">
							                			</div>
							                		</div><!--/span-->
							                		<div class="col-sm-6">
							                			<div class="form-group">
							                				<label class="control-label bold">Company Contact</label>
							                				<input type="text" name="company_contact" 
							                				    value="{{ $value['company_contact'] }}" class="form-control">
							                			</div>
							                		</div><!--/span-->
							                	</div><!--/row-->
        	                                </div>
        	                			</div>
        	                			<div class="modal-footer">
        	                				<button type="submit" class="btn blue">Save</button>
        	                				<button type="button" class="btn default" data-dismiss="modal">Close</button>
        	                			</div>
        	                			</form>
        	                		</div><!-- /.modal-content -->
        	                	</div><!-- /.modal-dialog -->
        	                </div><!-- end of edit hosts Modal-->
        	                
        	                <a data-toggle="modal" href="#modal_delete{{$value['id']}}" type="button" class="btn red btn-xs">Delete</a>
        	                <!-- Start of delete hosts Modal-->
        	                <div class="modal fade" id="modal_delete{{$value['id']}}" tabindex="-1" role="dialog" 
        	                    aria-labelledby="myModalLabel" aria-hidden="true">
        	                	<div class="modal-dialog">
        	                		<div class="modal-content">
        	                			<form action="{{url('hosts/processDelete')}}" method="post">
        	                			<input type="hidden" value="{{$value['id']}}" name="id">
        	                			{!! csrf_field() !!}
        	                			<div class="modal-header">
        	                				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        	                				<h4 class="modal-title">Delete Host</h4>
        	                			</div>
        	                			<div class="modal-body">
        	                				Are you sure you want to delete this host?
        	                			</div>
        	                			<div class="modal-footer">
        	                				<button type="submit" class="btn red">Delete</button>
        	                				<button type="button" class="btn default" data-dismiss="modal">Close</button>
        	                			</div>
        	                			</form>
        	                		</div><!-- /.modal-content -->
        	                	</div><!-- /.modal-dialog -->
        	                </div><!-- end of delete hosts Modal-->       
        		          </td>
        		      </tr>
        		      @endforeach
        			</tbody>
        	    </table>
            </div>
        </div>
    </div>  
</div>


<!-- Start of add hosts Modal-->
<div class="modal fade" id="modal_add" tabindex="-1" role="dialog" 
    aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="{{url('hosts/processAdd')}}" method="post">
			{!! csrf_field() !!}
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title">Add Host</h4>
			</div>
			<div class="modal-body">
				<div class="form-body">
					<div class="row">
                		<div class="col-sm-6">
                			<div class="form-group">
                				<label class="control-label bold">IP Address</label>
                				<input required type="text" name="ip_address" 
                				    value="" class="form-control">
                			</div>
                		</div><!--/span-->
                		<div class="col-sm-6">
                			<div class="form-group">
                				<label class="control-label bold">Company Name</label>
                				<input type="text" name="company_name" 
                				    value="" class="form-control">
                			</div>
                		</div><!--/span-->
                	</div><!--/row-->
					<div class="row">
                		<div class="col-sm-6">
                			<div class="form-group">
                				<label class="control-label bold">Company Address</label>
                				<input type="text" name="company_address" 
                				    value="" class="form-control">
                			</div>
                		</div><!--/span-->
                		<div class="col-sm-6">
                			<div class="form-group">
                				<label class="control-label bold">Company Contact</label>
                				<input type="text" name="company_contact" 
                				    value="" class="form-control">
                			</div>
                		</div><!--/span-->
                	</div><!--/row-->
                </div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn blue">Save</button>
				<button type="button" class="btn default" data-dismiss="modal">Close</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- end of add hosts Modal-->



@endsection