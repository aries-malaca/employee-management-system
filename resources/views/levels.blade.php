@extends('layouts.main')

@section('content')



@if(session()->has('adding') && session('adding') == 'success')
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-success alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
			<strong>User Level successfully added.</strong>
		</div>
    </div>
</div>
@elseif(!empty($errors->adding_level->all()))
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-danger alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
				<strong>Failed to add User Level. </strong>
				<br/>
				@foreach($errors->adding_level->all() as $key=>$value)
					{{ $value }} <br/>
				@endforeach
		</div>
    </div>
</div>
@endif

@if(session()->has('editing') && session('editing') == 'success')
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-success alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
			<strong>User Level successfully updated.</strong>
		</div>
    </div>
</div>
@elseif(!empty($errors->editing_level->all()))
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-danger alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
				<strong>Failed to update User Level. </strong>
				<br/>
				@foreach($errors->editing_level->all() as $key=>$value)
					{{ $value }} <br/>
				@endforeach
		</div>
    </div>
</div>
@endif

@if(session()->has('deleting') && session('deleting') == 'success')
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-success alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
			<strong>User Level successfully deleted.</strong>
		</div>
    </div>
</div>
@endif


<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue">
        	<div class="portlet-title">
        		<div class="caption">
        			<i class="fa fa-gift"></i> User Levels
        			<a data-toggle="modal" href="#portlet-config" type="button" class="btn purple btn-sm">Add User Level</a>
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
        				<th>Level Name</th>
        				<th>Level Description</th>
        				<th></th>
                    </tr>			
        			</thead>
        			<tbody> 
  					  @foreach($levels as $key=>$value)
        		      <tr>
        		          <td>{{ $value['id'] }}</td>
        		          <td>{{ $value['level_name'] }}</td>
        		          <td>{{ $value['level_role'] }}</td>
        		          <td>
                            <a data-toggle="modal" href="#modal_edit{{$value['id']}}" type="button" class="btn blue btn-xs">Edit</a>
        	                <!-- Start of edit bank Modal-->
        	                <div class="modal fade" id="modal_edit{{$value['id']}}" tabindex="-1" role="dialog" 
        	                    aria-labelledby="myModalLabel" aria-hidden="true">
        	                	<div class="modal-dialog">
        	                		<div class="modal-content">
        	                			<form action="{{url('levels/processEdit')}}" method="post">
        	                			<input type="hidden" value="{{$value['id']}}" name="id">
        	                			{!! csrf_field() !!}
        	                			<div class="modal-header">
        	                				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        	                				<h4 class="modal-title">Edit User Level</h4>
        	                			</div>
        	                			<div class="modal-body">
        	                				<div class="form-body">
        	                                	<div class="row">
        	                                		<div class="col-sm-4">
        	                                			<div class="form-group">
        	                                				<label class="control-label bold">Level Name</label>
        	                                				<input type="text" name="level_name" 
        	                                				    value="{{$value['level_name']}}" class="form-control">
        	                                			</div>
        	                                		</div><!--/span-->
        	                                		<div class="col-sm-8">
        	                                			<div class="form-group">
        	                                				<label class="control-label bold">Description</label>
        	                                				<input type="text" name="level_role" 
        	                                				       value="{{$value['level_role']}}" class="form-control">
        	                                			</div>
        	                                		</div><!--/span-->
        	                                	</div><!--/row-->
                                                <div class="row">

                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label class="control-label bold">Employee (Checked) / Supervisor(Unchecked) </label>
                                                            <div class="checkbox-list">
                                                                @foreach($levels as $level)
                                                                    <label>
                                                                        <input name="employees[]" type="checkbox" value="{{$level['id']}}"
                                                                            {{( in_array($level['id'] , json_decode($value['levels_as_employees']) )?'checked':'')}} /> {{$level['level_name']}}
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label class="control-label bold">Approval Levels </label>
                                                            <div class="checkbox-list">
                                                                @foreach($levels as $level)
                                                                    <label>
                                                                        <input name="approves[]" type="checkbox" value="{{$level['id']}}"
                                                                            {{( in_array($level['id'] , json_decode($value['levels_to_approve']) )?'checked':'')}} /> {{$level['level_name']}}
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label class="control-label bold">Viewable Levels </label>
                                                            <div class="checkbox-list">
                                                                @foreach($levels as $level)
                                                                    <label>
                                                                        <input name="views[]" type="checkbox" value="{{$level['id']}}"
                                                                            {{( in_array($level['id'] , json_decode($value['levels_to_view']) )?'checked':'')}} /> {{$level['level_name']}}
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6"></div>
                                                </div>

        	                                </div>
        	                			</div>
        	                			<div class="modal-footer">
        	                				<button type="submit" class="btn blue">Save</button>
        	                				<button type="button" class="btn default" data-dismiss="modal">Close</button>
        	                			</div>
        	                			</form>
        	                		</div><!-- /.modal-content -->
        	                	</div><!-- /.modal-dialog -->
        	                </div><!-- end of edit bank Modal-->
        	                
        	                <a data-toggle="modal" href="#modal_delete{{$value['id']}}" type="button" class="btn red btn-xs">Delete</a>
        	                <!-- Start of delete bank Modal-->
        	                <div class="modal fade" id="modal_delete{{$value['id']}}" tabindex="-1" role="dialog" 
        	                    aria-labelledby="myModalLabel" aria-hidden="true">
        	                	<div class="modal-dialog">
        	                		<div class="modal-content">
        	                			<form action="{{url('levels/processDelete')}}" method="post">
        	                			<input type="hidden" value="{{$value['id']}}" name="id">
        	                			{!! csrf_field() !!}
        	                			<div class="modal-header">
        	                				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        	                				<h4 class="modal-title">Delete User Level</h4>
        	                			</div>
        	                			<div class="modal-body">
        	                				Are you sure you want to delete this user level?
        	                			</div>
        	                			<div class="modal-footer">
        	                				<button type="submit" class="btn red">Delete</button>
        	                				<button type="button" class="btn default" data-dismiss="modal">Close</button>
        	                			</div>
        	                			</form>
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
    </div>  
