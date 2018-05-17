@extends('layouts.main')

@section('content')

@if(session()->has('update') && session('update') == 'success')
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-success alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
			<strong>Settings successfully updated.</strong>
		</div>
    </div>
</div>
@endif

<div class="portlet box green">
	<div class="portlet-title">
		<div class="caption">
			<i class="fa fa-gift"></i>System Settings
		</div>
		<div class="tools">
			<a href="javascript:;" class="collapse" data-original-title="" title="">
			</a>

		</div>
	</div>
	<div class="portlet-body">
		<form method="post" action="{{url('settings/processEdit')}}">
			{!! csrf_field() !!}
			<h4>System Basic Info</h4>
		    <div class="row">
		       <div class="col-sm-2">
	           		<div class="form-group">
				        <label class="control-label bold">App. Name</label>
				        <input required type="text" name="config[1]" readonly value="{{$config['app_name']}}" class="form-control"/>
	                </div>
		       </div>
		       <div class="col-sm-2">
	           		<div class="form-group">
				        <label class="control-label bold">App. Description</label>
				        <input required type="text" name="config[2]" readonly value="{{$config['app_description']}}" class="form-control"/>
	                </div>
		       </div>
		       <div class="col-sm-2">
	           		<div class="form-group">
				        <label class="control-label bold">&copy; Year</label>
				        <input required type="text" name="config[3]" readonly value="{{$config['copyright']}}" class="form-control"/>
	                </div>
		       </div>
		       <div class="col-sm-2">
	           		<div class="form-group">
				        <label class="control-label bold">Logo</label>
				        <input required type="text" name="config[4]" readonly value="{{$config['logo']}}" class="form-control"/>
	                </div>
		       </div>
		       <div class="col-sm-2">
	           		<div class="form-group">
				        <label class="control-label bold">Restriction Type</label>
	                    <select name="config[5]" class="form-control">
	                        <option {{ ($config['app_restriction']=='level'?'selected':'')}} value="level">Level</option>
	                        <option {{ ($config['app_restriction']=='position'?'selected':'')}} value="position">Position</option>
	                    </select>
	                </div>
		       </div>

		       	<div class="col-sm-2">
		            <div class="form-group">
	               		<label class="control-label bold">Application URL</label>
						<input required type="text" name="config[23]" value="{{$config['application_url']}}" class="form-control"/>
	    		    </div>
		       </div>

		    </div>
		    <h4>System Customization</h4>
		    <div class="row">
				<div class="col-sm-2">
		            <div class="form-group">
	               		<label class="control-label bold">Admin Level</label>
	    		        <select name="config[8]" class="form-control">
	    	            @foreach($levels as $level)
	    	                <option {{($level['id']==$config['admin_level_id']?'selected':'')}} 
	    	                    value="{{$level['id']}}">{{$level['level_name']}}</option>
	    	            @endforeach
	    		        </select>
	    		     </div>
		       </div>
		       <div class="col-sm-2">
		            <div class="form-group">
	               		<label class="control-label bold">Sub-Admin Level</label>
	    		        <select name="config[9]" class="form-control">
	    	            @foreach($levels as $level)
	    	                <option {{($level['id']==$config['sub_admin_level_id']?'selected':'')}} 
	    	                    value="{{$level['id']}}">{{$level['level_name']}}</option>
	    	            @endforeach
	    		        </select>
	    		    </div>
		        </div>

		       	<div class="col-sm-2">
		            <div class="form-group">
	               		<label class="control-label bold">HR Level</label>
	    		        <select name="config[6]" class="form-control">
	    	            @foreach($levels as $level)
	    	                <option {{($level['id']==$config['hr_level_id']?'selected':'')}} 
	    	                    value="{{$level['id']}}">{{$level['level_name']}}</option>
	    	            @endforeach
	    		        </select>
	    		     </div>
		       </div>
		       <div class="col-sm-2">
		            <div class="form-group">
	               		<label class="control-label bold">First Cut-off</label>
						<div class="row">
							<div class="col-md-6">
							<input type="number" placeholder="1" name="config[10][0]" min="1" max="31"
								value="{{ $config['first_cutoff'][0] }}" class="form-control"/>
							</div>
							<div class="col-md-6">
							<input type="number" placeholder="15" name="config[10][1]" min="1" max="31" 
								value="{{ $config['first_cutoff'][1] }}" class="form-control"/>
							</div>
						</div>
	    		     </div>
		        </div>
		        <div class="col-sm-2">
		            <div class="form-group">
	               		<label class="control-label bold">Second Cut-off</label>
						<div class="row">
							<div class="col-md-6">
							<input type="number"  placeholder="16" name="config[11][0]" min="1" max="31" 
								value="{{ $config['second_cutoff'][0] }}" class="form-control"/>
							</div>
							<div class="col-md-6">
							<input type="number" placeholder="31" name="config[11][1]" min="1" max="31" 
								value="{{ $config['second_cutoff'][1] }}" class="form-control"/>
							</div>
						</div>
	    		     </div>
		        </div>
		        <div class="col-sm-2">
					<div class="form-group">
        				<label class="control-label bold">No Timeout Policy</label>
        				<select class="form-control" name="config[21]">
        					<option value="absent" {{($config['no_timeout_policy']=='absent'?'selected':'')}}>Absent</option>
        					<option value="halfday" {{($config['no_timeout_policy']=='halfday'?'selected':'')}}>Halfday</option>
        					<option value="no action" {{($config['no_timeout_policy']=='no action'?'selected':'')}}>No Action</option>
        				</select>
        			</div>
		        </div>
		    </div>
		    
			<div class="row">
		       <div class="col-sm-2">
	           		<div class="form-group">
				        <label class="control-label bold">Time In/Out Button</label>
	                    <select name="config[7]" class="form-control">
	                        <option {{ ($config['show_timein_button']==1?'selected':'') }} value="1">Yes</option>
	                        <option {{ ($config['show_timein_button']==0?'selected':'') }} value="0">No</option>
	                    </select>
	                </div>
		        </div>
		       <div class="col-sm-2">
		            <div class="form-group">
	               		<label class="control-label bold">Enable Chat</label>
        				<select class="form-control" name="config[24]">
        					<option value="1" {{($config['enable_chat']=='1'?'selected':'')}}>Yes</option>
        					<option value="0" {{($config['enable_chat']=='0'?'selected':'')}}>No</option>
        				</select>
	    		     </div>
		       </div>

		       <div class="col-sm-4">
		            <div class="form-group">
	               		<label class="control-label bold">Salary Visibility</label>
						<select id="select2_sample2" class="form-control select2" name="config[22][]" multiple>
		    	            @foreach($levels as $level)
		    	            <option {{( in_array($level['id'] , $config['salary_visible_to'])?'selected':'')}} 
		    	                    value="{{$level['id']}}">{{$level['level_name']}}</option>
		    	            @endforeach
						</select>
	    		    </div>
		       </div>
		       <div class="col-sm-2">
		            <div class="form-group">
	               		<label class="control-label bold">Send Notifications</label>
        				<select class="form-control" name="config[26]">
        					<option value="1" {{($config['send_notifications']=='1'?'selected':'')}}>Yes</option>
        					<option value="0" {{($config['send_notifications']=='0'?'selected':'')}}>No</option>
        				</select>
	    		    </div>
		       </div>
		    	<div class="col-sm-2">
		            <div class="form-group">
	               		<label class="control-label bold">Deduct. Conversion</label>
        				<select class="form-control" name="config[25]">
        					<option value="1" {{($config['deduction_conversion']=='1'?'selected':'')}}>Yes</option>
        					<option value="0" {{($config['deduction_conversion']=='0'?'selected':'')}}>No</option>
        				</select>
	    		    </div>
		       </div>
		    </div>
			<div class="row">
				<div class="col-sm-3">
					<div class="form-group">
						<label class="control-label bold">Minimum Daily Wage</label>
						<input type="number" name="config[28]" value="{{ $config['minimum_wage'] }}" class="form-control"/>
					</div>
				</div>
				<div class="col-sm-3">
					<div class="form-group">
						<label class="control-label bold">Minimum Monthly Wage</label>
						<input type="number" name="config[29]" value="{{ $config['minimum_monthly_wage'] }}" class="form-control"/>
					</div>
				</div>
			</div>
		    <h4>Default Mailing Configuration</h4>
		    <div class="row">
		    	<div class="col-sm-2">
					<div class="form-group">
	               		<label class="control-label bold">Imap Host</label>
        				<input required type="text" name="config[27][imap_host]" value="{{$config['email_client_defaults']['imap_host']}}" class="form-control"/>
	    		    </div>
		    	</div>
		    	<div class="col-sm-2">
					<div class="form-group">
	               		<label class="control-label bold">Imap Port</label>
        				<input required type="text" name="config[27][imap_port]" value="{{$config['email_client_defaults']['imap_port']}}" class="form-control"/>
	    		    </div>
		    	</div>
		    	<div class="col-sm-2">
					<div class="form-group">
	               		<label class="control-label bold">SMTP Host</label>
        				<input required type="text" name="config[27][smtp_host]" value="{{$config['email_client_defaults']['smtp_host']}}" class="form-control"/>
	    		    </div>
		    	</div>
		    	<div class="col-sm-2">
					<div class="form-group">
	               		<label class="control-label bold">SMTP Port</label>
        				<input required type="text" name="config[27][smtp_port]" value="{{$config['email_client_defaults']['smtp_port']}}" class="form-control"/>
	    		    </div>
		    	</div>
		    	<div class="col-sm-2">
					<div class="form-group">
	               		<label class="control-label bold">Encryption</label>
        				<input required type="text" readonly name="config[27][encryption]" value="{{$config['email_client_defaults']['encryption']}}" class="form-control"/>
	    		    </div>
		    	</div>
		    </div>

		    <br/>

		    <div class="clearfix"></div>
		    <button type="submit" class="btn blue">Update</button>
		 </form>
	</div>
</div>
@endsection