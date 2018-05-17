<div class="portlet box green">
	<div class="portlet-title">
		<div class="caption">
			<i class="fa fa-gift"></i>Employee Transactions
			@if(Request::segment(1)=='transactions')
				<a data-toggle="modal" href="#add_transaction" type="button" class="btn purple btn-sm">Add Employee Transaction</a>
			@endif
		</div>
		<div class="tools">
			<a href="javascript:;" class="collapse" data-original-title="" title="">
			</a>

		</div>
	</div>
	<div class="portlet-body">
		<table id="sample_66" class="table dataTable table-striped table-bordered table-hover">
	        <thead>
			<tr>
			    @if(Request::segment(1)=='transactions')<th>Employee</th>@endif
				<th>Transaction Name</th>
                <th>Amount</th>
                <th>Start</th>
                <th>End</th>
                <th>Notes</th>
                <th>Frequency</th>
                <th>Cut-off</th>
                <th>%</th>
                @if(Request::segment(1)=='transactions')<th></th>@endif
	        </tr>			
			</thead>
			<tbody> 
			@foreach($transactions as $key=>$value)
			<tr>
				 @if(Request::segment(1)=='transactions')
				 	<td><a href="{{ url('employee/'.$value['employee_id']) }}">{{ $value['name'] }}</a></td>
				 @endif
				 <td>{{ $value['transaction_name'] }}</td>
				 <td>{{ number_format($value['amount'],2) }}</td>
				 <td>{{ dateNormal($value['start_date']) }}</td>
				 <td>{{ dateNormal($value['end_date']) }}</td>
				 <td>{{ $value['notes'] }}</td>
				 <td>{{ $value['frequency'] }}</td>
				 <td>{{ $value['transaction_cutoff'] }}</td>
				 <td><span class="badge badge-info">{{ dateProgress($value['start_date'], $value['end_date'] ) }}%</span></td>
				 @if(Request::segment(1)=='transactions')
				 <td>
                    <a data-toggle="modal" href="#modal_edit_trans{{$value['transaction_id']}}" type="button" class="btn blue btn-xs">Edit</a>
	                <!-- Start of add trans Modal-->
					<div class="modal fade" id="modal_edit_trans{{$value['transaction_id']}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
						<div class="modal-dialog">
							<div class="modal-content">
								<form action="{{url('transactions/processEdit')}}" method="post">
								{!! csrf_field() !!}
								<input type="hidden" value="{{$value['transaction_id']}}" name="id">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
									<h4 class="modal-title">Add Employee Transaction</h4>
								</div>
								<div class="modal-body">
									<div class="form-body">
					                	<div class="row">
					                		<div class="col-sm-6">
					                			<div class="form-group">
					                				<label class="control-label bold">Amount</label>
													<input type="text" value="{{$value['amount']}}" name="amount" class="form-control"/>
					                			</div>
					                		</div>
					                		<!--/span-->
					                		<div class="col-sm-6">
					                			<div class="form-group">
					                				<label class="control-label bold">Transaction Name</label>
					                				<select class="form-control" name="transaction_code_id">
													@foreach($transaction_codes as $key2=>$value2)
														@if($value2['is_regular_transaction']==1)
															@continue
														@endif
														<option {{ ($value['transaction_code_id'] == $value2['id'] ? 'selected':'')}}
															value="{{$value2['id']}}">{{$value2['transaction_name']}} ({{$value2['transaction_type']}})</option>
													@endforeach
					                				</select>
					                			</div>
					                		</div>
					                		<!--/span-->
					                	</div>
					                	<!--/row-->
					                	<div class="row">
					                		<div class="col-sm-6">
					                			<div class="form-group">
					                				<label class="control-label bold">Date Start</label>
													<div class="input-group date date-picker">
														<input class="form-control"  name="start_date" size="16" readonly required
															type="text" value="{{dateNormal($value['start_date'])}}"/>
														<span class="input-group-btn">
															<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
														</span>
													</div>	
					                			</div>
					                		</div>
					                		<!--/span-->
					                		<div class="col-sm-6">
					                			<div class="form-group">
					                				<label class="control-label bold">Date End</label>
													<div class="input-group date date-picker">
														<input class="form-control" readonly required name="end_date" size="16" 
															type="text" value="{{dateNormal($value['end_date'])}}"/>
														<span class="input-group-btn">
															<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
														</span>
													</div>	
					                			</div>
					                		</div>
					                		<!--/span-->
					                	</div>
					                	<!--/row-->
					                	<div class="row">
					                		<div class="col-sm-6">
					                			<div class="form-group">
					                				<label class="control-label bold">Frequency</label>
													<select class="form-control" name="frequency">
					                    				<option {{ ($value['frequency'] == 'recurring' ? 'selected':'')}} value="recurring">Recurring</option>
					                    				<option {{ ($value['frequency'] == 'once' ? 'selected':'')}} value="once">Once</option>
					                    			</select>
					                			</div>
					                		</div>
					                		<!--/span-->
					                		<div class="col-sm-6">
					                			<div class="form-group">
					                				<label class="control-label bold">Cut-off</label>
													<select class="form-control" name="cutoff">
					                    				<option {{ ($value['transaction_cutoff'] == 'first cutoff' ? 'selected':'')}} value="first cutoff">First Cut-off</option>
					                    				<option {{ ($value['transaction_cutoff'] == 'second cutoff' ? 'selected':'')}} value="second cutoff">Second Cut-off</option>
					                    				<option {{ ($value['transaction_cutoff'] == 'every cutoff' ? 'selected':'')}} value="every cutoff">Every Cut-off</option>
					                    			</select>
					                			</div>
					                		</div>
					                		<!--/span-->
					                	</div>
					                	<!--/row-->
					                	<div class="row">
					                		<div class="col-sm-12">
					                			<div class="form-group">
					                				<label class="control-label bold">Remarks</label>
													<textarea name="notes" class="form-control" style="height:60px">{{$value['notes']}}</textarea>
					                			</div>
					                		</div>
					                		<!--/span-->
					                	</div>
					                	<!--/row-->
					                </div>
								</div>
								<div class="modal-footer">
									<button type="submit" class="btn blue" onclick="this.setAttribute('style','display:none')">Save</button>
									<button type="button" class="btn default" data-dismiss="modal">Close</button>
								</div>
								</form>
							</div>
							<!-- /.modal-content -->
						</div>
						<!-- /.modal-dialog -->
					</div>
					<!-- end of add trans Modal-->
						                
	                <a data-toggle="modal" href="#modal_delete_trans{{$value['transaction_id']}}" type="button" class="btn red btn-xs">Delete</a>
	                <!-- Start of delete transcode Modal-->
	                <div class="modal fade" id="modal_delete_trans{{$value['transaction_id']}}" tabindex="-1" role="dialog" 
	                    aria-labelledby="myModalLabel" aria-hidden="true">
	                	<div class="modal-dialog">
	                		<div class="modal-content">
	                			<form action="{{url('transactions/processDelete')}}" method="post">
	                			<input type="hidden" value="{{$value['transaction_id']}}" name="id">
	                			{!! csrf_field() !!}
	                			<div class="modal-header">
	                				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
	                				<h4 class="modal-title">Delete Transaction</h4>
	                			</div>
	                			<div class="modal-body">
	                				Are you sure you want to delete this transaction?
	                			</div>
	                			<div class="modal-footer">
	                				<button type="submit" class="btn red">Delete</button>
	                				<button type="button" class="btn default" data-dismiss="modal">Close</button>
	                			</div>
	                			</form>
	                		</div><!-- /.modal-content -->
	                	</div><!-- /.modal-dialog -->
	                </div><!-- end of delete transcode Modal-->
				 </td>
				 @endif
			</tr>
			@endforeach
			</tbody>
		</table>
	</div>