</div>

<!-- Start of add level Modal-->
<div class="modal fade" id="portlet-config" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="{{url('levels/processAdd')}}" method="post">
			{!! csrf_field() !!}
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title">Add User Level</h4>
			</div>
			<div class="modal-body">
				<div class="form-body">
                	<div class="row">
                		<div class="col-sm-6">
                			<div class="form-group">
                				<label class="control-label bold">Level Name</label>
                				<input type="text" id="level_name" name="level_name" class="form-control">
                			</div>
                		</div>
                		<!--/span-->
                		<div class="col-sm-6">
                			<div class="form-group">
                				<label class="control-label bold">Level Description</label>
                				<input type="text" id="level_role" name="level_role" class="form-control">
                			</div>
                		</div>
                		<!--/span-->
                	</div>
                	<!--/row-->

                    <div class="row">

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="control-label bold">Employee (Checked) / Supervisor(Unchecked) </label>
                                <div class="checkbox-list">
                                    @foreach($levels as $level)
                                        <label>
                                            <input name="employees[]" type="checkbox" value="{{$level['id']}}"/> {{$level['level_name']}}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="control-label bold">Approval Levels </label>
                                <div class="checkbox-list">
                                    @foreach($levels as $level)
                                        <label>
                                            <input name="approves[]" type="checkbox" value="{{$level['id']}}" /> {{$level['level_name']}}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="control-label bold">Viewable Levels </label>
                                <div class="checkbox-list">
                                    @foreach($levels as $level)
                                        <label>
                                            <input name="views[]" type="checkbox" value="{{$level['id']}}"/> {{$level['level_name']}}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6"></div>
                    </div>

                </div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn blue">Save</button>
				<button type="button" class="btn default" data-dismiss="modal">Close</button>
			</div>
			</form>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- end of add level Modal-->
@endsection