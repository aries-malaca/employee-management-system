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

@if(Request::segment(3) !== null)
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-danger alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
			<strong>{{ urldecode(Request::segment(3)) }}</strong>
		</div>
    </div>
</div>
@endif

<div class="portlet box green">
	<div class="portlet-title">
		<div class="caption">
			<i class="fa fa-gift"></i>Setup Email Client
		</div>
		<div class="tools">
			<a href="javascript:;" class="collapse" data-original-title="" title="">
			</a>

		</div>
	</div>
	<div class="portlet-body">
		<form method="post" action="{{url('mail/processEdit')}}">
			{!! csrf_field() !!}
		    <div class="row">
		       <div class="col-sm-3">
	           		<div class="form-group">
				        <label class="control-label bold">IMAP Host</label>
				        <input required type="text" name="imap_host" value="{{ ($email_client!==null)? $email_client->imap_host : $email_client_defaults->imap_host}}" class="form-control"/>
	                </div>
		       </div>
		       <div class="col-sm-3">
	           		<div class="form-group">
				        <label class="control-label bold">SMTP Host</label>
				        <input required type="text" name="smtp_host" value="{{ ($email_client!==null)? $email_client->smtp_host : $email_client_defaults->smtp_host}}" class="form-control"/>
	                </div>
		       </div>
		       <div class="col-sm-2">
	           		<div class="form-group">
				        <label class="control-label bold">IMAP Port</label>
				        <input required type="text" name="imap_port" value="{{ ($email_client!==null)? $email_client->imap_port : $email_client_defaults->imap_port}}" class="form-control"/>
	                </div>
		       </div>
		       <div class="col-sm-2">
	           		<div class="form-group">
				        <label class="control-label bold">SMTP Port</label>
				        <input required type="text" name="smtp_port" value="{{ ($email_client!==null)? $email_client->smtp_port : $email_client_defaults->smtp_port}}" class="form-control"/>
	                </div>
		       </div>
		       <div class="col-sm-2">
	           		<div class="form-group">
				        <label class="control-label bold">Encryption</label>
				        <input required type="text" name="encryption" value="{{ ($email_client!==null)? $email_client->encryption : $email_client_defaults->encryption}}" class="form-control"/>
	                </div>
		       </div>
		    </div>
		    <div class="row">
				<div class="col-sm-3">
	           		<div class="form-group">
				        <label class="control-label bold">Email</label>
				        <input required type="text" name="email" value="{{($email_client!==null)? $email_client->email : ''}}" class="form-control"/>
	                </div>
		       </div>

		       	<div class="col-sm-3">
	           		<div class="form-group">
				        <label class="control-label bold">Password</label>
				        <input required type="password" name="password" value="{{($email_client!==null)? $email_client->password : ''}}" class="form-control"/>
	                </div>
		       </div>
		    </div>
		    <div class="clearfix"></div>

		    <button type="submit" class="btn blue">Update</button>
		     <a href="{{url('mail')}}" class="btn yellow">Go To Email Client</a>
		 </form>
	</div>
</div>
@endsection