</div>

@if(Request::segment(1)=='transactions')
<!-- Start of add trans Modal-->
<div class="modal fade" id="add_transaction" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="{{url('transactions/processAdd')}}" method="post">
			{!! csrf_field() !!}
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title">Add Employee Transaction</h4>
			</div>
			<div class="modal-body">
				<div class="form-body">
					<div class="row">
                		<div class="col-sm-12">
                			<div class="form-group">
                				<label class="control-label bold">Employee</label>
								<select id="select2_sample1" class="form-control select2" name="employee_id[]" multiple>
								@foreach($employees as $key => $value)
									<option value="{{$value['user_id']}}">{{$value['name']}}</option>
								@endforeach
								</select>
                			</div>
                		</div>
                		<!--/span-->
                	</div>
                	<!--/row-->
                	<div class="row">
                		<div class="col-sm-6">
                			<div class="form-group">
                				<label class="control-label bold">Amount</label>
								<input type="text" value="" name="amount" class="form-control"/>
                			</div>
                		</div>
                		<!--/span-->
                		<div class="col-sm-6">
                			<div class="form-group">
                				<label class="control-label bold">Transaction Name</label>
                				<select class="form-control" name="transaction_code_id">
								@foreach($transaction_codes as $key=>$value)
									@if($value['is_regular_transaction']==1)
										@continue
									@endif
									<option value="{{$value['id']}}">{{$value['transaction_name']}} ({{$value['transaction_type']}})</option>
								@endforeach
                				</select>
                			</div>
                		</div>
                		<!--/span-->
                	</div>
                	<!--/row-->
                	<div class="row">
                		<div class="col-sm-6">
                			<div class="form-group">
                				<label class="control-label bold">Date Start</label>
								
								<div class="input-group date date-picker">
									<input class="form-control" name="start_date" value="{{date('m/d/Y')}}" size="16" 
										type="text" value=""/>
									<span class="input-group-btn">
										<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
									</span>
								</div>	
                			</div>
                		</div>
                		<!--/span-->
                		<div class="col-sm-6">
                			<div class="form-group">
                				<label class="control-label bold">Date End</label>
								
								<div class="input-group date date-picker">
									<input class="form-control"  name="end_date" value="{{date('m/d/Y')}}" size="16" 
										type="text" value=""/>
									<span class="input-group-btn">
										<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
									</span>
								</div>
                			</div>
                		</div>
                		<!--/span-->
                	</div>
                	<!--/row-->
                	<div class="row">
                		<div class="col-sm-6">
                			<div class="form-group">
                				<label class="control-label bold">Frequency</label>
								<select class="form-control" name="frequency">
                    				<option value="recurring">Recurring</option>
                    				<option value="once">Once</option>
                    			</select>
                			</div>
                		</div>
                		<!--/span-->
                		<div class="col-sm-6">
                			<div class="form-group">
                				<label class="control-label bold">Cut-off</label>
								<select class="form-control" name="cutoff">
                    				<option value="first cutoff">First Cut-off</option>
                    				<option value="second cutoff">Second Cut-off</option>
                    				<option value="every cutoff">Every Cut-off</option>
                    			</select>
                			</div>
                		</div>
                		<!--/span-->
                	</div>
                	<!--/row-->
                	<div class="row">
                		<div class="col-sm-12">
                			<div class="form-group">
                				<label class="control-label bold">Remarks</label>
								<textarea name="notes" class="form-control" style="height:60px"></textarea>
                			</div>
                		</div>
                		<!--/span-->
                	</div>
                	<!--/row-->
                </div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn blue" onclick="this.setAttribute('style','display:none')">Save</button>
				<button type="button" class="btn default" data-dismiss="modal">Close</button>
			</div>
			</form>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- end of add trans Modal-->
@endif
