@extends('layouts.main')

@section('content')

<ul class="nav nav-tabs" >
    <li class="active">
		<a href="#tab_1_3" data-toggle="tab">
		Holiday List </a>
	</li>
	<li class="">
		<a href="#tab_1_4" data-toggle="tab">
		Holiday Types </a>
	</li>
</ul>
<div class="tab-content">
	<div class="tab-pane fade active in" id="tab_1_3">
		@if(session()->has('adding') && session('adding') == 'success')
		<div class="row">
		    <div class="col-md-12">
		        <div class="alert alert-success alert-dismissable">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
					<strong>Holiday successfully added.</strong>
				</div>
		    </div>
		</div>
		@elseif(!empty($errors->adding_holiday->all()))
		<div class="row">
		    <div class="col-md-12">
		        <div class="alert alert-danger alert-dismissable">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
						<strong>Failed to add Holiday. </strong>
						<br/>
						@foreach($errors->adding_holliday->all() as $key=>$value)
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
					<strong>Holiday successfully updated.</strong>
				</div>
		    </div>
		</div>
		@elseif(!empty($errors->editing_holiday->all()))
		<div class="row">
		    <div class="col-md-12">
		        <div class="alert alert-danger alert-dismissable">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
						<strong>Failed to update Holiday. </strong>
						<br/>
						@foreach($errors->editing_holiday->all() as $key=>$value)
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
					<strong>Holiday successfully deleted.</strong>
				</div>
		    </div>
		</div>
		@endif
		
		<div class="portlet box green">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-gift"></i>Holiday List
					<a data-toggle="modal" href="#portlet-config" type="button" class="btn purple btn-sm">Add Holiday</a>
				</div>
				<div class="tools">
					<a href="javascript:;" class="collapse" data-original-title="" title="">
					</a>


				</div>
			</div>
			<div class="portlet-body">
			    
		        <ul class="nav nav-tabs">
		        	<li class="active">
		        		<a href="#tab_1_1" data-toggle="tab">
		        	    Yearly </a>
		        	</li>
		        	<li>
		        		<a href="#tab_1_2" data-toggle="tab">
		        		Non-Yearly </a>
		        	</li>
		        </ul>
		        <div class="tab-content">
		        	<div class="tab-pane fade active in" id="tab_1_1">
		                <table id="sample_5" class="table dataTable table-striped table-bordered table-hover">
		        	        <thead>
		        			<tr>
		        				<th>Holiday Name</th>
		        				<th>Date</th>
		        				<th>Type</th>
		        				<th>Branches</th>
		        				<th>Is Yearly</th>
		        				<th style="width:120px"></th>
		        	        </tr>			
		        			</thead>
		        			<tbody> 
		        			    @foreach($holidays as $key => $value)
		        			        @if($value['is_yearly']==0)
		        			            @continue
		        			        @endif
		        		        <tr>
		        	            <td>{{ @$value['holiday_name'] }}</td>
		        	            <td>{{ 'Every '.date('F j',strtotime($value['holiday_date'])) }}</td>
		        	            <td>{{ @$value['holiday_type_name'] }}</td>
		        	            <td>{{ @$value['branches_name'] }}</td>
		        	            <td>{{ 'YES' }}</td>
		        	            <td>
		        	                <a data-toggle="modal" href="#delete_holiday{{$value['holiday_id']}}" type="button" class="btn red btn-xs">Delete</a>
		        	                <!-- Start of delete holiday Modal-->
		        	                <div class="modal fade" id="delete_holiday{{$value['holiday_id']}}" tabindex="-1" role="dialog" 
		        	                    aria-labelledby="myModalLabel" aria-hidden="true">
		        	                	<div class="modal-dialog">
		        	                		<div class="modal-content">
		        	                			<form action="{{url('holidays/processDelete')}}" method="post">
		        	                			<input type="hidden" value="{{$value['holiday_id']}}" name="id">
		        	                			{!! csrf_field() !!}
		        	                			<div class="modal-header">
		        	                				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
		        	                				<h4 class="modal-title">Delete Holiday</h4>
		        	                			</div>
		        	                			<div class="modal-body">
		        	                				Are you sure you want to delete this holiday?
		        	                			</div>
		        	                			<div class="modal-footer">
		        	                				<button type="submit" class="btn red">Delete</button>
		        	                				<button type="button" class="btn default" data-dismiss="modal">Close</button>
		        	                			</div>
		        	                			</form>
		        	                		</div><!-- /.modal-content -->
		        	                	</div><!-- /.modal-dialog -->
		        	                </div><!-- end of delete holiday Modal-->
		        	            </td>
		        		        </tr>
		        			    @endforeach
		        			</tbody>
		        		</table>
		        	</div>
		        	<div class="tab-pane fade" id="tab_1_2">
		                <table id="sample_6" class="table dataTable table-striped table-bordered table-hover">
		        	        <thead>
		        			<tr>
		        				<th>Holiday Name</th>
		        				<th>Date</th>
		        				<th>Type</th>
		        				<th>Companies</th>
		        				<th>Is Yearly</th>
		        				<th style="width:120px"></th>
		        	        </tr>			
		        			</thead>
		        			<tbody> 
		        			    @foreach($holidays as $key => $value)
		        			      @if($value['is_yearly']==1)
		        			            @continue
		        			      @endif
		        		        <tr>
		        	            <td>{{ @$value['holiday_name'] }}</td>
		        	            <td>{{ dateNormal($value['holiday_date']) }}</td>
		        	            <td>{{ @$value['holiday_type_name'] }}</td>
		        	            <td>{{ @$value['branches_name'] }}</td>
		        	            <td>{{ 'NO' }}</td>
		        	            <td>
		        	                <a data-toggle="modal" href="#delete_holiday2{{$value['holiday_id']}}" type="button" class="btn red btn-xs">Delete</a>
		        	                <!-- Start of delete holiday Modal-->
		        	                <div class="modal fade" id="delete_holiday2{{$value['holiday_id']}}" tabindex="-1" role="dialog" 
		        	                    aria-labelledby="myModalLabel" aria-hidden="true">
		        	                	<div class="modal-dialog">
		        	                		<div class="modal-content">
		        	                			<form action="{{url('holidays/processDelete')}}" method="post">
		        	                			<input type="hidden" value="{{$value['holiday_id']}}" name="id">
		        	                			{!! csrf_field() !!}
		        	                			<div class="modal-header">
		        	                				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
		        	                				<h4 class="modal-title">Delete holiday</h4>
		        	                			</div>
		        	                			<div class="modal-body">
		        	                				Are you sure you want to delete this holiday?
		        	                			</div>
		        	                			<div class="modal-footer">
		        	                				<button type="submit" class="btn red">Delete</button>
		        	                				<button type="button" class="btn default" data-dismiss="modal">Close</button>
		        	                			</div>
		        	                			</form>
		        	                		</div><!-- /.modal-content -->
		        	                	</div><!-- /.modal-dialog -->
		        	                </div><!-- end of delete holiday Modal-->
		        	            </td>
		        		        </tr>
		        			    @endforeach
		        			</tbody>
		        		</table>
		        	</div>
		        </div>
			</div>
		</div>
		
		<!-- Start of add holiday Modal-->
		<div class="modal fade" id="portlet-config" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<form action="{{url('holidays/processAdd')}}" method="post">
					{!! csrf_field() !!}
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
						<h4 class="modal-title">Add Holiday</h4>
					</div>
					<div class="modal-body">
						<div class="form-body">
		                	<div class="row">
		                		<div class="col-sm-5">
		                			<div class="form-group">
		                				<label class="control-label bold">Holiday Name</label>
		                				<input type="text" id="holiday_name" required name="holiday_name" class="form-control">
		                			</div>
		                		</div>
		                		<!--/span-->
		                		<div class="col-sm-7">
		                			<div class="form-group">
		                				<label class="control-label bold">Branches Covered</label>
		                    			<select id="select2_sample1" required class="form-control select2" name="branch[]" multiple>
											<option value="0">All</option>
		                    			    @foreach($branches as $key => $value)
                                            <option value="{{$value->id}}">{{$value->branch_name}}</option>
		                    			    @endforeach
		                    			</select>
		                			</div>
		                		</div>
		                		<!--/span-->
		                	</div>
		                	<!--/row-->
		                	
		                	<div class="row">
		                		<div class="col-sm-5">
		                			<div class="form-group">
		                				<label class="control-label bold">Holiday Type</label>
		                    			<select class="form-control select2" name="holiday_type_id">
		                    			@foreach($holiday_types as $key => $value)
		                    					<option value="{{$value->id}}">{{$value->holiday_type_name}}</option>
		                    			@endforeach
		                    			</select>
		                			</div>
		                		</div>
		                		<!--/span-->
		                		<div class="col-sm-4">
		                			<div class="form-group">
		                				<label class="control-label bold">Date</label>
		                                <div class="input-group date date-picker">
											<input class="form-control" name="holiday_date" required readonly
												value="{{date('m/d/Y')}}" size="16" type="text" value=""/>
											<span class="input-group-btn">
												<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
											</span>
										</div>	
		                			</div>
		                		</div>
		                		<!--/span-->
		                		<div class="col-sm-3">
		                			<div class="form-group">
		                				<label class="control-label bold">Is Yearly</label>
		                                <select class="form-control" name="is_yearly">  
		                                    <option value="1">YES</option>
		                                    <option value="0">NO</option>
		                                </select>
		                			</div>
		                		</div>
		                		<!--/span-->
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
		<!-- end of add holiday Modal-->
		
	</div>
	<div class="tab-pane fade" id="tab_1_4">
 		@if(session()->has('adding_type') && session('adding_type') == 'success')
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissable">
        			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
        			<strong>Holiday type successfully added.</strong>
        		</div>
            </div>
        </div>
        @elseif(!empty($errors->adding_type->all()))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissable">
        			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
        				<strong>Failed to add Holiday type. </strong>
        				<br/>
        				@foreach($errors->adding_type->all() as $key=>$value)
        					{{ $value }} <br/>
        				@endforeach
        		</div>
            </div>
        </div>
        @endif
        
        @if(session()->has('editing_type') && session('editing_type') == 'success')
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissable">
        			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
        			<strong>Holiday type successfully updated.</strong>
        		</div>
            </div>
        </div>
        @elseif(!empty($errors->editing_type->all()))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissable">
        			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
        				<strong>Failed to update holiday type. </strong>
        				<br/>
        				@foreach($errors->editing_type->all() as $key=>$value)
        					{{ $value }} <br/>
        				@endforeach
        		</div>
            </div>
        </div>
        @endif
        
        @if(session()->has('deleting_type') && session('deleting_type') == 'success')
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissable">
        			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
        			<strong>Holiday type successfully deleted.</strong>
        		</div>
            </div>
        </div>
        @endif
	    
        <div class="portlet box yellow">
        	<div class="portlet-title">
        		<div class="caption">
        			<i class="fa fa-gift"></i>Holiday Types
        			<a data-toggle="modal" href="#add_type" type="button" class="btn purple btn-sm">Add Holiday Type</a>
        		</div>
        		<div class="tools">
        			<a href="javascript:;" class="collapse" data-original-title="" title="">
        			</a>

        		</div>
        	</div>
        	<div class="portlet-body">
        		<table id="sample_59" class="table dataTable table-striped table-bordered table-hover">
        	        <thead>
        			<tr>
        			    <th>Holiday Type</th>
        			    <th>Present on <br/>Workday</th>
        			    <th>Absent on<br/> Workday</th>
        			    <th>Present on <br/>Restday</th>
        			    <th>Absent on <br/>Restday</th>
        			    <th>Rate After 8 <br/> Hours on Restday</th>
        			    <th>Rate After 8 <br/> Hours on Workday</th>
        			    <th></th>
        	        </tr>			
        			</thead>
        			<tbody> 
                    @foreach($holiday_types as $key =>$value)
        			<tr>
        				<td>{{$value->holiday_type_name}}</td>
        				<td>{{ json_decode($value->holiday_type_data)->present_workday }}</td>
        				<td>{{ json_decode($value->holiday_type_data)->absent_workday }}</td>
        				<td>{{ json_decode($value->holiday_type_data)->present_restday }}</td>
        				<td>{{ json_decode($value->holiday_type_data)->absent_restday }}</td>
        				<td>{{ json_decode($value->holiday_type_data)->beyond_workday }}</td>
        				<td>{{ json_decode($value->holiday_type_data)->beyond_restday }}</td>
						<td>
							<a data-toggle="modal" href="#edit{{$value->id}}" type="button" class="btn blue btn-xs">Edit</a>
							 <!-- Start of edit Modal-->
							<div class="modal fade" id="edit{{$value->id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
								<div class="modal-dialog">
									<div class="modal-content">
										<form action="{{url('holidays/type/processEdit')}}" method="post">
										{!! csrf_field() !!}
											<input type="hidden" value="{{$value->id}}" name="id">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
											<h4 class="modal-title">Edit Holiday Type</h4>
										</div>
										<div class="modal-body">
											<div class="form-body">
							                	<div class="row">
							                		<div class="col-sm-6">
							                			<div class="form-group">
							                				<label class="control-label bold">Holiday Type Name</label>
							                				<input type="text" id="holiday_type_name" required value="{{$value->holiday_type_name}}"
							                					name="holiday_type_name" class="form-control">
							                			</div>
							                		</div>
							                		<!--/span-->
							                		<div class="col-sm-3">
							                			<div class="form-group">
							                				<label class="control-label bold">Present on Workday</label>
															<input type="text" value="{{ json_decode($value->holiday_type_data)->present_workday }}" 
																required name="present_workday" class="form-control">
							                			</div>
							                		</div>
							                		<!--/span-->
							                		<div class="col-sm-3">
							                			<div class="form-group">
							                				<label class="control-label bold">Absent on Workday</label>
															<input type="text" value="{{ json_decode($value->holiday_type_data)->absent_workday }}" 
																required name="absent_workday" class="form-control">
							                			</div>
							                		</div>
							                		<!--/span-->
							                	</div>
							                	<!--/row-->
							                	
							                	<div class="row">
							                		<div class="col-sm-3">
							                			<div class="form-group">
							                				<label class="control-label bold">Present on Restday</label>
							                				<input type="text" value="{{ json_decode($value->holiday_type_data)->present_restday }}" 
							                					required name="present_restday" class="form-control">
							                			</div>
							                		</div>
							                		<!--/span-->
							                		<div class="col-sm-3">
							                			<div class="form-group">
							                				<label class="control-label bold">Absent on Restday</label>
							                                <input type="text" value="{{ json_decode($value->holiday_type_data)->absent_restday }}" 
							                                	required name="absent_restday" class="form-control">
							                			</div>
							                		</div>
							                		<!--/span-->
							                		<div class="col-sm-3">
							                			<div class="form-group">
							                				<label class="control-label bold">Beyond 8 hours on Workday</label>
							                                <input type="text" value="{{ json_decode($value->holiday_type_data)->beyond_workday }}" 
							                                	required name="beyond_workday" class="form-control">
							                			</div>
							                		</div>
							                		<!--/span-->
							                		<div class="col-sm-3">
							                			<div class="form-group">
							                				<label class="control-label bold">Beyond 8 hours on Restday</label>
							                                <input type="text" value="{{ json_decode($value->holiday_type_data)->beyond_restday }}" 
							                                	required name="beyond_restday" class="form-control">
							                			</div>
							                		</div>
							                		<!--/span-->
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
							<!-- end of edit Modal-->
		
							<a data-toggle="modal" href="#delete_holiday3{{$value->id}}" type="button" class="btn red btn-xs">Delete</a>
        	                <!-- Start of delete holiday  type Modal-->
        	                <div class="modal fade" id="delete_holiday3{{$value->id}}" tabindex="-1" role="dialog" 
        	                    aria-labelledby="myModalLabel" aria-hidden="true">
        	                	<div class="modal-dialog">
        	                		<div class="modal-content">
        	                			<form action="{{url('holidays/type/processDelete')}}" method="post">
        	                			<input type="hidden" value="{{$value->id}}" name="id">
        	                			{!! csrf_field() !!}
        	                			<div class="modal-header">
        	                				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        	                				<h4 class="modal-title">Delete holiday type</h4>
        	                			</div>
        	                			<div class="modal-body">
        	                				Are you sure you want to delete this holiday type?
        	                			</div>
        	                			<div class="modal-footer">
        	                				<button type="submit" class="btn red">Delete</button>
        	                				<button type="button" class="btn default" data-dismiss="modal">Close</button>
        	                			</div>
        	                			</form>
        	                		</div><!-- /.modal-content -->
        	                	</div><!-- /.modal-dialog -->
        	                </div><!-- end of delete holiday type Modal-->
						</td>
        	        </tr>
                    @endforeach
        			</tbody>
        		</table>
        	</div>
        </div>
        
        
        <!-- Start of add holiday Modal-->
		<div class="modal fade" id="add_type" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<form action="{{url('holidays/type/processAdd')}}" method="post">
					{!! csrf_field() !!}
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
						<h4 class="modal-title">Add Holiday Type</h4>
					</div>
					<div class="modal-body">
						<div class="form-body">
		                	<div class="row">
		                		<div class="col-sm-6">
		                			<div class="form-group">
		                				<label class="control-label bold">Holiday Type Name</label>
		                				<input type="text" id="holiday_type_name" required name="holiday_type_name" class="form-control">
		                			</div>
		                		</div>
		                		<!--/span-->
		                		<div class="col-sm-3">
		                			<div class="form-group">
		                				<label class="control-label bold">Present on Workday</label>
										<input type="text" value="0" required name="present_workday" class="form-control">
		                			</div>
		                		</div>
		                		<!--/span-->
		                		<div class="col-sm-3">
		                			<div class="form-group">
		                				<label class="control-label bold">Absent on Workday</label>
										<input type="text" value="0" required name="absent_workday" class="form-control">
		                			</div>
		                		</div>
		                		<!--/span-->
		                	</div>
		                	<!--/row-->
		                	
		                	<div class="row">
		                		<div class="col-sm-3">
		                			<div class="form-group">
		                				<label class="control-label bold">Present on Restday</label>
		                				<input type="text" value="0" required name="present_restday" class="form-control">
		                			</div>
		                		</div>
		                		<!--/span-->
		                		<div class="col-sm-3">
		                			<div class="form-group">
		                				<label class="control-label bold">Absent on Restday</label>
		                                <input type="text" value="0" required name="absent_restday" class="form-control">
		                			</div>
		                		</div>
		                		<!--/span-->
		                		<div class="col-sm-3">
		                			<div class="form-group">
		                				<label class="control-label bold">Beyond 8 hours on Workday</label>
		                                <input type="text" value="0" required name="beyond_workday" class="form-control">
		                			</div>
		                		</div>
		                		<!--/span-->
		                		<div class="col-sm-3">
		                			<div class="form-group">
		                				<label class="control-label bold">Beyond 8 hours on Restday</label>
		                                <input type="text" value="0" required name="beyond_restday" class="form-control">
		                			</div>
		                		</div>
		                		<!--/span-->
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
		<!-- end of add holiday Modal-->
		
	</div>
</div>

@endsection