@extends('layouts.main')

@section('content')

@if(session()->has('adding') && session('adding') == 'success')
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-success alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
			<strong>Employment Status successfully added.</strong>
		</div>
    </div>
</div>
@elseif(!empty($errors->adding_employment->all()))
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-danger alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
				<strong>Failed to add Employment Status. </strong>
				<br/>
				@foreach($errors->adding_employment->all() as $key=>$value)
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
			<strong>Employment Status successfully updated.</strong>
		</div>
    </div>
</div>
@elseif(!empty($errors->editing_employment->all()))
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-danger alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
				<strong>Failed to update Employment Status. </strong>
				<br/>
				@foreach($errors->editing_employment->all() as $key=>$value)
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
			<strong>Employment Status successfully deleted.</strong>
		</div>
    </div>
</div>
@endif

<div class="portlet box green">
	<div class="portlet-title">
		<div class="caption">
			<i class="fa fa-gift"></i>Employment Status List
			<a data-toggle="modal" href="#portlet-config" type="button" class="btn purple btn-sm">Add Employment Status</a>
		</div>
		<div class="tools">
			<a href="javascript:;" class="collapse" data-original-title="" title="">
			</a>

		</div>
	</div>
	<div class="portlet-body">
		<table id="sample_5" class="table dataTable table-striped table-bordered table-hover">
	        <thead>
			<tr>
				<th>Status Name</th>
				<th>Salary Frequency</th>
				<th>Cola Frequency</th>
				<th>Paid Holidays</th>
				<th>Paid Leaves</th>
				<th>Evaluation Months</th>
				<th style="width:120px"></th>
	        </tr>			
			</thead>
			<tbody> 
			    @foreach($employments as $key => $value)
		        <tr>
	            <td>{{ @$value['employment_status_name'] }}</td>
	            <td>{{ @$value['salary_frequency'] }}</td>
	            <td>{{ @$value['cola_frequency'] }}</td>
	            <td>
	            	@foreach($value['paid_holiday_type_names'] as $name)
	            		{{$name['holiday_type_name']}} <br/>
	            	@endforeach
	            </td>
	            <td>
	            	@foreach($value['paid_leave_type_names'] as $name)
	            		{{$name['leave_type_name']}} <br/>
	            	@endforeach
	            </td>
	            <td>{{ @$value['evaluation_months'] }}</td>
	            <td>
	                <a data-toggle="modal" href="#modal_edit{{$value['id']}}" type="button" class="btn blue btn-xs">Edit</a>
	                <!-- Start of edit status Modal-->
	                <div class="modal fade" id="modal_edit{{$value['id']}}" tabindex="-1" role="dialog" 
	                    aria-labelledby="myModalLabel" aria-hidden="true">
	                	<div class="modal-dialog">
	                		<div class="modal-content">
	                			<form action="{{url('employment/processEdit')}}" method="post">
	                			<input type="hidden" value="{{$value['id']}}" name="id">
	                			{!! csrf_field() !!}
	                			<div class="modal-header">
	                				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
	                				<h4 class="modal-title">Edit Employment Status</h4>
	                			</div>
	                			<div class="modal-body">
	                				<div class="form-body">
	                                	<div class="row">
	                                		<div class="col-sm-12">
	                                			<div class="form-group">
	                                				<label class="control-label bold">Status Name</label>
	                                				<input type="text" name="employment_status_name" 
	                                				    value="{{$value['employment_status_name']}}" class="form-control">
	                                			</div>
	                                		</div><!--/span-->
	                                	</div><!--/row-->
                                    	<div class="row">
                                    		<div class="col-sm-6">
                                    			<div class="form-group">
                                    				<label class="control-label bold">Cola Frequency</label>
                                    				<select class="form-control" name="cola_frequency">
                                    				    <option @if($value['cola_frequency'] =='monthly') selected @endif value="monthly">Monthly</option>
                                    				    <option @if($value['cola_frequency'] =='daily') selected @endif value="daily">Daily</option>
                                    				    <option @if($value['cola_frequency'] =='fixed') selected @endif value="fixed">Fixed</option>
                                    				</select>
                                    			</div>
                                    		</div>
                                    		<!--/span-->
                                    		<div class="col-sm-6">
                                    			<div class="form-group">
                                    				<label class="control-label bold">Salary Frequency</label>
                                    				<select class="form-control" name="salary_frequency">
                                    				    <option @if($value['salary_frequency'] =='monthly') selected @endif value="monthly">Monthly</option>
                                    				    <option @if($value['salary_frequency'] =='daily') selected @endif value="daily">Daily</option>
                                    				    <option @if($value['salary_frequency'] =='fixed') selected @endif value="fixed">Fixed</option>
                                    				</select>	
                                    			</div>
                                    		</div>
                                    		<!--/span-->
                                    	</div>
                                    	<!--/row-->
                                    	<div class="row">
                                    		<div class="col-sm-6">
                                    			<div class="form-group">
                                    				<label class="control-label bold">Paid Leaves</label>
                                    				<div class="checkbox-list">
                                            			@foreach($leave_types as $key_leave => $value_leave)
                    									<label>
                    									    <input @if( in_array($value_leave['id'], explode(',', $value['paid_leave_types'] ) ) ) 
                    									                checked 
                    									           @endif
                    									    type="checkbox" name="leaves[]" value="{{$value_leave['id']}}"> {{$value_leave['leave_type_name']}}
                    									</label>
                                            			@endforeach
                                        			</div>
                                    			</div>
                                    		</div>
                                    		<!--/span-->
                                    		<div class="col-sm-6">
                                    			<div class="form-group">
                                    				<label class="control-label bold">Paid Holidays</label>
                                    				<div class="checkbox-list">
                                        			@foreach($holiday_types as $key_holiday => $value_holiday)
                    								<label>
                    								    <input @if( in_array($value_holiday->id, explode(',', $value['paid_holidays_types'] ) ) ) 
            									                checked 
            									               @endif
                    								    type="checkbox" name="holidays[]" value="{{$value_holiday->id}}"> {{$value_holiday->holiday_type_name}}
                    								</label>
                                        			@endforeach
                                        			</div>
                                    			</div>
                                    		</div>
                                    		<!--/span-->
                                    	</div>
                                    	<!--/row-->
					                	<div class="row">
					                		<div class="col-sm-6">
					                			<label class="control-label bold">Evaluation Months</label>
					                			<input type="number" name="evaluation_months" value="{{$value['evaluation_months']}}" 
					                				class="form-control">
					                		</div>
					                		<div class="col-sm-6">
					
					                		</div>
					                	</div>
					                	<!--/row-->
	                                </div>
	                			</div>
	                			<div class="modal-footer">
	                				<button type="submit" class="btn blue">Save</button>
	                				<button type="button" class="btn default" data-dismiss="modal">Close</button>
	                			</div>
	                			</form>
	                		</div><!-- /.modal-content -->
	                	</div><!-- /.modal-dialog -->
	                </div><!-- end of edit status Modal-->
	                
	                <a data-toggle="modal" href="#modal_delete{{$value['id']}}" type="button" class="btn red btn-xs">Delete</a>
	                <!-- Start of delete employment Modal-->
	                <div class="modal fade" id="modal_delete{{$value['id']}}" tabindex="-1" role="dialog" 
	                    aria-labelledby="myModalLabel" aria-hidden="true">
	                	<div class="modal-dialog">
	                		<div class="modal-content">
	                			<form action="{{url('employment/processDelete')}}" method="post">
	                			<input type="hidden" value="{{$value['id']}}" name="id">
	                			{!! csrf_field() !!}
	                			<div class="modal-header">
	                				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
	                				<h4 class="modal-title">Delete Employment Status</h4>
	                			</div>
	                			<div class="modal-body">
	                				Are you sure you want to delete this Employment Status?
	                			</div>
	                			<div class="modal-footer">
	                				<button type="submit" class="btn red">Delete</button>
	                				<button type="button" class="btn default" data-dismiss="modal">Close</button>
	                			</div>
	                			</form>
	                		</div><!-- /.modal-content -->
	                	</div><!-- /.modal-dialog -->
	                </div><!-- end of delete employment Modal-->
	            </td>
		        </tr>
			    @endforeach
			</tbody>
		</table>
	</div>
</div>

<!-- Start of add employment Modal-->
<div class="modal fade" id="portlet-config" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="{{url('employment/processAdd')}}" method="post">
			{!! csrf_field() !!}
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title">Add Employment Status</h4>
			</div>
			<div class="modal-body">
				<div class="form-body">
                	<div class="row">
                		<div class="col-sm-12">
                			<div class="form-group">
                				<label class="control-label bold">Status Name</label>
                				<input type="text" id="employment_status_name" name="employment_status_name" class="form-control">
                			</div>
                		</div>
                		<!--/span-->
                	</div>
                	<!--/row-->
                	<div class="row">
                		<div class="col-sm-6">
                			<div class="form-group">
                				<label class="control-label bold">Cola Frequency</label>
                				<select class="form-control" name="cola_frequency">
                				    <option value="montly">Monthly</option>
                				    <option value="daily">Daily</option>
                				</select>
                			</div>
                		</div>
                		<!--/span-->
                		<div class="col-sm-6">
                			<div class="form-group">
                				<label class="control-label bold">Salary Frequency</label>
                				<select class="form-control" name="salary_frequency">
                				    <option value="montly">Monthly</option>
                				    <option value="daily">Daily</option>
                				</select>	
                			</div>
                		</div>
                		<!--/span-->
                	</div>
                	<!--/row-->
                	<div class="row">
                		<div class="col-sm-6">
                			<div class="form-group">
                				<label class="control-label bold">Paid Leaves</label>
                				<div class="checkbox-list">
                    			@foreach($leave_types as $key => $value)
									<label>
									    <input type="checkbox" name="leaves[]" value="{{$value['id']}}"> {{$value['leave_type_name']}}
									</label>
                    			@endforeach
                    			</div>
                			</div>
                		</div>
                		<!--/span-->
                		<div class="col-sm-6">
                			<div class="form-group">
                				<label class="control-label bold">Paid Holidays</label>
                				<div class="checkbox-list">
                    			@foreach($holiday_types as $key => $value)
								<label>
								    <input type="checkbox" name="holidays[]" value="{{$value->id}}"> {{$value->holiday_type_name}}
								</label>
                    			@endforeach
                    			</div>
                			</div>
                		</div>
                		<!--/span-->
                	</div>
                	<!--/row-->
                	<div class="row">
                		<div class="col-sm-6">
                			<label class="control-label bold">Evaluation Months</label>
                			<input type="number" name="evaluation_months" value="" class="form-control">
                		</div>
                		<div class="col-sm-6">

                		</div>
                	</div>
                	<!--/row-->
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
<!-- end of add employment Modal-->
@endsection